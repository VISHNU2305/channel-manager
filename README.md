# Mini Channel Manager

A small channel manager for hotels, built with Laravel and MySQL.
A hotel keeps one master inventory. Every booking that arrives from any booking site
updates it safely, so no room is sold twice, and the new availability is then pushed
to the other booking sites in the background.

**Live demo:** http://16.178.62.251/dashboard
(use the **Reset demo** button to restore the data after trying bookings)

## The problem

A hotel sells the same room on many booking sites at once. If the last room is booked
on two sites within seconds, the hotel overbooks. A channel manager solves this by
keeping one source of truth and keeping every site in sync with it.

## How overbooking is prevented

Each booking runs inside a database transaction. It locks the inventory rows for the
requested nights (`SELECT ... FOR UPDATE`), checks that every night has enough rooms,
then lowers the counts. A second request for the same room waits for the lock, sees the
new count, and is rejected.

**Tested:** 50 simultaneous booking requests against 5 rooms gave exactly 5 successful
bookings (HTTP 201) and 45 rejections (HTTP 409). The inventory ended at zero.

```
php scripts/concurrency_test.php
```

Bookings are also idempotent. A unique constraint on `(source, external_reference)`
means an OTA that resends the same booking never takes a second room.

## Channel sync engine

After a booking is saved, availability for the affected nights is pushed to the other
booking sites by a background queue.

- **Adapters:** one class per site behind a shared `OtaAdapter` interface.
  OTA-A takes JSON, OTA-B takes XML, and OTA-C is rate limited (5 requests a minute)
  and fails about 1 time in 5 on purpose.
- **Queue:** `SyncAvailabilityJob` runs on Laravel's database queue, with up to 6
  attempts and increasing delays (5s, 15s, 30s, 60s, 90s).
- **Sync log:** every push is recorded in `sync_logs` with its status, attempts and error.
  The dashboard shows the health of each site and the latest syncs.
- **No echo:** a site is never told about a booking it sent itself.
- **After commit:** the sync is queued only after the booking transaction has committed,
  so a rolled-back booking never reaches the other sites.

## Dashboard

- 3D availability board: bar height shows the rooms left for each night
- Live stats, a booking form and the latest bookings
- Channel sync status: per-site health cards and a log of recent syncs
- Reset demo button

## API

`POST /api/bookings`

```json
{
  "source": "ota-a",
  "external_reference": "A-1001",
  "room_type_id": 2,
  "guest_name": "Test Guest",
  "check_in": "2026-10-25",
  "check_out": "2026-10-27",
  "rooms": 1
}
```

| Response | Meaning |
|---|---|
| `201 Created` | Booking saved, inventory lowered, sync queued |
| `200 OK` | The same booking was sent before, the original is returned |
| `409 Conflict` | Not enough rooms for the requested nights |
| `422` | Validation error |

The nights booked are `check_in` up to, but not including, `check_out`.

The mock booking sites receive updates at `/api/mock-ota/a` (JSON), `/api/mock-ota/b`
(XML) and `/api/mock-ota/c` (rate limited and flaky).

## Stack

PHP 8, Laravel, MySQL, Apache, custom CSS and vanilla JavaScript.

## Deployment on AWS

```
Browser -> Apache + PHP (EC2, Ubuntu) -> MySQL (RDS)
                 |
         queue worker (systemd)
```

- **EC2:** Laravel served by Apache, updated with `git pull` from GitHub
- - **RDS:** managed MySQL, not publicly accessible. Only the app server can reach it,
  through a security group rule
- **Queue worker:** runs as a systemd service and restarts automatically
- **Security groups:** SSH limited to my IP, HTTP open, MySQL (3306) allowed from the
  app server
- **Elastic IP:** a fixed public address for the server
- **Region:** Asia Pacific (Sydney)

Update flow: edit and test locally, push to GitHub, run `git pull` on the server, then
`sudo systemctl restart channel-manager-worker`.

## Run locally

1. `composer install`
2. Copy `.env.example` to `.env`, set the MySQL details, and add
   `OTA_BASE_URL=http://127.0.0.1:8000`
3. `php artisan key:generate`
4. `php artisan migrate --seed --seeder=HotelSeeder`
5. `php artisan serve`, then open `/dashboard`
6. In a second terminal, run `php artisan queue:work` so syncs are processed

## Known limits and next steps

- The booking sites are mock endpoints inside the same app, not real OTA integrations.
- The API has no authentication. In production each booking site would get its own
  API key and signed requests.
- The queue uses the database driver on a single server. For more load I would move to
  Redis and run several workers.
- Rates and restrictions (minimum stay, closed to arrival) are not modelled yet,
  only room counts.
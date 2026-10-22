# Mini Channel Manager

A small channel manager for hotels, built with Laravel and MySQL.
A hotel keeps one master inventory, and every booking that arrives
from any booking site updates it safely, so no room is ever sold twice.

## The problem

A hotel sells the same room on many booking sites at once. If the last
room is booked on two sites within seconds, the hotel overbooks.

## How overbooking is prevented

Each booking runs inside a database transaction. It locks the inventory
rows for the requested nights (`SELECT ... FOR UPDATE`), checks that every
night has enough rooms, then lowers the counts. A second request for the
same room waits for the lock, sees the new count, and is rejected.

**Tested:** 50 simultaneous booking requests against 5 rooms gave exactly
5 successful bookings (HTTP 201) and 45 rejections (HTTP 409). The
inventory ended at zero.

php scripts/concurrency_test.php


## Features

- Master inventory per room type per night
- Booking API: `POST /api/bookings`
- Idempotent bookings: a resent booking is recognised by `(source, external_reference)` and never takes a second room
- Dashboard with a 3D availability board, live stats and a booking form

## Stack

PHP 8.3, Laravel, MySQL, Tailwind-free custom CSS, vanilla JavaScript

## Run locally

1. `composer install`
2. Copy `.env.example` to `.env` and set the MySQL details
3. `php artisan key:generate`
4. `php artisan migrate --seed --seeder=HotelSeeder`
5. `php artisan serve`, then open `/dashboard`

## Roadmap

- Sync engine: push availability to other booking sites through queued jobs with retries
- Adapters for different booking-site formats (JSON, XML, rate-limited API)
- Sync status page and audit log

## Live demo

http://16.178.62.251/dashboard

Use the **Reset demo** button to restore the data after trying bookings.

## Deployment on AWS


- **EC2:** Laravel served by Apache, deployed with `git pull` from GitHub
- **RDS:** managed MySQL, reachable from the app server through its security group
- **Security groups:** SSH limited to my IP, HTTP open, MySQL (3306) allowed only from the app server
- **Region:** Asia Pacific (Sydney)

### Update flow

Edit and test locally, push to GitHub, then run `git pull` on the server.
<?php

namespace Database\Seeders;

use App\Models\Hotel;
use App\Models\Inventory;
use Illuminate\Database\Seeder;

class HotelSeeder extends Seeder
{
    public function run(): void
    {
        $hotel = Hotel::create(['name' => 'Ebisu Garden Hotel']);

        $roomTypes = [
            ['name' => 'Standard Double', 'total_rooms' => 10, 'price' => 12000],
            ['name' => 'Deluxe Twin',     'total_rooms' => 5,  'price' => 18000],
        ];

        foreach ($roomTypes as $data) {
            $roomType = $hotel->roomTypes()->create([
                'name' => $data['name'],
                'total_rooms' => $data['total_rooms'],
            ]);

            $roomType->ratePlans()->create([
                'name' => 'Room only',
                'base_price' => $data['price'],
            ]);

            for ($i = 0; $i < 30; $i++) {
                Inventory::create([
                    'room_type_id' => $roomType->id,
                    'date' => now()->addDays($i)->toDateString(),
                    'available_count' => $data['total_rooms'],
                    'price' => $data['price'],
                ]);
            }
        }
    }
}
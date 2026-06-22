<?php

namespace Database\Seeders;

use App\Models\ServiceablePincode;
use Illuminate\Database\Seeder;

class ServiceablePincodeSeeder extends Seeder
{
    /**
     * Common Rajkot city delivery pincodes.
     *
     * @var array<int, array{pincode: string, locality: string|null}>
     */
    private const RAJKOT_PINCODES = [
        ['pincode' => '360001', 'locality' => 'Rajkot GPO'],
        ['pincode' => '360002', 'locality' => 'Rajkot HO'],
        ['pincode' => '360003', 'locality' => 'Rajkot City'],
        ['pincode' => '360004', 'locality' => 'Kalawad Road'],
        ['pincode' => '360005', 'locality' => 'University Road'],
        ['pincode' => '360006', 'locality' => 'Raiya Road'],
        ['pincode' => '360007', 'locality' => 'Mavdi'],
        ['pincode' => '360020', 'locality' => '150 Feet Ring Road'],
        ['pincode' => '360022', 'locality' => 'Nana Mava'],
        ['pincode' => '360024', 'locality' => 'Kothariya'],
        ['pincode' => '360025', 'locality' => 'Metoda GIDC'],
        ['pincode' => '360026', 'locality' => 'Shapar'],
        ['pincode' => '360311', 'locality' => 'Morbi Road'],
    ];

    public function run(): void
    {
        foreach (self::RAJKOT_PINCODES as $row) {
            ServiceablePincode::query()->updateOrCreate(
                ['pincode' => $row['pincode']],
                [
                    'locality' => $row['locality'],
                    'city' => 'Rajkot',
                    'state' => 'Gujarat',
                    'is_active' => true,
                ]
            );
        }
    }
}

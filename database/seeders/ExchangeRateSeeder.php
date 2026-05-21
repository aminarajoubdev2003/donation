<?php

namespace Database\Seeders;

use App\Models\ExchangeRate;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ExchangeRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ExchangeRate::insert([

            [
                'uuid'  => Str::uuid(),
                'currency' => 'SYP',
                'rate' => 13000,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'uuid'  => Str::uuid(),
                'currency' => 'EUR',
                'rate' => 0.92,
                'created_at' => now(),
                'updated_at' => now(),
            ],

        ]);
    }
}

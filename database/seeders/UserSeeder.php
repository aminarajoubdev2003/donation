<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'uuid' => Str::uuid(),
            'name' => 'Admin',
            'email' => 'admin.donation@gmail.com',
            'password' => Hash::make('6744IT#'),
            'phone' => '0922345687',
            'type' => 'أدمن',
            'profile' => 'null'
        ]);

        User::factory()
            ->count(50)
            ->create();
    }
}

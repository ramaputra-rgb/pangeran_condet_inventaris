<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Akun Tunggal Owner / Pemilik Usaha Pangeran Condet
        User::updateOrCreate(
            ['username' => 'owner'],
            [
                'name' => 'Owner Pangeran Condet',
                'email' => 'owner@pangerancondet.com',
                'password' => Hash::make('owner123'),
                'role' => 'owner',
                'email_verified_at' => now(),
            ]
        );
    }
}

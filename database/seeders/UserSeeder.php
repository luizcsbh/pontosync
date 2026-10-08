<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'luiz@pontosync.test'],
            [
                'name' => 'Luiz Santos',
                'password' => Hash::make('password'),
                'phone' => '(31) 99999-8888',
                'active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'maria@pontosync.test'],
            [
                'name' => 'Maria Silva',
                'password' => Hash::make('password'),
                'phone' => '(31) 98888-7777',
                'active' => true,
            ]
        );
    }
}

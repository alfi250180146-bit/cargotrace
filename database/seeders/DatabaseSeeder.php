<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $demo = [
            ['Admin Demo',     'admin@cargotrace.test',     User::ROLE_ADMIN],
            ['Operator Demo',  'operator@cargotrace.test',  User::ROLE_OPERATOR],
            ['Customer Demo',  'customer@cargotrace.test',  User::ROLE_CUSTOMER],
            ['Customer Two',   'customer2@cargotrace.test', User::ROLE_CUSTOMER],
        ];

        foreach ($demo as [$name, $email, $role]) {
            $user = User::firstOrNew(['email' => $email]);
            $user->name = $name;
            $user->password = Hash::make('password');
            $user->role = $role;
            $user->email_verified_at = now();
            $user->save();
        }

        $this->call([
            CarrierSeeder::class,
            LocationSeeder::class,
            ShipmentSeeder::class,
        ]);
    }
}
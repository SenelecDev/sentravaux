<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['matricule' => 'ADMIN'],
            [
                'name' => 'Administrateur',
                'email' => 'admin@senelec.sn',
                'password' => Hash::make('password'),
                'nom' => 'Administrateur',
                'prenom' => 'SENELEC',
                'organisation' => 'SENELEC',
                'is_active' => true,
            ]
        );

        $admin->assignRole('admin');
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SimulateUsersSeeder extends Seeder
{
    /**
     * Simuler 3000+ utilisateurs SENELEC réalistes.
     */
    public function run(): void
    {
        $this->command->info('Création de 3000 utilisateurs simulés...');

        // Assigner le rôle 'user' au gros lot
        $bar = $this->command->getOutput()->createProgressBar(3000);
        $bar->start();

        // Par batch de 500
        for ($i = 0; $i < 6; $i++) {
            $users = User::factory()->count(500)->create();
            
            foreach ($users as $user) {
                $user->assignRole('user');
                $bar->advance();
            }
        }

        $bar->finish();
        $this->command->newLine();

        // Créer quelques admins supplémentaires
        $this->command->info('Création de 5 admins supplémentaires...');
        User::factory()->count(5)->admin()->create();

        $this->command->info('Total utilisateurs: ' . User::count());
    }
}

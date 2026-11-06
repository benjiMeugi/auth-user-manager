<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AppSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Enregistre les applications clientes gérées par le service d'authentification central.
     */
    public function run(): void
    {
        $apps = [
            [
                'name' => 'Authentication & SSO',
                'description' => 'Service central d\'authentification et de gestion des utilisateurs/permissions.',
            ],
            [
                'name' => 'Employee Management',
                'description' => 'Gestion des dossiers du personnel et des employés.',
            ],
            [
                'name' => 'Webmail Management',
                'description' => 'Gestion des boîtes mail et de la messagerie interne.',
            ],
            [
                'name' => 'Crèche Management',
                'description' => 'Gestion des inscriptions, de la scolarité et du séjour quotidien des enfants à la crèche.',
            ],
        ];

        foreach ($apps as $app) {
            DB::table('apps')->updateOrInsert(
                ['name' => $app['name']],
                $app
            );
        }
    }
}

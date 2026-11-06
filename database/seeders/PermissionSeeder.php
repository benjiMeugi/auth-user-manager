<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Orchestre les seeders de permissions par application.
     *
     * Pour éviter d'injecter des permissions inutiles sur un environnement donné,
     * vous pouvez exécuter uniquement le seeder de l'application souhaitée :
     *   - php artisan db:seed --class=AuthPermissionSeeder
     *   - php artisan db:seed --class=EmployePermissionSeeder
     *   - php artisan db:seed --class=WebmailPermissionSeeder
     *   - php artisan db:seed --class=CrechePermissionSeeder
     */
    public function run(): void
    {
        $this->call([
            AuthPermissionSeeder::class,
            EmployePermissionSeeder::class,
            WebmailPermissionSeeder::class,
            CrechePermissionSeeder::class,
        ]);
    }
}

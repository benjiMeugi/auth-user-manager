<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AuthPermissionSeeder extends Seeder
{
    /**
     * Génère uniquement les permissions du socle Authentication & SSO.
     * Commande ciblée : php artisan db:seed --class=AuthPermissionSeeder
     */
    public function run(): void
    {
        $permissions = [];
        $actions = ['list', 'show', 'create', 'update', 'delete'];

        $authResources = [
            'user',
            'role',
            'permission',
            'permission_user',
            'permission_role',
            'role_user',
            'app',
        ];

        foreach ($authResources as $resource) {
            foreach ($actions as $action) {
                $permissionName = "{$resource}-{$action}";

                $permissions[] = [
                    'label' => $permissionName,
                    'description' => "[Auth API] Permet d'effectuer l'action [{$action}] sur la ressource [{$resource}]",
                ];
            }
        }

        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['label' => $permission['label']],
                $permission
            );
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ressources = [
            'user',
            'role',
            'permission',
            'permission_user',
            'permission_role',
            'role_user',
            'app',
        ];

        // Actions CRUD standard associées à ta fonction resolveAbility()
        $actions = ['list', 'show', 'create', 'update', 'delete'];

        $permissions = [];

        foreach ($ressources as $ressource) {
            foreach ($actions as $action) {
                // Format : "user-list", "user-create", etc.
                $permissionName = "{$ressource}-{$action}";

                $permissions[] = [
                    'label' => $permissionName,
                    'description' => "Permet d'effectuer l'action [{$action}] sur la ressource [{$ressource}]",
                ];
            }
        }

        // Insertion en base avec gestion des doublons (basée sur la colonne 'label')
        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['label' => $permission['label']],
                $permission
            );
        }
    }
}

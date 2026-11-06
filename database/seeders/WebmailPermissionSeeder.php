<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WebmailPermissionSeeder extends Seeder
{
    /**
     * Génère uniquement les permissions de l'application Webmail Management (webmail-api).
     * Commande ciblée : php artisan db:seed --class=WebmailPermissionSeeder
     */
    public function run(): void
    {
        $permissions = [];
        $actions = ['list', 'show', 'create', 'update', 'delete'];

        $webmailCrudResources = [
            'attachement',
            'contact',
            'folder',
            'mail_account',
            'message',
            'user_settings',
            'server_setting',
        ];

        foreach ($webmailCrudResources as $resource) {
            foreach ($actions as $action) {
                $permissionName = "{$resource}-{$action}";

                $permissions[] = [
                    'label' => $permissionName,
                    'description' => "[Webmail API] Permet d'effectuer l'action [{$action}] sur la ressource [{$resource}]",
                ];
            }
        }

        $webmailSpecificPermissions = [
            ['label' => 'mail_account-reset_password', 'description' => '[Webmail API] Réinitialiser le mot de passe d’un compte mail'],
            ['label' => 'mail_account-change_quota', 'description' => '[Webmail API] Modifier le quota d’un compte mail'],
            ['label' => 'mail_account-autoreply', 'description' => '[Webmail API] Configurer la réponse automatique d’un compte mail'],
            ['label' => 'mail_account-suspend', 'description' => '[Webmail API] Suspendre ou réactiver un compte mail'],
            ['label' => 'mail_account-sync', 'description' => '[Webmail API] Synchroniser les mails d’un compte mail'],
        ];

        $permissions = array_merge($permissions, $webmailSpecificPermissions);

        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['label' => $permission['label']],
                $permission
            );
        }
    }
}

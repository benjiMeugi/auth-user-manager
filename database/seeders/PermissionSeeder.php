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
        $permissions = [];

        // Actions CRUD standard associées à ta fonction resolveAbility()
        $actions = ['list', 'show', 'create', 'update', 'delete'];

        $authRessources = [
            'user',
            'role',
            'permission',
            'permission_user',
            'permission_role',
            'role_user',
            'app',
        ];

        $employeCrudResources = [
            'title',
            'position',
            'classification',
            'employe',
            'retirement',
            'dismissal',
            'sanction',
            'assignment',
            'unit',
            'promotion',
            'absence_type',
            'leave_grant',
            'absence_request',
            'absence',
            'holiday',
            'contracttype',
            'contract',
            'payslip',
            'payslipLine',
            'payrollLineType',
        ];

        $employeSpecificPermissions = [
            // career_event
            ['label' => 'career_event-list', 'description' => '[Employe API] Lister les événements de carrière'],
            ['label' => 'career_event-show', 'description' => '[Employe API] Voir un événement de carrière'],
            ['label' => 'career_event-delete', 'description' => '[Employe API] Supprimer un événement de carrière'],

            // attachment
            ['label' => 'attachment-list', 'description' => '[Employe API] Lister les pièces jointes'],
            ['label' => 'attachment-show', 'description' => '[Employe API] Voir ou télécharger une pièce jointe'],
            ['label' => 'attachment-create', 'description' => '[Employe API] Ajouter une pièce jointe (absence ou demande)'],
            ['label' => 'attachment-update', 'description' => '[Employe API] Mettre à jour une pièce jointe'],
            ['label' => 'attachment-delete', 'description' => '[Employe API] Supprimer une pièce jointe'],
            ['label' => 'attachment-download', 'description' => '[Employe API] Télécharger une pièce jointe'],

            // leave_balance
            ['label' => 'leave_balance-current', 'description' => '[Employe API] Voir le solde de congés courant'],
            ['label' => 'leave_balance-list', 'description' => '[Employe API] Lister les soldes de congés'],

            // ongoing_absence
            ['label' => 'ongoing_absence-current', 'description' => '[Employe API] Voir les absences en cours courantes'],
            ['label' => 'ongoing_absence-list', 'description' => '[Employe API] Lister les absences en cours'],

            // unit_absence_schedule
            ['label' => 'unit_absence_schedule-list', 'description' => '[Employe API] Lister les plannings d’absence par unité'],
            ['label' => 'unit_absence_schedule-coverage', 'description' => '[Employe API] Voir la couverture du personnel par unité'],
        ];


        $ressources = array_merge($authRessources, $employeCrudResources);
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

        $permissions = array_merge($permissions, $employeSpecificPermissions);

        // Insertion en base avec gestion des doublons (basée sur la colonne 'label')
        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['label' => $permission['label']],
                $permission
            );
        }
    }
}

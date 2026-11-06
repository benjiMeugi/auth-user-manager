<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployePermissionSeeder extends Seeder
{
    /**
     * Génère uniquement les permissions de l'application Employee Management (employe-api).
     * Commande ciblée : php artisan db:seed --class=EmployePermissionSeeder
     */
    public function run(): void
    {
        $permissions = [];
        $actions = ['list', 'show', 'create', 'update', 'delete'];

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

        foreach ($employeCrudResources as $resource) {
            foreach ($actions as $action) {
                $permissionName = "{$resource}-{$action}";

                $permissions[] = [
                    'label' => $permissionName,
                    'description' => "[Employe API] Permet d'effectuer l'action [{$action}] sur la ressource [{$resource}]",
                ];
            }
        }

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

        $permissions = array_merge($permissions, $employeSpecificPermissions);

        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['label' => $permission['label']],
                $permission
            );
        }
    }
}

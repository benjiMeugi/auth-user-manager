<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CrechePermissionSeeder extends Seeder
{
    /**
     * Génère uniquement les permissions de l'application Crèche Management (creche-manager-api).
     * Commande ciblée : php artisan db:seed --class=CrechePermissionSeeder
     */
    public function run(): void
    {
        $permissions = [];
        $actions = ['list', 'show', 'create', 'update', 'delete'];

        $crecheCrudResources = [
            // Référentiels normalisés & configurations dynamiques
            'section',
            'section_tranche',
            'language',
            'relationship_type',
            'document_type',
            'child_event_type',
            'payment_method',
            'pricing_configuration',
            'operating_schedule',

            // Ressources métiers principales
            'child',
            'guardian',
            'child_guardian',
            'child_medical_profile',
            'registration',
            'attachment',
            'tuition_installment',
            'additional_charge',
            'attendance',
            'child_event',
            'payment',
        ];

        foreach ($crecheCrudResources as $resource) {
            foreach ($actions as $action) {
                $permissionName = "{$resource}-{$action}";

                $permissions[] = [
                    'label' => $permissionName,
                    'description' => "[Crèche API] Permet d'effectuer l'action [{$action}] sur la ressource [{$resource}]",
                ];
            }
        }

        $crecheSpecificPermissions = [
            // Consultation du cahier de vie / timeline différée de l'enfant
            ['label' => 'child-timeline', 'description' => '[Crèche API] Consulter le fil d’actualité et le suivi différé d’un enfant'],

            // Décisions administratives sur les dossiers d'inscription
            ['label' => 'registration-approve', 'description' => '[Crèche API] Valider un dossier d’inscription et générer l’échéancier de scolarité'],
            ['label' => 'registration-reject', 'description' => '[Crèche API] Rejeter un dossier d’inscription'],

            // Pointages journaliers et heures supplémentaires post-18h30
            ['label' => 'attendance-check_in', 'description' => '[Crèche API] Pointer l’heure d’arrivée journalière d’un enfant'],
            ['label' => 'attendance-check_out', 'description' => '[Crèche API] Pointer l’heure de départ d’un enfant et calculer la garderie post-18h30'],

            // Règlements financiers (tranches de scolarité et frais additionnels)
            ['label' => 'tuition_installment-pay', 'description' => '[Crèche API] Enregistrer le paiement d’une tranche de scolarité'],
            ['label' => 'additional_charge-pay', 'description' => '[Crèche API] Enregistrer le paiement d’un frais additionnel (repas, garderie, forfait hebdo)'],

            // Téléchargement de justificatifs et photos d'activités
            ['label' => 'attachment-download', 'description' => '[Crèche API] Télécharger une pièce justificative ou une photo d’activité'],
        ];

        $permissions = array_merge($permissions, $crecheSpecificPermissions);

        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['label' => $permission['label']],
                $permission
            );
        }
    }
}

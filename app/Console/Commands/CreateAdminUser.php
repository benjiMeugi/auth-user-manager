<?php

namespace App\Console\Commands;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "app:create-admin {username} {password} {--email=admin@auth.local}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Crée un utilisateur administrateur et lui assigne toutes les permissions de l'application.";

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $username = $this->argument('username');
        $password = $this->argument('password');
        $email = $this->option('email');

        $this->info("Création de l'administrateur : {$username}...");

        // 1. Créer ou mettre à jour l'utilisateur
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'username' => $username,
                'password' => $password,
            ]
        );

        $this->info("Utilisateur enregistré avec succès (ID: {$user->id}).");

        // 2. Récupérer toutes les permissions de l'application
        $permissions = Permission::all();

        if ($permissions->isEmpty()) {
            $this->warn("Aucune permission trouvée en base de données. Pense à lancer tes seeders de permissions d'abord.");
            return Command::SUCCESS;
        }

        // 3. Assigner toutes les permissions à l'utilisateur (via ta table pivot permission_user)
        // On s'assure d'utiliser la relation ou l'insertion directe dans la table pivot
        try {
            // Si tu as défini une relation belongsToMany 'permissions()' dans ton modèle User :
            $user->permissions()->sync($permissions->pluck('id'));

            $this->info("✅ Succès : " . $permissions->count() . " permissions ont été assignées à l'administrateur {$username}.");

        } catch (\Exception $e) {
            $this->error("Erreur lors de l'assignation des permissions : " . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

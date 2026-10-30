<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Repository\Repository;
use App\Http\Controllers\Traits\ApiResponse;
use App\Models\User;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    use ApiResponse;
    /**
     * @var User
     */
    private $model;

    /**
     * @var Repository
     */
    private $repository;

    public function __construct()
    {
        $this->model = new User();
        $this->repository = new Repository($this->model);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->respondBadRequest(['errors' => $validator->errors()->all()]);
        }

        $user = User::where('username', $request->username)
            ->with('permissions', 'roles.permissions', 'apps')
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            // Utilisation d'un code 401 Unauthorized (standard) au lieu de 402 Payment Required
            return $this->respondUnauthorized('Identifiants incorrects.');
        }

        //Récupération et fusion de toutes les permissions de l'user ---        
        $allAbilities = $user->getAllPermissions();

        $expiration = config('sanctum.expiration');
        $expireAt = $expiration ? (new DateTime())->modify("+{$expiration} minutes") : null;

        $token = $user->createToken($user->username, $allAbilities, $expireAt);

        return $this->respondLogin(
            userData: [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'roles' => $user->getRoleLabels(),
                'permissions' => $allAbilities,
            ],
            token: $token->plainTextToken,
            expiresAt: $expireAt?->format("Y-m-d H:i:s")
        );
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        $response = ['You have been successfully logged out!'];
        return $this->respondOk($response, 'Déconnexion réussie.');
    }

    public function validate_token(Request $request)
    {
        // Récupère l'utilisateur authentifié via Sanctum
        $user = $request->user();

        if (!$user) {
            return $this->respondUnauthorized('Non authentifié. Veuillez fournir un token valide.');
        }

        // Charge les relations nécessaires pour calculer les rôles et habilités
        $user->loadMissing('permissions', 'roles.permissions');

        // Fusionne et déduplique toutes les habilités
        $allAbilities = $user->getAllPermissions();

        // Récupère les rôles de l'utilisateur
        $roles = $user->getRoleLabels();

        // Renvoie un format unifié pour tous les micro-services
        return $this->respondOk([
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'roles' => $roles,
            'permissions' => $allAbilities,
        ]);
    }

}

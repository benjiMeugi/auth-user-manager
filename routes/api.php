<?php

use App\Http\Controllers\AppController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PermissionRoleController;
use App\Http\Controllers\PermissionUserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RoleUserController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * Génère le nom d'habileté attendu par Gate::before() (ex: "permission-list")
 * préfixé par "can:" pour que Laravel sache qu'il s'agit d'une
 * vérification d'autorisation, pas un simple alias de middleware.
 */
function resolveAbility(string $ability, string $action)
{
    return 'can:' . $ability . '-' . $action;
}

//  Helper pour enregistrer rapidement les 5 routes standard d'un CRUD API
function registerApiCrud(string $prefix, string $controller)
{
    Route::prefix($prefix)->group(function () use ($controller, $prefix) {
        // Nettoie le préfixe si besoin (ex: "permission_user" -> "permission-user")
        // $abilityName = str_replace('_', '-', $prefix);
        $abilityName = $prefix;

        Route::get('/', [$controller, 'index'])->middleware(resolveAbility($abilityName, 'list'));
        Route::get('/{id}', [$controller, 'show'])->middleware(resolveAbility($abilityName, 'show'));
        Route::post('/', [$controller, 'store'])->middleware(resolveAbility($abilityName, 'create'));
        Route::put('/{id}', [$controller, 'update'])->middleware(resolveAbility($abilityName, 'update'));
        Route::delete('/{id}', [$controller, 'delete'])->middleware(resolveAbility($abilityName, 'delete'));
    });
}

Route::post('login', [AuthController::class, 'login'])->name('login');

Route::middleware(['auth:sanctum'])->group(function () {

    Route::get('/validate-token', [AuthController::class, 'validate_token']);

    Route::post('/logout', [AuthController::class, 'logout']);

    registerApiCrud('permission', PermissionController::class);
    registerApiCrud('user', UserController::class);
    registerApiCrud('permission_user', PermissionUserController::class);
    registerApiCrud('permission_role', PermissionRoleController::class);
    registerApiCrud('role', RoleController::class);
    registerApiCrud('role_user', RoleUserController::class);
    registerApiCrud('app', AppController::class);
});

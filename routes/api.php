<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PermisionUserController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PermissionRoleController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RoleUserController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

function resolveAbility($ability)
{
    return null;
    if (!$ability) {
        return null;
    }
    return 'permission' . ':' . $ability;
}
Route::post('login', [AuthController::class, 'login']);

// 'middleware' => 'auth:api'
Route::middleware(['auth:sanctum'])->group(function () {

// Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('/validate-token', [AuthController::class, 'validate_token']);

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::prefix('permission')->group(function () {
        $controler = PermissionController::class;
        $startAbility = 'permission';
        Route::get('/{id?}', [$controler, 'index'])->middleware(resolveAbility($startAbility . '-list'));
        Route::post('/', [$controler, 'store'])->middleware(resolveAbility($startAbility . '-create'));
        Route::put('/{id}', [$controler, 'update'])->middleware(resolveAbility($startAbility . '-update'));
        Route::delete('/{id}', [$controler, 'delete'])->middleware(resolveAbility($startAbility . '-delete'));
    });

    Route::prefix('user')->group(function () {
        $controler = UserController::class;
        $startAbility = 'user';
        Route::get('/{id?}', [$controler, 'index'])->middleware(resolveAbility($startAbility . '-list'));
        Route::post('/', [$controler, 'store'])->middleware(resolveAbility($startAbility . '-create'));
        Route::put('/{id}', [$controler, 'update'])->middleware(resolveAbility($startAbility . '-update'));
        Route::delete('/{id}', [$controler, 'delete'])->middleware(resolveAbility($startAbility . '-delete'));
    });

    Route::prefix('permission_user')->group(function () {
        $controler = PermisionUserController::class;
        $startAbility = 'permission_user';
        Route::get('/{id?}', [$controler, 'index'])->middleware(resolveAbility($startAbility . '-list'));
        Route::post('/', [$controler, 'store'])->middleware(resolveAbility($startAbility . '-create'));
        Route::put('/{id}', [$controler, 'update'])->middleware(resolveAbility($startAbility . '-update'));
        Route::delete('/{id}', [$controler, 'delete'])->middleware(resolveAbility($startAbility . '-delete'));
    });

    Route::prefix('permission_role')->group(function () {
        $controler = PermissionRoleController::class;
        $startAbility = 'permission_role';
        Route::get('/{id?}', [$controler, 'index'])->middleware(resolveAbility($startAbility . '-list'));
        Route::post('/', [$controler, 'store'])->middleware(resolveAbility($startAbility . '-create'));
        Route::put('/{id}', [$controler, 'update'])->middleware(resolveAbility($startAbility . '-update'));
        Route::delete('/{id}', [$controler, 'delete'])->middleware(resolveAbility($startAbility . '-delete'));
    });

    Route::prefix('role')->group(function () {
        $controler = RoleController::class;
        $startAbility = 'role';
        Route::get('/{id?}', [$controler, 'index'])->middleware(resolveAbility($startAbility . '-list'));
        Route::post('/', [$controler, 'store'])->middleware(resolveAbility($startAbility . '-create'));
        Route::put('/{id}', [$controler, 'update'])->middleware(resolveAbility($startAbility . '-update'));
        Route::delete('/{id}', [$controler, 'delete'])->middleware(resolveAbility($startAbility . '-delete'));
    });

    Route::prefix('role_user')->group(function () {
        $controler = RoleUserController::class;
        $startAbility = 'role_user';
        Route::get('/{id?}', [$controler, 'index'])->middleware(resolveAbility($startAbility . '-list'));
        Route::post('/', [$controler, 'store'])->middleware(resolveAbility($startAbility . '-create'));
        Route::put('/{id}', [$controler, 'update'])->middleware(resolveAbility($startAbility . '-update'));
        Route::delete('/{id}', [$controler, 'delete'])->middleware(resolveAbility($startAbility . '-delete'));
    });
});



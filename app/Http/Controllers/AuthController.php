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


    // public function register(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'name' => 'required|string|max:255',
    //         'email' => 'required|string|email|max:255|unique:users',
    //         'password' => 'required|string|min:6|confirmed',
    //     ]);
    //     if ($validator->fails()) {
    //         return response(['errors' => $validator->errors()->all()], 422);
    //     }
    //     $request['password'] = Hash::make($request['password']);
    //     $request['remember_token'] = Str::random(10);
    //     $user = User::create($request->toArray());
    //     $token = $user->createToken('Laravel Password Grant Client')->accessToken;
    //     $response = ['token' => $token];
    //     return response($response, 200);
    // }

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
            ->with('permissions', 'roles.permissions', 'permission_users', 'apps')
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            // Utilisation d'un code 401 Unauthorized (standard) au lieu de 402 Payment Required
            return $this->respondLogin(null, false, null, 401);
        }

        //Récupération et fusion de toutes les permissions de l'user ---        
        $directPermissions = $user->permissions->pluck('label');
        $rolePermissions = $user->roles->flatMap(function ($role) {
            return $role->permissions->pluck('label');
        });
        $allAbilities = $directPermissions->merge($rolePermissions)->unique()->toArray();

        $expiration = config('sanctum.expiration');
        $expireAt = $expiration ? (new DateTime())->modify("+{$expiration} minutes") : null;

        $token = $user->createToken($user->username, $allAbilities, $expireAt);

        return $this->respondLogin(
            userData: [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'permissions' => $allAbilities,
            ],
            token: $token->plainTextToken,
            expiresAt: $expireAt->format("Y-m-d H:i:s")
        );
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        $response = ['You have been successfully logged out!'];
        return $this->respondLogin($response, false, null, 200);
    }

    public function validate_token(Request $request)
    {
        $user = $request->user(); // Sanctum automatically resolves user

        if (!$user) {
            return $this->respondLogin(null, false, null, 401);
        }

        return $this->respondLogin($user, true, "", 200);
    }

}

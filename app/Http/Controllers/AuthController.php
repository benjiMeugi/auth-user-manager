<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Repository\Repository;
use App\Http\Controllers\Traits\ApiResponse;
use App\Models\User;
use DateTime;
use Illuminate\Http\Request;
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


    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);
        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->all()], 422);
        }
        $request['password'] = Hash::make($request['password']);
        $request['remember_token'] = Str::random(10);
        $user = User::create($request->toArray());
        $token = $user->createToken('Laravel Password Grant Client')->accessToken;
        $response = ['token' => $token];
        return response($response, 200);
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
            ->with('permissions', 'roles.permissions')
            ->first();
        
        if ($user) {
            if (Hash::check($request->password, $user->password)) {
                $expiration = config('sanctum.expiration');
                $expireAt = $expiration ? (new DateTime())->modify("+{$expiration} minutes") : null;

                $token = $user->createToken($user->username, ['*'], $expireAt);
                
                return $this->respondLogin($this->getUser($user), true, $token->plainTextToken, 200, "", $expireAt->format("Y-m-d H:i:s"));
            } else {
                // $response = ["Password mismatch"];
                return $this->respondLogin(null, false, null, 402);
            }
        } else {
            // $response = ['User does not exist'];
            return $this->respondLogin(null, false, null, 402);
        }
    }

    private function getUser(User $user)
    {
        $permissions = $user->permissions->map(function ($item) {
            return $item->label;
        });
        
        $permissionRoles = collect();
        if ($user->roles->isNotEmpty()) {
            $permissionRoles = $user->roles->permissions->map(function ($item) {
                return $item->label;
            });
        }

        $finalPermissions = $permissionRoles->concat($permissions)->unique()->values()->all();

        return [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'permissions' => $finalPermissions,
            'roles' => $user->roles->all()
        ];
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



    public function token(Request $request)
    {
        $request->request->add([
            'grant_type' => 'password',
            'client_id' => config('auth.passport.client_id'),
            'client_secret' => config('auth.passport.client_secret'),
        ]);

        $proxy = Request::create('oauth/token', 'post');

        return Route::dispatch($proxy);
    }
}

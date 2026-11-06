<?php

namespace App\Models;

use BenjiMeugi\Contracts\IModel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements IModel
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'first_name',
        'last_name',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public $relation_methods = ['permissions', 'roles', 'permission_users', 'apps'];

    public function getMigrateKey()
    {
        return $this->getForeignKey();
    }

    public function rules()
    {
        return [
            'username' => ['required', 'unique:'.$this->getTable()],
            'email' => ['required', 'email', 'unique:'.$this->getTable()],
            'password' => ['required', 'confirmed'],
        ];
    }

    public function update_rules()
    {
        return [
            'username' => ['sometimes', IModel::IGNORE_RULE],
            'email' => ['sometimes', 'email', IModel::IGNORE_RULE],
            'password' => 'sometimes',
            "id" => "required|exists:".$this->getTable().",$this->primaryKey"
        ];
    }

    // relations
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_users', 'user_id', 'role_id');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_users', 'user_id', 'permission_id');
    }
    
    public function permission_users()
    {
        return $this->hasMany(PermissionUser::class, 'user_id');
    }

    public function apps()
    {
        return $this->belongsToMany(App::class, 'app_users', 'user_id', 'app_id');
    }

    /**
     * Retourne la liste dédupliquée des labels de permissions (directes + via rôles).
     *
     * @return array<int, string>
     */
    public function getAllPermissions(): array
    {
        $this->loadMissing('permissions', 'roles.permissions');

        $directPermissions = $this->permissions->pluck('label');
        $rolePermissions = $this->roles->flatMap(function ($role) {
            return $role->permissions->pluck('label');
        });

        return $directPermissions->merge($rolePermissions)->unique()->values()->toArray();
    }

    /**
     * Retourne la liste dédupliquée des labels de rôles de l'utilisateur.
     *
     * @return array<int, string>
     */
    public function getRoleLabels(): array
    {
        $this->loadMissing('roles');

        return $this->roles->pluck('label')->unique()->values()->toArray();
    }

    /**
     * check if current user has a specific permission
     * 
     * @param string $permission
     * @return bool
     */
    public function hasPermission($permission)
    {
        return in_array($permission, $this->getAllPermissions(), true);
    }

}

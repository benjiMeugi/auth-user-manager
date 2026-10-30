<?php

namespace App\Models;

use App\Models\User;
use BenjiMeugi\Contracts\IModel;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model implements IModel
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'label',
        'description',
        'display_label',
    ];

    /**
     * Related models definitions
     *
     * @var array
     */
    public $relation_methods = ['users', 'roles', 'apps'];

    public function getMigrateKey()
    {
        return $this->getForeignKey();
    }
    
    public function rules()
    {
        return [
            'label' => ['required', 'unique:' . $this->getTable()]
        ];
    }

    public function update_rules()
    {
        return [
            'label' => ['sometimes', IModel::IGNORE_RULE],
            "id" => "required|exists:".$this->getTable().",$this->primaryKey"
        ];
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'permission_roles', 'permission_id', 'role_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'permission_users', 'permission_id', 'user_id');
    }

    public function apps()
    {
        return $this->belongsToMany(App::class, 'permission_apps', 'permission_id', 'app_id');
    }
}

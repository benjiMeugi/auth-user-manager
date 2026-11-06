<?php

namespace App\Models;

use BenjiMeugi\Contracts\IModel;
use Illuminate\Database\Eloquent\Model;

class App extends Model implements IModel
{
    protected $fillable = [
        'name',
        'description',
    ];

    public $relation_methods = ['users', 'permissions'];

    public function getMigrateKey()
    {
        return $this->getForeignKey();
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
        ];
    }

    public function update_rules()
    {
        return [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:255',
            "id" => "required|exists:" . $this->getTable() . ",$this->primaryKey"
        ];
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'app_users', 'app_id', 'user_id');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_apps', 'app_id', 'permission_id');
    }
}

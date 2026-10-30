<?php

namespace App\Models;

use BenjiMeugi\Contracts\IModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class PermissionApp extends Model implements IModel
{
    protected $fillable = [
        'permission_id',
        'app_id',
    ];

    public $relation_methods = ['permission', 'app'];

    public function getMigrateKey()
    {
        return $this->getForeignKey();
    }

    public function rules()
    {
        return [
            'permission_id' => 'bail|required|exists:permissions,id',
            'app_id' => [
                'bail',
                'required',
                'exists:apps,id',
                Rule::unique($this->getTable(), 'app_id')->where('permission_id', request('permission_id')),
            ],
        ];
    }

    public function update_rules()
    {
        return [
            'permission_id' => 'sometimes|exists:permissions,id',
            'app_id' => 'sometimes|exists:apps,id',
            'id' => 'required|exists:' . $this->getTable() . ',' . $this->primaryKey,
        ];
    }

    public function permission()
    {
        return $this->belongsTo(Permission::class, 'permission_id');
    }

    public function app()
    {
        return $this->belongsTo(App::class, 'app_id');
    }
}

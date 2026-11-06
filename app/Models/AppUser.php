<?php

namespace App\Models;

use BenjiMeugi\Contracts\IModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class AppUser extends Model implements IModel
{
    protected $fillable = [
        'app_id',
        'user_id',
    ];

    public $relation_methods = ['app', 'user'];

    public function getMigrateKey()
    {
        return $this->getForeignKey();
    }

    public function rules()
    {
        return [
            'app_id' => 'bail|required|exists:apps,id',
            'user_id' => [
                'bail',
                'required',
                'exists:users,id',
                Rule::unique($this->getTable(), 'user_id')->where('app_id', request('app_id')),
            ],
        ];
    }

    public function update_rules()
    {
        return [
            'app_id' => 'sometimes|exists:apps,id',
            'user_id' => 'sometimes|exists:users,id',
            'id' => 'required|exists:' . $this->getTable() . ',' . $this->primaryKey,
        ];
    }

    public function app()
    {
        return $this->belongsTo(App::class, 'app_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
}

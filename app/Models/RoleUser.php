<?php
namespace App\Models;

use App\Models\User;
use BenjiMeugi\Contracts\IModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class RoleUser extends Model implements IModel
{

    /**
     * Fillable column of the related table
     *
     * @var array
     */
    protected $fillable = ['role_id', 'user_id'];

    /**
     * Related models definitions
     *
     * @var array
     */
    public $relation_methods = ['user', 'role'];

    public function getMigrateKey()
    {
        return $this->getForeignKey();
    }


    
    public function rules()
    {
        return [
            'role_id' => 'bail|required|exists:roles,id',
            'user_id' => [
                'bail',
                'required',
                'exists:users,id',
                Rule::unique($this->getTable(), 'user_id')->where('role_id', request('role_id')),
            ],
        ];
    }

    public function update_rules()
    {
        return [
            'role_id' => 'sometimes|exists:roles,id',
            'user_id' => 'sometimes|exists:users,id',
            "id" => "required|exists:".$this->getTable().",$this->primaryKey"
        ];
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
}

<?php
namespace App\Models;

use App\Models\User;
use BenjiMeugi\Contracts\IModel;
use Illuminate\Database\Eloquent\Model;

class Role extends Model implements IModel
{

    /**
     * Fillable column of the related table
     *
     * @var array
     */
    protected $fillable = ['label', 'display_label'];

    /**
     * Related models definitions
     *
     * @var array
     */
    public $relation_methods = ['permissions', 'users', 'permission_roles'];

    public function getMigrateKey()
    {
        return $this->getForeignKey();
    }


    

    public function rules()
    {
        return [
            'label' => ['required', 'unique:' . $this->getTable()],
        ];
    }

    public function update_rules()
    {
        return [
            'label' => ['sometimes', IModel::IGNORE_RULE],
            "id" => "required|exists:".$this->getTable().",$this->primaryKey"
        ];
    }
    
    public function users()
    {
        return $this->belongsToMany(User::class, 'role_users', 'role_id', 'user_id');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_roles', 'role_id', 'permission_id');
    }

    public function permission_roles()
    {
        return $this->hasMany(PermissionRole::class);
    }
}

<?php 

namespace App\Http\Controllers\Repository;

use Illuminate\Http\Request;

interface IRepository {
    
    /**
     * @param bool $multiple
     */
    public function rules($multiple);
    
    public function update_rules();

    public function setModel($model);
}

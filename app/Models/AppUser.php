<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppUser extends Model
{
    protected $fillable = [
        'app_id',
        'user_id',
    ];

    public function app()
    {
        return $this->belongsTo(App::class, 'app_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
}

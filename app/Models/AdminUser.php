<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdminUser extends Model
{
    protected $table = 'admin_users';

    protected $fillable = ['username', 'password_hash'];

    protected $hidden = ['password_hash'];

    public function sessions(): HasMany
    {
        return $this->hasMany(AdminSession::class);
    }
}

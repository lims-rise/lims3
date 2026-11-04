<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $table = 'tbl_user';
    protected $primaryKey = 'id_users';
    public $timestamps = false;

    protected $hidden = ['password'];

    // tbl_user tidak punya kolom remember_token
    public function getRememberTokenName()
    {
        return '';
    }
}
<?php

namespace App\Models;

use App\Events\SendResetPasswordMail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use DB;

class User extends Authenticatable
{

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'email', 'password','fname', 'lname', 'user_type_id', 'fname_ar', 'lname_ar'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
   
    

    public function patient()
    {
        return $this->hasOne('App\Models\Patient');
    }

    public function doctor()
    {
        return $this->hasOne('App\Models\Doctor');
    }

}

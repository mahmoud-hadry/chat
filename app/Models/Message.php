<?php

namespace App\Models;

use App\Events\SendResetPasswordMail;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use DB;

class Message extends Authenticatable
{
    protected $table = 'messages';
}

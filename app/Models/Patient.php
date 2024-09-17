<?php

namespace App\Models;

use Eloquent as Model;
use DB;

class Patient extends Model
{

    public $table = 'patients';

    public $fillable = [
        'user_id',
        'doctor_id',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */

    /**
     * Validation rules
     *
     * @var array
     */
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
   
}

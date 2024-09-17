<?php

namespace App\Models;

use Eloquent as Model;

class Visit extends Model
{

    public $table = 'visits';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    public $fillable = [
        'patient_id',
        'doctor_id'
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
    public static $rules = [
        'patient_id' => 'required'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
   
}

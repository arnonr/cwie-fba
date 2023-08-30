<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Teacher extends Model
{

    use HasFactory;
    protected $table = 'teacher';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'user_id',
        'person_key',
        'prefix',
        'firstname',
        'surname',
        'citizen_id',
        'tel',
        'email',
        'address',
        'province_id',
        'amphur_id',
        'tumbol_id',
        'signature_file',
        'faculty_id',
        'department_id',
        'executive_position',
        'hris_last_updated_at',
        'active',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'deleted_at',
    ];
}

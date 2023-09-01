<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Department extends Model
{

    use HasFactory;
    protected $table = 'department';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'department_code',
        'name_th',
        'name_en',
        'tel',
        'fax',
        'email',
        'faculty_id',
        'active',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'deleted_at',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Major extends Model
{

    use HasFactory;
    protected $table = 'major';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'major_code',
        'name_th',
        'name_en',
        'department_id',
        'active',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'deleted_at',
    ];
}

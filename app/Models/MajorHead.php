<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MajorHead extends Model
{

    use HasFactory;
    protected $table = 'major_head';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'semester_id',
        'major_id',
        'teacher_id',
        'active',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'deleted_at',
    ];
}

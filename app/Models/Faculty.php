<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Faculty extends Model
{
    use HasFactory;
    protected $table = 'faculty';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'faculty_code',
        'name_th',
        'name_en',
        'tel',
        'fax',
        'email',
        'active',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'deleted_at',
    ];
}

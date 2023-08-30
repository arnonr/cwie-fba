<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tumbol extends Model
{

    use HasFactory;
    protected $table = 'tumbol';
    protected $primaryKey = 'tumbol_id';

    protected $fillable = [
        'tumbol_id',
        'tumbol_code',
        'name_th',
        'name_en',
        'postcode',
        'amphur_id',
        'active',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'deleted_at',
    ];
}

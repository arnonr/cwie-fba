<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Amphur extends Model
{

    use HasFactory;
    protected $table = 'amphur';
    protected $primaryKey = 'amphur_id';

    protected $fillable = [
        'amphur_id',
        'amphur_code',
        'name_th',
        'name_en',
        'province_id',
        'active',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'deleted_at',
    ];
}

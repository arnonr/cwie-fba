<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Company extends Model
{

    use HasFactory;
    protected $table = 'company';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'name_th',
        'name_en',
        'tel',
        'fax',
        'email',
        'website',
        'blacklist',
        'comment',
        'namecard_file',
        'address',
        'province_id',
        'amphur_id',
        'tumbol_id',
        'active',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'deleted_at',
    ];
}

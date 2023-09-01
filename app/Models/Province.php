<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Province extends Model
{

    use HasFactory;
    protected $table = 'province';
    protected $primaryKey = 'province_id';

    protected $fillable = [
        'province_id',
        'province_code',
        'name_th',
        'name_en',
        'visit_expense',
        'travel_expense',
        'active',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'deleted_at',
    ];
}

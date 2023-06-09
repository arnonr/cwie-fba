<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PrefixName extends Model
{

    use HasFactory;
    protected $table = 'prefix_name';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'prefix_name',
        'active',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'deleted_at',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NewsCategory extends Model
{

    use HasFactory;
    protected $table = 'news_category';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'name',
        'active',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'deleted_at',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class News extends Model
{

    use HasFactory;
    protected $table = 'news';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'news_title',
        'news_detail',
        'news_file',
        'pinned',
        'news_cate_id',
        'active',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'deleted_at',
    ];
}

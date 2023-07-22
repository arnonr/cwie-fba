<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DocumentDownloadType extends Model
{

    use HasFactory;
    protected $table = 'document_download_type';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'name',
        'description',
        'active',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'deleted_at',
    ];
}

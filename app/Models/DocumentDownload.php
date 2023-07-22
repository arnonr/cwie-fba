<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DocumentDownload extends Model
{

    use HasFactory;
    protected $table = 'document_download';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'document_name',
        'document_file',
        'document_type_id',
        'active',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'deleted_at',
    ];
}

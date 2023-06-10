<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StudentDocument extends Model
{

    use HasFactory;
    protected $table = 'student_document';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'document_name',
        'document_file',
        'student_id',
        'document_type_id',
        'active',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'deleted_at',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Semester extends Model
{

    use HasFactory;
    protected $table = 'semester';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'semester_year',
        'term',
        'round_no',
        'chairman_id',
        'default_request_doc_no',
        'default_request_doc_date',
        'start_date',
        'end_date',
        'regis_start_date',
        'regis_end_date',
        'active',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'deleted_at',
        'is_current'
    ];
}

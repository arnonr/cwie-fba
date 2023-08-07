<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VisitRejectLog extends Model
{

    use HasFactory;
    protected $table = 'visit_reject_log';
    protected $primaryKey = 'log_id';

    protected $fillable = [
        'log_id',
        'comment',
        'user_id',
        'visit_id',
        'reject_status_id',
        'active',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
        'deleted_at',
    ];
}
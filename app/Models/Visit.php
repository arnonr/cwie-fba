<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Visit extends Model
{

    use HasFactory;
    protected $table = 'visit';
    protected $primaryKey = 'visit_id';

    protected $fillable = [
        'visit_id',
        'supervision_id',
        'form_id',
        'visit_date',
        'visit_time',
        'co_name',
        'co_position',
        'document_number',
        'document_date',
        'visit_type',
        'googlemap_url',
        'googlemap_file',
        'approve_document_at',
        'address',
        'province_id',
        'amphur_id',
        'tumbol_id',
        'send_report_at',
        'report_status_id',
        'confirm_report_at',
        'reject_report_comment',
        'visit_detail',
        'report_file',
        'visit_expense',
        'travel_expense',
        'active',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'deleted_at',
        'visit_status',
        'chairman_approved_at',
        'major_head_approve_at',
        'visit_reject_status_id',
        'cancel_description',
        'cancel_file',
        'cancel_at',
    ];
}
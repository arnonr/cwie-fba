<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Form extends Model
{
    use HasFactory;
    protected $table = "form";
    protected $primaryKey = "id";

    protected $fillable = [
        "id",
        "supervision_id",
        "semester_id",
        "student_id",
        "company_id",
        "status_id",
        "start_date",
        "end_date",
        "co_name",
        "co_position",
        "co_tel",
        "co_email",
        "request_name",
        "request_position",
        "request_document_date",
        "request_document_number",
        "max_respone_date",
        "send_document_date",
        "send_document_number",
        "response_document_file",
        "response_send_at",
        "response_province_id",
        "confirm_response_at",
        "workplace_address",
        "workplace_province_id",
        "workplace_amphur_id",
        "workplace_tumbol_id",
        "workplace_googlemap_url",
        "workplace_googlemap_file",
        "plan_document_file",
        "plan_send_at",
        "plan_accept_at",
        "reject_status_id",
        "advisor_verified_at",
        "chairman_approved_at",
        "faculty_confirmed_at",
        "company_rating",
        "rating_comment",
        "next_coop",
        "namecard_file",
        "active",
        "created_at",
        "created_by",
        "updated_at",
        "updated_by",
        "deleted_at",
        "province_id",
        "amphur_id",
        "tumbold_id",
        "is_pass_coop_subject",
        "is_pass_general_subject",
        "is_pass_gpa",
        "is_pass_suspend",
        "is_pass_punishment",
        "is_pass_disease"
    ];

    public function reject_log(): HasMany
    {
        return $this->hasMany(RejectLog::class, "form_id", "id");
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }
}

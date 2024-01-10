<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Config extends Model
{
    use HasFactory;
    protected $table = "config";
    protected $primaryKey = "id";

    protected $fillable = [
        "setting_id",
        "email",
        "password",
        "active",
        "created_at",
        "created_by",
        "updated_at",
        "updated_by",
        "deleted_at",
        "teacher_year_default",
        "supervisor_year_default",
        "staff_year_default",
    ];
}

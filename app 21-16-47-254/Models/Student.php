<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Student extends Model
{

    use HasFactory;
    protected $table = 'student';
    protected $primaryKey = 'student_id';

    protected $fillable = [
        'student_id',
        'student_code',
        'prefix_id',
        'firstname',
        'surname',
        'citizen_id',
        'address',
        'province_id',
        'amphur_id',
        'tumbol_id',
        'tel',
        'email',
        'faculty_id',
        'department_id',
        'major_id',
        'class_year',
        'class_room',
        'advisor_id',
        'gpa',
        'contact1_name',
        'contact1_relation',
        'contact1_tel',
        'contact2_name',
        'contact2_relation',
        'contact2_tel',
        'blood_group',
        'congenital_disease',
        'drug_allergy',
        'emergency_tel',
        'height',
        'weight',
        'active',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'deleted_at',
    ];
}

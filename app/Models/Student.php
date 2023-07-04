<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{

    use HasFactory;
    protected $table = 'student';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
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
        'status_id',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'deleted_at',
    ];

    public function form(): HasMany
    {
        return $this->hasMany(Form::class, 'student_id', 'id');
    }


    // protected static function boot()
    // {
    //     parent::boot();

    //     static::saving(function ($model) {
    //         if (Auth::check()) {
    //             $authId = Auth::id();
    //             $hasCreated = Schema::hasColumn($model->getTable(), 'created_by');
    //             $hasUpdated = Schema::hasColumn($model->getTable(), 'updated_by');
    //             $hasDeleted = Schema::hasColumn($model->getTable(), 'deleted_by');

    //             if ($hasCreated && !$model->exists) {
    //                 $model->created_by = $authId;
    //             }

    //             if ($hasUpdated && $model->exists) {
    //                 $model->updated_by = $authId;
    //             }

    //             if ($hasDeleted && $model->isForceDeleting()) {
    //                 $model->deleted_by = $authId;
    //             }
    //         }
    //     });
    // }
}

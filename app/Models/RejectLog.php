<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RejectLog extends Model
{

    use HasFactory;
    protected $table = 'reject_log';
    protected $primaryKey = 'log_id';

    protected $fillable = [
        'log_id',
        'comment',
        'user_id',
        'form_id',
        'reject_status_id',
        'active',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'deleted_at',
    ];

    public function form():BelongsTo
    {
        return $this->belongsTo(Form::class,'form_id','id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = [
        'subject_code',
        'name',
        'credits',
        'department_id',
        'is_deleted',
    ];

    protected $casts = [
        'credits'    => 'integer',
        'is_deleted' => 'boolean',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function remedialRegistrations()
    {
        return $this->hasMany(RemedialRegistration::class, 'subject_id');
    }
}

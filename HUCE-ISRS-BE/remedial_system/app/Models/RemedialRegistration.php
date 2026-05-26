<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RemedialRegistration extends Model
{
    protected $fillable = [
        'remedial_periods',
        'registration_date',
        'lecture_name',
        'lecturer_phone_number',
        'lecturer_emal',
        'student_id',
        'remedial_term_id',
        'subject_id',
        'is_deleted',
    ];

    protected $casts = [
        'remedial_periods'  => 'integer',
        'registration_date' => 'datetime',
        'is_deleted'        => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('notDeleted', function (Builder $query) {
            $query->where('is_deleted', false);
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function remedialTerm()
    {
        return $this->belongsTo(RemedialTerm::class, 'remedial_term_id');
    }
}

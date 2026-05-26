<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RemedialTerm extends Model
{
    protected $fillable = [
        'name',
        'year',
        'semester',
        'start_date',
        'end_date',
        'registration_start',
        'registration_end',
        'remedial_coefficient',
        'price_per_period',
        'price_coefficient',
        'is_current_term',
        'is_deleted',
    ];

    protected $casts = [
        'year'                 => 'integer',
        'semester'             => 'integer',
        'start_date'           => 'datetime',
        'end_date'             => 'datetime',
        'registration_start'   => 'datetime',
        'registration_end'     => 'datetime',
        'remedial_coefficient' => 'integer',
        'price_per_period'     => 'integer',
        'price_coefficient'    => 'integer',
        'is_current_term'      => 'boolean',
        'is_deleted'           => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('notDeleted', function (Builder $query) {
            $query->where('is_deleted', false);
        });
    }

    public function remedialRegistrations()
    {
        return $this->hasMany(RemedialRegistration::class, 'remedial_term_id');
    }
}

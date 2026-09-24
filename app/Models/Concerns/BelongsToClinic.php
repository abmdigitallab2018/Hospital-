<?php

namespace App\Models\Concerns;

use App\Models\Clinic;
use App\Models\Scopes\ClinicScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait BelongsToClinic
{
    /**
     * Boot the trait.
     */
    protected static function bootBelongsToClinic(): void
    {
        static::addGlobalScope(new ClinicScope());

        static::creating(function ($model) {
            if (empty($model->clinic_id)) {
                if (Auth::check()) {
                    $user = Auth::user();
                    $model->clinic_id = session('active_clinic_id') ?? $user->clinic_id;
                }
            }
        });
    }

    /**
     * Get the clinic that owns the record.
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }
}

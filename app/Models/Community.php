<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'description', 'location', 'invite_code', 'created_by'])]
class Community extends Model
{
    protected $table = 'communities';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->created_by) && auth()->check()) {
                $model->created_by = auth()->id();
            }
        });
    }

    /**
     * Get the members of the community.
     */
    public function members(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the creator of the community.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the listings of the community.
     */
    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }
}

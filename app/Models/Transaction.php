<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

#[Fillable([
    'invoice_number',
    'listing_id',
    'buyer_id',
    'seller_id',
    'price',
    'admin_fee',
    'total_amount',
    'status',
    'payment_status',
    'payment_channel',
    'gateway_reference_id',
    'payment_token',
    'payment_url',
    'completed_at',
    'paid_at',
    'expired_at',
    'raw_response',
])]
class Transaction extends Model
{
    protected $table = 'transactions';

   protected function casts(): array
{
    return [
        'price' => 'decimal:2',
        'admin_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'completed_at' => 'datetime',
        'paid_at' => 'datetime',
        'expired_at' => 'datetime',
        'raw_response' => 'array',
    ];
}

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function buyerReview(): HasOne
    {
        return $this->hasOne(Review::class)->whereColumn('reviewer_id', 'buyer_id');
    }

    public function sellerReview(): HasOne
    {
        return $this->hasOne(Review::class)->whereColumn('reviewer_id', 'seller_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'selesai';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'dibatalkan';
    }

    public function getOtherUser(int $userId): ?User
    {
        if ($this->buyer_id === $userId) {
            return $this->seller;
        }

        return $this->buyer;
    }

    public function userRole(int $userId): string
    {
        return $this->buyer_id === $userId ? 'buyer' : 'seller';
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'selesai',
            'completed_at' => Carbon::now(),
        ]);

        if ($this->listing) {
            $this->listing->update(['status' => 'terjual']);
        }
    }

    public function cancel(): void
    {
        $this->update([
            'status' => 'dibatalkan',
        ]);

        if ($this->listing) {
            $this->listing->update(['status' => 'tersedia']);
        }
    }

    public function userReview(int $userId): ?Review
    {
        return $this->reviews()->where('reviewer_id', $userId)->first();
    }
}

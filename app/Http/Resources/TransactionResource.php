<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'price' => (float) $this->price,
            'admin_fee' => (float) ($this->admin_fee ?? 0),
            'total_amount' => (float) ($this->total_amount ?? $this->price),
            'status' => $this->status,
            'payment_status' => $this->payment_status ?? 'unpaid',
            'payment_channel' => $this->payment_channel,
            'gateway_reference_id' => $this->gateway_reference_id,
            'payment_token' => $this->payment_token,
            'payment_url' => $this->payment_url,
            'paid_at' => $this->paid_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'expired_at' => $this->expired_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),

            // Relations
            'listing' => $this->whenLoaded('listing', function () {
                return [
                    'id' => $this->listing->id,
                    'title' => $this->listing->title,
                    'description' => $this->listing->description,
                    'price' => (float) $this->listing->price,
                    'condition' => $this->listing->condition,
                    'status' => $this->listing->status,
                    'category' => $this->listing->category ? [
                        'id' => $this->listing->category->id,
                        'name' => $this->listing->category->name,
                    ] : null,
                    'images' => $this->listing->images->map(function ($img) {
                        return [
                            'id' => $img->id,
                            'url' => $img->url,
                        ];
                    }),
                ];
            }),

            'buyer' => $this->whenLoaded('buyer', function () {
                return [
                    'id' => $this->buyer->id,
                    'name' => $this->buyer->name,
                    'email' => $this->buyer->email,
                    'phone' => $this->buyer->phone,
                    'avatar' => $this->buyer->avatar,
                    'community' => $this->buyer->community ? [
                        'id' => $this->buyer->community->id,
                        'name' => $this->buyer->community->name,
                    ] : null,
                ];
            }),

            'seller' => $this->whenLoaded('seller', function () {
                return [
                    'id' => $this->seller->id,
                    'name' => $this->seller->name,
                    'email' => $this->seller->email,
                    'phone' => $this->seller->phone,
                    'avatar' => $this->seller->avatar,
                    'average_rating' => (float) $this->seller->averageRating(),
                    'community' => $this->seller->community ? [
                        'id' => $this->seller->community->id,
                        'name' => $this->seller->community->name,
                    ] : null,
                ];
            }),

            'review' => $this->whenLoaded('review', function () {
                if (! $this->review) {
                    return null;
                }
                return [
                    'id' => $this->review->id,
                    'rating' => $this->review->rating,
                    'comment' => $this->review->comment,
                    'created_at' => $this->review->created_at?->toIso8601String(),
                ];
            }),
        ];
    }
}

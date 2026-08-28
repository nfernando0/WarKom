<?php

namespace App\Livewire\Public;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\User;
use App\Models\Conversation;
use Illuminate\Validation\Rule;

#[Layout('layouts.public')]
class Profile extends Component
{
    public User $user;
    public bool $isSelf = false;
    public string $activeTab = 'listings'; // 'listings', 'reviews', 'edit'

    // Profile Edit State
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $address = '';

    public function mount(?User $user = null): void
    {
        if ($user && $user->exists) {
            $this->user = $user;
        } else {
            if (! auth()->check()) {
                session()->flash('info', 'Silakan masuk terlebih dahulu untuk melihat profil Anda.');
                $this->redirectRoute('login');
                return;
            }
            $this->user = auth()->user();
        }

        $this->isSelf = (auth()->id() === $this->user->id);

        $this->name = $this->user->name;
        $this->email = $this->user->email;
        $this->phone = $this->user->phone ?? '';
        $this->address = $this->user->address ?? '';
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function updateProfile(): void
    {
        if (! $this->isSelf) {
            abort(403, 'Unauthorized.');
        }

        $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($this->user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        $this->user->update([
            'name' => trim($this->name),
            'email' => trim($this->email),
            'phone' => trim($this->phone) ?: null,
            'address' => trim($this->address) ?: null,
        ]);

        session()->flash('success', 'Profil Anda berhasil diperbarui.');
    }

    public function render()
    {
        $listings = $this->user->listings()
            ->with(['images', 'category', 'community'])
            ->latest()
            ->get();

        $reviews = $this->user->reviewsReceived()
            ->with(['reviewer', 'transaction'])
            ->latest()
            ->get();

        $stats = [
            'totalListings' => $listings->count(),
            'activeListings' => $listings->where('status', 'tersedia')->count(),
            'totalSales' => $this->user->sellerTransactions()->where('status', 'selesai')->count(),
            'totalPurchases' => $this->user->buyerTransactions()->where('status', 'selesai')->count(),
            'averageRating' => $this->user->averageRating(),
            'reviewCount' => $reviews->count(),
        ];

        return view('livewire.public.profile', [
            'listings' => $listings,
            'reviews' => $reviews,
            'stats' => $stats,
        ])->title(($this->isSelf ? 'Profil Saya' : 'Profil ' . $this->user->name) . ' - WarKom');
    }
}

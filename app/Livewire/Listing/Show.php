<?php

namespace App\Livewire\Listing;

use Livewire\Component;
use App\Models\Listing;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Transaction;

class Show extends Component
{
    public Listing $listing;
    public ?string $quickMessage = 'Halo, apakah barang ini masih tersedia?';
    public int $selectedImageIndex = 0;

    public function mount(Listing $listing): void
    {
        $this->listing = $listing->load(['images', 'category', 'community', 'creator']);
    }

    public function selectImage(int $index): void
    {
        $this->selectedImageIndex = $index;
    }

    public function startChat(): void
    {
        $user = auth()->user();

        if (! $user) {
            $this->redirectRoute('login');
            return;
        }

        // Guard: seller cannot chat with themselves
        if ($this->listing->user_id === $user->id) {
            session()->flash('warning', 'Ini adalah barang yang Anda jual.');
            return;
        }

        // Find or create conversation
        $conversation = Conversation::firstOrCreate(
            [
                'listing_id' => $this->listing->id,
                'buyer_id' => $user->id,
            ],
            [
                'seller_id' => $this->listing->user_id,
            ]
        );

        // If there's a quick message to send and conversation was just created or message is explicitly set
        if (! empty(trim($this->quickMessage))) {
            Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $user->id,
                'body' => trim($this->quickMessage),
                'read_at' => null,
            ]);
            $conversation->touch();
        }

        $this->redirectRoute('chat.index', ['conversation' => $conversation->id]);
    }

    public function createTransaction(): void
    {
        $user = auth()->user();

        if (! $user) {
            $this->redirectRoute('login');
            return;
        }

        if ($this->listing->user_id === $user->id) {
            session()->flash('warning', 'Anda tidak dapat membeli barang milik sendiri.');
            return;
        }

        // Check if there is already an active pending transaction for this buyer and listing
        $existing = Transaction::where('listing_id', $this->listing->id)
            ->where('buyer_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            $this->redirectRoute('transaction.index');
            return;
        }

        $transaction = Transaction::create([
            'listing_id' => $this->listing->id,
            'buyer_id' => $user->id,
            'seller_id' => $this->listing->user_id,
            'price' => $this->listing->price,
            'status' => 'pending',
        ]);

        $this->listing->update(['status' => 'ditahan']);

        // Find or create chat conversation and send system message
        $conversation = Conversation::firstOrCreate(
            [
                'listing_id' => $this->listing->id,
                'buyer_id' => $user->id,
            ],
            [
                'seller_id' => $this->listing->user_id,
            ]
        );

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'body' => "📦 Transaksi baru dibuat untuk barang ini senilai Rp " . number_format($this->listing->price, 0, ',', '.') . " (Status: Menunggu Penyelesaian).",
            'read_at' => null,
        ]);
        $conversation->touch();

        session()->flash('success', 'Transaksi berhasil dibuat! Silakan pantau status transaksi Anda.');
        $this->redirectRoute('transaction.index');
    }

    public function delete(): void
    {
        $user = auth()->user();

        if ($this->listing->user_id !== $user->id && ! $user->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $title = $this->listing->title;
        $this->listing->delete();

        session()->flash('success', "Listing \"{$title}\" berhasil dihapus.");
        $this->redirectRoute('listing.index');
    }

    public function render()
    {
        $otherListings = Listing::with(['images', 'category'])
            ->where('community_id', $this->listing->community_id)
            ->where('id', '!=', $this->listing->id)
            ->where('status', 'tersedia')
            ->latest()
            ->take(4)
            ->get();

        return view('livewire.listing.show', [
            'otherListings' => $otherListings,
            'isOwner' => auth()->id() === $this->listing->user_id,
        ])->title($this->listing->title . ' - Marketplace WarKom');
    }
}

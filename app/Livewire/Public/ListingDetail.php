<?php

namespace App\Livewire\Public;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Listing;
use App\Models\Conversation;
use App\Models\Transaction;

#[Layout('layouts.public')]
class ListingDetail extends Component
{
    public Listing $listing;
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
        if (! auth()->check()) {
            session()->flash('info', 'Silakan masuk terlebih dahulu untuk memulai obrolan dengan penjual.');
            session(['url.intended' => route('public.start-chat', $this->listing)]);
            $this->redirectRoute('login');
            return;
        }

        $user = auth()->user();

        if ($this->listing->user_id === $user->id) {
            session()->flash('info', 'Ini adalah barang yang Anda jual sendiri.');
            return;
        }

        $conversation = Conversation::firstOrCreate(
            [
                'listing_id' => $this->listing->id,
                'buyer_id' => $user->id,
            ],
            [
                'seller_id' => $this->listing->user_id,
            ]
        );

        $this->redirectRoute('chat.index', ['conversation' => $conversation->id]);
    }

    public function buyNow(): void
    {
        if (! auth()->check()) {
            session()->flash('info', 'Silakan masuk terlebih dahulu untuk membeli barang ini.');
            $this->redirectRoute('login');
            return;
        }

        $user = auth()->user();

        if ($this->listing->user_id === $user->id) {
            session()->flash('info', 'Anda tidak dapat membeli barang yang Anda jual sendiri.');
            return;
        }

        if ($this->listing->status !== 'tersedia') {
            session()->flash('error', 'Barang ini sudah tidak tersedia atau sedang dalam proses transaksi.');
            return;
        }

        // Create pending transaction
        $transaction = Transaction::create([
            'listing_id' => $this->listing->id,
            'buyer_id' => $user->id,
            'seller_id' => $this->listing->user_id,
            'price' => $this->listing->price,
            'status' => 'pending',
        ]);

        $this->listing->update(['status' => 'ditahan']);

        // Start or continue conversation
        $conversation = Conversation::firstOrCreate(
            [
                'listing_id' => $this->listing->id,
                'buyer_id' => $user->id,
            ],
            [
                'seller_id' => $this->listing->user_id,
            ]
        );

        $conversation->messages()->create([
            'sender_id' => $user->id,
            'body' => "Halo! Saya ingin membeli produk \"{$this->listing->title}\" seharga Rp " . number_format($this->listing->price, 0, ',', '.') . ". Transaksi #{$transaction->id} telah dibuat.",
        ]);

        session()->flash('success', 'Transaksi berhasil dibuat! Silakan diskusikan teknis pembayaran dan COD melalui chat.');
        $this->redirectRoute('transaction.index');
    }

    public function render()
    {
        $relatedListings = Listing::with(['images', 'category', 'community', 'creator'])
            ->where('category_id', $this->listing->category_id)
            ->where('id', '!=', $this->listing->id)
            ->where('status', 'tersedia')
            ->take(4)
            ->get();

        return view('livewire.public.listing-detail', [
            'relatedListings' => $relatedListings,
        ])->title($this->listing->title . ' - WarKom');
    }
}

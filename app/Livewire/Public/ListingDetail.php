<?php

namespace App\Livewire\Public;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Listing;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.public')]
class ListingDetail extends Component
{
    public Listing $listing;
    public int $selectedImageIndex = 0;

    // Checkout Modal State
    public bool $showCheckoutModal = false;
    public string $paymentChannel = 'cod'; // 'cod', 'qris', 'bank_transfer'
    public string $notes = '';

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
            session(['url.intended' => route('public.listing.show', $this->listing)]);
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

        // Open checkout confirmation modal
        $this->showCheckoutModal = true;
    }

    public function closeCheckoutModal(): void
    {
        $this->showCheckoutModal = false;
    }

    public function confirmPurchase()
    {
        if (! auth()->check()) {
            $this->redirectRoute('login');
            return;
        }

        $user = auth()->user();

        if ($this->listing->user_id === $user->id) {
            session()->flash('error', 'Anda tidak dapat membeli barang yang Anda jual sendiri.');
            $this->showCheckoutModal = false;
            return;
        }

        // Re-check item status with fresh database state
        $freshListing = Listing::find($this->listing->id);
        if (! $freshListing || $freshListing->status !== 'tersedia') {
            session()->flash('error', 'Maaf, barang ini baru saja dipesan oleh pengguna lain.');
            $this->showCheckoutModal = false;
            $this->listing = $freshListing ?? $this->listing;
            return;
        }

        return DB::transaction(function () use ($user) {
            // Generate unique invoice number: INV-YYYYMMDD-XXXXXX
            $date = date('Ymd');
            $random = strtoupper(Str::random(6));
            $invoiceNumber = "INV-{$date}-{$random}";

            while (Transaction::where('invoice_number', $invoiceNumber)->exists()) {
                $random = strtoupper(Str::random(6));
                $invoiceNumber = "INV-{$date}-{$random}";
            }

            $price = (float) $this->listing->price;
            $adminFee = 0.00;
            $totalAmount = $price + $adminFee;

            $transaction = Transaction::create([
                'invoice_number' => $invoiceNumber,
                'listing_id' => $this->listing->id,
                'buyer_id' => $user->id,
                'seller_id' => $this->listing->user_id,
                'price' => $price,
                'admin_fee' => $adminFee,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'payment_status' => $this->paymentChannel === 'qris' ? 'pending' : 'unpaid',
                'payment_channel' => $this->paymentChannel,
                'expired_at' => now()->addDays(2),
            ]);

            // Update listing status to held / booked
            $this->listing->update(['status' => 'ditahan']);

            // Create or get conversation
            $conversation = Conversation::firstOrCreate(
                [
                    'listing_id' => $this->listing->id,
                    'buyer_id' => $user->id,
                ],
                [
                    'seller_id' => $this->listing->user_id,
                ]
            );

            // Send automated message
            $channelLabel = match ($this->paymentChannel) {
                'qris' => 'QRIS (Online)',
                'bank_transfer' => 'Transfer Bank',
                default => 'COD (Bayar di Tempat)',
            };

            $noteText = filled($this->notes) ? "\nCatatan: \"{$this->notes}\"" : "";
            Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $user->id,
                'body' => "Halo! Saya telah membuat pesanan untuk \"{$this->listing->title}\" (Invoice: {$invoiceNumber}) dengan metode {$channelLabel}. Total: Rp " . number_format($totalAmount, 0, ',', '.') . ".{$noteText}",
            ]);

            $conversation->touch();

            session()->flash('success', "Pesanan #{$invoiceNumber} berhasil dibuat! Silakan diskusikan teknis pembayaran dan serah terima barang.");
            
            return $this->redirectRoute('transaction.index', navigate: true);
        });
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

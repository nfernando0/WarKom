<?php

namespace App\Livewire\Transaction;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Transaction;
use App\Models\Review;
use App\Models\Message;
use App\Models\Conversation;

#[Layout('layouts.public')]
class Index extends Component
{
    use WithPagination;

    public string $roleFilter = 'all'; // 'all', 'buying', 'selling'
    public string $statusFilter = 'all'; // 'all', 'pending', 'selesai', 'dibatalkan'
    public string $search = '';

    // Complete Transaction Modal Properties
    public bool $showCompleteModal = false;
    public ?int $completeTransactionId = null;
    public ?Transaction $selectedCompleteTransaction = null;

    // Review Modal Properties
    public bool $showReviewModal = false;
    public ?int $reviewTransactionId = null;
    public int $rating = 5;
    public string $comment = '';

    protected $queryString = [
        'roleFilter' => ['as' => 'role', 'except' => 'all'],
        'statusFilter' => ['as' => 'status', 'except' => 'all'],
        'search' => ['except' => ''],
    ];

    public function updatingRoleFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function setRoleFilter(string $role): void
    {
        if (in_array($role, ['all', 'buying', 'selling'], true)) {
            $this->roleFilter = $role;
            $this->resetPage();
        }
    }

    public function openCompleteModal(int $id): void
    {
        $userId = auth()->id();
        $transaction = Transaction::with(['listing', 'buyer', 'seller'])->findOrFail($id);

        if ($transaction->seller_id !== $userId) {
            abort(403, 'Hanya penjual (pemilik barang) yang dapat menyelesaikan transaksi.');
        }

        if (! $transaction->isPending()) {
            return;
        }

        $this->completeTransactionId = $id;
        $this->selectedCompleteTransaction = $transaction;
        $this->showCompleteModal = true;
    }

    public function closeCompleteModal(): void
    {
        $this->showCompleteModal = false;
        $this->completeTransactionId = null;
        $this->selectedCompleteTransaction = null;
    }

    public function completeTransaction(?int $id = null): void
    {
        $targetId = $id ?? $this->completeTransactionId;
        if (! $targetId) {
            return;
        }

        $userId = auth()->id();
        $transaction = Transaction::with(['listing', 'buyer', 'seller'])->findOrFail($targetId);

        if ($transaction->seller_id !== $userId) {
            abort(403, 'Hanya penjual (pemilik barang) yang dapat menyelesaikan transaksi.');
        }

        if (! $transaction->isPending()) {
            $this->closeCompleteModal();
            return;
        }

        $transaction->markAsCompleted();

        // Send a system message to the chat conversation if exists
        $conversation = Conversation::where('listing_id', $transaction->listing_id)
            ->where('buyer_id', $transaction->buyer_id)
            ->first();

        if ($conversation) {
            Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $userId,
                'body' => "🎉 Transaksi senilai Rp " . number_format($transaction->price, 0, ',', '.') . " telah diselesaikan.",
                'read_at' => null,
            ]);
            $conversation->touch();
        }

        session()->flash('success', "Transaksi untuk \"{$transaction->listing->title}\" telah berhasil diselesaikan!");
        $this->closeCompleteModal();
    }

    public function cancelTransaction(int $id): void
    {
        $userId = auth()->id();
        $transaction = Transaction::with(['listing', 'buyer', 'seller'])->findOrFail($id);

        if ($transaction->buyer_id !== $userId && $transaction->seller_id !== $userId) {
            abort(403, 'Unauthorized.');
        }

        if (! $transaction->isPending()) {
            return;
        }

        $transaction->cancel();

        // Send a system message to the chat conversation if exists
        $conversation = Conversation::where('listing_id', $transaction->listing_id)
            ->where('buyer_id', $transaction->buyer_id)
            ->first();

        if ($conversation) {
            Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $userId,
                'body' => "⚠️ Transaksi untuk barang ini telah dibatalkan.",
                'read_at' => null,
            ]);
            $conversation->touch();
        }

        session()->flash('warning', "Transaksi untuk \"{$transaction->listing->title}\" telah dibatalkan.");
    }

    public function openReviewModal(int $id): void
    {
        $userId = auth()->id();
        $transaction = Transaction::with(['listing', 'buyer', 'seller', 'reviews'])->findOrFail($id);

        if ($transaction->buyer_id !== $userId && $transaction->seller_id !== $userId) {
            abort(403, 'Unauthorized.');
        }

        if (! $transaction->isCompleted()) {
            return;
        }

        // Check if user already reviewed
        $existingReview = $transaction->userReview($userId);
        if ($existingReview) {
            $this->rating = $existingReview->rating;
            $this->comment = $existingReview->comment ?? '';
        } else {
            $this->rating = 5;
            $this->comment = '';
        }

        $this->reviewTransactionId = $id;
        $this->showReviewModal = true;
    }

    public function closeReviewModal(): void
    {
        $this->showReviewModal = false;
        $this->reviewTransactionId = null;
        $this->rating = 5;
        $this->comment = '';
    }

    public function setRating(int $val): void
    {
        if ($val >= 1 && $val <= 5) {
            $this->rating = $val;
        }
    }

    public function submitReview(): void
    {
        $this->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $userId = auth()->id();
        $transaction = Transaction::with(['buyer', 'seller'])->findOrFail($this->reviewTransactionId);

        if ($transaction->buyer_id !== $userId && $transaction->seller_id !== $userId) {
            abort(403, 'Unauthorized.');
        }

        $reviewedUser = $transaction->getOtherUser($userId);

        Review::updateOrCreate(
            [
                'transaction_id' => $transaction->id,
                'reviewer_id' => $userId,
            ],
            [
                'reviewed_user_id' => $reviewedUser->id,
                'rating' => $this->rating,
                'comment' => trim($this->comment),
            ]
        );

        $this->closeReviewModal();
        session()->flash('success', 'Terima kasih! Ulasan & rating Anda telah berhasil dikirim.');
    }

    public function render()
    {
        $userId = auth()->id();

        $query = Transaction::with([
            'listing' => fn($q) => $q->with('images', 'category'),
            'buyer',
            'seller',
            'reviews',
        ])
        ->latest('updated_at');

        // Filter by role
        if ($this->roleFilter === 'buying') {
            $query->where('buyer_id', $userId);
        } elseif ($this->roleFilter === 'selling') {
            $query->where('seller_id', $userId);
        } else {
            $query->where(function ($q) use ($userId) {
                $q->where('buyer_id', $userId)
                  ->orWhere('seller_id', $userId);
            });
        }

        // Filter by status
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        // Filter by search
        if (! empty($this->search)) {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->whereHas('listing', function ($sub) use ($searchTerm) {
                    $sub->where('title', 'like', $searchTerm);
                })
                ->orWhereHas('buyer', function ($sub) use ($searchTerm) {
                    $sub->where('name', 'like', $searchTerm);
                })
                ->orWhereHas('seller', function ($sub) use ($searchTerm) {
                    $sub->where('name', 'like', $searchTerm);
                });
            });
        }

        // Summary counts for current user
        $stats = [
            'total' => Transaction::where(fn($q) => $q->where('buyer_id', $userId)->orWhere('seller_id', $userId))->count(),
            'pending' => Transaction::where(fn($q) => $q->where('buyer_id', $userId)->orWhere('seller_id', $userId))->where('status', 'pending')->count(),
            'completed' => Transaction::where(fn($q) => $q->where('buyer_id', $userId)->orWhere('seller_id', $userId))->where('status', 'selesai')->count(),
            'cancelled' => Transaction::where(fn($q) => $q->where('buyer_id', $userId)->orWhere('seller_id', $userId))->where('status', 'dibatalkan')->count(),
        ];

        return view('livewire.transaction.index', [
            'transactions' => $query->paginate(10),
            'stats' => $stats,
            'currentUserId' => $userId,
        ])->title('Riwayat Transaksi - WarKom');
    }
}

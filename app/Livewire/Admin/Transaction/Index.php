<?php

namespace App\Livewire\Admin\Transaction;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Transaction;
use App\Models\Community;
use Illuminate\Support\Carbon;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = 'all'; // 'all', 'pending', 'selesai', 'dibatalkan'
    public string $paymentStatusFilter = 'all'; // 'all', 'unpaid', 'pending', 'settlement', 'expired', 'failed', 'refunded'
    public string $selectedCommunity = '';
    public string $sortBy = 'latest';

    // Detail Modal State
    public bool $showDetailModal = false;
    public ?int $selectedTransactionId = null;

    // Status Override Modal State
    public bool $showStatusModal = false;
    public ?int $editingTransactionId = null;
    public string $newStatus = 'pending';
    public string $newPaymentStatus = 'unpaid';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['as' => 'status', 'except' => 'all'],
        'paymentStatusFilter' => ['as' => 'payment', 'except' => 'all'],
        'selectedCommunity' => ['as' => 'comm', 'except' => ''],
        'sortBy' => ['except' => 'latest'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingPaymentStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedCommunity(): void
    {
        $this->resetPage();
    }

    public function updatingSortBy(): void
    {
        $this->resetPage();
    }

    public function setStatusFilter(string $status): void
    {
        $this->statusFilter = $status;
        $this->resetPage();
    }

    public function setPaymentStatusFilter(string $paymentStatus): void
    {
        $this->paymentStatusFilter = $paymentStatus;
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'statusFilter', 'paymentStatusFilter', 'selectedCommunity', 'sortBy']);
        $this->resetPage();
    }

    public function viewDetail(int $id): void
    {
        $this->selectedTransactionId = $id;
        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->selectedTransactionId = null;
    }

    public function openStatusModal(int $id): void
    {
        $tx = Transaction::findOrFail($id);
        $this->editingTransactionId = $tx->id;
        $this->newStatus = $tx->status;
        $this->newPaymentStatus = $tx->payment_status ?? 'unpaid';
        $this->showStatusModal = true;
    }

    public function closeStatusModal(): void
    {
        $this->showStatusModal = false;
        $this->editingTransactionId = null;
    }

    public function updateStatus(): void
    {
        if (! $this->editingTransactionId) {
            return;
        }

        $tx = Transaction::findOrFail($this->editingTransactionId);

        $updates = [
            'status' => $this->newStatus,
            'payment_status' => $this->newPaymentStatus,
        ];

        if ($this->newStatus === 'selesai' && ! $tx->completed_at) {
            $updates['completed_at'] = now();
            $tx->listing?->update(['status' => 'terjual']);
        } elseif ($this->newStatus === 'dibatalkan') {
            $tx->listing?->update(['status' => 'tersedia']);
        } elseif ($this->newStatus === 'pending') {
            $tx->listing?->update(['status' => 'ditahan']);
        }

        if ($this->newPaymentStatus === 'settlement' && ! $tx->paid_at) {
            $updates['paid_at'] = now();
        }

        $tx->update($updates);

        $this->closeStatusModal();
        session()->flash('success', "Status transaksi #{$tx->invoice_number} berhasil diperbarui oleh Admin.");
    }

    public function markAsSettlement(int $id): void
    {
        $tx = Transaction::findOrFail($id);
        $tx->update([
            'payment_status' => 'settlement',
            'paid_at' => now(),
        ]);

        session()->flash('success', "Pembayaran untuk transaksi #{$tx->invoice_number} ditandai Lunas (Settlement).");
    }

    public function markAsCompleted(int $id): void
    {
        $tx = Transaction::findOrFail($id);
        $tx->update([
            'status' => 'selesai',
            'completed_at' => now(),
            'payment_status' => $tx->payment_status === 'unpaid' ? 'settlement' : $tx->payment_status,
            'paid_at' => $tx->paid_at ?? now(),
        ]);

        $tx->listing?->update(['status' => 'terjual']);

        session()->flash('success', "Transaksi #{$tx->invoice_number} berhasil ditandai Selesai.");
    }

    public function markAsCancelled(int $id): void
    {
        $tx = Transaction::findOrFail($id);
        $tx->update([
            'status' => 'dibatalkan',
            'payment_status' => in_array($tx->payment_status, ['settlement', 'pending']) ? 'refunded' : 'failed',
        ]);

        $tx->listing?->update(['status' => 'tersedia']);

        session()->flash('success', "Transaksi #{$tx->invoice_number} dibatalkan dan barang telah dipulihkan.");
    }

    public function render()
    {
        // Admin Summary Metrics
        $stats = [
            'total_count' => Transaction::count(),
            'total_gmv' => (float) Transaction::whereIn('payment_status', ['settlement'])->orWhere('status', 'selesai')->sum('total_amount'),
            'selesai_count' => Transaction::where('status', 'selesai')->count(),
            'pending_payment_count' => Transaction::whereIn('payment_status', ['unpaid', 'pending'])->where('status', '!=', 'dibatalkan')->count(),
            'cancelled_count' => Transaction::where('status', 'dibatalkan')->orWhere('payment_status', 'failed')->count(),
        ];

        // Query Builder
        $query = Transaction::with(['listing.images', 'listing.category', 'buyer.community', 'seller.community']);

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('id', $search)
                  ->orWhereHas('listing', function ($lq) use ($search) {
                      $lq->where('title', 'like', "%{$search}%");
                  })
                  ->orWhereHas('buyer', function ($bq) use ($search) {
                      $bq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('seller', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        if ($this->paymentStatusFilter !== 'all') {
            $query->where('payment_status', $this->paymentStatusFilter);
        }

        if ($this->selectedCommunity) {
            $commId = $this->selectedCommunity;
            $query->where(function ($q) use ($commId) {
                $q->whereHas('buyer', fn ($bq) => $bq->where('community_id', $commId))
                  ->orWhereHas('seller', fn ($sq) => $sq->where('community_id', $commId));
            });
        }

        switch ($this->sortBy) {
            case 'oldest':
                $query->oldest();
                break;
            case 'amount_desc':
                $query->orderByDesc('total_amount');
                break;
            case 'amount_asc':
                $query->orderBy('total_amount');
                break;
            default:
                $query->latest();
                break;
        }

        $transactions = $query->paginate(15);
        $communities = Community::orderBy('name')->get();

        $selectedTransaction = $this->selectedTransactionId
            ? Transaction::with(['listing.images', 'listing.category', 'buyer.community', 'seller.community'])->find($this->selectedTransactionId)
            : null;

        return view('livewire.admin.transaction.index', [
            'transactions' => $transactions,
            'stats' => $stats,
            'communities' => $communities,
            'selectedTransaction' => $selectedTransaction,
        ]);
    }
}

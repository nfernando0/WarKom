<?php

namespace App\Livewire\Listing;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Listing;

#[Layout('layouts.public')]
class MyListings extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = 'all'; // 'all', 'tersedia', 'ditahan', 'terjual'

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['as' => 'status', 'except' => 'all'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function setStatusFilter(string $status): void
    {
        $this->statusFilter = $status;
        $this->resetPage();
    }

    public function updateStatus(int $listingId, string $newStatus): void
    {
        if (! in_array($newStatus, ['tersedia', 'ditahan', 'terjual'], true)) {
            return;
        }

        $listing = Listing::where('id', $listingId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $listing->update(['status' => $newStatus]);

        session()->flash('success', 'Status barang "' . $listing->title . '" berhasil diubah menjadi ' . ucfirst($newStatus) . '.');
    }

    public function deleteListing(int $listingId): void
    {
        $listing = Listing::where('id', $listingId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $title = $listing->title;
        $listing->delete();

        session()->flash('success', 'Listing barang "' . $title . '" berhasil dihapus.');
    }

    public function render()
    {
        $userId = auth()->id();

        $stats = [
            'total' => Listing::where('user_id', $userId)->count(),
            'tersedia' => Listing::where('user_id', $userId)->where('status', 'tersedia')->count(),
            'ditahan' => Listing::where('user_id', $userId)->where('status', 'ditahan')->count(),
            'terjual' => Listing::where('user_id', $userId)->where('status', 'terjual')->count(),
        ];

        $query = Listing::where('user_id', $userId)
            ->with(['category', 'images', 'community'])
            ->latest();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        $listings = $query->paginate(12);

        return view('livewire.listing.my-listings', [
            'listings' => $listings,
            'stats' => $stats,
        ])->title('Listing Saya - WarKom');
    }
}

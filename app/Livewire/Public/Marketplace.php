<?php

namespace App\Livewire\Public;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Listing;
use App\Models\Category;
use App\Models\Community;
use App\Models\Conversation;

#[Layout('layouts.public')]
class Marketplace extends Component
{
    use WithPagination;

    public string $search = '';
    public string $selectedCategory = '';
    public string $selectedCommunity = '';
    public string $selectedCondition = '';
    public string $sortBy = 'latest';

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedCategory' => ['as' => 'cat', 'except' => ''],
        'selectedCommunity' => ['as' => 'community', 'except' => ''],
        'selectedCondition' => ['as' => 'condition', 'except' => ''],
        'sortBy' => ['as' => 'sort', 'except' => 'latest'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedCategory(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedCommunity(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedCondition(): void
    {
        $this->resetPage();
    }

    public function updatingSortBy(): void
    {
        $this->resetPage();
    }

    public function selectCategory(string $catId): void
    {
        $this->selectedCategory = ($this->selectedCategory === $catId) ? '' : $catId;
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'selectedCategory', 'selectedCommunity', 'selectedCondition', 'sortBy']);
        $this->resetPage();
    }

    public function startChat(int $listingId): void
    {
        $listing = Listing::findOrFail($listingId);

        if (! auth()->check()) {
            session()->flash('info', 'Silakan masuk atau daftar akun terlebih dahulu untuk mengirim pesan ke penjual.');
            session(['url.intended' => route('public.start-chat', $listing)]);
            $this->redirectRoute('login');
            return;
        }

        $user = auth()->user();

        if ($listing->user_id === $user->id) {
            session()->flash('info', 'Ini adalah barang yang Anda jual sendiri.');
            return;
        }

        $conversation = Conversation::firstOrCreate(
            [
                'listing_id' => $listing->id,
                'buyer_id' => $user->id,
            ],
            [
                'seller_id' => $listing->user_id,
            ]
        );

        $this->redirectRoute('chat.index', ['conversation' => $conversation->id]);
    }

    public function render()
    {
        $query = Listing::with(['category', 'community', 'creator', 'images'])
            ->where('status', 'tersedia');

        // Sorting
        if ($this->sortBy === 'price_asc') {
            $query->orderBy('price', 'asc');
        } elseif ($this->sortBy === 'price_desc') {
            $query->orderBy('price', 'desc');
        } else {
            $query->latest();
        }

        if (! empty($this->search)) {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                  ->orWhere('description', 'like', $searchTerm);
            });
        }

        if (! empty($this->selectedCategory)) {
            $query->where('category_id', $this->selectedCategory);
        }

        if (! empty($this->selectedCommunity)) {
            $query->where('community_id', $this->selectedCommunity);
        }

        if (! empty($this->selectedCondition)) {
            $query->where('condition', $this->selectedCondition);
        }

        return view('livewire.public.marketplace', [
            'listings' => $query->paginate(12),
            'categories' => Category::withCount('listings')->orderBy('name')->get(),
            'communities' => Community::withCount('listings')->orderBy('name')->get(),
        ])->title('Marketplace Publik - WarKom');
    }
}

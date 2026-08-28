<?php

namespace App\Livewire\Listing;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Listing;
use App\Models\Category;
use App\Models\Conversation;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $selectedCategory = '';
    public string $selectedCondition = '';
    public string $selectedStatus = 'tersedia';
    public string $scope = 'community'; // 'community' or 'all'
    public string $sortBy = 'latest'; // 'latest', 'price_asc', 'price_desc'

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedCategory' => ['as' => 'cat', 'except' => ''],
        'selectedCondition' => ['except' => ''],
        'selectedStatus' => ['except' => 'tersedia'],
        'scope' => ['except' => 'community'],
        'sortBy' => ['except' => 'latest'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedCategory(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedCondition(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedStatus(): void
    {
        $this->resetPage();
    }

    public function updatingScope(): void
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
        $this->reset(['search', 'selectedCategory', 'selectedCondition']);
        $this->selectedStatus = 'tersedia';
        $this->scope = 'community';
        $this->sortBy = 'latest';
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $listing = Listing::findOrFail($id);
        $user = auth()->user();

        if ($listing->user_id !== $user->id && ! $user->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $title = $listing->title;
        $listing->delete();

        session()->flash('success', "Listing \"{$title}\" berhasil dihapus.");
    }

    public function startChat(int $id): void
    {
        $user = auth()->user();
        if (! $user) {
            $this->redirectRoute('login');
            return;
        }

        $listing = Listing::findOrFail($id);

        if ($listing->user_id === $user->id) {
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
        $user = auth()->user();

        $query = Listing::with(['category', 'community', 'creator', 'images']);

        // Sorting
        if ($this->sortBy === 'price_asc') {
            $query->orderBy('price', 'asc');
        } elseif ($this->sortBy === 'price_desc') {
            $query->orderBy('price', 'desc');
        } else {
            $query->latest();
        }

        // Scope to user's community unless toggled to all
        if ($this->scope === 'community' && $user && $user->community_id) {
            $query->where('community_id', $user->community_id);
        }

        if (! empty($this->search)) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        if (! empty($this->selectedCategory)) {
            $query->where('category_id', $this->selectedCategory);
        }

        if (! empty($this->selectedCondition)) {
            $query->where('condition', $this->selectedCondition);
        }

        if (! empty($this->selectedStatus) && $this->selectedStatus !== 'all') {
            $query->where('status', $this->selectedStatus);
        }

        return view('livewire.listing.index', [
            'listings' => $query->paginate(12),
            'categories' => Category::withCount('listings')->orderBy('name')->get(),
            'userCommunity' => $user?->community,
        ])->title('Marketplace - WarKom');
    }
}

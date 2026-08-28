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
    public string $selectedStatus = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedCategory' => ['except' => ''],
        'selectedCondition' => ['except' => ''],
        'selectedStatus' => ['except' => ''],
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

    public function resetFilters(): void
    {
        $this->reset(['search', 'selectedCategory', 'selectedCondition', 'selectedStatus']);
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

        session()->flash('success', "Listing \"{$title}\" has been deleted.");
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

        $query = Listing::with(['category', 'community', 'creator', 'images'])
            ->latest();

        // Scope to user's community if member has a community
        if ($user && $user->community_id) {
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

        if (! empty($this->selectedStatus)) {
            $query->where('status', $this->selectedStatus);
        }

        return view('livewire.listing.index', [
            'listings' => $query->paginate(12),
            'categories' => Category::orderBy('name')->get(),
            'userCommunity' => $user?->community,
        ])->title('Marketplace - WarKom');
    }
}

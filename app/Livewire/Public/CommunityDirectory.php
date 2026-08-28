<?php

namespace App\Livewire\Public;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Community;

#[Layout('layouts.public')]
class CommunityDirectory extends Component
{
    public string $search = '';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function render()
    {
        $query = Community::with(['creator', 'listings' => function ($q) {
            $q->with('images')->where('status', 'tersedia')->latest()->take(3);
        }])
        ->withCount(['members', 'listings'])
        ->latest();

        if (! empty($this->search)) {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('description', 'like', $searchTerm)
                  ->orWhere('location', 'like', $searchTerm);
            });
        }

        return view('livewire.public.community-directory', [
            'communities' => $query->get(),
        ])->title('Direktori Komunitas - WarKom');
    }
}

<?php

namespace App\Livewire\Public;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Category;

#[Layout('layouts.public')]
class CategoryDirectory extends Component
{
    public string $search = '';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function render()
    {
        $query = Category::withCount('listings')
            ->with(['listings' => function ($q) {
                $q->with('images')->where('status', 'tersedia')->latest()->take(4);
            }])
            ->orderBy('name');

        if (! empty($this->search)) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        return view('livewire.public.category-directory', [
            'categories' => $query->get(),
        ])->title('Kategori Barang - WarKom');
    }
}

<?php

namespace App\Livewire\Category;

use Livewire\Component;
use App\Models\Category;

class Index extends Component
{
    public string $search = '';

    // Admin category modal state
    public bool $showModal = false;
    public ?int $editingCategoryId = null;
    public string $name = '';
    public string $icon = 'tag';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    protected array $availableIcons = [
        'tag' => 'Tag / Umum',
        'device-phone-mobile' => 'Elektronik / Gadget',
        'cake' => 'Makanan & Minuman',
        'home' => 'Rumah Tangga',
        'sparkles' => 'Hobi & Hiburan',
        'truck' => 'Kendaraan & Otomotif',
        'briefcase' => 'Jasa & Layanan',
        'shopping-bag' => 'Belanja & Retail',
        'book-open' => 'Buku & Edukasi',
        'heart' => 'Kesehatan & Kecantikan',
        'wrench' => 'Perkakas & Alat',
    ];

    public function openCreateModal(): void
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $this->editingCategoryId = null;
        $this->name = '';
        $this->icon = 'tag';
        $this->resetErrorBag();
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $category = Category::findOrFail($id);
        $this->editingCategoryId = $category->id;
        $this->name = $category->name;
        $this->icon = $category->icon ?: 'tag';
        $this->resetErrorBag();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->editingCategoryId = null;
        $this->name = '';
        $this->icon = 'tag';
    }

    public function saveCategory(): void
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $this->validate([
            'name' => 'required|string|min:2|max:100|unique:categories,name,' . $this->editingCategoryId,
            'icon' => 'nullable|string|max:50',
        ]);

        if ($this->editingCategoryId) {
            $cat = Category::findOrFail($this->editingCategoryId);
            $cat->update([
                'name' => trim($this->name),
                'icon' => $this->icon ?: 'tag',
            ]);
            session()->flash('success', "Kategori \"{$cat->name}\" berhasil diperbarui.");
        } else {
            $cat = Category::create([
                'name' => trim($this->name),
                'icon' => $this->icon ?: 'tag',
            ]);
            session()->flash('success', "Kategori baru \"{$cat->name}\" berhasil ditambahkan.");
        }

        $this->closeModal();
    }

    public function deleteCategory(int $id): void
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $category = Category::withCount('listings')->findOrFail($id);

        if ($category->listings_count > 0) {
            session()->flash('error', "Kategori \"{$category->name}\" tidak dapat dihapus karena masih memiliki {$category->listings_count} barang aktif.");
            return;
        }

        $name = $category->name;
        $category->delete();

        session()->flash('success', "Kategori \"{$name}\" berhasil dihapus.");
    }

    public function render()
    {
        $query = Category::withCount('listings')
            ->with(['listings' => function ($q) {
                $q->with('images')->latest()->take(3);
            }])
            ->orderBy('name');

        if (! empty($this->search)) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        return view('livewire.category.index', [
            'categories' => $query->get(),
            'availableIcons' => $this->availableIcons,
            'isAdmin' => auth()->user()?->isAdmin() ?? false,
        ])->title('Kategori Barang - WarKom');
    }
}

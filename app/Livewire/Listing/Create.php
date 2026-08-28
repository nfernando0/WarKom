<?php

namespace App\Livewire\Listing;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use App\Models\Listing;
use App\Models\Category;
use App\Models\ListingImage;

#[Layout('layouts.public')]
class Create extends Component
{
    use WithFileUploads;

    public string $title = '';
    public string $description = '';
    public string $price = '';
    public string $category_id = '';
    public string $condition = 'baru';
    public array $images = [];

    protected array $rules = [
        'title' => 'required|string|min:3|max:255',
        'description' => 'required|string|min:5',
        'price' => 'required|numeric|min:0',
        'category_id' => 'required|exists:categories,id',
        'condition' => 'required|in:baru,bekas',
        'images.*' => 'nullable|image|max:3072',
    ];

    protected array $messages = [
        'title.required' => 'Judul listing wajib diisi.',
        'title.min' => 'Judul minimal 3 karakter.',
        'description.required' => 'Deskripsi listing wajib diisi.',
        'description.min' => 'Deskripsi minimal 5 karakter.',
        'price.required' => 'Harga wajib diisi.',
        'price.numeric' => 'Harga harus berupa angka.',
        'price.min' => 'Harga minimal 0.',
        'category_id.required' => 'Kategori wajib dipilih.',
        'category_id.exists' => 'Kategori tidak valid.',
        'condition.required' => 'Kondisi barang wajib dipilih.',
        'condition.in' => 'Kondisi harus Baru atau Bekas.',
        'images.*.image' => 'File harus berupa gambar.',
        'images.*.max' => 'Ukuran gambar maksimal 3MB.',
    ];

    public function removeImage(int $index): void
    {
        array_splice($this->images, $index, 1);
    }

    public function save(): void
    {
        $user = auth()->user();

        if (! $user->community_id) {
            $this->addError('community', 'Anda harus bergabung dengan komunitas terlebih dahulu untuk membuat listing.');
            return;
        }

        $validated = $this->validate();

        $listing = Listing::create([
            'user_id' => $user->id,
            'community_id' => $user->community_id,
            'category_id' => $this->category_id,
            'title' => $this->title,
            'description' => $this->description,
            'price' => $this->price,
            'condition' => $this->condition,
            'status' => 'tersedia',
        ]);

        if (! empty($this->images)) {
            foreach ($this->images as $index => $image) {
                $path = $image->store('listings', 'public');
                ListingImage::create([
                    'listing_id' => $listing->id,
                    'image_path' => $path,
                    'order' => $index,
                ]);
            }
        }

        session()->flash('success', 'Listing berhasil dibuat.');

        $this->redirectRoute('listing.index');
    }

    public function render()
    {
        if (Category::count() === 0) {
            $defaultCategories = [
                ['name' => 'Elektronik', 'icon' => 'device-phone-mobile'],
                ['name' => 'Pakaian & Mode', 'icon' => 'tag'],
                ['name' => 'Makanan & Minuman', 'icon' => 'cake'],
                ['name' => 'Rumah Tangga', 'icon' => 'home'],
                ['name' => 'Hobi & Hiburan', 'icon' => 'sparkles'],
                ['name' => 'Kendaraan & Otomotif', 'icon' => 'truck'],
                ['name' => 'Jasa & Lainnya', 'icon' => 'briefcase'],
            ];

            foreach ($defaultCategories as $cat) {
                Category::firstOrCreate(['name' => $cat['name']], $cat);
            }
        }

        return view('livewire.listing.create', [
            'categories' => Category::orderBy('name')->get(),
            'userCommunity' => auth()->user()?->community,
        ])->title('Buat Listing Baru - WarKom');
    }
}

<?php

namespace App\Livewire\Listing;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Listing;
use App\Models\Category;
use App\Models\ListingImage;
use Illuminate\Support\Facades\Storage;

class Edit extends Component
{
    use WithFileUploads;

    public Listing $listing;
    public string $title = '';
    public string $description = '';
    public string $price = '';
    public string $category_id = '';
    public string $condition = 'baru';
    public string $status = 'tersedia';
    public array $newImages = [];

    protected array $rules = [
        'title' => 'required|string|min:3|max:255',
        'description' => 'required|string|min:5',
        'price' => 'required|numeric|min:0',
        'category_id' => 'required|exists:categories,id',
        'condition' => 'required|in:baru,bekas',
        'status' => 'required|in:tersedia,ditahan,terjual',
        'newImages.*' => 'nullable|image|max:3072',
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
        'status.required' => 'Status barang wajib dipilih.',
        'status.in' => 'Status tidak valid.',
        'newImages.*.image' => 'File harus berupa gambar.',
        'newImages.*.max' => 'Ukuran gambar maksimal 3MB.',
    ];

    public function mount(Listing $listing): void
    {
        // Strict guard: only the creator can edit this listing
        if ($listing->user_id !== auth()->id()) {
            abort(403, 'Hanya pembuat listing yang dapat mengedit listing ini.');
        }

        $this->listing = $listing;
        $this->title = $listing->title;
        $this->description = $listing->description;
        $this->price = (string) (int) $listing->price;
        $this->category_id = (string) $listing->category_id;
        $this->condition = $listing->condition;
        $this->status = $listing->status;
    }

    public function removeNewImage(int $index): void
    {
        array_splice($this->newImages, $index, 1);
    }

    public function deleteExistingImage(int $imageId): void
    {
        if ($this->listing->user_id !== auth()->id()) {
            abort(403, 'Unauthorized.');
        }

        $image = ListingImage::where('listing_id', $this->listing->id)->findOrFail($imageId);

        if (Storage::disk('public')->exists($image->image_path)) {
            Storage::disk('public')->delete($image->image_path);
        }

        $image->delete();
        $this->listing->refresh();

        session()->flash('image_success', 'Foto berhasil dihapus.');
    }

    public function update(): void
    {
        // Guard check again
        if ($this->listing->user_id !== auth()->id()) {
            abort(403, 'Hanya pembuat listing yang dapat mengedit listing ini.');
        }

        $this->validate();

        $this->listing->update([
            'category_id' => $this->category_id,
            'title' => $this->title,
            'description' => $this->description,
            'price' => $this->price,
            'condition' => $this->condition,
            'status' => $this->status,
        ]);

        if (! empty($this->newImages)) {
            $lastOrder = $this->listing->images()->max('order') ?? 0;

            foreach ($this->newImages as $index => $image) {
                $path = $image->store('listings', 'public');
                ListingImage::create([
                    'listing_id' => $this->listing->id,
                    'image_path' => $path,
                    'order' => $lastOrder + $index + 1,
                ]);
            }
        }

        session()->flash('success', "Listing \"{$this->listing->title}\" berhasil diperbarui.");

        $this->redirectRoute('listing.index');
    }

    public function render()
    {
        return view('livewire.listing.edit', [
            'categories' => Category::orderBy('name')->get(),
            'existingImages' => $this->listing->images()->get(),
        ])->title('Edit Listing - WarKom');
    }
}

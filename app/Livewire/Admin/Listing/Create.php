<?php

namespace App\Livewire\Admin\Listing;

use App\Models\Category;
use App\Models\Community;
use App\Models\Listing;
use App\Models\ListingImage;
use Livewire\Component;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;

    public string $title = '';
    public string $description = '';
    public string $price = '';
    public string $condition = 'bekas';
    public string $status = 'tersedia';
    public ?int $category_id = null;
    public ?int $community_id = null;

    /** @var array<\Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $images = [];

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'community_id' => ['nullable', 'integer', 'exists:communities,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'condition' => ['required', 'in:baru,bekas'],
            'status' => ['required', 'in:tersedia,ditahan,terjual'],
            'description' => ['nullable', 'string', 'max:5000'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['image', 'max:3072'], // Max 3MB per photo
        ];
    }

    protected function messages(): array
    {
        return [
            'title.required' => 'Judul barang wajib diisi.',
            'category_id.required' => 'Kategori barang wajib dipilih.',
            'category_id.exists' => 'Kategori yang dipilih tidak valid.',
            'price.required' => 'Harga barang wajib diisi.',
            'price.numeric' => 'Harga harus berupa angka yang valid.',
            'price.min' => 'Harga tidak boleh negatif.',
            'images.max' => 'Maksimal 5 foto barang.',
            'images.*.image' => 'File harus berupa gambar (JPG, PNG, WEBP).',
            'images.*.max' => 'Ukuran foto maksimal 3MB per file.',
        ];
    }

    public function mount(): void
    {
        $this->community_id = auth()->user()->community_id;
    }

    public function removeImage(int $index): void
    {
        unset($this->images[$index]);
        $this->images = array_values($this->images);
    }

    public function save()
    {
        $this->validate();

        $user = auth()->user();

        $listing = Listing::create([
            'user_id' => $user->id,
            'community_id' => $this->community_id ?: $user->community_id,
            'category_id' => $this->category_id,
            'title' => trim($this->title),
            'description' => trim($this->description),
            'price' => (float) $this->price,
            'condition' => $this->condition,
            'status' => $this->status,
        ]);

        if (! empty($this->images)) {
            foreach ($this->images as $index => $image) {
                $path = $image->store('listings', 'public');

                ListingImage::create([
                    'listing_id' => $listing->id,
                    'image_path' => $path,
                    'is_primary' => $index === 0,
                ]);
            }
        }

        session()->flash('success', "Listing barang \"{$listing->title}\" berhasil dibuat!");

        return $this->redirect(route('listing.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.listing.create', [
            'categories' => Category::orderBy('name')->get(),
            'communities' => Community::orderBy('name')->get(),
        ]);
    }
}

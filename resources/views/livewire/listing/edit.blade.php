<div>
    {{-- Breadcrumbs --}}
    <div class="bg-zinc-200 dark:bg-zinc-800 p-4 rounded-xl border border-zinc-300 dark:border-zinc-700">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item :href="route('dashboard')" wire:navigate>Home</flux:breadcrumbs.item>
            <flux:breadcrumbs.item :href="route('listing.index')" wire:navigate>Marketplace</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Edit Listing</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </div>

    {{-- Notifications --}}
    @if (session()->has('image_success'))
        <div class="mt-4">
            <flux:callout variant="success" icon="check-circle" :heading="session('image_success')" />
        </div>
    @endif

    <div class="mt-6 max-w-3xl">
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
            <div>
                <flux:heading size="xl">Edit Listing</flux:heading>
                <flux:subheading>
                    Perbarui informasi barang yang Anda jual di komunitas <strong>{{ $listing->community?->name }}</strong>.
                </flux:subheading>
            </div>

            <form wire:submit="update" class="mt-6 space-y-5">
                {{-- Title --}}
                <flux:field>
                    <flux:label>Judul Listing</flux:label>
                    <flux:input wire:model="title" placeholder="Contoh: Monitor Gaming 24 Inch 144Hz" />
                    <flux:error name="title" />
                </flux:field>

                {{-- Category, Condition & Status Grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    {{-- Category --}}
                    <flux:field>
                        <flux:label>Kategori</flux:label>
                        <flux:select wire:model="category_id" placeholder="Pilih Kategori">
                            @foreach ($categories as $category)
                                <flux:select.option value="{{ $category->id }}">{{ $category->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="category_id" />
                    </flux:field>

                    {{-- Condition --}}
                    <flux:field>
                        <flux:label>Kondisi Barang</flux:label>
                        <flux:select wire:model="condition">
                            <flux:select.option value="baru">Baru</flux:select.option>
                            <flux:select.option value="bekas">Bekas</flux:select.option>
                        </flux:select>
                        <flux:error name="condition" />
                    </flux:field>

                    {{-- Status --}}
                    <flux:field>
                        <flux:label>Status Ketersediaan</flux:label>
                        <flux:select wire:model="status">
                            <flux:select.option value="tersedia">Tersedia</flux:select.option>
                            <flux:select.option value="ditahan">Ditahan (Booked)</flux:select.option>
                            <flux:select.option value="terjual">Terjual (Sold)</flux:select.option>
                        </flux:select>
                        <flux:error name="status" />
                    </flux:field>
                </div>

                {{-- Price --}}
                <flux:field>
                    <flux:label>Harga (Rp)</flux:label>
                    <flux:input wire:model="price" type="number" min="0" step="1000" placeholder="Contoh: 250000" icon="banknotes" />
                    <flux:error name="price" />
                </flux:field>

                {{-- Description --}}
                <flux:field>
                    <flux:label>Deskripsi Lengkap</flux:label>
                    <flux:textarea wire:model="description" rows="4" placeholder="Jelaskan spesifikasi, kondisi fisik, kelengkapan, minus, atau alasan dijual secara rinci..." />
                    <flux:error name="description" />
                </flux:field>

                {{-- Existing Images Gallery --}}
                @if ($existingImages->isNotEmpty())
                    <div>
                        <flux:label class="mb-2 block">Foto Saat Ini ({{ $existingImages->count() }})</flux:label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            @foreach ($existingImages as $img)
                                <div wire:key="existing-img-{{ $img->id }}" class="relative group rounded-lg overflow-hidden border border-zinc-200 dark:border-zinc-700 aspect-square bg-zinc-100 dark:bg-zinc-800">
                                    <img src="{{ $img->url }}" class="w-full h-full object-cover" />
                                    <button
                                        type="button"
                                        wire:click="deleteExistingImage({{ $img->id }})"
                                        wire:confirm="Yakin ingin menghapus foto ini?"
                                        class="absolute top-1.5 right-1.5 p-1 rounded-full bg-red-600 hover:bg-red-700 text-white transition shadow-sm"
                                        title="Hapus foto"
                                    >
                                        <flux:icon name="trash" class="size-3.5" />
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Add New Images --}}
                <flux:field>
                    <flux:label>Tambah Foto Baru (Opsional)</flux:label>
                    <flux:input type="file" wire:model="newImages" multiple accept="image/png,image/jpeg,image/jpg,image/webp" />
                    <flux:error name="newImages.*" />
                    <flux:error name="newImages" />

                    {{-- Upload Progress Indicator --}}
                    <div wire:loading wire:target="newImages" class="mt-2 text-xs text-primary-600 dark:text-primary-400 font-medium">
                        Sedang mengunggah foto baru...
                    </div>

                    {{-- Preview new uploaded images --}}
                    @if (! empty($newImages))
                        <div class="mt-3 grid grid-cols-2 sm:grid-cols-4 gap-3">
                            @foreach ($newImages as $index => $img)
                                <div class="relative group rounded-lg overflow-hidden border border-zinc-200 dark:border-zinc-700 aspect-square bg-zinc-100 dark:bg-zinc-800">
                                    <img src="{{ $img->temporaryUrl() }}" class="w-full h-full object-cover" />
                                    <button
                                        type="button"
                                        wire:click="removeNewImage({{ $index }})"
                                        class="absolute top-1.5 right-1.5 p-1 rounded-full bg-black/60 hover:bg-black/80 text-white transition"
                                        title="Batal upload foto ini"
                                    >
                                        <flux:icon name="x-mark" class="size-3.5" />
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </flux:field>

                {{-- Action Buttons --}}
                <div class="pt-4 flex items-center justify-end gap-3 border-t border-zinc-100 dark:border-zinc-800">
                    <flux:button variant="ghost" :href="route('listing.index')" wire:navigate>
                        Batal
                    </flux:button>
                    <flux:button variant="primary" type="submit" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="update">Simpan Perubahan</span>
                        <span wire:loading wire:target="update">Menyimpan...</span>
                    </flux:button>
                </div>
            </form>
        </div>
    </div>
</div>

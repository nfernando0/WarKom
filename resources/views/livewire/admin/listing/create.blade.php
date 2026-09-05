<div class="space-y-6">

    {{-- Breadcrumbs --}}
    <div class="bg-zinc-100 dark:bg-zinc-800/60 p-3.5 rounded-xl border border-zinc-200 dark:border-zinc-700">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item :href="route('dashboard')" wire:navigate>Dashboard</flux:breadcrumbs.item>
            <flux:breadcrumbs.item :href="route('listing.index')" wire:navigate>Marketplace</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Buat Listing Baru</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </div>

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-zinc-900 dark:text-zinc-50 tracking-tight">
                Pasang Listing Baru (Admin)
            </h1>
            <p class="text-xs sm:text-sm text-zinc-500 mt-1">
                Tambahkan produk atau barang baru ke katalog marketplace komunitas.
            </p>
        </div>

        <flux:button variant="ghost" icon="arrow-left" :href="route('listing.index')" wire:navigate>
            Kembali
        </flux:button>
    </div>

    {{-- Main Form Card --}}
    <div class="bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-200 dark:border-zinc-800 shadow-xs overflow-hidden">
        <form wire:submit="save" class="p-6 sm:p-8 space-y-6">

            {{-- Row 1: Judul Barang --}}
            <flux:field>
                <flux:label required>Judul Barang / Produk</flux:label>
                <flux:input
                    wire:model="title"
                    placeholder="Contoh: Sepeda Lipat Polygon Urbano 3"
                    required
                />
                <flux:error name="title" />
            </flux:field>

            {{-- Row 2: Kategori & Komunitas --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                <flux:field>
                    <flux:label required>Kategori</flux:label>
                    <flux:select wire:model="category_id" placeholder="Pilih Kategori Barang" required>
                        @foreach ($categories as $cat)
                            <flux:select.option value="{{ $cat->id }}">{{ $cat->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="category_id" />
                </flux:field>

                <flux:field>
                    <flux:label>Target Komunitas (Opsional)</flux:label>
                    <flux:select wire:model="community_id" placeholder="Semua Komunitas (Publik)">
                        <flux:select.option value="">Semua Komunitas</flux:select.option>
                        @foreach ($communities as $comm)
                            <flux:select.option value="{{ $comm->id }}">{{ $comm->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:description>Pilih komunitas spesifik jika barang hanya dijual untuk anggota komunitas tertentu.</flux:description>
                    <flux:error name="community_id" />
                </flux:field>
            </div>

            {{-- Row 3: Harga, Kondisi, Status --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6">
                <flux:field>
                    <flux:label required>Harga (Rp)</flux:label>
                    <flux:input
                        type="number"
                        wire:model="price"
                        placeholder="Contoh: 150000"
                        min="0"
                        step="1000"
                        required
                    />
                    <flux:error name="price" />
                </flux:field>

                <flux:field>
                    <flux:label required>Kondisi Barang</flux:label>
                    <flux:select wire:model="condition" required>
                        <flux:select.option value="bekas">Bekas (Second)</flux:select.option>
                        <flux:select.option value="baru">Baru</flux:select.option>
                    </flux:select>
                    <flux:error name="condition" />
                </flux:field>

                <flux:field>
                    <flux:label required>Status Ketersediaan</flux:label>
                    <flux:select wire:model="status" required>
                        <flux:select.option value="tersedia">Tersedia</flux:select.option>
                        <flux:select.option value="ditahan">Ditahan / Booking</flux:select.option>
                        <flux:select.option value="terjual">Terjual</flux:select.option>
                    </flux:select>
                    <flux:error name="status" />
                </flux:field>
            </div>

            {{-- Row 4: Deskripsi Lengkap --}}
            <flux:field>
                <flux:label>Deskripsi Barang</flux:label>
                <flux:textarea
                    wire:model="description"
                    rows="4"
                    placeholder="Jelaskan spesifikasi, kondisi fisik, kelengkapan, minus, atau alasan dijual secara rinci..."
                />
                <flux:error name="description" />
            </flux:field>

            {{-- Row 5: Foto Barang --}}
            <div class="space-y-3">
                <flux:label>Foto Barang (Maks. 5 Foto)</flux:label>

                <div class="p-6 border-2 border-dashed border-zinc-200 dark:border-zinc-700 rounded-2xl bg-zinc-50 dark:bg-zinc-800/40 text-center relative hover:bg-zinc-100/60 dark:hover:bg-zinc-800/60 transition">
                    <input
                        type="file"
                        wire:model="images"
                        multiple
                        accept="image/*"
                        id="admin-listing-photos"
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                    />
                    <div class="space-y-1 text-zinc-500">
                        <flux:icon name="photo" class="size-8 mx-auto stroke-1 text-zinc-400" />
                        <p class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">
                            Klik atau seret foto ke area ini
                        </p>
                        <p class="text-xs text-zinc-400">
                            Format JPG, PNG, WEBP hingga 3MB per file
                        </p>
                    </div>
                </div>

                {{-- Uploading indicator --}}
                <div wire:loading wire:target="images" class="text-xs text-primary-600 dark:text-primary-400 font-semibold flex items-center gap-2">
                    <flux:icon name="arrow-path" class="size-4 animate-spin" />
                    <span>Mengunggah foto...</span>
                </div>

                <flux:error name="images" />
                <flux:error name="images.*" />

                {{-- Photo Previews --}}
                @if (! empty($images))
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3 pt-2">
                        @foreach ($images as $index => $img)
                            <div class="relative group aspect-square rounded-xl overflow-hidden bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 shadow-xs">
                                <img src="{{ $img->temporaryUrl() }}" alt="Preview" class="w-full h-full object-cover" />
                                
                                @if ($index === 0)
                                    <span class="absolute top-1.5 left-1.5 px-1.5 py-0.5 rounded bg-primary-600 text-white text-[9px] font-bold uppercase shadow-xs">
                                        Utama
                                    </span>
                                @endif

                                <button
                                    type="button"
                                    wire:click="removeImage({{ $index }})"
                                    class="absolute top-1.5 right-1.5 size-6 rounded-full bg-red-600/90 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition shadow-sm hover:scale-110 cursor-pointer"
                                    title="Hapus Foto"
                                >
                                    <flux:icon name="x-mark" class="size-3.5" />
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Form Submit Buttons --}}
            <div class="pt-4 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-end gap-3">
                <flux:button variant="ghost" :href="route('listing.index')" wire:navigate>
                    Batal
                </flux:button>
                <flux:button variant="primary" type="submit" wire:loading.attr="disabled" icon="check">
                    <span wire:loading.remove wire:target="save">Simpan & Publikasikan Listing</span>
                    <span wire:loading wire:target="save">Menyimpan...</span>
                </flux:button>
            </div>

        </form>
    </div>

</div>

<div class="py-8 sm:py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        {{-- Breadcrumbs --}}
        <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-2xs">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item :href="route('home')" wire:navigate>Home</flux:breadcrumbs.item>
                <flux:breadcrumbs.item :href="route('public.marketplace')" wire:navigate>Marketplace</flux:breadcrumbs.item>
                <flux:breadcrumbs.item :href="route('my-listings')" wire:navigate>Listing Saya</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>Pasang Iklan</flux:breadcrumbs.item>
            </flux:breadcrumbs>
        </div>

    @if (! $userCommunity)
        <div class="mt-6">
            <flux:callout variant="warning" icon="exclamation-triangle" heading="Belum Bergabung dengan Komunitas">
                <p class="mt-1">
                    Untuk dapat menjual barang di marketplace, Anda harus bergabung dengan salah satu komunitas terlebih dahulu.
                </p>
                <div class="mt-3">
                    <flux:button variant="primary" :href="route('community.index')" wire:navigate size="sm">
                        Lihat & Bergabung Komunitas
                    </flux:button>
                </div>
            </flux:callout>
        </div>
    @else
        <div class="mt-6 max-w-3xl">
            <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
                <div>
                    <flux:heading size="xl">Buat Listing Baru</flux:heading>
                    <flux:subheading>
                        Barang ini akan dipublikasikan di komunitas <strong>{{ $userCommunity->name }}</strong>.
                    </flux:subheading>
                </div>

                @error('community')
                    <div class="mt-4">
                        <flux:callout variant="danger" icon="exclamation-circle" :heading="$message" />
                    </div>
                @enderror

                <form wire:submit="save" class="mt-6 space-y-5">
                    {{-- Title --}}
                    <flux:field>
                        <flux:label>Judul Listing</flux:label>
                        <flux:input wire:model="title" placeholder="Contoh: Monitor Gaming 24 Inch 144Hz" autofocus />
                        <flux:error name="title" />
                    </flux:field>

                    {{-- Category & Condition Grid --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Category --}}
                        <flux:field>
                            <flux:label>Kategori</flux:label>
                            <flux:select wire:model="category_id" placeholder="Pilih Kategori">
                                <flux:select.option value="">Pilih Kategori...</flux:select.option>
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

                    {{-- Image Upload --}}
                    <flux:field>
                        <flux:label>Foto Barang (Maksimal 5 Foto)</flux:label>
                        <flux:input type="file" wire:model="images" multiple accept="image/png,image/jpeg,image/jpg,image/webp" />
                        <flux:error name="images.*" />
                        <flux:error name="images" />

                        {{-- Upload Progress Indicator --}}
                        <div wire:loading wire:target="images" class="mt-2 text-xs text-primary-600 dark:text-primary-400 font-medium">
                            Sedang mengunggah foto...
                        </div>

                        {{-- Preview uploaded images --}}
                        @if (! empty($images))
                            <div class="mt-3 grid grid-cols-2 sm:grid-cols-4 gap-3">
                                @foreach ($images as $index => $img)
                                    <div class="relative group rounded-lg overflow-hidden border border-zinc-200 dark:border-zinc-700 aspect-square bg-zinc-100 dark:bg-zinc-800">
                                        <img src="{{ $img->temporaryUrl() }}" class="w-full h-full object-cover" />
                                        <button
                                            type="button"
                                            wire:click="removeImage({{ $index }})"
                                            class="absolute top-1.5 right-1.5 p-1 rounded-full bg-black/60 hover:bg-black/80 text-white transition"
                                            title="Hapus foto"
                                        >
                                            <flux:icon name="x-mark" class="size-3.5" />
                                        </button>
                                        @if ($index === 0)
                                            <span class="absolute bottom-1 left-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-black/60 text-white">
                                                Utama
                                            </span>
                                        @endif
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
                            <span wire:loading.remove wire:target="save">Pasang Listing</span>
                            <span wire:loading wire:target="save">Menyimpan...</span>
                        </flux:button>
                    </div>
                </form>
            </div>
        </div>
    @endif
    </div>
</div>

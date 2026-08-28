<div class="space-y-6">
    {{-- Breadcrumbs --}}
    <div class="bg-zinc-200 dark:bg-zinc-800 p-4 rounded-xl border border-zinc-300 dark:border-zinc-700">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item :href="route('dashboard')" wire:navigate>Home</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Kategori</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </div>

    {{-- Notifications --}}
    @if (session()->has('success'))
        <flux:callout variant="success" icon="check-circle" :heading="session('success')" />
    @endif
    @if (session()->has('error'))
        <flux:callout variant="danger" icon="exclamation-circle" :heading="session('error')" />
    @endif

    {{-- Header & Search --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">Kategori Barang</flux:heading>
            <flux:subheading>
                Telusuri berbagai kelompok barang yang dijual oleh anggota komunitas.
            </flux:subheading>
        </div>

        <div class="flex items-center gap-3">
            <div class="w-full sm:w-64">
                <flux:input
                    wire:model.live.debounce.250ms="search"
                    placeholder="Cari kategori..."
                    icon="magnifying-glass"
                    size="sm"
                    clearable
                />
            </div>

            @if ($isAdmin)
                <flux:button variant="primary" icon="plus" wire:click="openCreateModal" size="sm" class="shrink-0">
                    Tambah Kategori
                </flux:button>
            @endif
        </div>
    </div>

    {{-- Categories Grid --}}
    <div>
        @if ($categories->isEmpty())
            <div class="py-16 text-center bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-8 shadow-xs">
                <div class="size-16 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center mx-auto mb-4 text-zinc-400">
                    <flux:icon name="tag" class="size-8 stroke-1" />
                </div>
                <flux:heading size="lg">Kategori Tidak Ditemukan</flux:heading>
                <p class="text-sm text-zinc-500 max-w-sm mx-auto mt-1">
                    @if ($search)
                        Tidak ada kategori yang cocok dengan kata kunci "{{ $search }}".
                    @else
                        Belum ada kategori yang ditambahkan ke sistem.
                    @endif
                </p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
                @foreach ($categories as $cat)
                    <div wire:key="category-card-{{ $cat->id }}" class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 hover:border-primary-500/50 shadow-xs hover:shadow-md transition flex flex-col justify-between p-5 space-y-4 group">
                        
                        {{-- Top Icon & Title --}}
                        <div class="space-y-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="size-12 rounded-xl bg-primary-500/10 text-primary-600 dark:text-primary-400 flex items-center justify-center group-hover:scale-110 transition">
                                    <flux:icon :name="$cat->icon ?: 'tag'" class="size-6" />
                                </div>

                                <flux:badge size="sm" color="zinc">
                                    {{ $cat->listings_count }} barang
                                </flux:badge>
                            </div>

                            <div>
                                <h3 class="font-bold text-base text-zinc-900 dark:text-zinc-100 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition">
                                    {{ $cat->name }}
                                </h3>
                                <p class="text-xs text-zinc-500 mt-0.5">
                                    {{ $cat->listings_count > 0 ? 'Tersedia di marketplace komunitas' : 'Belum ada barang aktif' }}
                                </p>
                            </div>

                            {{-- Product Thumbnails Preview --}}
                            @if ($cat->listings->isNotEmpty())
                                <div class="flex items-center gap-2 pt-1">
                                    @foreach ($cat->listings as $previewListing)
                                        <div class="size-10 rounded-lg overflow-hidden bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 shrink-0">
                                            @if ($previewListing->images->isNotEmpty())
                                                <img src="{{ $previewListing->images->first()->url }}" class="w-full h-full object-cover" title="{{ $previewListing->title }}" />
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-zinc-400">
                                                    <flux:icon name="photo" class="size-4" />
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- Footer Actions --}}
                        <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between gap-2">
                            <flux:button
                                size="xs"
                                variant="primary"
                                icon-trailing="arrow-right"
                                :href="route('listing.index', ['selectedCategory' => $cat->id])"
                                wire:navigate
                                class="flex-1 text-center justify-center"
                            >
                                Lihat Barang
                            </flux:button>

                            {{-- Admin Actions --}}
                            @if ($isAdmin)
                                <div class="flex items-center gap-1 shrink-0">
                                    <flux:button
                                        size="xs"
                                        variant="subtle"
                                        icon="pencil-square"
                                        wire:click="openEditModal({{ $cat->id }})"
                                        title="Edit kategori"
                                    />
                                    <flux:button
                                        size="xs"
                                        variant="subtle"
                                        icon="trash"
                                        class="text-red-500 hover:text-red-600"
                                        wire:click="deleteCategory({{ $cat->id }})"
                                        wire:confirm="Yakin ingin menghapus kategori '{{ $cat->name }}'?"
                                        title="Hapus kategori"
                                    />
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Admin Create / Edit Modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-6 max-w-md w-full shadow-xl space-y-5 animate-in fade-in zoom-in duration-150">
                <div class="flex items-center justify-between">
                    <flux:heading size="lg">{{ $editingCategoryId ? 'Edit Kategori' : 'Tambah Kategori Baru' }}</flux:heading>
                    <button type="button" wire:click="closeModal" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200">
                        <flux:icon name="x-mark" class="size-5" />
                    </button>
                </div>

                <form wire:submit="saveCategory" class="space-y-4">
                    {{-- Category Name --}}
                    <flux:field>
                        <flux:label>Nama Kategori</flux:label>
                        <flux:input wire:model="name" placeholder="Contoh: Elektronik & Gadget" autofocus />
                        <flux:error name="name" />
                    </flux:field>

                    {{-- Icon Selector --}}
                    <flux:field>
                        <flux:label>Pilihan Ikon</flux:label>
                        <flux:select wire:model="icon">
                            @foreach ($availableIcons as $iconKey => $iconLabel)
                                <flux:select.option value="{{ $iconKey }}">{{ $iconLabel }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="icon" />
                    </flux:field>

                    {{-- Actions --}}
                    <div class="pt-3 flex items-center justify-end gap-2.5 border-t border-zinc-100 dark:border-zinc-800">
                        <flux:button variant="ghost" type="button" wire:click="closeModal">
                            Batal
                        </flux:button>
                        <flux:button variant="primary" type="submit" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="saveCategory">Simpan</span>
                            <span wire:loading wire:target="saveCategory">Menyimpan...</span>
                        </flux:button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

<div class="py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        
        {{-- Header & Search --}}
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-purple-500/10 text-purple-600 dark:text-purple-400 text-xs font-semibold mb-2">
                    <flux:icon name="tag" class="size-3.5" />
                    <span>Jelajahi Kategori</span>
                </div>
                <h1 class="text-3xl sm:text-4xl font-extrabold text-zinc-900 dark:text-zinc-50 tracking-tight">
                    Kategori Barang
                </h1>
                <p class="text-sm text-zinc-500 mt-1 max-w-2xl">
                    Pilih kategori yang Anda minati untuk menemukan barang dengan cepat dan tepat.
                </p>
            </div>

            <div class="w-full sm:w-72 shrink-0">
                <flux:input
                    wire:model.live.debounce.250ms="search"
                    placeholder="Cari kategori..."
                    icon="magnifying-glass"
                    size="sm"
                    clearable
                />
            </div>
        </div>

        {{-- Categories Grid --}}
        <div>
            @if ($categories->isEmpty())
                <div class="py-20 text-center bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-8 shadow-xs">
                    <div class="size-16 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center mx-auto mb-4 text-zinc-400">
                        <flux:icon name="tag" class="size-8 stroke-1" />
                    </div>
                    <h3 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">Kategori Tidak Ditemukan</h3>
                    <p class="text-sm text-zinc-500 max-w-sm mx-auto mt-1">
                        @if ($search)
                            Tidak ada kategori yang cocok dengan kata kunci "{{ $search }}".
                        @else
                            Belum ada kategori yang ditambahkan.
                        @endif
                    </p>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    @foreach ($categories as $cat)
                        <div wire:key="pub-cat-card-{{ $cat->id }}" class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 hover:border-primary-500/60 shadow-xs hover:shadow-lg transition duration-200 flex flex-col justify-between p-6 space-y-5 group">
                            
                            <div class="space-y-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="size-14 rounded-2xl bg-gradient-to-br from-primary-500/15 to-indigo-500/15 text-primary-600 dark:text-primary-400 flex items-center justify-center group-hover:scale-110 transition shrink-0">
                                        <flux:icon :name="$cat->icon ?: 'tag'" class="size-7" />
                                    </div>

                                    <flux:badge size="sm" color="zinc">
                                        {{ $cat->listings_count }} barang
                                    </flux:badge>
                                </div>

                                <div>
                                    <h3 class="font-bold text-lg text-zinc-900 dark:text-zinc-100 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition">
                                        {{ $cat->name }}
                                    </h3>
                                    <p class="text-xs text-zinc-500 mt-1">
                                        {{ $cat->listings_count > 0 ? "Tersedia {$cat->listings_count} barang aktif" : 'Belum ada barang aktif' }}
                                    </p>
                                </div>

                                {{-- Product Thumbnails Preview --}}
                                @if ($cat->listings->isNotEmpty())
                                    <div class="space-y-1.5 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                                        <span class="text-[11px] text-zinc-400 font-medium block">Contoh Barang:</span>
                                        <div class="flex items-center gap-2">
                                            @foreach ($cat->listings as $previewListing)
                                                <a href="{{ route('public.listing.show', $previewListing) }}" wire:navigate class="size-11 rounded-lg overflow-hidden bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 shrink-0 hover:opacity-80 transition" title="{{ $previewListing->title }}">
                                                    @if ($previewListing->images->isNotEmpty())
                                                        <img src="{{ $previewListing->images->first()->url }}" class="w-full h-full object-cover" />
                                                    @else
                                                        <div class="w-full h-full flex items-center justify-center text-zinc-400">
                                                            <flux:icon name="photo" class="size-4" />
                                                        </div>
                                                    @endif
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- Action --}}
                            <div class="pt-4 border-t border-zinc-100 dark:border-zinc-800">
                                <flux:button
                                    size="sm"
                                    variant="primary"
                                    icon-trailing="arrow-right"
                                    :href="route('public.marketplace', ['cat' => $cat->id])"
                                    wire:navigate
                                    class="w-full text-center justify-center"
                                >
                                    Lihat Semua Barang
                                </flux:button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

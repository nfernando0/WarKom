<div class="py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        
        {{-- Header & Search --}}
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 text-xs font-semibold mb-2">
                    <flux:icon name="users" class="size-3.5" />
                    <span>Jaringan Lokal</span>
                </div>
                <h1 class="text-3xl sm:text-4xl font-extrabold text-zinc-900 dark:text-zinc-50 tracking-tight">
                    Direktori Komunitas Warga
                </h1>
                <p class="text-sm text-zinc-500 mt-1 max-w-2xl">
                    Temukan komunitas lingkungan RT/RW, komplek, atau kelompok minat Anda untuk saling berbagi dan berjual-beli barang.
                </p>
            </div>

            <div class="w-full sm:w-72 shrink-0">
                <flux:input
                    wire:model.live.debounce.250ms="search"
                    placeholder="Cari nama atau lokasi..."
                    icon="magnifying-glass"
                    size="sm"
                    clearable
                />
            </div>
        </div>

        {{-- Communities Grid --}}
        <div>
            @if ($communities->isEmpty())
                <div class="py-20 text-center bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-8 shadow-xs">
                    <div class="size-16 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center mx-auto mb-4 text-zinc-400">
                        <flux:icon name="users" class="size-8 stroke-1" />
                    </div>
                    <h3 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">Komunitas Tidak Ditemukan</h3>
                    <p class="text-sm text-zinc-500 max-w-sm mx-auto mt-1">
                        @if ($search)
                            Tidak ada komunitas yang cocok dengan pencarian "{{ $search }}".
                        @else
                            Belum ada komunitas yang terdaftar.
                        @endif
                    </p>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($communities as $community)
                        <div wire:key="pub-comm-{{ $community->id }}" class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 hover:border-zinc-300 dark:hover:border-zinc-700 shadow-xs hover:shadow-lg transition duration-200 flex flex-col justify-between p-6 space-y-5">
                            
                            <div class="space-y-4">
                                {{-- Top Header --}}
                                <div class="flex items-start justify-between gap-3">
                                    <div class="size-12 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold shrink-0">
                                        <flux:icon name="users" class="size-6" />
                                    </div>

                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <flux:badge size="sm" color="zinc">
                                            {{ $community->members_count }} Anggota
                                        </flux:badge>
                                        <flux:badge size="sm" color="emerald">
                                            {{ $community->listings_count }} Barang
                                        </flux:badge>
                                    </div>
                                </div>

                                {{-- Name & Description --}}
                                <div>
                                    <h3 class="font-bold text-lg text-zinc-900 dark:text-zinc-100">
                                        {{ $community->name }}
                                    </h3>
                                    <p class="text-xs text-zinc-500 mt-1 line-clamp-2 leading-relaxed">
                                        {{ $community->description ?: 'Komunitas aktif jual beli dan pertukaran barang antar anggota.' }}
                                    </p>
                                </div>

                                {{-- Location Badge --}}
                                @if ($community->location)
                                    <div class="flex items-center gap-1.5 text-xs text-zinc-600 dark:text-zinc-400">
                                        <flux:icon name="map-pin" class="size-4 text-zinc-400" />
                                        <span>{{ $community->location }}</span>
                                    </div>
                                @endif

                                {{-- Active Items Thumbnails --}}
                                @if ($community->listings->isNotEmpty())
                                    <div class="space-y-1.5 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                                        <span class="text-[11px] text-zinc-400 font-medium block">Barang Terbaru:</span>
                                        <div class="flex items-center gap-2">
                                            @foreach ($community->listings as $commListing)
                                                <a href="{{ route('public.listing.show', $commListing) }}" wire:navigate class="size-12 rounded-lg overflow-hidden bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 shrink-0 hover:opacity-80 transition" title="{{ $commListing->title }}">
                                                    @if ($commListing->images->isNotEmpty())
                                                        <img src="{{ $commListing->images->first()->url }}" class="w-full h-full object-cover" />
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

                            {{-- Actions --}}
                            <div class="pt-4 border-t border-zinc-100 dark:border-zinc-800 flex items-center gap-2">
                                <flux:button
                                    size="sm"
                                    variant="primary"
                                    icon-trailing="arrow-right"
                                    :href="route('public.marketplace', ['community' => $community->id])"
                                    wire:navigate
                                    class="flex-1 text-center justify-center"
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

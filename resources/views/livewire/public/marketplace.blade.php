<div class="py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        
        {{-- Header & Search Hero --}}
        <div class="space-y-4 text-center sm:text-left">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                <div>
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary-500/10 text-primary-600 dark:text-primary-400 text-xs font-semibold mb-2">
                        <flux:icon name="shopping-bag" class="size-3.5" />
                        <span>Katalog Publik</span>
                    </div>
                    <h1 class="text-3xl sm:text-4xl font-extrabold text-zinc-900 dark:text-zinc-50 tracking-tight">
                        Marketplace Komunitas
                    </h1>
                    <p class="text-sm text-zinc-500 mt-1 max-w-2xl">
                        Temukan berbagai barang kebutuhan sehari-hari yang dijual oleh tetangga dan anggota komunitas lokal Anda.
                    </p>
                </div>

                @auth
                    <flux:button variant="primary" icon="plus" :href="route('listing.create')" wire:navigate class="shrink-0">
                        Pasang Iklan Barang
                    </flux:button>
                @endauth
            </div>
        </div>

        {{-- Horizontal Category Chips --}}
        <div class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar text-xs">
            <button
                type="button"
                wire:click="$set('selectedCategory', '')"
                class="px-4 py-2 rounded-full border transition shrink-0 font-medium {{ empty($selectedCategory) ? 'bg-primary-600 text-white border-primary-600 shadow-xs' : 'bg-white dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-zinc-300' }}"
            >
                Semua Kategori
            </button>
            @foreach ($categories as $cat)
                <button
                    type="button"
                    wire:key="pub-cat-{{ $cat->id }}"
                    wire:click="selectCategory('{{ $cat->id }}')"
                    class="px-4 py-2 rounded-full border transition shrink-0 font-medium {{ $selectedCategory == $cat->id ? 'bg-primary-600 text-white border-primary-600 shadow-xs' : 'bg-white dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-zinc-300' }}"
                >
                    {{ $cat->name }} ({{ $cat->listings_count }})
                </button>
            @endforeach
        </div>

        {{-- Search & Filters Card --}}
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                {{-- Search input --}}
                <div class="lg:col-span-1">
                    <flux:input
                        wire:model.live.debounce.300ms="search"
                        placeholder="Cari nama barang..."
                        icon="magnifying-glass"
                        size="sm"
                        clearable
                    />
                </div>

                {{-- Community filter --}}
                <div>
                    <flux:select wire:model.live="selectedCommunity" size="sm">
                        <flux:select.option value="">Semua Komunitas</flux:select.option>
                        @foreach ($communities as $comm)
                            <flux:select.option value="{{ $comm->id }}">{{ $comm->name }} ({{ $comm->listings_count }})</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                {{-- Sorting --}}
                <div>
                    <flux:select wire:model.live="sortBy" size="sm">
                        <flux:select.option value="latest">Urutkan: Terbaru</flux:select.option>
                        <flux:select.option value="price_asc">Urutkan: Harga Terendah</flux:select.option>
                        <flux:select.option value="price_desc">Urutkan: Harga Tertinggi</flux:select.option>
                    </flux:select>
                </div>

                {{-- Condition select --}}
                <div>
                    <flux:select wire:model.live="selectedCondition" size="sm">
                        <flux:select.option value="">Semua Kondisi</flux:select.option>
                        <flux:select.option value="baru">Kondisi: Baru</flux:select.option>
                        <flux:select.option value="bekas">Kondisi: Bekas</flux:select.option>
                    </flux:select>
                </div>
            </div>

            @if ($search || $selectedCategory || $selectedCommunity || $selectedCondition || $sortBy !== 'latest')
                <div class="flex items-center justify-between pt-3 border-t border-zinc-100 dark:border-zinc-800 text-xs">
                    <span class="text-zinc-500">Filter pencarian aktif diterapkan</span>
                    <button type="button" wire:click="resetFilters" class="text-primary-600 hover:underline font-medium">
                        Reset Semua Filter
                    </button>
                </div>
            @endif
        </div>

        {{-- Listings Grid --}}
        <div>
            @if ($listings->isEmpty())
                <div class="py-20 text-center bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-8 shadow-xs">
                    <div class="size-16 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center mx-auto mb-4 text-zinc-400">
                        <flux:icon name="shopping-bag" class="size-8 stroke-1" />
                    </div>
                    <h3 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">Barang Tidak Ditemukan</h3>
                    <p class="text-sm text-zinc-500 max-w-sm mx-auto mt-1">
                        @if ($search || $selectedCategory || $selectedCommunity || $selectedCondition)
                            Tidak ada barang yang cocok dengan kriteria filter yang Anda pilih. Coba sesuaikan kata kunci atau reset filter.
                        @else
                            Belum ada barang yang dipasang saat ini.
                        @endif
                    </p>
                    @if ($search || $selectedCategory || $selectedCommunity || $selectedCondition)
                        <div class="mt-4">
                            <flux:button size="sm" variant="subtle" wire:click="resetFilters">
                                Reset Filter
                            </flux:button>
                        </div>
                    @endif
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    @foreach ($listings as $listing)
                        <div wire:key="pub-listing-{{ $listing->id }}" class="group bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 hover:border-zinc-300 dark:hover:border-zinc-700 shadow-xs hover:shadow-lg transition duration-200 flex flex-col overflow-hidden">
                            
                            {{-- Thumbnail Image --}}
                            <a href="{{ route('public.listing.show', $listing) }}" wire:navigate class="block relative aspect-4/3 w-full bg-zinc-100 dark:bg-zinc-800 overflow-hidden">
                                @if ($listing->images->isNotEmpty())
                                    <img
                                        src="{{ $listing->images->first()->url }}"
                                        alt="{{ $listing->title }}"
                                        class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                                    />
                                @else
                                    <div class="w-full h-full flex flex-col items-center justify-center text-zinc-400">
                                        <flux:icon name="photo" class="size-10 stroke-1" />
                                        <span class="text-xs mt-1">Tanpa Foto</span>
                                    </div>
                                @endif

                                {{-- Badges --}}
                                <div class="absolute top-2.5 left-2.5 flex flex-wrap gap-1">
                                    <flux:badge size="xs" :color="$listing->condition === 'baru' ? 'emerald' : 'amber'">
                                        {{ ucfirst($listing->condition) }}
                                    </flux:badge>
                                </div>

                                @if ($listing->category)
                                    <div class="absolute bottom-2.5 left-2.5">
                                        <span class="px-2 py-0.5 rounded-md text-[11px] font-medium bg-black/60 backdrop-blur-md text-white">
                                            {{ $listing->category->name }}
                                        </span>
                                    </div>
                                @endif

                                @if ($listing->community)
                                    <div class="absolute bottom-2.5 right-2.5">
                                        <span class="px-2 py-0.5 rounded-md text-[11px] font-medium bg-zinc-900/80 backdrop-blur-md text-zinc-200">
                                            📍 {{ $listing->community->name }}
                                        </span>
                                    </div>
                                @endif
                            </a>

                            {{-- Content Details --}}
                            <div class="p-4 flex-1 flex flex-col justify-between space-y-4">
                                <div>
                                    <a href="{{ route('public.listing.show', $listing) }}" wire:navigate class="font-bold text-base text-zinc-900 dark:text-zinc-100 line-clamp-1 hover:text-primary-600 dark:hover:text-primary-400 transition" title="{{ $listing->title }}">
                                        {{ $listing->title }}
                                    </a>

                                    <div class="mt-2 text-xl font-extrabold text-zinc-900 dark:text-zinc-50">
                                        Rp {{ number_format($listing->price, 0, ',', '.') }}
                                    </div>

                                    <p class="mt-1 text-xs text-zinc-500 line-clamp-2">
                                        {{ $listing->description }}
                                    </p>
                                </div>

                                {{-- Footer with seller & action --}}
                                <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800/80 flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <div class="size-6 rounded-full bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center font-bold text-[10px] text-zinc-700 dark:text-zinc-300 shrink-0">
                                            {{ $listing->creator?->initials() ?? '?' }}
                                        </div>
                                        <span class="text-xs text-zinc-600 dark:text-zinc-400 truncate">
                                            {{ $listing->creator?->name ?? 'Penjual' }}
                                        </span>
                                    </div>

                                    <div class="flex items-center gap-1.5 shrink-0">
                                        <flux:button
                                            size="xs"
                                            variant="primary"
                                            icon="chat-bubble-left-right"
                                            wire:click="startChat({{ $listing->id }})"
                                        >
                                            Chat
                                        </flux:button>
                                        <flux:button
                                            size="xs"
                                            variant="subtle"
                                            :href="route('public.listing.show', $listing)"
                                            wire:navigate
                                        >
                                            Detail
                                        </flux:button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="mt-8">
                    {{ $listings->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

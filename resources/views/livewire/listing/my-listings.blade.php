<div class="py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        {{-- Breadcrumbs & Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item :href="route('home')" wire:navigate>Home</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item :href="route('public.marketplace')" wire:navigate>Marketplace</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item>Listing Saya</flux:breadcrumbs.item>
                </flux:breadcrumbs>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-zinc-900 dark:text-zinc-50 tracking-tight mt-2">
                    Kelola Listing & Barang Saya
                </h1>
                <p class="text-xs sm:text-sm text-zinc-500">
                    Kelola barang yang Anda jual, perbarui ketersediaan, atau pasang iklan barang baru.
                </p>
            </div>

            <div class="shrink-0">
                <flux:button variant="primary" icon="plus" :href="route('listing.create')" wire:navigate class="w-full sm:w-auto shadow-md shadow-primary-500/20">
                    Pasang Iklan Baru
                </flux:button>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if (session()->has('success'))
            <flux:callout variant="success" icon="check-circle" :heading="session('success')" />
        @endif

        {{-- Stats Grid --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="p-5 bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 text-center shadow-xs">
                <p class="text-2xl sm:text-3xl font-extrabold text-zinc-900 dark:text-zinc-100">{{ $stats['total'] }}</p>
                <p class="text-xs text-zinc-500 font-medium mt-1">Total Listing</p>
            </div>
            <div class="p-5 bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 text-center shadow-xs">
                <p class="text-2xl sm:text-3xl font-extrabold text-emerald-600 dark:text-emerald-400">{{ $stats['tersedia'] }}</p>
                <p class="text-xs text-zinc-500 font-medium mt-1">Status Tersedia</p>
            </div>
            <div class="p-5 bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 text-center shadow-xs">
                <p class="text-2xl sm:text-3xl font-extrabold text-amber-500">{{ $stats['ditahan'] }}</p>
                <p class="text-xs text-zinc-500 font-medium mt-1">Status Ditahan / Booking</p>
            </div>
            <div class="p-5 bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 text-center shadow-xs">
                <p class="text-2xl sm:text-3xl font-extrabold text-indigo-600 dark:text-indigo-400">{{ $stats['terjual'] }}</p>
                <p class="text-xs text-zinc-500 font-medium mt-1">Sudah Terjual</p>
            </div>
        </div>

        {{-- Filter & Search Toolbar --}}
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 p-4 bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs">
            {{-- Filter Status Tabs --}}
            <div class="flex flex-wrap items-center gap-1.5">
                <button
                    type="button"
                    wire:click="setStatusFilter('all')"
                    class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition {{ $statusFilter === 'all' ? 'bg-primary-600 text-white shadow-xs' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-700' }}"
                >
                    Semua ({{ $stats['total'] }})
                </button>
                <button
                    type="button"
                    wire:click="setStatusFilter('tersedia')"
                    class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition {{ $statusFilter === 'tersedia' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-700' }}"
                >
                    Tersedia ({{ $stats['tersedia'] }})
                </button>
                <button
                    type="button"
                    wire:click="setStatusFilter('ditahan')"
                    class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition {{ $statusFilter === 'ditahan' ? 'bg-amber-600 text-white shadow-xs' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-700' }}"
                >
                    Ditahan ({{ $stats['ditahan'] }})
                </button>
                <button
                    type="button"
                    wire:click="setStatusFilter('terjual')"
                    class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition {{ $statusFilter === 'terjual' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-700' }}"
                >
                    Terjual ({{ $stats['terjual'] }})
                </button>
            </div>

            {{-- Search Input --}}
            <div class="w-full sm:w-72">
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    icon="magnifying-glass"
                    placeholder="Cari listing Anda..."
                    clearable
                />
            </div>
        </div>

        {{-- Listings Catalog --}}
        @if ($listings->isEmpty())
            <div class="py-16 text-center bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-200 dark:border-zinc-800 p-8 shadow-xs">
                <div class="size-16 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center mx-auto mb-4 text-zinc-400">
                    <flux:icon name="shopping-bag" class="size-8 stroke-1" />
                </div>
                <h3 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">Tidak Ada Listing Ditemukan</h3>
                <p class="text-sm text-zinc-500 max-w-sm mx-auto mt-1">
                    @if ($search || $statusFilter !== 'all')
                        Tidak ada barang yang cocok dengan filter pencarian ini.
                    @else
                        Anda belum memasang barang jualan. Mulai pasang barang pertama Anda sekarang!
                    @endif
                </p>
                <div class="mt-5">
                    <flux:button variant="primary" icon="plus" :href="route('listing.create')" wire:navigate>
                        Pasang Iklan Sekarang
                    </flux:button>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach ($listings as $listing)
                    <div wire:key="my-listing-{{ $listing->id }}" class="group bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 hover:border-zinc-300 dark:hover:border-zinc-700 shadow-xs hover:shadow-lg transition flex flex-col overflow-hidden">
                        {{-- Thumbnail --}}
                        <div class="relative aspect-4/3 w-full bg-zinc-100 dark:bg-zinc-800 overflow-hidden">
                            @if ($listing->images->isNotEmpty())
                                <img src="{{ $listing->images->first()->url }}" alt="{{ $listing->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" />
                            @else
                                <div class="w-full h-full flex flex-col items-center justify-center text-zinc-400">
                                    <flux:icon name="photo" class="size-10 stroke-1" />
                                    <span class="text-xs mt-1">Tanpa Foto</span>
                                </div>
                            @endif

                            {{-- Badges --}}
                            <div class="absolute top-2.5 left-2.5 flex flex-wrap gap-1">
                                <flux:badge size="xs" :color="$listing->status === 'tersedia' ? 'emerald' : ($listing->status === 'ditahan' ? 'amber' : 'zinc')">
                                    {{ ucfirst($listing->status) }}
                                </flux:badge>
                                <flux:badge size="xs" color="zinc">
                                    {{ ucfirst($listing->condition) }}
                                </flux:badge>
                            </div>
                        </div>

                        {{-- Details --}}
                        <div class="p-4 flex-1 flex flex-col justify-between space-y-4">
                            <div>
                                <span class="text-[11px] font-semibold text-primary-600 dark:text-primary-400 block truncate">
                                    {{ $listing->category?->name ?? 'Tanpa Kategori' }}
                                </span>
                                <h3 class="font-bold text-base text-zinc-900 dark:text-zinc-100 line-clamp-1 mt-0.5" title="{{ $listing->title }}">
                                    {{ $listing->title }}
                                </h3>
                                <div class="mt-1 text-lg font-extrabold text-zinc-900 dark:text-zinc-50">
                                    Rp {{ number_format($listing->price, 0, ',', '.') }}
                                </div>
                                <p class="mt-1 text-xs text-zinc-500 line-clamp-2">
                                    {{ $listing->description }}
                                </p>
                            </div>

                            {{-- Status change & Actions --}}
                            <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800 space-y-2.5">
                                {{-- Quick Status Switcher --}}
                                <div class="flex items-center justify-between gap-1 text-xs">
                                    <span class="text-zinc-400 text-[11px]">Ubah Status:</span>
                                    <div class="flex items-center gap-1">
                                        @if ($listing->status !== 'tersedia')
                                            <button
                                                type="button"
                                                wire:click="updateStatus({{ $listing->id }}, 'tersedia')"
                                                class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-500/10 text-emerald-600 hover:bg-emerald-500/20 transition"
                                                title="Tandai Tersedia"
                                            >
                                                Tersedia
                                            </button>
                                        @endif
                                        @if ($listing->status !== 'ditahan')
                                            <button
                                                type="button"
                                                wire:click="updateStatus({{ $listing->id }}, 'ditahan')"
                                                class="px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-500/10 text-amber-600 hover:bg-amber-500/20 transition"
                                                title="Tandai Ditahan / Booking"
                                            >
                                                Ditahan
                                            </button>
                                        @endif
                                        @if ($listing->status !== 'terjual')
                                            <button
                                                type="button"
                                                wire:click="updateStatus({{ $listing->id }}, 'terjual')"
                                                class="px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-500/10 text-indigo-600 hover:bg-indigo-500/20 transition"
                                                title="Tandai Terjual"
                                            >
                                                Terjual
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                {{-- Action Buttons --}}
                                <div class="flex items-center gap-1.5">
                                    <flux:button size="xs" variant="primary" icon="eye" :href="route('public.listing.show', $listing)" wire:navigate class="flex-1 justify-center">
                                        Lihat
                                    </flux:button>
                                    <flux:button size="xs" variant="subtle" icon="pencil-square" :href="route('listing.edit', $listing)" wire:navigate title="Edit Iklan" />
                                    <flux:button
                                        size="xs"
                                        variant="ghost"
                                        icon="trash"
                                        wire:click="deleteListing({{ $listing->id }})"
                                        wire:confirm="Apakah Anda yakin ingin menghapus barang ini?"
                                        class="text-red-600 hover:bg-red-50 dark:hover:bg-red-950"
                                        title="Hapus Iklan"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="pt-4">
                {{ $listings->links() }}
            </div>
        @endif

    </div>
</div>

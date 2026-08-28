<x-layouts::app :title="__('Admin Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">

        {{-- Top Admin Banner --}}
        <div class="p-6 bg-gradient-to-r from-primary-600 via-indigo-600 to-purple-600 rounded-3xl text-white shadow-md flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-white/20 text-white backdrop-blur-md uppercase tracking-wider">
                        Admin Control Panel
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Selamat Datang, {{ auth()->user()->name }}!
                </h1>
                <p class="text-xs sm:text-sm text-primary-100 max-w-xl">
                    Panel administrasi WarKom untuk mengelola komunitas, kategori produk, dan memoderasi listing barang di seluruh ekosistem komunitas.
                </p>
            </div>

            <div class="flex items-center gap-2.5">
                <flux:button variant="subtle" icon="plus" :href="route('community.create')" wire:navigate class="bg-white text-zinc-900 font-semibold text-xs border-none hover:bg-zinc-100">
                    Tambah Komunitas
                </flux:button>
                <flux:button variant="subtle" icon="tag" :href="route('category.index')" wire:navigate class="bg-black/20 text-white font-semibold text-xs border-white/20 hover:bg-black/30">
                    Kelola Kategori
                </flux:button>
            </div>
        </div>

        {{-- Statistics Overview (6 Cards) --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            <div class="p-4 bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 text-center shadow-xs">
                <p class="text-2xl font-extrabold text-primary-600 dark:text-primary-400">{{ $stats['communities'] ?? 0 }}</p>
                <p class="text-[11px] text-zinc-500 font-medium mt-1">Komunitas</p>
            </div>
            <div class="p-4 bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 text-center shadow-xs">
                <p class="text-2xl font-extrabold text-purple-600 dark:text-purple-400">{{ $stats['categories'] ?? 0 }}</p>
                <p class="text-[11px] text-zinc-500 font-medium mt-1">Kategori</p>
            </div>
            <div class="p-4 bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 text-center shadow-xs">
                <p class="text-2xl font-extrabold text-indigo-600 dark:text-indigo-400">{{ $stats['users'] ?? 0 }}</p>
                <p class="text-[11px] text-zinc-500 font-medium mt-1">Pengguna Terdaftar</p>
            </div>
            <div class="p-4 bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 text-center shadow-xs">
                <p class="text-2xl font-extrabold text-zinc-900 dark:text-zinc-100">{{ $stats['listings'] ?? 0 }}</p>
                <p class="text-[11px] text-zinc-500 font-medium mt-1">Total Listing</p>
            </div>
            <div class="p-4 bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 text-center shadow-xs">
                <p class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400">{{ $stats['active_listings'] ?? 0 }}</p>
                <p class="text-[11px] text-zinc-500 font-medium mt-1">Listing Aktif</p>
            </div>
            <div class="p-4 bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 text-center shadow-xs">
                <p class="text-2xl font-extrabold text-amber-500">{{ $stats['transactions'] ?? 0 }}</p>
                <p class="text-[11px] text-zinc-500 font-medium mt-1">Transaksi Selesai</p>
            </div>
        </div>

        {{-- 2-Column Tables (Recent Communities & Recent Listings) --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Recent Communities --}}
            <div class="p-6 bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <flux:heading size="lg">Komunitas Terdaftar</flux:heading>
                        <flux:subheading>Daftar komunitas yang ada di platform</flux:subheading>
                    </div>
                    <flux:button size="xs" variant="ghost" :href="route('community.index')" wire:navigate>
                        Lihat Semua &rarr;
                    </flux:button>
                </div>

                @if ($recentCommunities->isEmpty())
                    <div class="py-8 text-center text-zinc-400 text-xs">
                        Belum ada data komunitas.
                    </div>
                @else
                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach ($recentCommunities as $comm)
                            <div class="py-3 flex items-center justify-between gap-3">
                                <div class="space-y-0.5">
                                    <span class="font-bold text-sm text-zinc-900 dark:text-zinc-100 block">
                                        {{ $comm->name }}
                                    </span>
                                    <span class="text-xs text-zinc-500 block">
                                        {{ $comm->location ?: 'Lokasi belum diatur' }}
                                    </span>
                                </div>
                                <flux:badge size="xs" color="zinc">{{ $comm->members_count }} Anggota</flux:badge>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Recent Listings --}}
            <div class="p-6 bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <flux:heading size="lg">Listing Terbaru</flux:heading>
                        <flux:subheading>Barang yang baru dipasang oleh pengguna</flux:subheading>
                    </div>
                    <flux:button size="xs" variant="ghost" :href="route('listing.index')" wire:navigate>
                        Moderasi &rarr;
                    </flux:button>
                </div>

                @if ($recentListings->isEmpty())
                    <div class="py-8 text-center text-zinc-400 text-xs">
                        Belum ada listing barang.
                    </div>
                @else
                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach ($recentListings as $item)
                            <div class="py-3 flex items-center justify-between gap-3">
                                <div class="space-y-0.5 min-w-0">
                                    <a href="{{ route('public.listing.show', $item) }}" wire:navigate class="font-bold text-sm text-zinc-900 dark:text-zinc-100 hover:text-primary-600 block truncate" title="{{ $item->title }}">
                                        {{ $item->title }}
                                    </a>
                                    <span class="text-xs text-zinc-500 block">
                                        Oleh <strong>{{ $item->creator?->name ?? 'Pengguna' }}</strong> &bull; Rp {{ number_format($item->price, 0, ',', '.') }}
                                    </span>
                                </div>
                                <flux:badge size="xs" :color="$item->status === 'tersedia' ? 'emerald' : 'zinc'">
                                    {{ ucfirst($item->status) }}
                                </flux:badge>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>

    </div>
</x-layouts::app>

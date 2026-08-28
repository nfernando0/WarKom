<div>
    {{-- Breadcrumbs --}}
    <div class="bg-zinc-200 dark:bg-zinc-800 p-4 rounded-xl border border-zinc-300 dark:border-zinc-700">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item :href="route('dashboard')" wire:navigate>Home</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Marketplace</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </div>

    {{-- Notifications --}}
    @if (session()->has('success'))
        <div class="mt-4">
            <flux:callout variant="success" icon="check-circle" :heading="session('success')" />
        </div>
    @endif

    {{-- Header & Create Action --}}
    <div class="mt-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">Marketplace Komunitas</flux:heading>
            <flux:subheading>
                @if ($userCommunity)
                    Menampilkan produk di komunitas <strong class="text-zinc-900 dark:text-zinc-100">{{ $userCommunity->name }}</strong>
                @else
                    Jual beli barang praktis dan aman di dalam komunitas Anda.
                @endif
            </flux:subheading>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <flux:button variant="primary" icon="plus" :href="route('listing.create')" wire:navigate>
                Buat Listing
            </flux:button>
        </div>
    </div>

    {{-- Search & Filters Card --}}
    <div class="mt-6 p-4 rounded-xl bg-zinc-100/80 dark:bg-zinc-900/80 border border-zinc-200 dark:border-zinc-800 space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
            {{-- Search input --}}
            <div class="sm:col-span-2">
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    placeholder="Cari nama barang atau deskripsi..."
                    icon="magnifying-glass"
                    clearable
                />
            </div>

            {{-- Category select --}}
            <div>
                <flux:select wire:model.live="selectedCategory" placeholder="Semua Kategori">
                    <flux:select.option value="">Semua Kategori</flux:select.option>
                    @foreach ($categories as $cat)
                        <flux:select.option value="{{ $cat->id }}">{{ $cat->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            {{-- Condition select --}}
            <div>
                <flux:select wire:model.live="selectedCondition" placeholder="Semua Kondisi">
                    <flux:select.option value="">Semua Kondisi</flux:select.option>
                    <flux:select.option value="baru">Baru</flux:select.option>
                    <flux:select.option value="bekas">Bekas</flux:select.option>
                </flux:select>
            </div>
        </div>

        @if ($search || $selectedCategory || $selectedCondition || $selectedStatus)
            <div class="flex items-center justify-between pt-2 border-t border-zinc-200 dark:border-zinc-800 text-xs">
                <span class="text-zinc-500">Filter aktif diterapkan</span>
                <flux:button size="xs" variant="ghost" wire:click="resetFilters">
                    Reset Filter
                </flux:button>
            </div>
        @endif
    </div>

    {{-- Listings Grid --}}
    <div class="mt-6">
        @if ($listings->isEmpty())
            <div class="py-16 text-center bg-zinc-50 dark:bg-zinc-900/40 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-8">
                <div class="size-16 rounded-full bg-zinc-200 dark:bg-zinc-800 flex items-center justify-center mx-auto mb-4 text-zinc-400">
                    <flux:icon name="shopping-bag" class="size-8" />
                </div>
                <flux:heading size="lg">Belum Ada Listing</flux:heading>
                <flux:text class="mt-1 max-w-sm mx-auto">
                    @if ($search || $selectedCategory || $selectedCondition)
                        Tidak ada barang yang cocok dengan kriteria filter pencarian Anda.
                    @else
                        Jadilah yang pertama menjual barang di komunitas Anda!
                    @endif
                </flux:text>
                <div class="mt-6">
                    <flux:button variant="primary" icon="plus" :href="route('listing.create')" wire:navigate>
                        Buat Listing Sekarang
                    </flux:button>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
                @foreach ($listings as $listing)
                    <div wire:key="listing-{{ $listing->id }}" class="group bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 hover:border-zinc-300 dark:hover:border-zinc-700 shadow-sm hover:shadow-md transition duration-200 flex flex-col overflow-hidden">
                        {{-- Image area --}}
                        <a href="{{ route('listing.show', $listing) }}" wire:navigate class="block relative aspect-square w-full bg-zinc-100 dark:bg-zinc-800 overflow-hidden">
                            @if ($listing->images->isNotEmpty())
                                <img
                                    src="{{ $listing->images->first()->url }}"
                                    alt="{{ $listing->title }}"
                                    class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                                />
                            @else
                                <div class="w-full h-full flex flex-col items-center justify-center text-zinc-400 bg-zinc-100 dark:bg-zinc-800">
                                    <flux:icon name="photo" class="size-10 stroke-1" />
                                    <span class="text-xs mt-1">Tanpa Foto</span>
                                </div>
                            @endif

                            {{-- Badges on top of image --}}
                            <div class="absolute top-2.5 left-2.5 flex flex-wrap gap-1">
                                <flux:badge size="xs" :color="$listing->condition === 'baru' ? 'emerald' : 'amber'">
                                    {{ ucfirst($listing->condition) }}
                                </flux:badge>
                                @if ($listing->status !== 'tersedia')
                                    <flux:badge size="xs" color="zinc">
                                        {{ ucfirst($listing->status) }}
                                    </flux:badge>
                                @endif
                            </div>

                            @if ($listing->category)
                                <div class="absolute bottom-2.5 left-2.5">
                                    <span class="px-2 py-0.5 rounded-md text-[11px] font-medium bg-black/60 backdrop-blur-md text-white">
                                        {{ $listing->category->name }}
                                    </span>
                                </div>
                            @endif
                        </a>

                        {{-- Card content --}}
                        <div class="p-4 flex-1 flex flex-col justify-between">
                            <div>
                                <a href="{{ route('listing.show', $listing) }}" wire:navigate class="block font-semibold text-zinc-900 dark:text-zinc-100 line-clamp-1 hover:text-primary-600 dark:hover:text-primary-400 transition" title="{{ $listing->title }}">
                                    {{ $listing->title }}
                                </a>

                                <div class="mt-2 text-lg font-bold text-zinc-900 dark:text-zinc-50">
                                    Rp {{ number_format($listing->price, 0, ',', '.') }}
                                </div>

                                <p class="mt-1 text-xs text-zinc-500 line-clamp-2">
                                    {{ $listing->description }}
                                </p>
                            </div>

                            {{-- Footer with seller & actions --}}
                            <div class="mt-4 pt-3 border-t border-zinc-100 dark:border-zinc-800/80 flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2 min-w-0">
                                    <div class="size-6 rounded-full bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center font-bold text-[10px] text-zinc-700 dark:text-zinc-300 shrink-0">
                                        {{ $listing->creator?->initials() ?? '?' }}
                                    </div>
                                    <span class="text-xs text-zinc-600 dark:text-zinc-400 truncate">
                                        {{ $listing->creator?->name ?? 'Penjual' }}
                                    </span>
                                </div>

                                {{-- Actions --}}
                                <div class="flex items-center gap-1 shrink-0">
                                    {{-- Chat button for other users --}}
                                    @if (auth()->id() !== $listing->user_id)
                                        <flux:button
                                            size="xs"
                                            variant="primary"
                                            icon="chat-bubble-left-right"
                                            wire:click="startChat({{ $listing->id }})"
                                            title="Chat Penjual"
                                        >
                                            Chat
                                        </flux:button>
                                    @endif

                                    {{-- Creator can edit --}}
                                    @if (auth()->id() === $listing->user_id)
                                        <flux:button
                                            size="xs"
                                            variant="subtle"
                                            icon="pencil-square"
                                            :href="route('listing.edit', $listing)"
                                            wire:navigate
                                            title="Edit listing"
                                        />
                                    @endif

                                    {{-- Owner or Admin can delete --}}
                                    @if (auth()->id() === $listing->user_id || auth()->user()?->isAdmin())
                                        <flux:button
                                            size="xs"
                                            variant="subtle"
                                            icon="trash"
                                            class="text-red-500 hover:text-red-600"
                                            wire:click="delete({{ $listing->id }})"
                                            wire:confirm="Yakin ingin menghapus listing '{{ $listing->title }}'?"
                                            title="Hapus listing"
                                        />
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-6">
                {{ $listings->links() }}
            </div>
        @endif
    </div>
</div>

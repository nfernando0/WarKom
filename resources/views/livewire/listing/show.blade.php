<div>
    {{-- Breadcrumbs --}}
    <div class="bg-zinc-200 dark:bg-zinc-800 p-4 rounded-xl border border-zinc-300 dark:border-zinc-700">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item :href="route('dashboard')" wire:navigate>Home</flux:breadcrumbs.item>
            <flux:breadcrumbs.item :href="route('listing.index')" wire:navigate>Marketplace</flux:breadcrumbs.item>
            <flux:breadcrumbs.item class="truncate max-w-[200px] sm:max-w-md">{{ $listing->title }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </div>

    {{-- Notifications --}}
    @if (session()->has('warning'))
        <div class="mt-4">
            <flux:callout variant="warning" icon="exclamation-triangle" :heading="session('warning')" />
        </div>
    @endif

    {{-- Main Product Layout --}}
    <div class="mt-6 grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        {{-- Left Column: Images Gallery & Description (7 Cols) --}}
        <div class="lg:col-span-7 space-y-6">
            {{-- Image Gallery --}}
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-4 shadow-sm overflow-hidden">
                @php
                    $images = $listing->images;
                    $selectedImage = $images->get($selectedImageIndex) ?? $images->first();
                @endphp

                {{-- Main Image Box --}}
                <div class="relative aspect-4/3 w-full rounded-xl overflow-hidden bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center">
                    @if ($selectedImage)
                        <img
                            src="{{ $selectedImage->url }}"
                            alt="{{ $listing->title }}"
                            class="w-full h-full object-contain"
                        />
                    @else
                        <div class="flex flex-col items-center justify-center text-zinc-400">
                            <flux:icon name="photo" class="size-16 stroke-1" />
                            <span class="text-sm mt-2">Tidak ada foto produk</span>
                        </div>
                    @endif

                    {{-- Badges --}}
                    <div class="absolute top-3 left-3 flex flex-wrap gap-1.5">
                        <flux:badge :color="$listing->condition === 'baru' ? 'emerald' : 'amber'">
                            {{ ucfirst($listing->condition) }}
                        </flux:badge>
                        @if ($listing->status !== 'tersedia')
                            <flux:badge color="zinc">
                                {{ ucfirst($listing->status) }}
                            </flux:badge>
                        @endif
                    </div>
                </div>

                {{-- Image Thumbnails List --}}
                @if ($images->count() > 1)
                    <div class="mt-3 flex items-center gap-3 overflow-x-auto pb-1">
                        @foreach ($images as $index => $img)
                            <button
                                type="button"
                                wire:click="selectImage({{ $index }})"
                                class="relative size-16 rounded-lg overflow-hidden shrink-0 border-2 transition {{ $selectedImageIndex === $index ? 'border-primary-600 dark:border-primary-500 scale-95 shadow-sm' : 'border-transparent opacity-70 hover:opacity-100' }}"
                            >
                                <img src="{{ $img->url }}" class="w-full h-full object-cover" />
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Product Description Card --}}
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-6 shadow-sm space-y-4">
                <flux:heading size="lg">Deskripsi Barang</flux:heading>
                <div class="text-sm text-zinc-700 dark:text-zinc-300 whitespace-pre-line leading-relaxed">
                    {{ $listing->description }}
                </div>

                <div class="pt-4 border-t border-zinc-100 dark:border-zinc-800 flex flex-wrap items-center gap-6 text-xs text-zinc-500">
                    <div>
                        <span class="text-zinc-400">Kategori:</span>
                        <strong class="text-zinc-800 dark:text-zinc-200 ml-1">{{ $listing->category?->name ?? '-' }}</strong>
                    </div>
                    <div>
                        <span class="text-zinc-400">Dipublikasikan:</span>
                        <strong class="text-zinc-800 dark:text-zinc-200 ml-1">{{ $listing->created_at->translatedFormat('d F Y') }}</strong>
                    </div>
                    <div>
                        <span class="text-zinc-400">Komunitas:</span>
                        <strong class="text-zinc-800 dark:text-zinc-200 ml-1">{{ $listing->community?->name ?? '-' }}</strong>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Buy / Chat Action Box & Seller Card (5 Cols) --}}
        <div class="lg:col-span-5 space-y-6">
            {{-- Price & Summary Card --}}
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-6 shadow-sm space-y-5">
                <div>
                    <span class="text-xs uppercase font-bold tracking-wider text-primary-600 dark:text-primary-400">
                        {{ $listing->category?->name ?? 'Marketplace' }}
                    </span>
                    <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 mt-1">
                        {{ $listing->title }}
                    </h1>
                    <div class="mt-4 text-3xl font-extrabold text-zinc-900 dark:text-zinc-50">
                        Rp {{ number_format($listing->price, 0, ',', '.') }}
                    </div>
                </div>

                <div class="p-3.5 bg-zinc-50 dark:bg-zinc-800/50 rounded-xl border border-zinc-200/70 dark:border-zinc-800 flex items-center justify-between text-xs">
                    <span class="text-zinc-500">Status Ketersediaan</span>
                    <flux:badge size="sm" :color="$listing->status === 'tersedia' ? 'emerald' : 'zinc'">
                        {{ ucfirst($listing->status) }}
                    </flux:badge>
                </div>

                {{-- Action Box --}}
                @if ($isOwner)
                    {{-- Owner view --}}
                    <div class="p-4 bg-amber-500/10 border border-amber-500/20 rounded-xl space-y-3">
                        <div class="flex items-center gap-2 text-xs font-semibold text-amber-700 dark:text-amber-400">
                            <flux:icon name="information-circle" class="size-4" />
                            <span>Ini adalah barang yang Anda pasang di marketplace.</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <flux:button variant="primary" icon="pencil-square" :href="route('listing.edit', $listing)" wire:navigate class="w-full">
                                Edit Listing
                            </flux:button>
                            <flux:button
                                variant="subtle"
                                icon="trash"
                                class="text-red-500 hover:text-red-600"
                                wire:click="delete"
                                wire:confirm="Yakin ingin menghapus listing ini?"
                            >
                                Hapus
                            </flux:button>
                        </div>
                    </div>
                @else
                    {{-- Buyer Action Section --}}
                    <div class="pt-2 space-y-4">
                        @if ($listing->status === 'tersedia')
                            <flux:button
                                variant="primary"
                                icon="shopping-bag"
                                wire:click="createTransaction"
                                wire:confirm="Buat transaksi untuk barang ini seharga Rp {{ number_format($listing->price, 0, ',', '.') }}?"
                                wire:loading.attr="disabled"
                                class="w-full text-center justify-center py-3 text-base font-bold shadow-md bg-emerald-600 hover:bg-emerald-700 text-white"
                            >
                                <span wire:loading.remove wire:target="createTransaction">Beli Barang Ini</span>
                                <span wire:loading wire:target="createTransaction">Memproses Transaksi...</span>
                            </flux:button>
                        @endif

                        <div class="space-y-3 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                            <flux:field>
                                <flux:label class="text-xs">Kirim Pesan Cepat ke Penjual</flux:label>
                                <flux:textarea
                                    wire:model="quickMessage"
                                    rows="2"
                                    placeholder="Tulis pesan untuk penjual..."
                                />
                            </flux:field>

                            <flux:button
                                variant="subtle"
                                icon="chat-bubble-left-right"
                                wire:click="startChat"
                                wire:loading.attr="disabled"
                                class="w-full text-center justify-center py-2.5 border border-zinc-300 dark:border-zinc-700"
                            >
                                <span wire:loading.remove wire:target="startChat">Chat Penjual</span>
                                <span wire:loading wire:target="startChat">Menghubungkan...</span>
                            </flux:button>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Seller Profile Card --}}
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-6 shadow-sm space-y-4">
                <flux:heading size="sm" class="text-zinc-400 uppercase tracking-wider text-xs">Informasi Penjual</flux:heading>
                
                <div class="flex items-center gap-4">
                    <div class="size-14 rounded-full bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center font-bold text-base text-zinc-700 dark:text-zinc-300 shrink-0">
                        {{ $listing->creator?->initials() ?? '?' }}
                    </div>

                    <div class="min-w-0 flex-1">
                        <h3 class="font-bold text-zinc-900 dark:text-zinc-100 text-base truncate">
                            {{ $listing->creator?->name ?? 'Penjual' }}
                        </h3>
                        <p class="text-xs text-zinc-500 truncate">
                            @if ($listing->creator?->community)
                                Anggota {{ $listing->creator->community->name }}
                            @else
                                Anggota Terverifikasi
                            @endif
                        </p>
                        <p class="text-[11px] text-zinc-400 mt-1">
                            Bergabung {{ $listing->creator?->created_at?->translatedFormat('F Y') }}
                        </p>
                    </div>
                </div>

                @if (! $isOwner)
                    <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between text-xs text-zinc-500">
                        <span class="flex items-center gap-1">
                            <flux:icon name="shield-check" class="size-4 text-emerald-500" />
                            Transaksi Aman di Komunitas
                        </span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Other Listings in Community --}}
    @if ($otherListings->isNotEmpty())
        <div class="mt-12 pt-8 border-t border-zinc-200 dark:border-zinc-800">
            <div class="flex items-center justify-between mb-6">
                <flux:heading size="lg">Barang Lainnya di Komunitas Ini</flux:heading>
                <flux:button variant="ghost" size="sm" :href="route('listing.index')" wire:navigate>
                    Lihat Semua
                </flux:button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-5">
                @foreach ($otherListings as $other)
                    <div wire:key="other-listing-{{ $other->id }}" class="group bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 hover:border-zinc-300 dark:hover:border-zinc-700 shadow-xs hover:shadow-md transition flex flex-col overflow-hidden">
                        <a href="{{ route('listing.show', $other) }}" wire:navigate class="block relative aspect-square w-full bg-zinc-100 dark:bg-zinc-800 overflow-hidden">
                            @if ($other->images->isNotEmpty())
                                <img src="{{ $other->images->first()->url }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" />
                            @else
                                <div class="w-full h-full flex items-center justify-center text-zinc-400">
                                    <flux:icon name="photo" class="size-8" />
                                </div>
                            @endif
                        </a>
                        <div class="p-3 flex-1 flex flex-col justify-between">
                            <div>
                                <a href="{{ route('listing.show', $other) }}" wire:navigate class="font-semibold text-sm text-zinc-900 dark:text-zinc-100 line-clamp-1 hover:text-primary-600 dark:hover:text-primary-400">
                                    {{ $other->title }}
                                </a>
                                <div class="mt-1 font-bold text-sm text-zinc-900 dark:text-zinc-50">
                                    Rp {{ number_format($other->price, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

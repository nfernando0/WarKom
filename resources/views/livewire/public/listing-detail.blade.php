<div class="py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
        
        {{-- Breadcrumbs --}}
        <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-2xs">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item :href="route('home')" wire:navigate>Home</flux:breadcrumbs.item>
                <flux:breadcrumbs.item :href="route('public.marketplace')" wire:navigate>Marketplace</flux:breadcrumbs.item>
                @if ($listing->category)
                    <flux:breadcrumbs.item :href="route('public.marketplace', ['cat' => $listing->category_id])" wire:navigate>
                        {{ $listing->category->name }}
                    </flux:breadcrumbs.item>
                @endif
                <flux:breadcrumbs.item class="truncate max-w-[200px]">{{ $listing->title }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>
        </div>

        {{-- Notifications --}}
        @if (session()->has('info'))
            <flux:callout variant="info" icon="information-circle" :heading="session('info')" />
        @endif
        @if (session()->has('error'))
            <flux:callout variant="danger" icon="exclamation-circle" :heading="session('error')" />
        @endif
        @if (session()->has('success'))
            <flux:callout variant="success" icon="check-circle" :heading="session('success')" />
        @endif

        {{-- Main Detail Card --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10">
            
            {{-- Left Column: Gallery (7 Cols) --}}
            <div class="lg:col-span-7 space-y-4">
                {{-- Main Active Photo --}}
                <div class="relative aspect-4/3 w-full rounded-2xl bg-zinc-100 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 overflow-hidden shadow-sm">
                    @if ($listing->images->isNotEmpty())
                        @php
                            $activeImg = $listing->images[$selectedImageIndex] ?? $listing->images->first();
                        @endphp
                        <img
                            src="{{ $activeImg->url }}"
                            alt="{{ $listing->title }}"
                            class="w-full h-full object-cover"
                        />
                    @else
                        <div class="w-full h-full flex flex-col items-center justify-center text-zinc-400">
                            <flux:icon name="photo" class="size-16 stroke-1" />
                            <span class="text-sm mt-2">Tidak ada foto produk</span>
                        </div>
                    @endif

                    {{-- Badges on main image --}}
                    <div class="absolute top-3 left-3 flex items-center gap-2">
                        <flux:badge size="sm" :color="$listing->condition === 'baru' ? 'emerald' : 'amber'">
                            {{ ucfirst($listing->condition) }}
                        </flux:badge>
                        @if ($listing->status !== 'tersedia')
                            <flux:badge size="sm" color="zinc">
                                {{ ucfirst($listing->status) }}
                            </flux:badge>
                        @endif
                    </div>
                </div>

                {{-- Thumbnails Strip --}}
                @if ($listing->images->count() > 1)
                    <div class="flex items-center gap-3 overflow-x-auto pb-2">
                        @foreach ($listing->images as $index => $img)
                            <button
                                type="button"
                                wire:click="selectImage({{ $index }})"
                                class="relative size-20 rounded-xl overflow-hidden border-2 transition shrink-0 {{ $selectedImageIndex === $index ? 'border-primary-500 shadow-md ring-2 ring-primary-500/20' : 'border-zinc-200 dark:border-zinc-800 opacity-70 hover:opacity-100' }}"
                            >
                                <img src="{{ $img->url }}" class="w-full h-full object-cover" />
                            </button>
                        @endforeach
                    </div>
                @endif

                {{-- Product Description --}}
                <div class="p-6 bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-4">
                    <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Deskripsi Lengkap</h2>
                    <p class="text-sm text-zinc-600 dark:text-zinc-300 leading-relaxed whitespace-pre-line">
                        {{ $listing->description }}
                    </p>
                </div>
            </div>

            {{-- Right Column: Information & Actions (5 Cols) --}}
            <div class="lg:col-span-5 space-y-6">
                
                {{-- Price & Title Card --}}
                <div class="p-6 bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-5">
                    <div>
                        <div class="flex flex-wrap items-center gap-2 mb-2">
                            @if ($listing->category)
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary-500/10 text-primary-600 dark:text-primary-400">
                                    {{ $listing->category->name }}
                                </span>
                            @endif
                            @if ($listing->community)
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300">
                                    📍 {{ $listing->community->name }}
                                </span>
                            @endif
                        </div>

                        <h1 class="text-2xl sm:text-3xl font-extrabold text-zinc-900 dark:text-zinc-50 leading-tight">
                            {{ $listing->title }}
                        </h1>
                    </div>

                    {{-- Price --}}
                    <div class="p-4 bg-zinc-50 dark:bg-zinc-800/60 rounded-xl">
                        <span class="text-xs text-zinc-500 font-medium block">Harga Jual</span>
                        <span class="text-3xl font-extrabold text-zinc-900 dark:text-zinc-50">
                            Rp {{ number_format($listing->price, 0, ',', '.') }}
                        </span>
                    </div>

                    {{-- Specs summary --}}
                    <div class="grid grid-cols-2 gap-3 text-xs text-zinc-500">
                        <div class="p-3 rounded-lg bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-100 dark:border-zinc-800">
                            <span class="block text-zinc-400">Kondisi</span>
                            <span class="font-bold text-zinc-800 dark:text-zinc-200 mt-0.5 block">{{ ucfirst($listing->condition) }}</span>
                        </div>
                        <div class="p-3 rounded-lg bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-100 dark:border-zinc-800">
                            <span class="block text-zinc-400">Tanggal Pasang</span>
                            <span class="font-bold text-zinc-800 dark:text-zinc-200 mt-0.5 block">{{ $listing->created_at->translatedFormat('d M Y') }}</span>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="pt-2 space-y-2.5">
                        @if ($listing->status === 'tersedia')
                            @if (auth()->id() !== $listing->user_id)
                                <flux:button
                                    variant="primary"
                                    icon="shopping-cart"
                                    wire:click="buyNow"
                                    class="w-full py-3 font-bold justify-center"
                                >
                                    Beli Barang Ini
                                </flux:button>
                                <flux:button
                                    variant="subtle"
                                    icon="chat-bubble-left-right"
                                    wire:click="startChat"
                                    class="w-full py-3 font-semibold justify-center"
                                >
                                    Chat Penjual
                                </flux:button>
                            @else
                                <div class="p-3 bg-zinc-100 dark:bg-zinc-800 rounded-xl text-center text-xs text-zinc-500 font-medium">
                                    Ini adalah barang yang Anda jual sendiri.
                                </div>
                            @endif
                        @else
                            <div class="p-3 bg-amber-500/10 border border-amber-500/20 text-amber-600 dark:text-amber-400 rounded-xl text-center text-xs font-semibold">
                                Barang ini berstatus {{ strtoupper($listing->status) }}
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Seller Information Card --}}
                <div class="p-6 bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-4">
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Informasi Penjual</h3>

                    <a href="{{ route('user.profile', $listing->creator) }}" wire:navigate class="flex items-center gap-3.5 group hover:opacity-90 transition">
                        <div class="size-12 rounded-full bg-primary-600 text-white flex items-center justify-center font-bold text-sm shadow-sm group-hover:scale-105 transition">
                            {{ $listing->creator?->initials() ?? '?' }}
                        </div>
                        <div>
                            <span class="font-bold text-base text-zinc-900 dark:text-zinc-100 block group-hover:text-primary-600 transition">
                                {{ $listing->creator?->name ?? 'Penjual' }}
                            </span>
                            <div class="flex items-center gap-1 text-xs text-amber-500 mt-0.5">
                                <flux:icon name="star" class="size-3.5 fill-amber-400 text-amber-400" />
                                <span class="font-bold text-zinc-700 dark:text-zinc-300">{{ number_format($listing->creator?->averageRating() ?? 5.0, 1) }}</span>
                                <span class="text-zinc-400">({{ $listing->creator?->reviewsReceived()->count() ?? 0 }} ulasan)</span>
                            </div>
                        </div>
                    </a>

                    @if ($listing->community)
                        <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800 text-xs space-y-1">
                            <span class="text-zinc-400">Anggota Komunitas:</span>
                            <p class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $listing->community->name }}</p>
                            @if ($listing->community->location)
                                <p class="text-zinc-500 text-[11px]">{{ $listing->community->location }}</p>
                            @endif
                        </div>
                    @endif
                </div>

            </div>
        </div>

        {{-- Related Listings --}}
        @if ($relatedListings->isNotEmpty())
            <div class="pt-8 border-t border-zinc-200 dark:border-zinc-800 space-y-6">
                <div>
                    <h3 class="text-xl font-bold text-zinc-900 dark:text-zinc-100">Barang Serupa Lainnya</h3>
                    <p class="text-xs text-zinc-500">Pilihan lain dalam kategori {{ $listing->category?->name }}</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-5">
                    @foreach ($relatedListings as $rel)
                        <a href="{{ route('public.listing.show', $rel) }}" wire:navigate class="group bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 overflow-hidden shadow-xs hover:shadow-md transition">
                            <div class="aspect-4/3 w-full bg-zinc-100 dark:bg-zinc-800 overflow-hidden">
                                @if ($rel->images->isNotEmpty())
                                    <img src="{{ $rel->images->first()->url }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" />
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-zinc-400">
                                        <flux:icon name="photo" class="size-8 stroke-1" />
                                    </div>
                                @endif
                            </div>
                            <div class="p-3 space-y-1">
                                <h4 class="font-semibold text-sm text-zinc-900 dark:text-zinc-100 truncate group-hover:text-primary-600 transition">{{ $rel->title }}</h4>
                                <p class="text-sm font-bold text-zinc-900 dark:text-zinc-50">Rp {{ number_format($rel->price, 0, ',', '.') }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

    </div>

    {{-- Checkout Confirmation Modal --}}
    @if ($showCheckoutModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <div class="bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-200 dark:border-zinc-800 shadow-2xl max-w-lg w-full p-6 space-y-6 animate-in fade-in zoom-in-95 duration-150">
                
                {{-- Header --}}
                <div class="flex items-center justify-between pb-4 border-b border-zinc-100 dark:border-zinc-800">
                    <div>
                        <flux:heading size="lg">Konfirmasi Pembelian</flux:heading>
                        <flux:subheading>Pastikan rincian pesanan Anda sudah sesuai</flux:subheading>
                    </div>
                    <flux:button size="xs" variant="ghost" icon="x-mark" wire:click="closeCheckoutModal" />
                </div>

                {{-- Product Summary --}}
                <div class="flex items-center gap-3.5 p-3.5 bg-zinc-50 dark:bg-zinc-800/40 rounded-2xl border border-zinc-100 dark:border-zinc-800">
                    <div class="size-16 rounded-xl bg-zinc-200 dark:bg-zinc-700 overflow-hidden shrink-0">
                        @if ($listing->images->isNotEmpty())
                            <img src="{{ $listing->images->first()->url }}" class="w-full h-full object-cover" />
                        @else
                            <div class="w-full h-full flex items-center justify-center text-zinc-400">
                                <flux:icon name="photo" class="size-6" />
                            </div>
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <span class="font-bold text-sm text-zinc-900 dark:text-zinc-100 block truncate">
                            {{ $listing->title }}
                        </span>
                        <span class="text-xs text-zinc-500 block">
                            Penjual: <strong>{{ $listing->creator?->name }}</strong>
                        </span>
                        <span class="text-base font-extrabold text-primary-600 dark:text-primary-400 block mt-0.5">
                            Rp {{ number_format($listing->price, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                {{-- Payment Method Selection --}}
                <div class="space-y-3">
                    <label class="block text-xs font-bold uppercase tracking-wider text-zinc-500">Pilih Metode Pembayaran</label>
                    
                    <div class="grid grid-cols-1 gap-2.5">
                        <label class="flex items-center justify-between p-3.5 rounded-xl border cursor-pointer transition {{ $paymentChannel === 'cod' ? 'border-primary-600 bg-primary-50/40 dark:bg-primary-950/20 ring-2 ring-primary-500/20' : 'border-zinc-200 dark:border-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-800/40' }}">
                            <div class="flex items-center gap-3">
                                <input type="radio" wire:model.live="paymentChannel" value="cod" class="text-primary-600 focus:ring-primary-500" />
                                <div>
                                    <span class="font-bold text-sm text-zinc-900 dark:text-zinc-100 block">COD (Bayar di Tempat)</span>
                                    <span class="text-xs text-zinc-500 block">Bayar tunai langsung saat serah terima barang</span>
                                </div>
                            </div>
                            <flux:icon name="banknotes" class="size-5 text-zinc-400" />
                        </label>

                        <label class="flex items-center justify-between p-3.5 rounded-xl border cursor-pointer transition {{ $paymentChannel === 'qris' ? 'border-primary-600 bg-primary-50/40 dark:bg-primary-950/20 ring-2 ring-primary-500/20' : 'border-zinc-200 dark:border-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-800/40' }}">
                            <div class="flex items-center gap-3">
                                <input type="radio" wire:model.live="paymentChannel" value="qris" class="text-primary-600 focus:ring-primary-500" />
                                <div>
                                    <span class="font-bold text-sm text-zinc-900 dark:text-zinc-100 block">QRIS (Instant Pay)</span>
                                    <span class="text-xs text-zinc-500 block">Scan QRIS dari e-wallet / mobile banking apa saja</span>
                                </div>
                            </div>
                            <flux:icon name="qr-code" class="size-5 text-zinc-400" />
                        </label>

                        <label class="flex items-center justify-between p-3.5 rounded-xl border cursor-pointer transition {{ $paymentChannel === 'bank_transfer' ? 'border-primary-600 bg-primary-50/40 dark:bg-primary-950/20 ring-2 ring-primary-500/20' : 'border-zinc-200 dark:border-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-800/40' }}">
                            <div class="flex items-center gap-3">
                                <input type="radio" wire:model.live="paymentChannel" value="bank_transfer" class="text-primary-600 focus:ring-primary-500" />
                                <div>
                                    <span class="font-bold text-sm text-zinc-900 dark:text-zinc-100 block">Transfer Bank / VA</span>
                                    <span class="text-xs text-zinc-500 block">Transfer virtual account antar bank</span>
                                </div>
                            </div>
                            <flux:icon name="credit-card" class="size-5 text-zinc-400" />
                        </label>
                    </div>
                </div>

                {{-- Notes for Seller --}}
                <flux:field>
                    <flux:label>Catatan untuk Penjual (Opsional)</flux:label>
                    <flux:input
                        wire:model="notes"
                        placeholder="Contoh: Boleh ketemuan di pos satpam jam 4 sore?"
                    />
                </flux:field>

                {{-- Cost Summary --}}
                <div class="p-3.5 bg-zinc-50 dark:bg-zinc-800/50 rounded-2xl border border-zinc-100 dark:border-zinc-800 space-y-1.5 text-xs">
                    <div class="flex items-center justify-between text-zinc-500">
                        <span>Harga Barang</span>
                        <span>Rp {{ number_format($listing->price, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between text-zinc-500">
                        <span>Biaya Layanan Komunitas</span>
                        <span class="text-emerald-600 font-bold">Gratis (Rp 0)</span>
                    </div>
                    <div class="flex items-center justify-between pt-1.5 border-t border-zinc-200 dark:border-zinc-700 text-sm font-extrabold text-zinc-900 dark:text-zinc-100">
                        <span>Total Pembayaran</span>
                        <span class="text-primary-600 dark:text-primary-400">Rp {{ number_format($listing->price, 0, ',', '.') }}</span>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="pt-2 flex items-center justify-end gap-2.5">
                    <flux:button variant="ghost" wire:click="closeCheckoutModal">
                        Batal
                    </flux:button>
                    <flux:button
                        variant="primary"
                        wire:click="confirmPurchase"
                        wire:loading.attr="disabled"
                        icon="check"
                        class="font-bold"
                    >
                        <span wire:loading.remove wire:target="confirmPurchase">Konfirmasi Pesanan</span>
                        <span wire:loading wire:target="confirmPurchase">Memproses...</span>
                    </flux:button>
                </div>

            </div>
        </div>
    @endif
</div>

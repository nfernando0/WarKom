<div class="space-y-6">
    {{-- Breadcrumbs --}}
    <div class="bg-zinc-200 dark:bg-zinc-800 p-4 rounded-xl border border-zinc-300 dark:border-zinc-700">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item :href="route('dashboard')" wire:navigate>Home</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Transaksi</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </div>

    {{-- Notifications --}}
    @if (session()->has('success'))
        <flux:callout variant="success" icon="check-circle" :heading="session('success')" />
    @endif
    @if (session()->has('warning'))
        <flux:callout variant="warning" icon="exclamation-triangle" :heading="session('warning')" />
    @endif

    {{-- Header & Stats --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">Kelola Transaksi</flux:heading>
            <flux:subheading>
                Pantau proses jual-beli barang, selesaikan transaksi, dan berikan penilaian ulasan komunitas.
            </flux:subheading>
        </div>
        <div class="shrink-0 flex items-center gap-2">
            <flux:button variant="primary" icon="shopping-bag" :href="route('listing.index')" wire:navigate>
                Jelajahi Marketplace
            </flux:button>
        </div>
    </div>

    {{-- Statistics Metric Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-xs flex items-center gap-3.5">
            <div class="size-11 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-600 dark:text-zinc-400">
                <flux:icon name="receipt-percent" class="size-6" />
            </div>
            <div>
                <p class="text-xs text-zinc-500 font-medium">Total Transaksi</p>
                <p class="text-xl font-bold text-zinc-900 dark:text-zinc-100">{{ $stats['total'] }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-xs flex items-center gap-3.5">
            <div class="size-11 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                <flux:icon name="clock" class="size-6" />
            </div>
            <div>
                <p class="text-xs text-zinc-500 font-medium">Menunggu (Pending)</p>
                <p class="text-xl font-bold text-amber-600 dark:text-amber-400">{{ $stats['pending'] }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-xs flex items-center gap-3.5">
            <div class="size-11 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                <flux:icon name="check-badge" class="size-6" />
            </div>
            <div>
                <p class="text-xs text-zinc-500 font-medium">Selesai</p>
                <p class="text-xl font-bold text-emerald-600 dark:text-emerald-400">{{ $stats['completed'] }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-xs flex items-center gap-3.5">
            <div class="size-11 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-400 flex items-center justify-center">
                <flux:icon name="x-circle" class="size-6" />
            </div>
            <div>
                <p class="text-xs text-zinc-500 font-medium">Dibatalkan</p>
                <p class="text-xl font-bold text-zinc-600 dark:text-zinc-400">{{ $stats['cancelled'] }}</p>
            </div>
        </div>
    </div>

    {{-- Filters & Search --}}
    <div class="p-4 bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            {{-- Role Tab Pills --}}
            <div class="flex items-center gap-1.5 p-1 bg-zinc-100 dark:bg-zinc-800 rounded-lg text-xs font-medium w-full md:w-auto">
                <button
                    type="button"
                    wire:click="setRoleFilter('all')"
                    class="flex-1 md:flex-none py-1.5 px-3 rounded-md transition text-center {{ $roleFilter === 'all' ? 'bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 shadow-xs font-semibold' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200' }}"
                >
                    Semua Transaksi
                </button>
                <button
                    type="button"
                    wire:click="setRoleFilter('buying')"
                    class="flex-1 md:flex-none py-1.5 px-3 rounded-md transition text-center {{ $roleFilter === 'buying' ? 'bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 shadow-xs font-semibold' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200' }}"
                >
                    Pembelian Saya
                </button>
                <button
                    type="button"
                    wire:click="setRoleFilter('selling')"
                    class="flex-1 md:flex-none py-1.5 px-3 rounded-md transition text-center {{ $roleFilter === 'selling' ? 'bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 shadow-xs font-semibold' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200' }}"
                >
                    Penjualan Saya
                </button>
            </div>

            {{-- Status & Search Row --}}
            <div class="flex flex-col sm:flex-row items-center gap-2.5 w-full md:w-auto">
                <div class="w-full sm:w-44">
                    <flux:select wire:model.live="statusFilter" size="sm">
                        <flux:select.option value="all">Semua Status</flux:select.option>
                        <flux:select.option value="pending">Menunggu (Pending)</flux:select.option>
                        <flux:select.option value="selesai">Selesai</flux:select.option>
                        <flux:select.option value="dibatalkan">Dibatalkan</flux:select.option>
                    </flux:select>
                </div>

                <div class="w-full sm:w-64">
                    <flux:input
                        wire:model.live.debounce.300ms="search"
                        placeholder="Cari barang atau nama..."
                        icon="magnifying-glass"
                        size="sm"
                        clearable
                    />
                </div>
            </div>
        </div>
    </div>

    {{-- Transactions List --}}
    <div>
        @if ($transactions->isEmpty())
            <div class="py-16 text-center bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-8 shadow-xs">
                <div class="size-16 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center mx-auto mb-4 text-zinc-400">
                    <flux:icon name="receipt-percent" class="size-8 stroke-1" />
                </div>
                <flux:heading size="lg">Belum Ada Transaksi</flux:heading>
                <flux:text class="mt-1 max-w-sm mx-auto">
                    @if ($search || $statusFilter !== 'all' || $roleFilter !== 'all')
                        Tidak ditemukan transaksi yang cocok dengan filter pencarian Anda.
                    @else
                        Mulai berbelanja atau jual barang di marketplace komunitas Anda.
                    @endif
                </flux:text>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($transactions as $trans)
                    @php
                        $isBuyer = ($trans->buyer_id === $currentUserId);
                        $isSeller = ($trans->seller_id === $currentUserId);
                        $otherUser = $trans->getOtherUser($currentUserId);
                        $listing = $trans->listing;
                        $myReview = $trans->userReview($currentUserId);
                    @endphp

                    <div wire:key="transaction-card-{{ $trans->id }}" class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs hover:border-zinc-300 dark:hover:border-zinc-700 transition overflow-hidden">
                        {{-- Card Header --}}
                        <div class="px-5 py-3 bg-zinc-50 dark:bg-zinc-800/50 border-b border-zinc-200/80 dark:border-zinc-800 flex flex-wrap items-center justify-between gap-3 text-xs">
                            <div class="flex items-center gap-3 text-zinc-500">
                                <span>ID Transaksi: <strong class="text-zinc-800 dark:text-zinc-200 font-mono">#TRX-{{ str_pad($trans->id, 5, '0', STR_PAD_LEFT) }}</strong></span>
                                <span>&bull;</span>
                                <span>{{ $trans->created_at->translatedFormat('d M Y, H:i') }}</span>
                            </div>

                            <div class="flex items-center gap-2">
                                <flux:badge size="xs" :color="$isBuyer ? 'sky' : 'emerald'">
                                    {{ $isBuyer ? 'Sebagai Pembeli' : 'Sebagai Penjual' }}
                                </flux:badge>

                                @if ($trans->status === 'pending')
                                    <flux:badge size="xs" color="amber" icon="clock">
                                        Menunggu Penyelesaian
                                    </flux:badge>
                                @elseif ($trans->status === 'selesai')
                                    <flux:badge size="xs" color="emerald" icon="check-circle">
                                        Selesai
                                    </flux:badge>
                                @else
                                    <flux:badge size="xs" color="zinc" icon="x-circle">
                                        Dibatalkan
                                    </flux:badge>
                                @endif
                            </div>
                        </div>

                        {{-- Card Body --}}
                        <div class="p-5 flex flex-col md:flex-row md:items-center justify-between gap-5">
                            {{-- Product & Counterpart Info --}}
                            <div class="flex items-start gap-4 min-w-0 flex-1">
                                {{-- Thumbnail --}}
                                <div class="size-20 rounded-xl overflow-hidden bg-zinc-100 dark:bg-zinc-800 shrink-0 border border-zinc-200 dark:border-zinc-700">
                                    @if ($listing && $listing->images->isNotEmpty())
                                        <img src="{{ $listing->images->first()->url }}" alt="{{ $listing->title }}" class="w-full h-full object-cover" />
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-zinc-400">
                                            <flux:icon name="photo" class="size-7" />
                                        </div>
                                    @endif
                                </div>

                                <div class="min-w-0 flex-1 space-y-1">
                                    @if ($listing)
                                        <a href="{{ route('listing.show', $listing) }}" wire:navigate class="font-bold text-base text-zinc-900 dark:text-zinc-100 hover:text-primary-600 dark:hover:text-primary-400 truncate block">
                                            {{ $listing->title }}
                                        </a>
                                    @else
                                        <span class="font-bold text-base text-zinc-500 italic">Barang telah dihapus</span>
                                    @endif

                                    <div class="flex flex-wrap items-center gap-3 text-xs text-zinc-500 pt-0.5">
                                        <div class="flex items-center gap-1.5">
                                            <span>{{ $isBuyer ? 'Penjual:' : 'Pembeli:' }}</span>
                                            <div class="flex items-center gap-1 font-medium text-zinc-800 dark:text-zinc-200">
                                                <div class="size-4 rounded-full bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center text-[9px] font-bold">
                                                    {{ $otherUser?->initials() ?? '?' }}
                                                </div>
                                                <span>{{ $otherUser?->name ?? 'Pengguna' }}</span>
                                            </div>
                                        </div>
                                        @if ($listing?->category)
                                            <span>&bull;</span>
                                            <span>Kategori: {{ $listing->category->name }}</span>
                                        @endif
                                    </div>

                                    @if ($trans->completed_at)
                                        <p class="text-[11px] text-zinc-400">
                                            Diselesaikan pada {{ $trans->completed_at->translatedFormat('d F Y, H:i') }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            {{-- Price & Actions --}}
                            <div class="flex flex-col sm:flex-row md:flex-col items-start sm:items-center md:items-end justify-between gap-3 shrink-0 pt-3 md:pt-0 border-t md:border-t-0 border-zinc-100 dark:border-zinc-800">
                                <div>
                                    <span class="text-xs text-zinc-400 block md:text-right">Total Transaksi:</span>
                                    <span class="text-xl font-extrabold text-zinc-900 dark:text-zinc-50">
                                        Rp {{ number_format($trans->price, 0, ',', '.') }}
                                    </span>
                                </div>

                                {{-- Action Buttons --}}
                                <div class="flex flex-wrap items-center gap-2 mt-1">
                                    {{-- If Pending --}}
                                    @if ($trans->isPending())
                                        <flux:button
                                            size="sm"
                                            variant="primary"
                                            icon="check"
                                            wire:click="completeTransaction({{ $trans->id }})"
                                            wire:confirm="Konfirmasi bahwa transaksi telah selesai (barang telah diterima/diserahkan)?"
                                        >
                                            Selesaikan Transaksi
                                        </flux:button>
                                        <flux:button
                                            size="sm"
                                            variant="subtle"
                                            icon="x-mark"
                                            class="text-red-500 hover:text-red-600"
                                            wire:click="cancelTransaction({{ $trans->id }})"
                                            wire:confirm="Yakin ingin membatalkan transaksi ini?"
                                        >
                                            Batalkan
                                        </flux:button>
                                    @endif

                                    {{-- If Completed --}}
                                    @if ($trans->isCompleted())
                                        @if ($myReview)
                                            <div class="flex items-center gap-1.5 px-2.5 py-1 bg-amber-500/10 rounded-lg text-xs text-amber-700 dark:text-amber-400 font-medium">
                                                <flux:icon name="star" class="size-3.5 fill-amber-400 text-amber-400" />
                                                <span>Rating Anda: {{ $myReview->rating }}/5</span>
                                                <button
                                                    type="button"
                                                    wire:click="openReviewModal({{ $trans->id }})"
                                                    class="ml-1 text-[11px] underline hover:text-amber-800 dark:hover:text-amber-300"
                                                >
                                                    Ubah
                                                </button>
                                            </div>
                                        @else
                                            <flux:button
                                                size="sm"
                                                variant="subtle"
                                                icon="star"
                                                class="text-amber-600 dark:text-amber-400 border border-amber-500/30"
                                                wire:click="openReviewModal({{ $trans->id }})"
                                            >
                                                Beri Ulasan
                                            </flux:button>
                                        @endif
                                    @endif

                                    {{-- Chat button --}}
                                    <flux:button
                                        size="sm"
                                        variant="subtle"
                                        icon="chat-bubble-left-right"
                                        :href="route('chat.index')"
                                        wire:navigate
                                        title="Buka Pesan"
                                    >
                                        Chat
                                    </flux:button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-6">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>

    {{-- Review & Rating Modal --}}
    @if ($showReviewModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-6 max-w-md w-full shadow-xl space-y-5 animate-in fade-in zoom-in duration-150">
                <div class="flex items-center justify-between">
                    <flux:heading size="lg">Beri Ulasan & Penilaian</flux:heading>
                    <button type="button" wire:click="closeReviewModal" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200">
                        <flux:icon name="x-mark" class="size-5" />
                    </button>
                </div>

                <p class="text-xs text-zinc-500">
                    Bagikan pengalaman transaksi Anda untuk membangun reputasi komunitas yang terpercaya.
                </p>

                <form wire:submit="submitReview" class="space-y-4">
                    {{-- Star Rating Selector --}}
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-2">Penilaian Bintang</label>
                        <div class="flex items-center gap-2">
                            @for ($i = 1; $i <= 5; $i++)
                                <button
                                    type="button"
                                    wire:click="setRating({{ $i }})"
                                    class="p-1 transition transform hover:scale-110"
                                >
                                    <flux:icon
                                        name="star"
                                        class="size-8 {{ $i <= $rating ? 'fill-amber-400 text-amber-400' : 'text-zinc-300 dark:text-zinc-700' }}"
                                    />
                                </button>
                            @endfor
                            <span class="ml-2 text-sm font-bold text-zinc-800 dark:text-zinc-200">
                                {{ $rating }} / 5
                            </span>
                        </div>
                        @error('rating')
                            <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Review Comment --}}
                    <flux:field>
                        <flux:label>Komentar Ulasan (Opsional)</flux:label>
                        <flux:textarea
                            wire:model="comment"
                            rows="3"
                            placeholder="Contoh: Penjual sangat responsif, barang sesuai deskripsi dan kondisi mulus!"
                        />
                        <flux:error name="comment" />
                    </flux:field>

                    {{-- Actions --}}
                    <div class="pt-3 flex items-center justify-end gap-2.5 border-t border-zinc-100 dark:border-zinc-800">
                        <flux:button variant="ghost" type="button" wire:click="closeReviewModal">
                            Batal
                        </flux:button>
                        <flux:button variant="primary" type="submit" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="submitReview">Simpan Ulasan</span>
                            <span wire:loading wire:target="submitReview">Menyimpan...</span>
                        </flux:button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

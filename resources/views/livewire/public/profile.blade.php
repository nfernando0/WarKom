<div class="py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        
        {{-- Breadcrumbs --}}
        <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-2xs">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item :href="route('home')" wire:navigate>Home</flux:breadcrumbs.item>
                <flux:breadcrumbs.item :href="route('public.marketplace')" wire:navigate>Marketplace</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ $isSelf ? 'Profil Saya' : 'Profil ' . $user->name }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>
        </div>

        {{-- Flash notifications --}}
        @if (session()->has('success'))
            <flux:callout variant="success" icon="check-circle" :heading="session('success')" />
        @endif
        @if (session()->has('info'))
            <flux:callout variant="info" icon="information-circle" :heading="session('info')" />
        @endif

        {{-- Profile Header Card --}}
        <div class="bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-200 dark:border-zinc-800 p-6 sm:p-8 shadow-xs relative overflow-hidden">
            {{-- Decorative gradient background element --}}
            <div class="absolute top-0 right-0 w-96 h-40 bg-gradient-to-l from-primary-500/10 via-indigo-500/5 to-transparent pointer-events-none"></div>

            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6 relative">
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5">
                    {{-- Avatar --}}
                    <div class="size-20 sm:size-24 rounded-2xl bg-gradient-to-br from-primary-500 to-indigo-600 flex items-center justify-center font-extrabold text-2xl sm:text-3xl text-white shadow-md ring-4 ring-primary-500/20 shrink-0">
                        {{ $user->initials() }}
                    </div>

                    {{-- User Details --}}
                    <div class="space-y-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="text-2xl sm:text-3xl font-extrabold text-zinc-900 dark:text-zinc-50 tracking-tight">
                                {{ $user->name }}
                            </h1>
                            @if ($user->isAdmin())
                                <flux:badge size="sm" color="purple">Admin WarKom</flux:badge>
                            @else
                                <flux:badge size="sm" color="zinc">Anggota</flux:badge>
                            @endif
                        </div>

                        {{-- Community info & phone --}}
                        <div class="flex flex-wrap items-center gap-3 text-xs sm:text-sm text-zinc-500">
                            @if ($user->community)
                                <div class="flex items-center gap-1 font-medium text-zinc-700 dark:text-zinc-300">
                                    <flux:icon name="users" class="size-4 text-primary-500" />
                                    <span>{{ $user->community->name }}</span>
                                </div>
                            @endif

                            @if ($user->phone)
                                <div class="flex items-center gap-1">
                                    <flux:icon name="phone" class="size-4 text-zinc-400" />
                                    <span>{{ $user->phone }}</span>
                                </div>
                            @endif

                            <div class="flex items-center gap-1 text-zinc-400 text-xs">
                                <flux:icon name="calendar" class="size-3.5" />
                                <span>Bergabung {{ $user->created_at->translatedFormat('F Y') }}</span>
                            </div>
                        </div>

                        {{-- Reputation Star Rating --}}
                        <div class="flex items-center gap-2 pt-0.5">
                            <div class="flex items-center gap-1 text-amber-500">
                                @for ($i = 1; $i <= 5; $i++)
                                    <flux:icon name="star" class="size-4 {{ $i <= round($stats['averageRating']) ? 'fill-amber-400 text-amber-400' : 'text-zinc-300 dark:text-zinc-700' }}" />
                                @endfor
                            </div>
                            <span class="font-extrabold text-sm text-zinc-800 dark:text-zinc-200">
                                {{ number_format($stats['averageRating'], 1) }}
                            </span>
                            <span class="text-xs text-zinc-400">
                                ({{ $stats['reviewCount'] }} ulasan dari pembeli)
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center gap-2.5 w-full sm:w-auto shrink-0">
                    @if ($isSelf)
                        <flux:button
                            variant="{{ $activeTab === 'edit' ? 'primary' : 'subtle' }}"
                            icon="pencil-square"
                            wire:click="setTab('edit')"
                            class="flex-1 sm:flex-none"
                        >
                            Edit Profil
                        </flux:button>
                        <flux:button
                            variant="primary"
                            icon="plus"
                            :href="route('listing.create')"
                            wire:navigate
                            class="flex-1 sm:flex-none"
                        >
                            Pasang Iklan
                        </flux:button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Profile Stats Grid --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="p-5 bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 text-center shadow-xs">
                <p class="text-2xl sm:text-3xl font-extrabold text-primary-600 dark:text-primary-400">{{ $stats['activeListings'] }}</p>
                <p class="text-xs text-zinc-500 font-medium mt-1">Barang Aktif Dijual</p>
            </div>
            <div class="p-5 bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 text-center shadow-xs">
                <p class="text-2xl sm:text-3xl font-extrabold text-emerald-600 dark:text-emerald-400">{{ $stats['totalSales'] }}</p>
                <p class="text-xs text-zinc-500 font-medium mt-1">Penjualan Selesai</p>
            </div>
            <div class="p-5 bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 text-center shadow-xs">
                <p class="text-2xl sm:text-3xl font-extrabold text-indigo-600 dark:text-indigo-400">{{ $stats['totalPurchases'] }}</p>
                <p class="text-xs text-zinc-500 font-medium mt-1">Pembelian Selesai</p>
            </div>
            <div class="p-5 bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 text-center shadow-xs">
                <p class="text-2xl sm:text-3xl font-extrabold text-amber-500">{{ number_format($stats['averageRating'], 1) }}</p>
                <p class="text-xs text-zinc-500 font-medium mt-1">Rating Kepuasan</p>
            </div>
        </div>

        {{-- Tab Navigation --}}
        <div class="border-b border-zinc-200 dark:border-zinc-800 flex items-center gap-4 text-sm font-semibold">
            <button
                type="button"
                wire:click="setTab('listings')"
                class="pb-3 border-b-2 transition flex items-center gap-2 {{ $activeTab === 'listings' ? 'border-primary-600 text-primary-600 dark:text-primary-400' : 'border-transparent text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-100' }}"
            >
                <flux:icon name="shopping-bag" class="size-4" />
                <span>Etalase Barang ({{ $stats['totalListings'] }})</span>
            </button>

            <button
                type="button"
                wire:click="setTab('reviews')"
                class="pb-3 border-b-2 transition flex items-center gap-2 {{ $activeTab === 'reviews' ? 'border-primary-600 text-primary-600 dark:text-primary-400' : 'border-transparent text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-100' }}"
            >
                <flux:icon name="star" class="size-4" />
                <span>Ulasan & Rating ({{ $stats['reviewCount'] }})</span>
            </button>

            @if ($isSelf)
                <button
                    type="button"
                    wire:click="setTab('edit')"
                    class="pb-3 border-b-2 transition flex items-center gap-2 {{ $activeTab === 'edit' ? 'border-primary-600 text-primary-600 dark:text-primary-400' : 'border-transparent text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-100' }}"
                >
                    <flux:icon name="pencil-square" class="size-4" />
                    <span>Edit Biodata & Kontak</span>
                </button>
            @endif
        </div>

        {{-- Tab 1: Listings Grid --}}
        @if ($activeTab === 'listings')
            <div class="space-y-4">
                @if ($isSelf && $listings->isNotEmpty())
                    <div class="flex items-center justify-between pb-2">
                        <span class="text-xs text-zinc-500">Menampilkan {{ $listings->count() }} barang yang Anda jual.</span>
                        <flux:button size="xs" variant="subtle" icon="cog-6-tooth" :href="route('my-listings')" wire:navigate>
                            Buka Manajemen Listing &rarr;
                        </flux:button>
                    </div>
                @endif
                @if ($listings->isEmpty())
                    <div class="py-16 text-center bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-8 shadow-xs">
                        <div class="size-16 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center mx-auto mb-4 text-zinc-400">
                            <flux:icon name="shopping-bag" class="size-8 stroke-1" />
                        </div>
                        <h3 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">Belum Ada Barang Dipasang</h3>
                        <p class="text-sm text-zinc-500 max-w-sm mx-auto mt-1">
                            {{ $isSelf ? 'Anda belum menjual barang apapun. Pasang barang yang sudah tidak terpakai sekarang!' : 'Pengguna ini belum memasang barang apapun.' }}
                        </p>
                        @if ($isSelf)
                            <div class="mt-4">
                                <flux:button variant="primary" icon="plus" :href="route('listing.create')" wire:navigate>
                                    Pasang Iklan Pertama
                                </flux:button>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                        @foreach ($listings as $listing)
                            <div wire:key="prof-listing-{{ $listing->id }}" class="group bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 hover:border-zinc-300 dark:hover:border-zinc-700 shadow-xs hover:shadow-lg transition duration-200 flex flex-col overflow-hidden">
                                <a href="{{ route('public.listing.show', $listing) }}" wire:navigate class="block relative aspect-4/3 w-full bg-zinc-100 dark:bg-zinc-800 overflow-hidden">
                                    @if ($listing->images->isNotEmpty())
                                        <img src="{{ $listing->images->first()->url }}" alt="{{ $listing->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" />
                                    @else
                                        <div class="w-full h-full flex flex-col items-center justify-center text-zinc-400">
                                            <flux:icon name="photo" class="size-10 stroke-1" />
                                            <span class="text-xs mt-1">Tanpa Foto</span>
                                        </div>
                                    @endif

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
                                </a>

                                <div class="p-4 flex-1 flex flex-col justify-between space-y-4">
                                    <div>
                                        <a href="{{ route('public.listing.show', $listing) }}" wire:navigate class="font-bold text-base text-zinc-900 dark:text-zinc-100 line-clamp-1 hover:text-primary-600 transition" title="{{ $listing->title }}">
                                            {{ $listing->title }}
                                        </a>
                                        <div class="mt-1 text-lg font-extrabold text-zinc-900 dark:text-zinc-50">
                                            Rp {{ number_format($listing->price, 0, ',', '.') }}
                                        </div>
                                        <p class="mt-1 text-xs text-zinc-500 line-clamp-2">
                                            {{ $listing->description }}
                                        </p>
                                    </div>

                                    <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between gap-2">
                                        <flux:button size="xs" variant="primary" icon="eye" :href="route('public.listing.show', $listing)" wire:navigate class="w-full justify-center">
                                            Lihat Detail
                                        </flux:button>
                                        @if ($isSelf)
                                            <flux:button size="xs" variant="subtle" icon="pencil-square" :href="route('listing.edit', $listing)" wire:navigate title="Edit Listing" />
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        {{-- Tab 2: Reviews List --}}
        @if ($activeTab === 'reviews')
            <div class="space-y-4">
                @if ($reviews->isEmpty())
                    <div class="py-16 text-center bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-8 shadow-xs">
                        <div class="size-16 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center mx-auto mb-4 text-zinc-400">
                            <flux:icon name="star" class="size-8 stroke-1" />
                        </div>
                        <h3 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">Belum Ada Ulasan</h3>
                        <p class="text-sm text-zinc-500 max-w-sm mx-auto mt-1">
                            Ulasan bintang akan muncul setelah transaksi jual-beli berhasil diselesaikan.
                        </p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach ($reviews as $rev)
                            <div wire:key="rev-item-{{ $rev->id }}" class="p-5 bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <div class="size-10 rounded-full bg-primary-500/10 text-primary-600 font-bold text-xs flex items-center justify-center">
                                            {{ $rev->reviewer?->initials() ?? '?' }}
                                        </div>
                                        <div>
                                            <span class="font-bold text-sm text-zinc-900 dark:text-zinc-100 block">
                                                {{ $rev->reviewer?->name ?? 'Pembeli' }}
                                            </span>
                                            <span class="text-xs text-zinc-400">
                                                {{ $rev->created_at->diffForHumans() }}
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Star Rating --}}
                                    <div class="flex items-center gap-1 text-amber-500">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <flux:icon name="star" class="size-3.5 {{ $i <= $rev->rating ? 'fill-amber-400 text-amber-400' : 'text-zinc-300 dark:text-zinc-700' }}" />
                                        @endfor
                                    </div>
                                </div>

                                @if ($rev->comment)
                                    <p class="text-xs sm:text-sm text-zinc-600 dark:text-zinc-300 leading-relaxed bg-zinc-50 dark:bg-zinc-800/50 p-3 rounded-xl border border-zinc-100 dark:border-zinc-800">
                                        "{{ $rev->comment }}"
                                    </p>
                                @endif

                                @if ($rev->transaction && $rev->transaction->listing)
                                    <div class="text-[11px] text-zinc-400 flex items-center gap-1 pt-1">
                                        <flux:icon name="tag" class="size-3" />
                                        <span>Produk: <strong>{{ $rev->transaction->listing->title }}</strong></span>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        {{-- Tab 3: Edit Profile Form (Self only) --}}
        @if ($activeTab === 'edit' && $isSelf)
            <div class="p-6 sm:p-8 bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-200 dark:border-zinc-800 shadow-xs max-w-2xl">
                <form wire:submit="updateProfile" class="space-y-5">
                    <div>
                        <flux:heading size="lg">Edit Biodata & Informasi Kontak</flux:heading>
                        <flux:subheading>Perbarui data profil yang ditampilkan kepada sesama anggota komunitas.</flux:subheading>
                    </div>

                    {{-- Name --}}
                    <flux:field>
                        <flux:label>Nama Lengkap</flux:label>
                        <flux:input wire:model="name" placeholder="Nama Anda" />
                        <flux:error name="name" />
                    </flux:field>

                    {{-- Email --}}
                    <flux:field>
                        <flux:label>Alamat Email</flux:label>
                        <flux:input wire:model="email" type="email" placeholder="email@contoh.com" />
                        <flux:error name="email" />
                    </flux:field>

                    {{-- Phone --}}
                    <flux:field>
                        <flux:label>Nomor WhatsApp / HP</flux:label>
                        <flux:input wire:model="phone" placeholder="Contoh: 08123456789" />
                        <flux:error name="phone" />
                    </flux:field>

                    {{-- Address / Location --}}
                    <flux:field>
                        <flux:label>Alamat / Keterangan Rumah (Komunitas)</flux:label>
                        <flux:textarea wire:model="address" rows="3" placeholder="Contoh: Blok B3 No. 12, RT 05 / RW 02" />
                        <flux:error name="address" />
                    </flux:field>

                    {{-- Actions --}}
                    <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-end gap-3">
                        <flux:button variant="ghost" type="button" wire:click="setTab('listings')">
                            Batal
                        </flux:button>
                        <flux:button variant="primary" type="submit" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="updateProfile">Simpan Perubahan</span>
                            <span wire:loading wire:target="updateProfile">Menyimpan...</span>
                        </flux:button>
                    </div>
                </form>
            </div>
        @endif

    </div>
</div>

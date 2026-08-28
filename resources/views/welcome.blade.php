<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
    <title>WarKom - Marketplace Jual Beli Komunitas Terpercaya</title>
</head>

<body
    class="min-h-screen bg-zinc-50 dark:bg-zinc-950 text-zinc-900 dark:text-zinc-100 flex flex-col font-sans antialiased selection:bg-primary-500 selection:text-white">

    {{-- Top Announcement Bar --}}
    <div
        class="bg-gradient-to-r from-primary-600 via-indigo-600 to-purple-600 text-white text-xs py-2 px-4 text-center font-medium shadow-xs">
        <span>✨ Selamat datang di <strong>WarKom</strong> — Marketplace jual-beli aman dan praktis di dalam komunitas
            Anda!</span>
    </div>

    {{-- Navigation Header --}}
    <header
        class="sticky top-0 z-40 bg-white/80 dark:bg-zinc-900/80 backdrop-blur-md border-b border-zinc-200 dark:border-zinc-800 transition">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">

            {{-- Logo & Brand --}}
            <div class="flex items-center gap-6">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 group">
                    <div
                        class="size-9 rounded-xl bg-gradient-to-br from-primary-500 to-indigo-600 flex items-center justify-center text-white shadow-md group-hover:scale-105 transition">
                        <flux:icon name="shopping-bag" class="size-5" />
                    </div>
                    <span
                        class="font-extrabold text-xl tracking-tight bg-gradient-to-r from-zinc-900 via-primary-600 to-indigo-600 dark:from-white dark:via-primary-400 dark:to-indigo-400 bg-clip-text text-transparent">
                        WarKom
                    </span>
                </a>

                {{-- Desktop Nav Links --}}
                <nav class="hidden md:flex items-center gap-1 text-sm font-medium text-zinc-600 dark:text-zinc-300">
                    <a href="{{ route('public.marketplace') }}"
                        class="px-3 py-1.5 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-900 dark:hover:text-white transition">
                        Marketplace
                    </a>
                    <a href="{{ route('public.community') }}"
                        class="px-3 py-1.5 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-900 dark:hover:text-white transition">
                        Komunitas
                    </a>
                    <a href="{{ route('public.category') }}"
                        class="px-3 py-1.5 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-900 dark:hover:text-white transition">
                        Kategori
                    </a>
                </nav>
            </div>

            {{-- Right Auth & Action Buttons --}}
            <div class="flex items-center gap-3">
                @auth
                    <div class="flex items-center gap-3">
                        <flux:button variant="subtle" icon="chat-bubble-left-right" :href="route('chat.index')"
                            wire:navigate class="hidden sm:inline-flex">
                            Pesan
                        </flux:button>

                        <flux:dropdown position="bottom" align="end">
                            <button type="button"
                                class="flex items-center gap-2 p-1.5 rounded-xl hover:bg-zinc-100 dark:hover:bg-zinc-800 transition border border-zinc-200 dark:border-zinc-700/80 cursor-pointer">
                                <div
                                    class="size-8 rounded-lg bg-gradient-to-br from-primary-500 to-indigo-600 flex items-center justify-center font-bold text-xs text-white shadow-xs">
                                    {{ auth()->user()->initials() }}
                                </div>
                                <div class="hidden sm:block text-left text-xs leading-tight pr-1">
                                    <span class="font-bold text-zinc-900 dark:text-zinc-100 block truncate max-w-[120px]">{{ auth()->user()->name }}</span>
                                    <span class="text-[10px] text-zinc-400 block">{{ auth()->user()->community?->name ?? 'Anggota' }}</span>
                                </div>
                                <flux:icon name="chevron-down" class="size-3.5 text-zinc-400" />
                            </button>

                            <flux:menu class="w-64">
                                <div class="flex items-center gap-2.5 px-2 py-2 text-start">
                                    <div
                                        class="size-9 rounded-xl bg-primary-600 text-white flex items-center justify-center font-bold text-xs shrink-0">
                                        {{ auth()->user()->initials() }}
                                    </div>
                                    <div class="grid flex-1 text-start text-xs leading-tight min-w-0">
                                        <flux:heading class="truncate font-bold">{{ auth()->user()->name }}</flux:heading>
                                        <flux:text class="truncate text-[11px] text-zinc-400">{{ auth()->user()->email }}</flux:text>
                                    </div>
                                </div>

                                <flux:menu.separator />

                                <flux:menu.item icon="user" :href="route('user.profile')" wire:navigate>
                                    Profil Saya
                                </flux:menu.item>
                                <flux:menu.item icon="layout-grid" :href="route('dashboard')" wire:navigate>
                                    Dashboard
                                </flux:menu.item>
                                <flux:menu.item icon="shopping-bag" :href="route('my-listings')" wire:navigate>
                                    Listing Saya
                                </flux:menu.item>
                                <flux:menu.item icon="receipt-percent" :href="route('transaction.index')" wire:navigate>
                                    Transaksi Saya
                                </flux:menu.item>
                                <flux:menu.item icon="chat-bubble-left-right" :href="route('chat.index')" wire:navigate>
                                    Pesan Obrolan
                                </flux:menu.item>

                                <flux:menu.separator />

                                <form method="POST" action="{{ route('logout') }}" class="w-full">
                                    @csrf
                                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle"
                                        class="w-full cursor-pointer text-red-600 dark:text-red-400">
                                        Keluar
                                    </flux:menu.item>
                                </form>
                            </flux:menu>
                        </flux:dropdown>
                    </div>
                @else
                    <flux:button variant="ghost" :href="route('login')">
                        Masuk
                    </flux:button>
                    <flux:button variant="primary" :href="route('register')">
                        Daftar Akun
                    </flux:button>
                @endauth
            </div>
        </div>
    </header>

    {{-- Main Content --}}
    <main class="flex-1">

        {{-- Hero Section --}}
        <section
            class="relative overflow-hidden pt-12 pb-20 lg:pt-20 lg:pb-28 border-b border-zinc-200 dark:border-zinc-800/80 bg-gradient-to-b from-white via-zinc-50 to-zinc-100 dark:from-zinc-900 dark:via-zinc-950 dark:to-zinc-950">
            <div class="absolute -top-24 -left-24 size-96 bg-primary-500/15 rounded-full blur-3xl pointer-events-none">
            </div>
            <div class="absolute top-1/2 -right-24 size-96 bg-indigo-500/15 rounded-full blur-3xl pointer-events-none">
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
                <div class="text-center max-w-3xl mx-auto space-y-6">

                    {{-- Badge Tag --}}
                    <div
                        class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-primary-500/10 border border-primary-500/20 text-primary-600 dark:text-primary-400 text-xs font-semibold shadow-xs">
                        <flux:icon name="shield-check" class="size-4" />
                        <span>Platform Jual Beli Komunitas Terpercaya</span>
                    </div>

                    {{-- Main Headline --}}
                    <h1
                        class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-zinc-900 dark:text-zinc-50 leading-[1.15]">
                        Jual Beli Barang di <span
                            class="bg-gradient-to-r from-primary-600 via-indigo-600 to-purple-600 bg-clip-text text-transparent">Komunitas
                            Anda</span> Jadi Lebih Mudah
                    </h1>

                    {{-- Subtitle --}}
                    <p class="text-base sm:text-lg text-zinc-600 dark:text-zinc-400 max-w-2xl mx-auto leading-relaxed">
                        Temukan berbagai barang berkualitas dari tetangga atau rekan satu komunitas. Chat langsung
                        dengan penjual, nego harga ramah, dan transaksi aman tanpa perantara.
                    </p>

                    {{-- CTA Buttons --}}
                    <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-3.5">
                        <flux:button variant="primary" icon="shopping-bag" :href="route('public.marketplace')"
                            class="w-full sm:w-auto py-3 px-6 text-base font-semibold shadow-md shadow-primary-500/20">
                            Jelajahi Marketplace
                        </flux:button>
                        <flux:button variant="subtle" icon="plus" :href="route('listing.create')"
                            class="w-full sm:w-auto py-3 px-6 text-base font-semibold border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900">
                            Pasang Iklan Barang
                        </flux:button>
                    </div>

                    {{-- Key Badges --}}
                    <div
                        class="pt-6 flex flex-wrap items-center justify-center gap-6 text-xs text-zinc-500 dark:text-zinc-400">
                        <div class="flex items-center gap-1.5">
                            <flux:icon name="check-circle" class="size-4 text-emerald-500" />
                            <span>100% Bebas Biaya Admin</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <flux:icon name="chat-bubble-left-right" class="size-4 text-primary-500" />
                            <span>Chat Penjual Real-Time</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <flux:icon name="star" class="size-4 text-amber-500" />
                            <span>Rating & Ulasan Komunitas</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Live Trust Statistics Bar --}}
        <section class="border-b border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div
                    class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center divide-y md:divide-y-0 md:divide-x divide-zinc-200 dark:divide-zinc-800">
                    <div class="pt-4 md:pt-0">
                        <p class="text-3xl font-extrabold text-zinc-900 dark:text-zinc-100">{{ $stats['listings'] }}</p>
                        <p class="text-xs text-zinc-500 font-medium mt-1">Barang Tersedia</p>
                    </div>
                    <div class="pt-4 md:pt-0">
                        <p class="text-3xl font-extrabold text-primary-600 dark:text-primary-400">
                            {{ $stats['communities'] }}</p>
                        <p class="text-xs text-zinc-500 font-medium mt-1">Komunitas Terdaftar</p>
                    </div>
                    <div class="pt-4 md:pt-0">
                        <p class="text-3xl font-extrabold text-emerald-600 dark:text-emerald-400">
                            {{ $stats['transactions'] }}</p>
                        <p class="text-xs text-zinc-500 font-medium mt-1">Transaksi Selesai</p>
                    </div>
                    <div class="pt-4 md:pt-0">
                        <p class="text-3xl font-extrabold text-indigo-600 dark:text-indigo-400">{{ $stats['users'] }}
                        </p>
                        <p class="text-xs text-zinc-500 font-medium mt-1">Anggota Aktif</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Categories Section --}}
        @if ($categories->isNotEmpty())
            <section id="kategori"
                class="py-14 bg-zinc-50/70 dark:bg-zinc-950/70 border-b border-zinc-200 dark:border-zinc-800/80">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <flux:heading size="xl">Kategori Pilihan</flux:heading>
                            <flux:subheading>Temukan barang sesuai dengan kebutuhan spesifik Anda</flux:subheading>
                        </div>
                        <flux:button variant="ghost" size="sm" :href="route('public.category')">
                            Semua Kategori &rarr;
                        </flux:button>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3.5">
                        @foreach ($categories as $cat)
                            <a href="{{ route('public.marketplace', ['cat' => $cat->id]) }}"
                                class="p-4 bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 hover:border-primary-500 dark:hover:border-primary-500 shadow-2xs hover:shadow-md transition text-center group flex flex-col items-center justify-center gap-2">
                                <div
                                    class="size-10 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-600 dark:text-zinc-400 group-hover:bg-primary-500/10 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition">
                                    <flux:icon name="tag" class="size-5" />
                                </div>
                                <div>
                                    <span
                                        class="font-semibold text-xs sm:text-sm text-zinc-800 dark:text-zinc-200 group-hover:text-primary-600 dark:group-hover:text-primary-400 block truncate">
                                        {{ $cat->name }}
                                    </span>
                                    <span class="text-[11px] text-zinc-400 block mt-0.5">
                                        {{ $cat->listings_count }} barang
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        {{-- Featured Listings Showcase --}}
        <section class="py-16 bg-white dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

                {{-- Section Header --}}
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                    <div>
                        <flux:badge size="sm" color="emerald" class="mb-2">Terbaru di Marketplace</flux:badge>
                        <flux:heading size="xl">Etalase Barang Komunitas</flux:heading>
                        <flux:subheading>Barang-barang terbaru yang siap Anda pinang hari ini</flux:subheading>
                    </div>

                    <flux:button variant="primary" :href="route('public.marketplace')">
                        Lihat Semua Barang ({{ $stats['listings'] }})
                    </flux:button>
                </div>

                {{-- Listings Grid --}}
                @if ($featuredListings->isEmpty())
                    <div
                        class="py-16 text-center bg-zinc-50 dark:bg-zinc-900/40 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-8">
                        <div
                            class="size-16 rounded-full bg-zinc-200 dark:bg-zinc-800 flex items-center justify-center mx-auto mb-4 text-zinc-400">
                            <flux:icon name="shopping-bag" class="size-8" />
                        </div>
                        <flux:heading size="lg">Belum Ada Barang Dipasang</flux:heading>
                        <p class="text-sm text-zinc-500 max-w-sm mx-auto mt-1">
                            Jadilah orang pertama yang menjual barang di komunitas Anda!
                        </p>
                        <div class="mt-6">
                            <flux:button variant="primary" icon="plus" :href="route('listing.create')">
                                Pasang Iklan Pertama
                            </flux:button>
                        </div>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                        @foreach ($featuredListings as $listing)
                            <div
                                class="group bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 hover:border-zinc-300 dark:hover:border-zinc-700 shadow-xs hover:shadow-lg transition flex flex-col overflow-hidden">

                                {{-- Thumbnail --}}
                                <a href="{{ route('public.listing.show', $listing) }}"
                                    class="block relative aspect-4/3 w-full bg-zinc-100 dark:bg-zinc-800 overflow-hidden">
                                    @if ($listing->images->isNotEmpty())
                                        <img src="{{ $listing->images->first()->url }}" alt="{{ $listing->title }}"
                                            class="w-full h-full object-cover group-hover:scale-105 transition duration-300" />
                                    @else
                                        <div
                                            class="w-full h-full flex flex-col items-center justify-center text-zinc-400">
                                            <flux:icon name="photo" class="size-10 stroke-1" />
                                            <span class="text-xs mt-1">Tanpa Foto</span>
                                        </div>
                                    @endif

                                    {{-- Badges --}}
                                    <div class="absolute top-2.5 left-2.5 flex flex-wrap gap-1">
                                        <flux:badge size="xs"
                                            :color="$listing->condition === 'baru' ? 'emerald' : 'amber'">
                                            {{ ucfirst($listing->condition) }}
                                        </flux:badge>
                                    </div>

                                    @if ($listing->category)
                                        <div class="absolute bottom-2.5 left-2.5">
                                            <span
                                                class="px-2 py-0.5 rounded-md text-[11px] font-medium bg-black/60 backdrop-blur-md text-white">
                                                {{ $listing->category->name }}
                                            </span>
                                        </div>
                                    @endif
                                </a>

                                {{-- Card Details --}}
                                <div class="p-4 flex-1 flex flex-col justify-between space-y-4">
                                    <div>
                                        <a href="{{ route('public.listing.show', $listing) }}"
                                            class="font-bold text-base text-zinc-900 dark:text-zinc-100 line-clamp-1 hover:text-primary-600 dark:hover:text-primary-400 transition"
                                            title="{{ $listing->title }}">
                                            {{ $listing->title }}
                                        </a>

                                        <div class="mt-2 text-xl font-extrabold text-zinc-900 dark:text-zinc-50">
                                            Rp {{ number_format($listing->price, 0, ',', '.') }}
                                        </div>

                                        <p class="mt-1 text-xs text-zinc-500 line-clamp-2">
                                            {{ $listing->description }}
                                        </p>
                                    </div>

                                    {{-- Footer with seller & community --}}
                                    <div
                                        class="pt-3 border-t border-zinc-100 dark:border-zinc-800/80 flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <div
                                                class="size-6 rounded-full bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center font-bold text-[10px] text-zinc-700 dark:text-zinc-300 shrink-0">
                                                {{ $listing->creator?->initials() ?? '?' }}
                                            </div>
                                            <span class="text-xs text-zinc-600 dark:text-zinc-400 truncate">
                                                {{ $listing->creator?->name ?? 'Penjual' }}
                                            </span>
                                        </div>

                                        <div class="flex items-center gap-1.5 shrink-0">
                                            <flux:button size="xs" variant="primary"
                                                icon="chat-bubble-left-right"
                                                :href="route('public.start-chat', $listing)">
                                                Chat
                                            </flux:button>
                                            <flux:button size="xs" variant="subtle"
                                                :href="route('public.listing.show', $listing)">
                                                Detail
                                            </flux:button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

        {{-- Features Section --}}
        <section id="fitur"
            class="py-20 bg-zinc-50 dark:bg-zinc-950 border-b border-zinc-200 dark:border-zinc-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">

                <div class="text-center max-w-2xl mx-auto space-y-3">
                    <flux:badge color="sky">Keunggulan Platform</flux:badge>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-zinc-900 dark:text-zinc-100">
                        Kenapa Berjual-Beli di WarKom?
                    </h2>
                    <p class="text-sm sm:text-base text-zinc-600 dark:text-zinc-400">
                        Dirancang khusus untuk menghidupkan ekonomi gotong royong dan kemudahan transaksi antar warga
                        komunitas.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div
                        class="p-6 bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-4 hover:border-primary-500/50 transition">
                        <div
                            class="size-12 rounded-xl bg-primary-500/10 text-primary-600 dark:text-primary-400 flex items-center justify-center">
                            <flux:icon name="chat-bubble-left-right" class="size-6" />
                        </div>
                        <h3 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">Chat Penjual Real-Time</h3>
                        <p class="text-xs sm:text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                            Kirim pesan langsung ke penjual, ajukan pertanyaan kondisi barang, atau tawar harga dengan
                            template cepat tanpa keluar aplikasi.
                        </p>
                    </div>

                    <div
                        class="p-6 bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-4 hover:border-emerald-500/50 transition">
                        <div
                            class="size-12 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                            <flux:icon name="receipt-percent" class="size-6" />
                        </div>
                        <h3 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">Transaksi & Rating Bintang</h3>
                        <p class="text-xs sm:text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                            Setiap kesepakatan tercatat rapi dari status Pending hingga Selesai. Dilengkapi sistem
                            ulasan dan bintang untuk menjaga reputasi terpercaya.
                        </p>
                    </div>

                    <div
                        class="p-6 bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-4 hover:border-indigo-500/50 transition">
                        <div
                            class="size-12 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                            <flux:icon name="users" class="size-6" />
                        </div>
                        <h3 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">Jaringan Komunitas Lokal</h3>
                        <p class="text-xs sm:text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                            Beli barang dari tetangga satu komplek atau rekan satu hobi. Ambil barang langsung (COD)
                            dengan cepat tanpa beban ongkir mahal.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Communities Showcase --}}
        @if ($communities->isNotEmpty())
            <section class="py-16 bg-white dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-800">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <flux:heading size="xl">Komunitas Terpopuler</flux:heading>
                            <flux:subheading>Bergabung dengan komunitas sekitar Anda untuk mulai bertransaksi
                            </flux:subheading>
                        </div>
                        <flux:button variant="ghost" size="sm" :href="route('public.community')">
                            Lihat Semua Komunitas &rarr;
                        </flux:button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-5">
                        @foreach ($communities as $comm)
                            <div
                                class="p-5 bg-zinc-50 dark:bg-zinc-800/40 rounded-2xl border border-zinc-200 dark:border-zinc-800 hover:border-zinc-300 dark:hover:border-zinc-700 transition flex flex-col justify-between">
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-base text-zinc-900 dark:text-zinc-100">
                                            {{ $comm->name }}
                                        </span>
                                        <flux:badge size="xs" color="zinc">{{ $comm->members_count }} Anggota
                                        </flux:badge>
                                    </div>
                                    <p class="text-xs text-zinc-500 line-clamp-2">
                                        {{ $comm->description ?: 'Komunitas aktif jual beli dan berbagi informasi barang.' }}
                                    </p>
                                    @if ($comm->location)
                                        <div class="flex items-center gap-1 text-[11px] text-zinc-400 pt-1">
                                            <flux:icon name="map-pin" class="size-3.5" />
                                            <span>{{ $comm->location }}</span>
                                        </div>
                                    @endif
                                </div>

                                <div class="mt-4 pt-3 border-t border-zinc-200/60 dark:border-zinc-700/60">
                                    <flux:button size="xs" variant="subtle"
                                        :href="route('public.marketplace', ['community' => $comm->id])"
                                        class="w-full">
                                        Lihat Barang di Komunitas Ini
                                    </flux:button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        {{-- Call To Action Banner --}}
        <section
            class="py-16 bg-gradient-to-r from-primary-600 via-indigo-600 to-purple-700 text-white relative overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative space-y-6">
                <h2 class="text-3xl sm:text-4xl font-extrabold max-w-2xl mx-auto">
                    Siap Memulai Jual Beli di Komunitas Anda?
                </h2>
                <p class="text-primary-100 max-w-xl mx-auto text-sm sm:text-base">
                    Daftar akun gratis hari ini, temukan barang impian dengan harga terbaik, dan pasang barang tidak
                    terpakai Anda.
                </p>
                <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3">
                    <a href="{{ route('register') }}"
                        class="w-full sm:w-auto px-6 py-3 rounded-xl bg-white text-zinc-900 font-bold text-sm hover:bg-zinc-100 transition shadow-lg">
                        Daftar Akun Sekarang
                    </a>
                    <a href="{{ route('public.marketplace') }}"
                        class="w-full sm:w-auto px-6 py-3 rounded-xl bg-black/20 text-white border border-white/20 font-bold text-sm hover:bg-black/30 transition">
                        Jelajahi Marketplace
                    </a>
                </div>
            </div>
        </section>

    </main>

    {{-- Footer --}}
    <footer
        class="bg-white dark:bg-zinc-950 border-t border-zinc-200 dark:border-zinc-800 text-xs text-zinc-500 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="space-y-3">
                <div class="flex items-center gap-2">
                    <div
                        class="size-7 rounded-lg bg-primary-600 flex items-center justify-center text-white font-bold text-xs">
                        W
                    </div>
                    <span class="font-extrabold text-base text-zinc-900 dark:text-zinc-100">WarKom</span>
                </div>
                <p class="text-zinc-500 leading-relaxed">
                    Marketplace lokal berbasis komunitas untuk jual beli barang aman, transparan, dan praktis.
                </p>
            </div>

            <div>
                <h4 class="font-bold text-zinc-800 dark:text-zinc-200 mb-3">Navigasi</h4>
                <ul class="space-y-2">
                    <li><a href="{{ route('public.marketplace') }}"
                            class="hover:text-primary-600 transition">Marketplace</a></li>
                    <li><a href="{{ route('public.community') }}"
                            class="hover:text-primary-600 transition">Komunitas</a></li>
                    <li><a href="{{ route('public.category') }}"
                            class="hover:text-primary-600 transition">Kategori</a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-bold text-zinc-800 dark:text-zinc-200 mb-3">Fitur Utama</h4>
                <ul class="space-y-2">
                    <li><a href="{{ route('chat.index') }}" class="hover:text-primary-600 transition">Chat
                            Penjual</a></li>
                    <li><a href="{{ route('transaction.index') }}"
                            class="hover:text-primary-600 transition">Manajemen Transaksi</a></li>
                    <li><a href="{{ route('listing.create') }}" class="hover:text-primary-600 transition">Pasang
                            Iklan Barang</a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-bold text-zinc-800 dark:text-zinc-200 mb-3">Akun</h4>
                <ul class="space-y-2">
                    <li><a href="{{ route('user.profile') }}" class="hover:text-primary-600 transition">Profil Saya</a></li>
                    <li><a href="{{ route('login') }}" class="hover:text-primary-600 transition">Masuk Akun</a></li>
                    <li><a href="{{ route('register') }}" class="hover:text-primary-600 transition">Daftar Akun
                            Baru</a></li>
                    <li><a href="{{ route('dashboard') }}" class="hover:text-primary-600 transition">Dashboard
                            Pengguna</a></li>
                </ul>
            </div>
        </div>

        <div
            class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 pt-8 border-t border-zinc-100 dark:border-zinc-900 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p>&copy; {{ date('Y') }} WarKom (Warung Komunitas). Hak cipta dilindungi.</p>
            <p>Dibangun untuk kemajuan ekonomi komunitas.</p>
        </div>
    </footer>

    {{-- Floating Quick Chat Button --}}
    <div class="fixed bottom-6 right-6 z-50">
        @auth
            @php
                $floatingUnread = auth()->user()->unreadMessagesCount();
            @endphp
            <a href="{{ route('chat.index') }}" wire:navigate
                class="flex items-center gap-2.5 px-4 py-3 rounded-full bg-gradient-to-r from-primary-600 to-indigo-600 text-white font-bold text-sm shadow-xl hover:scale-105 hover:shadow-2xl transition duration-200 group ring-4 ring-primary-500/20"
                title="Buka Obrolan & Pesan">
                <div class="relative">
                    <flux:icon name="chat-bubble-left-right" class="size-5" />
                    @if ($floatingUnread > 0)
                        <span
                            class="absolute -top-2 -right-2 size-4 bg-red-500 text-white text-[10px] font-extrabold rounded-full flex items-center justify-center ring-2 ring-white">
                            {{ $floatingUnread }}
                        </span>
                    @endif
                </div>
                <span>Chat Penjual</span>
            </a>
        @else
            <a href="{{ route('chat.index') }}"
                class="flex items-center gap-2.5 px-4 py-3 rounded-full bg-zinc-900 dark:bg-zinc-800 text-white border border-zinc-700/80 font-semibold text-sm shadow-xl hover:scale-105 transition group"
                title="Buka Chat Penjual">
                <flux:icon name="chat-bubble-left-right"
                    class="size-5 text-primary-400 group-hover:scale-110 transition" />
                <span>Chat Penjual</span>
            </a>
        @endauth
    </div>

    @fluxScripts
</body>

</html>

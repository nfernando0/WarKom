<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include('partials.head')
    <title>{{ $title ?? 'WarKom - Marketplace Jual Beli Komunitas' }}</title>
</head>
<body class="min-h-screen bg-zinc-50 dark:bg-zinc-950 text-zinc-900 dark:text-zinc-100 flex flex-col font-sans antialiased selection:bg-primary-500 selection:text-white">

    {{-- Top Announcement Bar --}}
    <div class="bg-gradient-to-r from-primary-600 via-indigo-600 to-purple-600 text-white text-xs py-1.5 px-4 text-center font-medium shadow-xs">
        <span>✨ <strong>WarKom</strong> — Marketplace jual-beli lokal aman & praktis di dalam komunitas Anda!</span>
    </div>

    {{-- Public Navigation Header --}}
    <header class="sticky top-0 z-40 bg-white/85 dark:bg-zinc-900/85 backdrop-blur-md border-b border-zinc-200 dark:border-zinc-800 transition">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
            
            {{-- Logo & Brand --}}
            <div class="flex items-center gap-6">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 group">
                    <div class="size-9 rounded-xl bg-gradient-to-br from-primary-500 to-indigo-600 flex items-center justify-center text-white shadow-md group-hover:scale-105 transition">
                        <flux:icon name="shopping-bag" class="size-5" />
                    </div>
                    <span class="font-extrabold text-xl tracking-tight bg-gradient-to-r from-zinc-900 via-primary-600 to-indigo-600 dark:from-white dark:via-primary-400 dark:to-indigo-400 bg-clip-text text-transparent">
                        WarKom
                    </span>
                </a>

                {{-- Desktop Public Nav Links --}}
                <nav class="hidden md:flex items-center gap-1 text-sm font-medium text-zinc-600 dark:text-zinc-300">
                    <a
                        href="{{ route('public.marketplace') }}"
                        wire:navigate
                        class="px-3 py-1.5 rounded-lg transition {{ request()->routeIs('public.marketplace*') ? 'bg-primary-500/10 text-primary-600 dark:text-primary-400 font-semibold' : 'hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-900 dark:hover:text-white' }}"
                    >
                        Marketplace
                    </a>
                    <a
                        href="{{ route('public.community') }}"
                        wire:navigate
                        class="px-3 py-1.5 rounded-lg transition {{ request()->routeIs('public.community*') ? 'bg-primary-500/10 text-primary-600 dark:text-primary-400 font-semibold' : 'hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-900 dark:hover:text-white' }}"
                    >
                        Komunitas
                    </a>
                    <a
                        href="{{ route('public.category') }}"
                        wire:navigate
                        class="px-3 py-1.5 rounded-lg transition {{ request()->routeIs('public.category*') ? 'bg-primary-500/10 text-primary-600 dark:text-primary-400 font-semibold' : 'hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-900 dark:hover:text-white' }}"
                    >
                        Kategori
                    </a>
                </nav>
            </div>

            {{-- Right Actions --}}
            <div class="flex items-center gap-3">
                @auth
                    <div class="flex items-center gap-3">
                        <flux:button
                            variant="subtle"
                            icon="chat-bubble-left-right"
                            :href="route('chat.index')"
                            wire:navigate
                            class="hidden sm:inline-flex"
                        >
                            Pesan
                        </flux:button>

                        <flux:dropdown position="bottom" align="end">
                            <button type="button" class="flex items-center gap-2 p-1.5 rounded-xl hover:bg-zinc-100 dark:hover:bg-zinc-800 transition border border-zinc-200 dark:border-zinc-700/80 cursor-pointer">
                                <div class="size-8 rounded-lg bg-gradient-to-br from-primary-500 to-indigo-600 flex items-center justify-center font-bold text-xs text-white shadow-xs">
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
                                    <div class="size-9 rounded-xl bg-primary-600 text-white flex items-center justify-center font-bold text-xs shrink-0">
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
                                @if (auth()->user()->isAdmin())
                                    <flux:menu.item icon="layout-grid" :href="route('dashboard')" wire:navigate>
                                        Dashboard Admin
                                    </flux:menu.item>
                                @endif
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
                                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full cursor-pointer text-red-600 dark:text-red-400">
                                        Keluar
                                    </flux:menu.item>
                                </form>
                            </flux:menu>
                        </flux:dropdown>
                    </div>
                @else
                    <flux:button
                        variant="ghost"
                        :href="route('login')"
                    >
                        Masuk
                    </flux:button>
                    <flux:button
                        variant="primary"
                        :href="route('register')"
                    >
                        Daftar Akun
                    </flux:button>
                @endauth
            </div>
        </div>
    </header>

    {{-- Main Slot --}}
    <main class="flex-1">
        {{ $slot }}
    </main>

    {{-- Floating Quick Chat Button --}}
    <div class="fixed bottom-6 right-6 z-50">
        @auth
            @php
                $floatingUnread = auth()->user()->unreadMessagesCount();
            @endphp
            <a
                href="{{ route('chat.index') }}"
                wire:navigate
                class="flex items-center gap-2.5 px-4 py-3 rounded-full bg-gradient-to-r from-primary-600 to-indigo-600 text-white font-bold text-sm shadow-xl hover:scale-105 hover:shadow-2xl transition duration-200 group ring-4 ring-primary-500/20"
                title="Buka Obrolan & Pesan"
            >
                <div class="relative">
                    <flux:icon name="chat-bubble-left-right" class="size-5" />
                    @if ($floatingUnread > 0)
                        <span class="absolute -top-2 -right-2 size-4 bg-red-500 text-white text-[10px] font-extrabold rounded-full flex items-center justify-center ring-2 ring-white">
                            {{ $floatingUnread }}
                        </span>
                    @endif
                </div>
                <span>Chat Penjual</span>
            </a>
        @else
            <a
                href="{{ route('chat.index') }}"
                class="flex items-center gap-2.5 px-4 py-3 rounded-full bg-zinc-900 dark:bg-zinc-800 text-white border border-zinc-700/80 font-semibold text-sm shadow-xl hover:scale-105 transition group"
                title="Buka Chat Penjual"
            >
                <flux:icon name="chat-bubble-left-right" class="size-5 text-primary-400 group-hover:scale-110 transition" />
                <span>Chat Penjual</span>
            </a>
        @endauth
    </div>

    {{-- Public Footer --}}
    <footer class="bg-white dark:bg-zinc-950 border-t border-zinc-200 dark:border-zinc-800 text-xs text-zinc-500 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="space-y-3">
                <div class="flex items-center gap-2">
                    <div class="size-7 rounded-lg bg-primary-600 flex items-center justify-center text-white font-bold text-xs">
                        W
                    </div>
                    <span class="font-extrabold text-base text-zinc-900 dark:text-zinc-100">WarKom</span>
                </div>
                <p class="text-zinc-500 leading-relaxed">
                    Marketplace lokal berbasis komunitas untuk jual beli barang aman, transparan, dan praktis.
                </p>
            </div>

            <div>
                <h4 class="font-bold text-zinc-800 dark:text-zinc-200 mb-3">Navigasi Publik</h4>
                <ul class="space-y-2">
                    <li><a href="{{ route('public.marketplace') }}" class="hover:text-primary-600 transition">Marketplace</a></li>
                    <li><a href="{{ route('public.community') }}" class="hover:text-primary-600 transition">Direktori Komunitas</a></li>
                    <li><a href="{{ route('public.category') }}" class="hover:text-primary-600 transition">Kategori Produk</a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-bold text-zinc-800 dark:text-zinc-200 mb-3">Fitur Utama</h4>
                <ul class="space-y-2">
                    <li><a href="{{ route('chat.index') }}" class="hover:text-primary-600 transition">Chat Penjual Real-Time</a></li>
                    <li><a href="{{ route('transaction.index') }}" class="hover:text-primary-600 transition">Manajemen Transaksi</a></li>
                    <li><a href="{{ route('listing.create') }}" class="hover:text-primary-600 transition">Pasang Iklan Barang</a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-bold text-zinc-800 dark:text-zinc-200 mb-3">Akun & Bantuan</h4>
                <ul class="space-y-2">
                    <li><a href="{{ route('user.profile') }}" class="hover:text-primary-600 transition">Profil Saya</a></li>
                    <li><a href="{{ route('my-listings') }}" class="hover:text-primary-600 transition">Listing Saya</a></li>
                    <li><a href="{{ route('transaction.index') }}" class="hover:text-primary-600 transition">Transaksi Saya</a></li>
                    <li><a href="{{ route('login') }}" class="hover:text-primary-600 transition">Masuk Akun</a></li>
                    <li><a href="{{ route('register') }}" class="hover:text-primary-600 transition">Daftar Akun Baru</a></li>
                </ul>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 pt-8 border-t border-zinc-100 dark:border-zinc-900 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p>&copy; {{ date('Y') }} WarKom (Warung Komunitas). Hak cipta dilindungi.</p>
            <p>Platform Jual Beli Komunitas Terpercaya.</p>
        </div>
    </footer>

    @fluxScripts
</body>
</html>

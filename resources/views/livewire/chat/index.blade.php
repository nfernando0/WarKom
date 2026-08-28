<div class="py-6 sm:py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div wire:poll.3s="markActiveAsRead" class="flex flex-col h-[calc(100vh-14rem)] min-h-[550px]">
        {{-- Breadcrumbs --}}
        <div class="mb-4 bg-white dark:bg-zinc-900 p-3.5 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-2xs shrink-0">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item :href="route('home')" wire:navigate>Home</flux:breadcrumbs.item>
                <flux:breadcrumbs.item :href="route('public.marketplace')" wire:navigate>Marketplace</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>Pesan & Obrolan</flux:breadcrumbs.item>
            </flux:breadcrumbs>
        </div>

        {{-- Main Chat Box Layout --}}
        <div class="flex-1 min-h-0 bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs flex overflow-hidden">
        
        {{-- Left Sidebar: Conversations List --}}
        <div class="w-full md:w-80 lg:w-96 border-r border-zinc-200 dark:border-zinc-800 flex flex-col {{ $activeConversationId ? 'hidden md:flex' : 'flex' }} shrink-0 bg-zinc-50/50 dark:bg-zinc-900/50">
            
            {{-- Header & Search --}}
            <div class="p-4 border-b border-zinc-200 dark:border-zinc-800 space-y-3 shrink-0">
                <div class="flex items-center justify-between">
                    <flux:heading size="lg">Percakapan</flux:heading>
                    <flux:badge size="sm" color="zinc">{{ $conversations->count() }}</flux:badge>
                </div>

                {{-- Filter Tabs --}}
                <div class="flex items-center gap-1 p-1 bg-zinc-200/70 dark:bg-zinc-800/70 rounded-lg text-xs font-medium">
                    <button
                        type="button"
                        wire:click="setFilter('all')"
                        class="flex-1 py-1 px-2 rounded-md transition text-center {{ $filter === 'all' ? 'bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 shadow-xs' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200' }}"
                    >
                        Semua
                    </button>
                    <button
                        type="button"
                        wire:click="setFilter('buying')"
                        class="flex-1 py-1 px-2 rounded-md transition text-center {{ $filter === 'buying' ? 'bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 shadow-xs' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200' }}"
                    >
                        Beli
                    </button>
                    <button
                        type="button"
                        wire:click="setFilter('selling')"
                        class="flex-1 py-1 px-2 rounded-md transition text-center {{ $filter === 'selling' ? 'bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 shadow-xs' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200' }}"
                    >
                        Jual
                    </button>
                </div>

                {{-- Search --}}
                <div>
                    <flux:input
                        wire:model.live.debounce.250ms="search"
                        placeholder="Cari pesan atau penjual..."
                        icon="magnifying-glass"
                        size="sm"
                        clearable
                    />
                </div>
            </div>

            {{-- Conversation Items --}}
            <div class="flex-1 overflow-y-auto divide-y divide-zinc-100 dark:divide-zinc-800/60">
                @forelse ($conversations as $conv)
                    @php
                        $otherUser = $conv->getOtherUser($currentUserId);
                        $isSeller = ($conv->seller_id === $currentUserId);
                        $unreadCount = $conv->unreadCountFor($currentUserId);
                        $isSelected = ($conv->id === $activeConversationId);
                        $lastMsg = $conv->latestMessage;
                    @endphp
                    <div
                        wire:key="conv-item-{{ $conv->id }}"
                        wire:click="selectConversation({{ $conv->id }})"
                        class="p-3.5 cursor-pointer transition flex items-start gap-3 hover:bg-zinc-100/70 dark:hover:bg-zinc-800/40 {{ $isSelected ? 'bg-zinc-100 dark:bg-zinc-800/80 border-l-4 border-l-primary-600 dark:border-l-primary-500' : '' }}"
                    >
                        {{-- Avatar --}}
                        <div class="relative shrink-0 mt-0.5">
                            <div class="size-11 rounded-full bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center font-bold text-xs text-zinc-700 dark:text-zinc-300">
                                {{ $otherUser?->initials() ?? '?' }}
                            </div>
                            @if ($unreadCount > 0)
                                <span class="absolute -top-1 -right-1 size-4 bg-primary-600 text-white text-[10px] font-bold rounded-full flex items-center justify-center ring-2 ring-white dark:ring-zinc-900">
                                    {{ $unreadCount }}
                                </span>
                            @endif
                        </div>

                        {{-- Details --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-1 mb-0.5">
                                <span class="font-semibold text-sm text-zinc-900 dark:text-zinc-100 truncate">
                                    {{ $otherUser?->name ?? 'Pengguna' }}
                                </span>
                                @if ($lastMsg)
                                    <span class="text-[11px] text-zinc-400 shrink-0">
                                        {{ $lastMsg->created_at->diffForHumans(null, true, true) }}
                                    </span>
                                @endif
                            </div>

                            {{-- Product Sub-info --}}
                            @if ($conv->listing)
                                <div class="flex items-center gap-1.5 text-xs text-zinc-500 dark:text-zinc-400 mb-1">
                                    <flux:icon name="shopping-bag" class="size-3.5 text-zinc-400 shrink-0" />
                                    <span class="truncate font-medium text-zinc-700 dark:text-zinc-300">{{ $conv->listing->title }}</span>
                                    <flux:badge size="xs" :color="$isSeller ? 'emerald' : 'sky'" class="scale-90 origin-left">
                                        {{ $isSeller ? 'Penjual' : 'Pembeli' }}
                                    </flux:badge>
                                </div>
                            @endif

                            {{-- Last Message Snippet --}}
                            <p class="text-xs truncate {{ $unreadCount > 0 ? 'font-semibold text-zinc-900 dark:text-zinc-100' : 'text-zinc-500 dark:text-zinc-400' }}">
                                @if ($lastMsg)
                                    @if ($lastMsg->sender_id === $currentUserId)
                                        <span class="text-zinc-400">Anda: </span>
                                    @endif
                                    {{ $lastMsg->body }}
                                @else
                                    <span class="italic text-zinc-400">Belum ada pesan</span>
                                @endif
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-zinc-400 space-y-2">
                        <flux:icon name="chat-bubble-left-right" class="size-10 mx-auto text-zinc-300 dark:text-zinc-700 stroke-1" />
                        <p class="text-sm font-medium">Tidak ada percakapan</p>
                        <p class="text-xs text-zinc-500">Mulai chat dengan penjual melalui halaman marketplace.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Right Panel: Active Chat Area --}}
        <div class="flex-1 flex flex-col min-w-0 bg-white dark:bg-zinc-900 {{ $activeConversationId ? 'flex' : 'hidden md:flex' }}">
            @if ($activeConversation)
                @php
                    $otherUser = $activeConversation->getOtherUser($currentUserId);
                    $isSeller = ($activeConversation->seller_id === $currentUserId);
                    $listing = $activeConversation->listing;
                @endphp

                {{-- Chat Room Top Header --}}
                <div class="p-3.5 px-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between gap-3 bg-zinc-50/60 dark:bg-zinc-900/60 shrink-0">
                    <div class="flex items-center gap-3 min-w-0">
                        {{-- Mobile back button --}}
                        <button
                            type="button"
                            wire:click="$set('activeConversationId', null)"
                            class="md:hidden p-1.5 -ml-1 text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200 rounded-lg hover:bg-zinc-200 dark:hover:bg-zinc-800 transition"
                        >
                            <flux:icon name="arrow-left" class="size-5" />
                        </button>

                        {{-- User avatar & details --}}
                        <div class="size-10 rounded-full bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center font-bold text-xs text-zinc-700 dark:text-zinc-300 shrink-0">
                            {{ $otherUser?->initials() ?? '?' }}
                        </div>

                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <h2 class="font-bold text-zinc-900 dark:text-zinc-100 truncate text-sm sm:text-base">
                                    {{ $otherUser?->name ?? 'Pengguna' }}
                                </h2>
                                <flux:badge size="xs" :color="$isSeller ? 'emerald' : 'sky'">
                                    {{ $isSeller ? 'Anda Penjual' : 'Anda Pembeli' }}
                                </flux:badge>
                            </div>
                            <p class="text-xs text-zinc-500 truncate">
                                @if ($otherUser?->community)
                                    Komunitas {{ $otherUser->community->name }}
                                @else
                                    Anggota WarKom
                                @endif
                            </p>
                        </div>
                    </div>

                    {{-- Actions --}}
                    @if ($listing)
                        <div class="shrink-0 flex items-center gap-2">
                            <flux:button
                                size="xs"
                                variant="subtle"
                                icon="arrow-top-right-on-square"
                                :href="route('listing.show', $listing)"
                                wire:navigate
                            >
                                Lihat Barang
                            </flux:button>
                        </div>
                    @endif
                </div>

                {{-- Listing Info Bar Banner --}}
                @if ($listing)
                    <div class="px-4 py-2.5 bg-zinc-100/70 dark:bg-zinc-800/40 border-b border-zinc-200/80 dark:border-zinc-800 flex items-center justify-between gap-3 text-xs shrink-0">
                        <div class="flex items-center gap-3 min-w-0">
                            {{-- Listing Image --}}
                            <div class="size-9 rounded-md overflow-hidden bg-zinc-200 dark:bg-zinc-700 shrink-0 border border-zinc-200 dark:border-zinc-700">
                                @if ($listing->images->isNotEmpty())
                                    <img src="{{ $listing->images->first()->url }}" alt="{{ $listing->title }}" class="w-full h-full object-cover" />
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-zinc-400">
                                        <flux:icon name="photo" class="size-4" />
                                    </div>
                                @endif
                            </div>

                            <div class="min-w-0">
                                <a href="{{ route('listing.show', $listing) }}" wire:navigate class="font-medium text-zinc-900 dark:text-zinc-100 hover:underline truncate block">
                                    {{ $listing->title }}
                                </a>
                                <div class="flex items-center gap-2 text-zinc-500">
                                    <span class="font-bold text-zinc-900 dark:text-zinc-100">
                                        Rp {{ number_format($listing->price, 0, ',', '.') }}
                                    </span>
                                    <span>&bull;</span>
                                    <span>Kondisi: {{ ucfirst($listing->condition) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <flux:badge size="xs" :color="$listing->status === 'tersedia' ? 'emerald' : 'zinc'">
                                {{ ucfirst($listing->status) }}
                            </flux:badge>

                            @if ($listing->status === 'tersedia')
                                <flux:button
                                    size="xs"
                                    variant="primary"
                                    icon="shopping-bag"
                                    wire:click="createTransactionFromChat"
                                    wire:confirm="Buat transaksi kesepakatan untuk barang ini seharga Rp {{ number_format($listing->price, 0, ',', '.') }}?"
                                    title="Buat Transaksi"
                                >
                                    Buat Transaksi
                                </flux:button>
                            @else
                                <flux:button
                                    size="xs"
                                    variant="subtle"
                                    icon="receipt-percent"
                                    :href="route('transaction.index')"
                                    wire:navigate
                                    title="Lihat Transaksi"
                                >
                                    Transaksi
                                </flux:button>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Message History Stream --}}
                <div
                    x-data="{
                        scrollToBottom() {
                            this.$nextTick(() => {
                                this.$el.scrollTop = this.$el.scrollHeight;
                            });
                        }
                    }"
                    x-init="scrollToBottom()"
                    @chat-switched.window="scrollToBottom()"
                    @message-sent.window="scrollToBottom()"
                    class="flex-1 overflow-y-auto p-4 space-y-4"
                >
                    @if ($messages->isEmpty())
                        <div class="h-full flex flex-col items-center justify-center text-center p-6 text-zinc-400">
                            <div class="size-12 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-400 mb-3">
                                <flux:icon name="chat-bubble-bottom-center-text" class="size-6 stroke-1" />
                            </div>
                            <h3 class="font-semibold text-zinc-700 dark:text-zinc-300">Mulai Percakapan</h3>
                            <p class="text-xs text-zinc-500 max-w-xs mt-1">
                                Kirim pesan untuk menanyakan ketersediaan barang, kondisi, atau negosiasi harga.
                            </p>
                        </div>
                    @else
                        @php
                            $lastDate = null;
                        @endphp
                        @foreach ($messages as $msg)
                            @php
                                $msgDate = $msg->created_at->format('Y-m-d');
                                $isMine = ($msg->sender_id === $currentUserId);
                            @endphp

                            {{-- Date Separator --}}
                            @if ($lastDate !== $msgDate)
                                @php $lastDate = $msgDate; @endphp
                                <div class="flex items-center justify-center my-3">
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-500 border border-zinc-200 dark:border-zinc-700">
                                        @if ($msg->created_at->isToday())
                                            Hari Ini
                                        @elseif ($msg->created_at->isYesterday())
                                            Kemarin
                                        @else
                                            {{ $msg->created_at->translatedFormat('d F Y') }}
                                        @endif
                                    </span>
                                </div>
                            @endif

                            {{-- Message Bubble --}}
                            <div wire:key="msg-{{ $msg->id }}" class="flex flex-col {{ $isMine ? 'items-end' : 'items-start' }}">
                                <div class="flex items-end gap-2 max-w-[85%] sm:max-w-[70%] {{ $isMine ? 'flex-row-reverse' : 'flex-row' }}">
                                    {{-- Mini Avatar for partner --}}
                                    @if (! $isMine)
                                        <div class="size-6 rounded-full bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center text-[10px] font-bold text-zinc-700 dark:text-zinc-300 shrink-0 mb-1">
                                            {{ $msg->sender?->initials() ?? '?' }}
                                        </div>
                                    @endif

                                    <div class="rounded-2xl px-4 py-2.5 text-sm shadow-xs break-words {{ $isMine ? 'bg-primary-600 text-white rounded-br-xs' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 rounded-bl-xs border border-zinc-200/60 dark:border-zinc-700/60' }}">
                                        <p class="whitespace-pre-wrap leading-relaxed">{{ $msg->body }}</p>
                                    </div>
                                </div>

                                {{-- Timestamp & Read Receipt --}}
                                <div class="mt-1 flex items-center gap-1 text-[10px] text-zinc-400 {{ $isMine ? 'pr-1' : 'pl-8' }}">
                                    <span>{{ $msg->created_at->format('H:i') }}</span>
                                    @if ($isMine)
                                        <span>&bull;</span>
                                        @if ($msg->isRead())
                                            <span class="text-primary-500 font-medium flex items-center gap-0.5">
                                                <flux:icon name="check" class="size-3" /> Dibaca
                                            </span>
                                        @else
                                            <span>Terkirim</span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                {{-- Quick Reply Templates --}}
                <div class="px-4 py-2 bg-zinc-50/70 dark:bg-zinc-900/70 border-t border-zinc-100 dark:border-zinc-800/80 flex items-center gap-2 overflow-x-auto no-scrollbar shrink-0">
                    <span class="text-[11px] text-zinc-400 shrink-0">Template:</span>
                    <button
                        type="button"
                        wire:click="sendQuickMessage('Halo, apakah barang ini masih ada?')"
                        class="px-2.5 py-1 text-xs rounded-full bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 whitespace-nowrap transition"
                    >
                        Masih ada?
                    </button>
                    <button
                        type="button"
                        wire:click="sendQuickMessage('Bisa nego harganya?')"
                        class="px-2.5 py-1 text-xs rounded-full bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 whitespace-nowrap transition"
                    >
                        Bisa nego?
                    </button>
                    <button
                        type="button"
                        wire:click="sendQuickMessage('Bisa COD / ketemuan di mana?')"
                        class="px-2.5 py-1 text-xs rounded-full bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 whitespace-nowrap transition"
                    >
                        Bisa COD di mana?
                    </button>
                    <button
                        type="button"
                        wire:click="sendQuickMessage('Kondisi barang apakah masih mulus & normal?')"
                        class="px-2.5 py-1 text-xs rounded-full bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 whitespace-nowrap transition"
                    >
                        Kondisi barang?
                    </button>
                </div>

                {{-- Chat Input Bar --}}
                <div class="p-3.5 bg-white dark:bg-zinc-900 border-t border-zinc-200 dark:border-zinc-800 shrink-0">
                    <form wire:submit="sendMessage" class="flex items-end gap-2">
                        <div class="flex-1">
                            <textarea
                                wire:model="messageText"
                                rows="1"
                                placeholder="Ketik pesan Anda di sini... (Tekan Enter untuk kirim)"
                                @keydown.enter.exact.prevent="$wire.sendMessage()"
                                class="w-full resize-none rounded-xl border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/80 px-3.5 py-2.5 text-sm text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-hidden"
                                autofocus
                            ></textarea>
                            @error('messageText')
                                <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <flux:button
                            variant="primary"
                            type="submit"
                            icon="paper-airplane"
                            wire:loading.attr="disabled"
                            class="shrink-0 h-[42px] px-4"
                        >
                            <span class="hidden sm:inline">Kirim</span>
                        </flux:button>
                    </form>
                </div>
            @else
                {{-- Empty State when no conversation selected --}}
                <div class="flex-1 flex flex-col items-center justify-center p-8 text-center text-zinc-400">
                    <div class="size-16 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-400 mb-4">
                        <flux:icon name="chat-bubble-left-right" class="size-8 stroke-1" />
                    </div>
                    <flux:heading size="lg">Pilih Percakapan</flux:heading>
                    <p class="text-sm text-zinc-500 max-w-sm mt-1">
                        Pilih salah satu percakapan di daftar samping untuk melihat obrolan dengan penjual atau pembeli.
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
</div>

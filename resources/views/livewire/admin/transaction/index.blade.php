<div class="space-y-6">

    {{-- Breadcrumbs --}}
    <div class="bg-zinc-100 dark:bg-zinc-800/60 p-3.5 rounded-xl border border-zinc-200 dark:border-zinc-700">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item :href="route('dashboard')" wire:navigate>Dashboard</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Kelola Transaksi</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </div>

    {{-- Flash Notifications --}}
    @if (session()->has('success'))
        <flux:callout variant="success" icon="check-circle" :heading="session('success')" />
    @endif

    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-zinc-900 dark:text-zinc-50 tracking-tight">
                Monitoring & Manajemen Transaksi
            </h1>
            <p class="text-xs sm:text-sm text-zinc-500 mt-1">
                Pantau riwayat transaksi seluruh komunitas, verifikasi status pembayaran gateway, dan lakukan moderasi.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <flux:button size="sm" variant="subtle" icon="arrow-path" wire:click="$refresh" title="Segarkan Data">
                Refresh
            </flux:button>
        </div>
    </div>

    {{-- Metric Stat Cards (5 Cards) --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        <div class="p-4 bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs">
            <span class="text-xs text-zinc-500 font-medium block">Total Nilai Transaksi</span>
            <p class="text-xl sm:text-2xl font-extrabold text-primary-600 dark:text-primary-400 mt-1 truncate">
                Rp {{ number_format($stats['total_gmv'], 0, ',', '.') }}
            </p>
            <span class="text-[11px] text-zinc-400 block mt-0.5">Semua transaksi lunas/selesai</span>
        </div>

        <div class="p-4 bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs">
            <span class="text-xs text-zinc-500 font-medium block">Total Transaksi</span>
            <p class="text-xl sm:text-2xl font-extrabold text-zinc-900 dark:text-zinc-100 mt-1">
                {{ $stats['total_count'] }}
            </p>
            <span class="text-[11px] text-zinc-400 block mt-0.5">Keseluruhan pesanan</span>
        </div>

        <div class="p-4 bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs">
            <span class="text-xs text-zinc-500 font-medium block">Transaksi Selesai</span>
            <p class="text-xl sm:text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-1">
                {{ $stats['selesai_count'] }}
            </p>
            <span class="text-[11px] text-zinc-400 block mt-0.5">Barang telah diterima</span>
        </div>

        <div class="p-4 bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs">
            <span class="text-xs text-zinc-500 font-medium block">Menunggu Bayar / Pending</span>
            <p class="text-xl sm:text-2xl font-extrabold text-amber-500 mt-1">
                {{ $stats['pending_payment_count'] }}
            </p>
            <span class="text-[11px] text-zinc-400 block mt-0.5">Belum lunas / COD</span>
        </div>

        <div class="p-4 bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs">
            <span class="text-xs text-zinc-500 font-medium block">Dibatalkan / Gagal</span>
            <p class="text-xl sm:text-2xl font-extrabold text-red-600 dark:text-red-400 mt-1">
                {{ $stats['cancelled_count'] }}
            </p>
            <span class="text-[11px] text-zinc-400 block mt-0.5">Batal atau kadaluarsa</span>
        </div>
    </div>

    {{-- Filter & Search Toolbar --}}
    <div class="p-4 bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-3">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            {{-- Search --}}
            <div class="lg:col-span-2">
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    icon="magnifying-glass"
                    placeholder="Cari invoice, barang, nama pembeli/penjual..."
                    clearable
                />
            </div>

            {{-- Status Pesanan --}}
            <div>
                <flux:select wire:model.live="statusFilter" placeholder="Status Pesanan">
                    <flux:select.option value="all">Semua Status Pesanan</flux:select.option>
                    <flux:select.option value="pending">Pending</flux:select.option>
                    <flux:select.option value="selesai">Selesai</flux:select.option>
                    <flux:select.option value="dibatalkan">Dibatalkan</flux:select.option>
                </flux:select>
            </div>

            {{-- Status Pembayaran --}}
            <div>
                <flux:select wire:model.live="paymentStatusFilter" placeholder="Status Pembayaran">
                    <flux:select.option value="all">Semua Status Bayar</flux:select.option>
                    <flux:select.option value="unpaid">Unpaid (Belum Bayar)</flux:select.option>
                    <flux:select.option value="pending">Pending (Menunggu)</flux:select.option>
                    <flux:select.option value="settlement">Settlement (Lunas)</flux:select.option>
                    <flux:select.option value="expired">Expired (Kedaluwarsa)</flux:select.option>
                    <flux:select.option value="failed">Failed (Gagal)</flux:select.option>
                    <flux:select.option value="refunded">Refunded (Dikembalikan)</flux:select.option>
                </flux:select>
            </div>

            {{-- Komunitas --}}
            <div>
                <flux:select wire:model.live="selectedCommunity" placeholder="Filter Komunitas">
                    <flux:select.option value="">Semua Komunitas</flux:select.option>
                    @foreach ($communities as $comm)
                        <flux:select.option value="{{ $comm->id }}">{{ $comm->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
        </div>

        @if ($search || $statusFilter !== 'all' || $paymentStatusFilter !== 'all' || $selectedCommunity || $sortBy !== 'latest')
            <div class="flex items-center justify-between pt-2 border-t border-zinc-100 dark:border-zinc-800 text-xs">
                <span class="text-zinc-500">Filter aktif diterapkan</span>
                <button type="button" wire:click="resetFilters" class="text-primary-600 dark:text-primary-400 font-semibold hover:underline">
                    Reset Filter
                </button>
            </div>
        @endif
    </div>

    {{-- Main Transactions Table --}}
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs overflow-hidden">
        @if ($transactions->isEmpty())
            <div class="py-16 text-center text-zinc-400 p-8">
                <div class="size-16 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center mx-auto mb-4">
                    <flux:icon name="receipt-percent" class="size-8 stroke-1" />
                </div>
                <flux:heading size="lg">Tidak Ada Data Transaksi</flux:heading>
                <p class="text-xs sm:text-sm text-zinc-500 max-w-sm mx-auto mt-1">
                    Tidak ada transaksi yang cocok dengan filter pencarian ini.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs sm:text-sm">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/60 text-zinc-500 font-semibold border-b border-zinc-200 dark:border-zinc-800 text-[11px] uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-3.5">Invoice</th>
                            <th class="px-4 py-3.5">Barang</th>
                            <th class="px-4 py-3.5">Pembeli & Penjual</th>
                            <th class="px-4 py-3.5">Total Bayar</th>
                            <th class="px-4 py-3.5">Status Order</th>
                            <th class="px-4 py-3.5">Status Bayar</th>
                            <th class="px-4 py-3.5">Waktu</th>
                            <th class="px-4 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @foreach ($transactions as $tx)
                            <tr wire:key="admin-tx-{{ $tx->id }}" class="hover:bg-zinc-50/70 dark:hover:bg-zinc-800/40 transition">
                                {{-- Invoice --}}
                                <td class="px-4 py-3.5">
                                    <div class="font-bold text-zinc-900 dark:text-zinc-100 font-mono text-xs">
                                        {{ $tx->invoice_number ?: '#' . $tx->id }}
                                    </div>
                                    <span class="text-[10px] text-zinc-400">ID: {{ $tx->id }}</span>
                                </td>

                                {{-- Listing Item --}}
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center gap-2.5 max-w-xs">
                                        <div class="size-10 rounded-lg bg-zinc-100 dark:bg-zinc-800 shrink-0 overflow-hidden">
                                            @if ($tx->listing && $tx->listing->images->isNotEmpty())
                                                <img src="{{ $tx->listing->images->first()->url }}" alt="{{ $tx->listing->title }}" class="w-full h-full object-cover" />
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-zinc-400">
                                                    <flux:icon name="photo" class="size-4" />
                                                </div>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <span class="font-bold text-xs text-zinc-900 dark:text-zinc-100 block truncate" title="{{ $tx->listing?->title }}">
                                                {{ $tx->listing?->title ?? 'Barang Dihapus' }}
                                            </span>
                                            <span class="text-[10px] text-zinc-400 block truncate">
                                                {{ $tx->listing?->category?->name ?? '-' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                {{-- Buyer & Seller --}}
                                <td class="px-4 py-3.5">
                                    <div class="space-y-0.5 text-xs">
                                        <div class="flex items-center gap-1">
                                            <span class="text-zinc-400 text-[10px]">B:</span>
                                            <span class="font-semibold text-zinc-800 dark:text-zinc-200 truncate max-w-[110px]" title="{{ $tx->buyer?->name }}">
                                                {{ $tx->buyer?->name ?? 'User #' . $tx->buyer_id }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <span class="text-zinc-400 text-[10px]">S:</span>
                                            <span class="font-semibold text-zinc-800 dark:text-zinc-200 truncate max-w-[110px]" title="{{ $tx->seller?->name }}">
                                                {{ $tx->seller?->name ?? 'User #' . $tx->seller_id }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                {{-- Total Amount --}}
                                <td class="px-4 py-3.5">
                                    <div class="font-extrabold text-xs text-zinc-900 dark:text-zinc-100">
                                        Rp {{ number_format($tx->total_amount ?: $tx->price, 0, ',', '.') }}
                                    </div>
                                    @if ($tx->admin_fee > 0)
                                        <span class="text-[10px] text-zinc-400 block">Fee: Rp {{ number_format($tx->admin_fee, 0, ',', '.') }}</span>
                                    @endif
                                </td>

                                {{-- Order Status --}}
                                <td class="px-4 py-3.5">
                                    @if ($tx->status === 'selesai')
                                        <flux:badge size="xs" color="emerald">Selesai</flux:badge>
                                    @elseif ($tx->status === 'dibatalkan')
                                        <flux:badge size="xs" color="red">Dibatalkan</flux:badge>
                                    @else
                                        <flux:badge size="xs" color="amber">Pending</flux:badge>
                                    @endif
                                </td>

                                {{-- Payment Status --}}
                                <td class="px-4 py-3.5">
                                    @php
                                        $pStatus = $tx->payment_status ?? 'unpaid';
                                    @endphp
                                    @if ($pStatus === 'settlement')
                                        <flux:badge size="xs" color="emerald">Settlement (Lunas)</flux:badge>
                                    @elseif ($pStatus === 'pending')
                                        <flux:badge size="xs" color="amber">Pending</flux:badge>
                                    @elseif ($pStatus === 'unpaid')
                                        <flux:badge size="xs" color="zinc">Unpaid</flux:badge>
                                    @elseif ($pStatus === 'expired')
                                        <flux:badge size="xs" color="zinc">Expired</flux:badge>
                                    @elseif ($pStatus === 'refunded')
                                        <flux:badge size="xs" color="purple">Refunded</flux:badge>
                                    @else
                                        <flux:badge size="xs" color="red">{{ ucfirst($pStatus) }}</flux:badge>
                                    @endif
                                    @if ($tx->payment_channel)
                                        <span class="text-[10px] text-zinc-400 block mt-0.5 uppercase">{{ $tx->payment_channel }}</span>
                                    @endif
                                </td>

                                {{-- Date --}}
                                <td class="px-4 py-3.5 text-zinc-500 text-xs">
                                    <span>{{ $tx->created_at->format('d/m/y H:i') }}</span>
                                </td>

                                {{-- Actions --}}
                                <td class="px-4 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <flux:button
                                            size="xs"
                                            variant="subtle"
                                            icon="eye"
                                            wire:click="viewDetail({{ $tx->id }})"
                                            title="Lihat Detail Transaksi"
                                        />
                                        <flux:button
                                            size="xs"
                                            variant="subtle"
                                            icon="pencil-square"
                                            wire:click="openStatusModal({{ $tx->id }})"
                                            title="Ubah Status Admin"
                                        />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="p-4 border-t border-zinc-200 dark:border-zinc-800">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>

    {{-- Detail Modal --}}
    @if ($showDetailModal && $selectedTransaction)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <div class="bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-200 dark:border-zinc-800 shadow-2xl max-w-2xl w-full max-h-[90vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                
                {{-- Header --}}
                <div class="p-5 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between shrink-0">
                    <div>
                        <div class="flex items-center gap-2">
                            <flux:heading size="lg">Detail Transaksi</flux:heading>
                            <span class="font-mono text-xs font-bold px-2 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                                {{ $selectedTransaction->invoice_number ?: '#' . $selectedTransaction->id }}
                            </span>
                        </div>
                        <flux:subheading>Dibuat pada {{ $selectedTransaction->created_at->translatedFormat('d F Y, H:i') }} WIB</flux:subheading>
                    </div>
                    <flux:button size="xs" variant="ghost" icon="x-mark" wire:click="closeDetailModal" />
                </div>

                {{-- Body --}}
                <div class="p-6 overflow-y-auto space-y-6 flex-1 text-xs sm:text-sm">
                    
                    {{-- Status Grid --}}
                    <div class="grid grid-cols-2 gap-4 p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-2xl border border-zinc-100 dark:border-zinc-800">
                        <div>
                            <span class="text-zinc-400 text-xs block">Status Pesanan:</span>
                            <span class="font-bold text-sm text-zinc-900 dark:text-zinc-100 capitalize mt-0.5 block">
                                {{ $selectedTransaction->status }}
                            </span>
                            @if ($selectedTransaction->completed_at)
                                <span class="text-[11px] text-zinc-400">Selesai: {{ $selectedTransaction->completed_at->format('d/m/Y H:i') }}</span>
                            @endif
                        </div>
                        <div>
                            <span class="text-zinc-400 text-xs block">Status Pembayaran:</span>
                            <span class="font-bold text-sm text-zinc-900 dark:text-zinc-100 uppercase mt-0.5 block">
                                {{ $selectedTransaction->payment_status ?? 'unpaid' }}
                            </span>
                            @if ($selectedTransaction->paid_at)
                                <span class="text-[11px] text-emerald-600 dark:text-emerald-400">Lunas: {{ $selectedTransaction->paid_at->format('d/m/Y H:i') }}</span>
                            @endif
                        </div>
                    </div>

                    {{-- Item Summary --}}
                    <div>
                        <h4 class="font-bold text-zinc-900 dark:text-zinc-100 mb-2">Informasi Barang</h4>
                        @if ($selectedTransaction->listing)
                            <div class="flex items-center gap-3 p-3 bg-zinc-50 dark:bg-zinc-800/40 rounded-xl border border-zinc-100 dark:border-zinc-800">
                                <div class="size-14 rounded-lg bg-zinc-200 dark:bg-zinc-700 overflow-hidden shrink-0">
                                    @if ($selectedTransaction->listing->images->isNotEmpty())
                                        <img src="{{ $selectedTransaction->listing->images->first()->url }}" class="w-full h-full object-cover" />
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <span class="font-bold text-zinc-900 dark:text-zinc-100 block truncate">
                                        {{ $selectedTransaction->listing->title }}
                                    </span>
                                    <span class="text-xs text-zinc-500 block">Kategori: {{ $selectedTransaction->listing->category?->name ?? '-' }}</span>
                                    <span class="font-extrabold text-sm text-zinc-900 dark:text-zinc-100 block mt-1">
                                        Rp {{ number_format($selectedTransaction->price, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        @else
                            <p class="text-zinc-400">Barang telah dihapus.</p>
                        @endif
                    </div>

                    {{-- Parties: Buyer & Seller --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-3 bg-zinc-50 dark:bg-zinc-800/40 rounded-xl border border-zinc-100 dark:border-zinc-800 space-y-1">
                            <span class="text-zinc-400 text-xs font-semibold block">Pembeli (Buyer):</span>
                            <p class="font-bold text-zinc-900 dark:text-zinc-100">{{ $selectedTransaction->buyer?->name }}</p>
                            <p class="text-xs text-zinc-500">{{ $selectedTransaction->buyer?->email }}</p>
                            <p class="text-xs text-zinc-500">{{ $selectedTransaction->buyer?->phone ?: 'No HP belum diisi' }}</p>
                            @if ($selectedTransaction->buyer?->community)
                                <flux:badge size="xs" color="zinc">{{ $selectedTransaction->buyer->community->name }}</flux:badge>
                            @endif
                        </div>

                        <div class="p-3 bg-zinc-50 dark:bg-zinc-800/40 rounded-xl border border-zinc-100 dark:border-zinc-800 space-y-1">
                            <span class="text-zinc-400 text-xs font-semibold block">Penjual (Seller):</span>
                            <p class="font-bold text-zinc-900 dark:text-zinc-100">{{ $selectedTransaction->seller?->name }}</p>
                            <p class="text-xs text-zinc-500">{{ $selectedTransaction->seller?->email }}</p>
                            <p class="text-xs text-zinc-500">{{ $selectedTransaction->seller?->phone ?: 'No HP belum diisi' }}</p>
                            @if ($selectedTransaction->seller?->community)
                                <flux:badge size="xs" color="zinc">{{ $selectedTransaction->seller->community->name }}</flux:badge>
                            @endif
                        </div>
                    </div>

                    {{-- Payment Technical Details --}}
                    <div class="space-y-2 p-4 bg-zinc-50 dark:bg-zinc-800/40 rounded-xl border border-zinc-100 dark:border-zinc-800 text-xs font-mono">
                        <h4 class="font-bold font-sans text-zinc-900 dark:text-zinc-100 mb-2">Informasi Gateway & Pembayaran</h4>
                        <div class="grid grid-cols-2 gap-2">
                            <div><span class="text-zinc-400 font-sans">Payment Channel:</span> <strong class="uppercase">{{ $selectedTransaction->payment_channel ?: 'COD' }}</strong></div>
                            <div><span class="text-zinc-400 font-sans">Gateway Ref ID:</span> <strong>{{ $selectedTransaction->gateway_reference_id ?: '-' }}</strong></div>
                            <div><span class="text-zinc-400 font-sans">Payment Token:</span> <span class="truncate block">{{ $selectedTransaction->payment_token ?: '-' }}</span></div>
                            <div><span class="text-zinc-400 font-sans">Expired At:</span> <span>{{ $selectedTransaction->expired_at?->format('d/m/Y H:i') ?: '-' }}</span></div>
                        </div>
                    </div>

                    {{-- Quick Action Buttons for Admin --}}
                    <div class="pt-2 flex flex-wrap items-center gap-2 border-t border-zinc-100 dark:border-zinc-800">
                        @if ($selectedTransaction->payment_status !== 'settlement')
                            <flux:button size="xs" variant="primary" wire:click="markAsSettlement({{ $selectedTransaction->id }})">
                                Tandai Lunas (Settlement)
                            </flux:button>
                        @endif
                        @if ($selectedTransaction->status !== 'selesai')
                            <flux:button size="xs" variant="subtle" wire:click="markAsCompleted({{ $selectedTransaction->id }})">
                                Tandai Selesai
                            </flux:button>
                        @endif
                        @if ($selectedTransaction->status !== 'dibatalkan')
                            <flux:button size="xs" variant="ghost" wire:click="markAsCancelled({{ $selectedTransaction->id }})" wire:confirm="Batalkan transaksi ini dan kembalikan status barang menjadi tersedia?">
                                Batalkan Transaksi
                            </flux:button>
                        @endif
                    </div>
                </div>

                {{-- Footer --}}
                <div class="p-4 border-t border-zinc-200 dark:border-zinc-800 flex justify-end shrink-0">
                    <flux:button variant="ghost" wire:click="closeDetailModal">Tutup</flux:button>
                </div>
            </div>
        </div>
    @endif

    {{-- Status Override Modal --}}
    @if ($showStatusModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <div class="bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-200 dark:border-zinc-800 shadow-2xl max-w-md w-full p-6 space-y-5 animate-in fade-in zoom-in-95 duration-150">
                <div>
                    <flux:heading size="lg">Ubah Status Transaksi (Admin)</flux:heading>
                    <flux:subheading>Ubah status pesanan dan status pembayaran secara manual.</flux:subheading>
                </div>

                <div class="space-y-4">
                    <flux:field>
                        <flux:label>Status Pesanan</flux:label>
                        <flux:select wire:model="newStatus">
                            <flux:select.option value="pending">Pending</flux:select.option>
                            <flux:select.option value="selesai">Selesai</flux:select.option>
                            <flux:select.option value="dibatalkan">Dibatalkan</flux:select.option>
                        </flux:select>
                    </flux:field>

                    <flux:field>
                        <flux:label>Status Pembayaran</flux:label>
                        <flux:select wire:model="newPaymentStatus">
                            <flux:select.option value="unpaid">Unpaid</flux:select.option>
                            <flux:select.option value="pending">Pending</flux:select.option>
                            <flux:select.option value="settlement">Settlement (Lunas)</flux:select.option>
                            <flux:select.option value="expired">Expired</flux:select.option>
                            <flux:select.option value="failed">Failed</flux:select.option>
                            <flux:select.option value="refunded">Refunded</flux:select.option>
                        </flux:select>
                    </flux:field>
                </div>

                <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-end gap-2.5">
                    <flux:button variant="ghost" wire:click="closeStatusModal">Batal</flux:button>
                    <flux:button variant="primary" wire:click="updateStatus">Simpan Perubahan</flux:button>
                </div>
            </div>
        </div>
    @endif

</div>

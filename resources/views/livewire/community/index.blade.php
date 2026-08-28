<div class="space-y-6">
    {{-- Breadcrumbs --}}
    <div class="bg-zinc-200 dark:bg-zinc-800 p-4 rounded-xl border border-zinc-300 dark:border-zinc-700">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item :href="route('dashboard')" wire:navigate>Home</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Komunitas</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </div>

    {{-- Notifications --}}
    @if (session()->has('success'))
        <flux:callout variant="success" icon="check-circle" :heading="session('success')" />
    @endif
    @if (session()->has('error'))
        <flux:callout variant="danger" icon="exclamation-circle" :heading="session('error')" />
    @endif

    {{-- User Community Status Alert Banner --}}
    @if ($currentCommunityId)
        @php
            $myComm = $communities->firstWhere('id', $currentCommunityId);
        @endphp
        @if ($myComm)
            <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="size-10 rounded-xl bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                        <flux:icon name="shield-check" class="size-6" />
                    </div>
                    <div>
                        <p class="text-xs text-zinc-500">Komunitas Anda Saat Ini</p>
                        <h3 class="font-bold text-sm sm:text-base text-zinc-900 dark:text-zinc-100">
                            {{ $myComm->name }}
                        </h3>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <flux:button
                        size="xs"
                        variant="subtle"
                        class="text-red-500 hover:text-red-600 border border-red-500/20"
                        wire:click="leaveCommunity"
                        wire:confirm="Yakin ingin keluar dari komunitas {{ $myComm->name }}?"
                    >
                        Keluar Komunitas
                    </flux:button>
                </div>
            </div>
        @endif
    @else
        <div class="p-4 bg-amber-500/10 border border-amber-500/20 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="size-10 rounded-xl bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                    <flux:icon name="information-circle" class="size-6" />
                </div>
                <div>
                    <h3 class="font-bold text-sm text-amber-800 dark:text-amber-300">Belum Bergabung dengan Komunitas</h3>
                    <p class="text-xs text-zinc-500 mt-0.5">
                        Masukkan kode undangan dari pengurus lingkungan Anda untuk mulai berjual-beli.
                    </p>
                </div>
            </div>
            <flux:button variant="primary" icon="plus" wire:click="openJoinModal" size="sm" class="shrink-0">
                Gabung Komunitas Sekarang
            </flux:button>
        </div>
    @endif

    {{-- Header & Search --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">Direktori Komunitas</flux:heading>
            <flux:subheading>
                Daftar komunitas warga yang terdaftar di WarKom.
            </flux:subheading>
        </div>

        <div class="flex flex-col sm:flex-row items-center gap-2.5">
            {{-- Search --}}
            <div class="w-full sm:w-64">
                <flux:input
                    wire:model.live.debounce.250ms="search"
                    placeholder="Cari nama atau lokasi..."
                    icon="magnifying-glass"
                    size="sm"
                    clearable
                />
            </div>

            {{-- View Mode Switcher --}}
            <div class="flex items-center gap-1 p-1 bg-zinc-100 dark:bg-zinc-800 rounded-lg shrink-0">
                <button
                    type="button"
                    wire:click="$set('viewMode', 'grid')"
                    class="p-1.5 rounded-md transition {{ $viewMode === 'grid' ? 'bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 shadow-xs' : 'text-zinc-400 hover:text-zinc-600' }}"
                    title="Grid View"
                >
                    <flux:icon name="squares-2x2" class="size-4" />
                </button>
                <button
                    type="button"
                    wire:click="$set('viewMode', 'table')"
                    class="p-1.5 rounded-md transition {{ $viewMode === 'table' ? 'bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 shadow-xs' : 'text-zinc-400 hover:text-zinc-600' }}"
                    title="Table View"
                >
                    <flux:icon name="bars-3" class="size-4" />
                </button>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <flux:button variant="subtle" wire:click="openJoinModal" size="sm" class="flex-1 sm:flex-none">
                    Gabung Kode
                </flux:button>
                @if ($isAdmin)
                    <flux:button variant="primary" icon="plus" :href="route('community.create')" wire:navigate size="sm" class="flex-1 sm:flex-none">
                        Buat Komunitas
                    </flux:button>
                @endif
            </div>
        </div>
    </div>

    {{-- Communities Listing --}}
    <div>
        @if ($communities->isEmpty())
            <div class="py-16 text-center bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-8 shadow-xs">
                <div class="size-16 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center mx-auto mb-4 text-zinc-400">
                    <flux:icon name="users" class="size-8 stroke-1" />
                </div>
                <flux:heading size="lg">Komunitas Tidak Ditemukan</flux:heading>
                <p class="text-sm text-zinc-500 max-w-sm mx-auto mt-1">
                    @if ($search)
                        Tidak ada komunitas yang cocok dengan kata kunci "{{ $search }}".
                    @else
                        Belum ada komunitas yang terdaftar.
                    @endif
                </p>
            </div>
        @else
            @if ($viewMode === 'grid')
                {{-- Grid View Cards --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach ($communities as $community)
                        @php
                            $isMember = ($community->id === $currentCommunityId);
                        @endphp
                        <div wire:key="community-grid-{{ $community->id }}" class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 hover:border-zinc-300 dark:hover:border-zinc-700 shadow-xs hover:shadow-md transition flex flex-col justify-between p-5 space-y-4 {{ $isMember ? 'ring-2 ring-emerald-500/40' : '' }}">
                            <div class="space-y-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="size-11 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0 font-bold">
                                        <flux:icon name="users" class="size-6" />
                                    </div>

                                    <div class="flex flex-wrap items-center gap-1.5">
                                        @if ($isMember)
                                            <flux:badge color="emerald" size="sm" icon="check">Tergabung</flux:badge>
                                        @endif
                                        <flux:badge size="sm" color="zinc">
                                            {{ $community->members_count }} Anggota
                                        </flux:badge>
                                    </div>
                                </div>

                                <div>
                                    <h3 class="font-bold text-base text-zinc-900 dark:text-zinc-100">
                                        {{ $community->name }}
                                    </h3>
                                    <p class="text-xs text-zinc-500 mt-1 line-clamp-2">
                                        {{ $community->description ?: 'Komunitas aktif jual beli warga.' }}
                                    </p>
                                </div>

                                <div class="flex flex-wrap items-center gap-3 text-xs text-zinc-500 pt-1">
                                    @if ($community->location)
                                        <div class="flex items-center gap-1">
                                            <flux:icon name="map-pin" class="size-3.5 text-zinc-400" />
                                            <span>{{ $community->location }}</span>
                                        </div>
                                    @endif
                                    <div class="flex items-center gap-1">
                                        <flux:icon name="shopping-bag" class="size-3.5 text-zinc-400" />
                                        <span>{{ $community->listings_count }} barang dijual</span>
                                    </div>
                                </div>

                                @if ($isAdmin || $isMember)
                                    <div class="p-2 bg-zinc-100 dark:bg-zinc-800 rounded-lg text-xs flex items-center justify-between">
                                        <span class="text-zinc-500">Kode Undangan:</span>
                                        <span class="font-mono font-bold text-zinc-800 dark:text-zinc-200 bg-white dark:bg-zinc-900 px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700">
                                            {{ $community->invite_code }}
                                        </span>
                                    </div>
                                @endif
                            </div>

                            {{-- Actions --}}
                            <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between gap-2">
                                @if (! $currentCommunityId)
                                    <flux:button
                                        size="xs"
                                        variant="primary"
                                        wire:click="openJoinModal"
                                        class="flex-1 text-center justify-center"
                                    >
                                        Gabung Komunitas
                                    </flux:button>
                                @endif

                                @if ($isAdmin)
                                    <div class="flex items-center gap-1 w-full justify-end">
                                        <flux:button size="xs" variant="subtle" icon="users" wire:click="openMembersModal({{ $community->id }})" title="Kelola Anggota" />
                                        <flux:button size="xs" variant="subtle" icon="pencil-square" wire:click="openEditModal({{ $community->id }})" title="Edit" />
                                        <flux:button size="xs" variant="subtle" icon="trash" class="text-red-500 hover:text-red-600" wire:click="openDeleteModal({{ $community->id }})" title="Hapus" />
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                {{-- Table View --}}
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs overflow-hidden">
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>Nama Komunitas</flux:table.column>
                            <flux:table.column>Lokasi</flux:table.column>
                            <flux:table.column>Anggota</flux:table.column>
                            <flux:table.column>Barang Dijual</flux:table.column>
                            <flux:table.column>Status</flux:table.column>
                            @if ($isAdmin)
                                <flux:table.column class="text-end">Aksi</flux:table.column>
                            @endif
                        </flux:table.columns>

                        <flux:table.rows>
                            @foreach ($communities as $community)
                                <flux:table.row :key="$community->id">
                                    <flux:table.cell class="font-medium">
                                        <div>
                                            <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ $community->name }}</span>
                                            <p class="text-xs text-zinc-500 line-clamp-1">{{ $community->description }}</p>
                                        </div>
                                    </flux:table.cell>
                                    <flux:table.cell>{{ $community->location ?: '-' }}</flux:table.cell>
                                    <flux:table.cell>
                                        <flux:badge size="sm" color="zinc">{{ $community->members_count }}</flux:badge>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <span class="text-xs font-semibold">{{ $community->listings_count }} barang</span>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        @if ($community->id === $currentCommunityId)
                                            <flux:badge color="emerald" size="sm">Tergabung</flux:badge>
                                        @else
                                            <span class="text-xs text-zinc-400">-</span>
                                        @endif
                                    </flux:table.cell>
                                    @if ($isAdmin)
                                        <flux:table.cell class="text-end">
                                            <div class="flex items-center justify-end gap-1">
                                                <flux:button size="xs" variant="ghost" icon="users" wire:click="openMembersModal({{ $community->id }})">Anggota</flux:button>
                                                <flux:button size="xs" variant="ghost" icon="pencil-square" wire:click="openEditModal({{ $community->id }})">Edit</flux:button>
                                                <flux:button size="xs" variant="danger" icon="trash" wire:click="openDeleteModal({{ $community->id }})">Hapus</flux:button>
                                            </div>
                                        </flux:table.cell>
                                    @endif
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                </div>
            @endif
        @endif
    </div>

    {{-- Join Community Modal --}}
    @if ($showJoinModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-6 max-w-md w-full shadow-xl space-y-5 animate-in fade-in zoom-in duration-150">
                <div class="flex items-center justify-between">
                    <flux:heading size="lg">Gabung Komunitas</flux:heading>
                    <button type="button" wire:click="closeJoinModal" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200">
                        <flux:icon name="x-mark" class="size-5" />
                    </button>
                </div>

                <p class="text-xs text-zinc-500">
                    Masukkan kode undangan 6 digit yang diberikan oleh ketua/pengurus komunitas Anda.
                </p>

                <form wire:submit="join" class="space-y-4">
                    <flux:field>
                        <flux:label>Kode Undangan</flux:label>
                        <flux:input wire:model="invite_code" placeholder="Contoh: ABC123" autofocus />
                        <flux:error name="invite_code" />
                    </flux:field>

                    <div class="pt-3 flex items-center justify-end gap-2.5 border-t border-zinc-100 dark:border-zinc-800">
                        <flux:button variant="ghost" type="button" wire:click="closeJoinModal">
                            Batal
                        </flux:button>
                        <flux:button variant="primary" type="submit" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="join">Gabung</span>
                            <span wire:loading wire:target="join">Memproses...</span>
                        </flux:button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Edit Community Modal (Admin) --}}
    @if ($showEditModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-6 max-w-lg w-full shadow-xl space-y-5 animate-in fade-in zoom-in duration-150">
                <div class="flex items-center justify-between">
                    <flux:heading size="lg">Edit Komunitas</flux:heading>
                    <button type="button" wire:click="closeEditModal" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200">
                        <flux:icon name="x-mark" class="size-5" />
                    </button>
                </div>

                <form wire:submit="update" class="space-y-4">
                    <flux:field>
                        <flux:label>Nama Komunitas</flux:label>
                        <flux:input wire:model="edit_name" />
                        <flux:error name="edit_name" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Lokasi / Wilayah</flux:label>
                        <flux:input wire:model="edit_location" placeholder="Contoh: RT 05 / RW 02, Kel. Sukamaju" />
                        <flux:error name="edit_location" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Kode Undangan</flux:label>
                        <flux:input wire:model="edit_invite_code" />
                        <flux:error name="edit_invite_code" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Deskripsi</flux:label>
                        <flux:textarea wire:model="edit_description" rows="3" />
                        <flux:error name="edit_description" />
                    </flux:field>

                    <div class="pt-3 flex items-center justify-end gap-2.5 border-t border-zinc-100 dark:border-zinc-800">
                        <flux:button variant="ghost" type="button" wire:click="closeEditModal">
                            Batal
                        </flux:button>
                        <flux:button variant="primary" type="submit" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="update">Simpan Perubahan</span>
                            <span wire:loading wire:target="update">Menyimpan...</span>
                        </flux:button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Delete Community Modal (Admin) --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-6 max-w-md w-full shadow-xl space-y-4 animate-in fade-in zoom-in duration-150">
                <div class="size-12 rounded-full bg-red-500/10 text-red-600 flex items-center justify-center mx-auto">
                    <flux:icon name="exclamation-triangle" class="size-6" />
                </div>
                <div class="text-center space-y-2">
                    <flux:heading size="lg">Hapus Komunitas?</flux:heading>
                    <p class="text-xs text-zinc-500">
                        Yakin ingin menghapus komunitas <strong>"{{ $deletingCommunityName }}"</strong>? Anggota yang tergabung akan dipisahkan dari komunitas ini.
                    </p>
                </div>

                <div class="pt-3 flex items-center justify-center gap-2.5 border-t border-zinc-100 dark:border-zinc-800">
                    <flux:button variant="ghost" type="button" wire:click="closeDeleteModal">
                        Batal
                    </flux:button>
                    <flux:button variant="danger" type="button" wire:click="delete" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="delete">Ya, Hapus Komunitas</span>
                        <span wire:loading wire:target="delete">Menghapus...</span>
                    </flux:button>
                </div>
            </div>
        </div>
    @endif

    {{-- Members Modal (Admin) --}}
    @if ($showMembersModal && $selectedCommunity)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-6 max-w-lg w-full shadow-xl space-y-4 max-h-[85vh] flex flex-col animate-in fade-in zoom-in duration-150">
                <div class="flex items-center justify-between">
                    <div>
                        <flux:heading size="lg">Daftar Anggota</flux:heading>
                        <flux:subheading>{{ $selectedCommunity->name }} ({{ $selectedCommunity->members->count() }} Anggota)</flux:subheading>
                    </div>
                    <button type="button" wire:click="closeMembersModal" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200">
                        <flux:icon name="x-mark" class="size-5" />
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto divide-y divide-zinc-100 dark:divide-zinc-800 pr-1">
                    @forelse ($selectedCommunity->members as $member)
                        <div wire:key="member-row-{{ $member->id }}" class="py-3 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="size-9 rounded-full bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center font-bold text-xs text-zinc-700 dark:text-zinc-300">
                                    {{ $member->initials() }}
                                </div>
                                <div>
                                    <span class="font-semibold text-sm text-zinc-900 dark:text-zinc-100 block">{{ $member->name }}</span>
                                    <span class="text-xs text-zinc-400">{{ $member->email }}</span>
                                </div>
                            </div>

                            @if ($member->id !== auth()->id())
                                <flux:button
                                    size="xs"
                                    variant="subtle"
                                    class="text-red-500 hover:text-red-600"
                                    wire:click="kickMember({{ $member->id }})"
                                    wire:confirm="Keluarkan {{ $member->name }} dari komunitas?"
                                >
                                    Keluarkan
                                </flux:button>
                            @else
                                <flux:badge size="xs" color="zinc">Anda</flux:badge>
                            @endif
                        </div>
                    @empty
                        <p class="text-xs text-zinc-400 text-center py-6">Belum ada anggota.</p>
                    @endforelse
                </div>

                <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800 flex justify-end">
                    <flux:button variant="ghost" type="button" wire:click="closeMembersModal">
                        Tutup
                    </flux:button>
                </div>
            </div>
        </div>
    @endif
</div>

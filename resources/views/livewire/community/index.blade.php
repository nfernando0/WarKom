<div>
    <div class="bg-zinc-200 dark:bg-zinc-800 p-4 rounded-xl border border-zinc-300 dark:border-zinc-700">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item :href="route('dashboard')" wire:navigate>Home</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Community</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </div>

    <div class="mt-4">
        @if (session()->has('success'))
            <div class="mb-4">
                <flux:callout variant="success" icon="check-circle" :heading="session('success')" />
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mb-4">
                <flux:callout variant="danger" icon="exclamation-circle" :heading="session('error')" />
            </div>
        @endif

        <div class="flex items-center gap-2">
            @if (auth()->user()?->isAdmin())
                <flux:button variant="primary" :href="route('community.create')" wire:navigate>Create Community</flux:button>
            @endif
            <flux:button variant="filled" wire:click="openJoinModal">Join Community</flux:button>
        </div>

        <div class="mt-4">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Name</flux:table.column>
                    <flux:table.column>Description</flux:table.column>
                    <flux:table.column>Location</flux:table.column>
                    <flux:table.column>Invite Code</flux:table.column>
                    <flux:table.column>Members</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    @if (auth()->user()?->isAdmin())
                        <flux:table.column class="text-end">Actions</flux:table.column>
                    @endif
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($communities as $community)
                        <flux:table.row :key="$community->id">
                            <flux:table.cell class="font-medium">{{ $community->name }}</flux:table.cell>
                            <flux:table.cell>{{ $community->description }}</flux:table.cell>
                            <flux:table.cell>{{ $community->location }}</flux:table.cell>
                            <flux:table.cell>
                                <span class="font-mono bg-zinc-100 dark:bg-zinc-800 px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700 text-xs">
                                    {{ $community->invite_code }}
                                </span>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if (auth()->user()?->isAdmin())
                                    <button type="button" wire:click="openMembersModal({{ $community->id }})" class="cursor-pointer hover:opacity-80 transition">
                                        <flux:badge size="sm" color="zinc">
                                            {{ $community->members_count }} {{ Str::plural('member', $community->members_count) }}
                                        </flux:badge>
                                    </button>
                                @else
                                    <flux:badge size="sm" color="zinc">
                                        {{ $community->members_count }} {{ Str::plural('member', $community->members_count) }}
                                    </flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                @if ($community->id === $currentCommunityId)
                                    <div class="flex items-center gap-2">
                                        <flux:badge color="emerald" size="sm">Joined</flux:badge>
                                        <flux:button
                                            size="xs"
                                            variant="subtle"
                                            class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 font-medium"
                                            wire:click="leaveCommunity"
                                            wire:confirm="Are you sure you want to leave {{ $community->name }}?"
                                            wire:loading.attr="disabled"
                                        >
                                            Leave
                                        </flux:button>
                                    </div>
                                @else
                                    <flux:text class="text-xs text-zinc-400">-</flux:text>
                                @endif
                            </flux:table.cell>
                            @if (auth()->user()?->isAdmin())
                                <flux:table.cell class="text-end">
                                    <div class="flex items-center justify-end gap-1">
                                        <flux:button size="xs" variant="ghost" icon="users" wire:click="openMembersModal({{ $community->id }})">
                                            Members
                                        </flux:button>
                                        <flux:button size="xs" variant="ghost" icon="pencil-square" wire:click="openEditModal({{ $community->id }})">
                                            Edit
                                        </flux:button>
                                        <flux:button size="xs" variant="danger" icon="trash" wire:click="openDeleteModal({{ $community->id }})">
                                            Delete
                                        </flux:button>
                                    </div>
                                </flux:table.cell>
                            @endif
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell :colspan="auth()->user()?->isAdmin() ? 7 : 6">No communities found.</flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
    </div>

    <flux:modal name="join-community-modal" wire:model="showJoinModal" class="max-w-md">
        <form wire:submit="join" class="space-y-6">
            <div>
                <flux:heading size="lg">Join Community</flux:heading>
                <flux:subheading>Enter the community invite code to become a member.</flux:subheading>
            </div>

            <flux:field>
                <flux:label>Invite Code</flux:label>
                <flux:input wire:model="invite_code" placeholder="Enter invite code (e.g. ABC123)" autofocus />
                <flux:error name="invite_code" />
            </flux:field>

            <div class="flex justify-end gap-2">
                <flux:button variant="ghost" wire:click="closeJoinModal" type="button">
                    Cancel
                </flux:button>
                <flux:button variant="primary" type="submit" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="join">Join</span>
                    <span wire:loading wire:target="join">Joining...</span>
                </flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="edit-community-modal" wire:model="showEditModal" class="max-w-xl">
        <div class="space-y-6">
            <form wire:submit="update" class="space-y-4">
                <div>
                    <flux:heading size="lg">Edit Community</flux:heading>
                    <flux:subheading>Update community details and invite code.</flux:subheading>
                </div>

                <flux:field>
                    <flux:label>Name</flux:label>
                    <flux:input wire:model="edit_name" />
                    <flux:error name="edit_name" />
                </flux:field>

                <flux:field>
                    <flux:label>Description</flux:label>
                    <flux:textarea wire:model="edit_description" rows="3" />
                    <flux:error name="edit_description" />
                </flux:field>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Location</flux:label>
                        <flux:input wire:model="edit_location" />
                        <flux:error name="edit_location" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Invite Code</flux:label>
                        <flux:input wire:model="edit_invite_code" />
                        <flux:error name="edit_invite_code" />
                    </flux:field>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <flux:button variant="ghost" wire:click="closeEditModal" type="button">
                        Cancel
                    </flux:button>
                    <flux:button variant="primary" type="submit" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="update">Save Changes</span>
                        <span wire:loading wire:target="update">Saving...</span>
                    </flux:button>
                </div>
            </form>

            <flux:separator />

            <div class="space-y-4">
                <div>
                    <flux:heading size="base">Add Member</flux:heading>
                    <flux:subheading>Directly assign a registered user to this community.</flux:subheading>
                </div>

                @error('add_member_error')
                    <flux:callout variant="danger" icon="exclamation-circle" :heading="$message" />
                @enderror

                <div class="flex flex-col sm:flex-row gap-2">
                    <div class="flex-1">
                        @if ($availableUsers->isNotEmpty())
                            <flux:select wire:model="add_member_user_id" placeholder="Choose a user...">
                                <flux:select.option value="">Select registered user...</flux:select.option>
                                @foreach ($availableUsers as $user)
                                    <flux:select.option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</flux:select.option>
                                @endforeach
                            </flux:select>
                        @else
                            <flux:input wire:model="add_member_email" placeholder="Enter user's email address..." />
                        @endif
                    </div>
                    <flux:button variant="filled" wire:click="addMemberToCommunity" wire:loading.attr="disabled" class="shrink-0">
                        <span wire:loading.remove wire:target="addMemberToCommunity">Add Member</span>
                        <span wire:loading wire:target="addMemberToCommunity">Adding...</span>
                    </flux:button>
                </div>

                @if ($editingCommunity && $editingCommunity->members->isNotEmpty())
                    <div class="pt-2">
                        <flux:label class="mb-2 block">Current Members ({{ $editingCommunity->members->count() }})</flux:label>
                        <div class="border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden divide-y divide-zinc-200 dark:divide-zinc-700 max-h-48 overflow-y-auto">
                            @foreach ($editingCommunity->members as $member)
                                <div class="p-2.5 flex items-center justify-between gap-2 bg-zinc-50 dark:bg-zinc-800/50">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <div class="size-7 rounded-full bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center font-semibold text-xs text-zinc-700 dark:text-zinc-300 shrink-0">
                                            {{ $member->initials() }}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1.5">
                                                <span class="font-medium text-xs text-zinc-900 dark:text-zinc-100 truncate">{{ $member->name }}</span>
                                                @if ($member->id === $editingCommunity->created_by)
                                                    <flux:badge size="xs" color="amber">Creator</flux:badge>
                                                @endif
                                            </div>
                                            <p class="text-xs text-zinc-500 truncate">{{ $member->email }}</p>
                                        </div>
                                    </div>

                                    <div class="shrink-0">
                                        @if ($member->id === auth()->id())
                                            <flux:badge size="xs" color="zinc">You</flux:badge>
                                        @else
                                            <flux:button
                                                size="xs"
                                                variant="subtle"
                                                icon="x-mark"
                                                wire:click="removeMemberFromEdit({{ $member->id }})"
                                                wire:confirm="Remove {{ $member->name }} from this community?"
                                                title="Remove member"
                                            />
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </flux:modal>

    <flux:modal name="delete-community-modal" wire:model="showDeleteModal" class="max-w-md">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Delete Community</flux:heading>
                <flux:text class="mt-2">
                    Are you sure you want to delete community <strong class="text-zinc-900 dark:text-zinc-100">"{{ $deletingCommunityName }}"</strong>? This action cannot be undone.
                </flux:text>
            </div>

            <div class="flex justify-end gap-2">
                <flux:button variant="ghost" wire:click="closeDeleteModal" type="button">
                    Cancel
                </flux:button>
                <flux:button variant="danger" wire:click="delete" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="delete">Delete Community</span>
                    <span wire:loading wire:target="delete">Deleting...</span>
                </flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:modal name="community-members-modal" wire:model="showMembersModal" class="max-w-xl">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">
                    Community Members
                    @if ($selectedCommunity)
                        <span class="text-zinc-500 font-normal text-base block sm:inline"> - {{ $selectedCommunity->name }}</span>
                    @endif
                </flux:heading>
                <flux:subheading>
                    Manage and view members registered in this community.
                </flux:subheading>
            </div>

            @error('kick_error')
                <flux:callout variant="danger" icon="exclamation-circle" :heading="$message" />
            @enderror

            @if ($selectedCommunity)
                <div class="border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden divide-y divide-zinc-200 dark:divide-zinc-700 max-h-96 overflow-y-auto">
                    @forelse ($selectedCommunity->members as $member)
                        <div class="p-3.5 flex items-center justify-between gap-3 bg-zinc-50 dark:bg-zinc-800/50 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="size-9 rounded-full bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center font-semibold text-xs text-zinc-700 dark:text-zinc-300 shrink-0">
                                    {{ $member->initials() }}
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium text-sm text-zinc-900 dark:text-zinc-100 truncate">{{ $member->name }}</span>
                                        @if ($member->id === $selectedCommunity->created_by)
                                            <flux:badge size="xs" color="amber">Creator</flux:badge>
                                        @endif
                                        @if ($member->isAdmin())
                                            <flux:badge size="xs" color="indigo">Admin</flux:badge>
                                        @endif
                                    </div>
                                    <p class="text-xs text-zinc-500 truncate">{{ $member->email }}</p>
                                </div>
                            </div>

                            <div class="shrink-0 flex items-center gap-2">
                                @if ($member->id === auth()->id())
                                    <flux:badge size="sm" color="zinc">You</flux:badge>
                                    <flux:button
                                        size="xs"
                                        variant="subtle"
                                        class="text-red-500 hover:text-red-600"
                                        wire:click="leaveCommunity"
                                        wire:confirm="Are you sure you want to leave this community?"
                                    >
                                        Leave
                                    </flux:button>
                                @else
                                    <flux:button
                                        size="xs"
                                        variant="danger"
                                        wire:click="kickMember({{ $member->id }})"
                                        wire:confirm="Are you sure you want to kick {{ $member->name }} from this community?"
                                        wire:loading.attr="disabled"
                                    >
                                        Kick
                                    </flux:button>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-sm text-zinc-500">
                            No members found in this community.
                        </div>
                    @endforelse
                </div>
            @endif

            <div class="flex justify-end">
                <flux:button variant="ghost" wire:click="closeMembersModal">
                    Close
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>

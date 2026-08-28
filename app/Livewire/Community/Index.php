<?php

namespace App\Livewire\Community;

use Livewire\Component;
use App\Models\Community;
use App\Models\User;

class Index extends Component
{
    // Join Community state
    public string $invite_code = '';
    public bool $showJoinModal = false;

    // Edit Community state
    public bool $showEditModal = false;
    public ?int $editingCommunityId = null;
    public string $edit_name = '';
    public string $edit_description = '';
    public string $edit_location = '';
    public string $edit_invite_code = '';
    public string $add_member_user_id = '';
    public string $add_member_email = '';

    // Delete Community state
    public bool $showDeleteModal = false;
    public ?int $deletingCommunityId = null;
    public string $deletingCommunityName = '';

    // Members Modal state
    public bool $showMembersModal = false;
    public ?int $selectedCommunityId = null;

    protected array $rules = [
        'invite_code' => 'required|string|min:3',
    ];

    protected array $messages = [
        'invite_code.required' => 'Invite code is required',
        'invite_code.min' => 'Invite code must be at least 3 characters long',
    ];

    public function openJoinModal(): void
    {
        $user = auth()->user();

        if (! empty($user->community_id)) {
            session()->flash('error', 'You already belong to a community. Please leave your current community first before joining another.');
            return;
        }

        $this->reset('invite_code');
        $this->resetErrorBag();
        $this->showJoinModal = true;
    }

    public function closeJoinModal(): void
    {
        $this->showJoinModal = false;
        $this->reset('invite_code');
        $this->resetErrorBag();
    }

    public function join(): void
    {
        $this->validate();

        $user = auth()->user();

        if (! empty($user->community_id)) {
            $this->addError('invite_code', 'You already belong to a community. Please leave your current community first before joining another.');
            return;
        }

        $community = Community::where('invite_code', trim($this->invite_code))->first();

        if (! $community) {
            $this->addError('invite_code', 'Invalid invite code. Community not found.');
            return;
        }

        $user->update([
            'community_id' => $community->id,
        ]);

        $this->closeJoinModal();

        session()->flash('success', "Successfully joined \"{$community->name}\"!");
    }

    public function leaveCommunity(): void
    {
        $user = auth()->user();

        if (! $user || ! $user->community_id) {
            return;
        }

        $communityName = $user->community?->name ?? 'the community';

        $user->update([
            'community_id' => null,
        ]);

        session()->flash('success', "You have left \"{$communityName}\".");
    }

    public function openEditModal(int $id): void
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $community = Community::findOrFail($id);

        $this->editingCommunityId = $community->id;
        $this->edit_name = $community->name;
        $this->edit_description = $community->description ?? '';
        $this->edit_location = $community->location;
        $this->edit_invite_code = $community->invite_code;
        $this->reset(['add_member_user_id', 'add_member_email']);

        $this->resetErrorBag();
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->reset(['editingCommunityId', 'edit_name', 'edit_description', 'edit_location', 'edit_invite_code', 'add_member_user_id', 'add_member_email']);
        $this->resetErrorBag();
    }

    public function update(): void
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $this->validate([
            'edit_name' => 'required|string|min:3',
            'edit_description' => 'required|string|min:3',
            'edit_location' => 'required|string|min:3',
            'edit_invite_code' => 'required|string|min:3|unique:communities,invite_code,' . $this->editingCommunityId,
        ], [
            'edit_name.required' => 'Name is required',
            'edit_name.min' => 'Name must be at least 3 characters long',
            'edit_description.required' => 'Description is required',
            'edit_description.min' => 'Description must be at least 3 characters long',
            'edit_location.required' => 'Location is required',
            'edit_location.min' => 'Location must be at least 3 characters long',
            'edit_invite_code.required' => 'Invite code is required',
            'edit_invite_code.min' => 'Invite code must be at least 3 characters long',
            'edit_invite_code.unique' => 'Invite code has already been taken',
        ]);

        $community = Community::findOrFail($this->editingCommunityId);

        $community->update([
            'name' => $this->edit_name,
            'description' => $this->edit_description,
            'location' => $this->edit_location,
            'invite_code' => $this->edit_invite_code,
        ]);

        $this->closeEditModal();

        session()->flash('success', "Community \"{$community->name}\" updated successfully.");
    }

    public function addMemberToCommunity(): void
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $user = null;
        if (! empty($this->add_member_user_id)) {
            $user = User::find($this->add_member_user_id);
        } elseif (! empty($this->add_member_email)) {
            $user = User::where('email', trim($this->add_member_email))->first();
        }

        if (! $user) {
            $this->addError('add_member_error', 'User not found. Please select a user or enter a registered email.');
            return;
        }

        if ($user->community_id === $this->editingCommunityId) {
            $this->addError('add_member_error', 'User is already a member of this community.');
            return;
        }

        $user->update([
            'community_id' => $this->editingCommunityId,
        ]);

        $this->reset(['add_member_user_id', 'add_member_email']);
        $this->resetErrorBag('add_member_error');

        session()->flash('success', "Member \"{$user->name}\" added to the community.");
    }

    public function removeMemberFromEdit(int $userId): void
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $member = User::findOrFail($userId);

        if ($member->id === auth()->id()) {
            $this->addError('add_member_error', 'You cannot remove yourself from the community.');
            return;
        }

        if ($member->community_id === $this->editingCommunityId) {
            $member->update(['community_id' => null]);
            session()->flash('success', "Member \"{$member->name}\" was removed from the community.");
        }
    }

    public function openDeleteModal(int $id): void
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $community = Community::findOrFail($id);

        $this->deletingCommunityId = $community->id;
        $this->deletingCommunityName = $community->name;

        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->reset(['deletingCommunityId', 'deletingCommunityName']);
    }

    public function delete(): void
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $community = Community::findOrFail($this->deletingCommunityId);
        $name = $community->name;

        $community->delete();

        $this->closeDeleteModal();

        session()->flash('success', "Community \"{$name}\" deleted successfully.");
    }

    public function openMembersModal(int $communityId): void
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $this->selectedCommunityId = $communityId;
        $this->resetErrorBag();
        $this->showMembersModal = true;
    }

    public function closeMembersModal(): void
    {
        $this->showMembersModal = false;
        $this->selectedCommunityId = null;
        $this->resetErrorBag();
    }

    public function kickMember(int $userId): void
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $member = User::findOrFail($userId);

        if ($member->id === auth()->id()) {
            $this->addError('kick_error', 'You cannot kick yourself from the community.');
            return;
        }

        if ($member->community_id === $this->selectedCommunityId) {
            $member->update(['community_id' => null]);
            session()->flash('success', "Member \"{$member->name}\" was removed from the community.");
        }
    }

    public function render()
    {
        $selectedCommunity = $this->selectedCommunityId
            ? Community::with('members')->find($this->selectedCommunityId)
            : null;

        $editingCommunity = $this->editingCommunityId
            ? Community::with('members')->find($this->editingCommunityId)
            : null;

        $availableUsers = ($this->editingCommunityId && auth()->user()?->isAdmin())
            ? User::where(function ($query) {
                $query->whereNull('community_id')
                    ->orWhere('community_id', '!=', $this->editingCommunityId);
            })->orderBy('name')->get()
            : collect();

        return view('livewire.community.index', [
            'communities' => Community::with('creator')->withCount('members')->latest()->get(),
            'currentCommunityId' => auth()->user()?->community_id,
            'selectedCommunity' => $selectedCommunity,
            'editingCommunity' => $editingCommunity,
            'availableUsers' => $availableUsers,
        ])->title('Communities - WarKom');
    }
}

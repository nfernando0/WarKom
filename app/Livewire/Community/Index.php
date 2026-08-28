<?php

namespace App\Livewire\Community;

use Livewire\Component;
use App\Models\Community;
use App\Models\User;

class Index extends Component
{
    public string $search = '';
    public string $viewMode = 'grid'; // 'grid' or 'table'

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
        'invite_code.required' => 'Kode undangan wajib diisi.',
        'invite_code.min' => 'Kode undangan minimal 3 karakter.',
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'viewMode' => ['except' => 'grid'],
    ];

    public function openJoinModal(): void
    {
        $user = auth()->user();

        if (! empty($user->community_id)) {
            session()->flash('error', 'Anda sudah terdaftar di suatu komunitas. Silakan keluar terlebih dahulu sebelum bergabung dengan komunitas lain.');
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
            $this->addError('invite_code', 'Anda sudah terdaftar di suatu komunitas. Silakan keluar terlebih dahulu.');
            return;
        }

        $community = Community::where('invite_code', trim($this->invite_code))->first();

        if (! $community) {
            $this->addError('invite_code', 'Kode undangan tidak valid atau komunitas tidak ditemukan.');
            return;
        }

        $user->update([
            'community_id' => $community->id,
        ]);

        $this->closeJoinModal();

        session()->flash('success', "Selamat! Anda berhasil bergabung dengan komunitas \"{$community->name}\".");
    }

    public function leaveCommunity(): void
    {
        $user = auth()->user();

        if (! $user || ! $user->community_id) {
            return;
        }

        $communityName = $user->community?->name ?? 'komunitas';

        $user->update([
            'community_id' => null,
        ]);

        session()->flash('success', "Anda telah keluar dari komunitas {$communityName}.");
    }

    public function openEditModal(int $id): void
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $community = Community::findOrFail($id);
        $this->editingCommunityId = $community->id;
        $this->edit_name = $community->name;
        $this->edit_description = $community->description ?? '';
        $this->edit_location = $community->location ?? '';
        $this->edit_invite_code = $community->invite_code;
        $this->add_member_user_id = '';
        $this->add_member_email = '';
        $this->resetErrorBag();
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->editingCommunityId = null;
        $this->reset(['edit_name', 'edit_description', 'edit_location', 'edit_invite_code', 'add_member_user_id', 'add_member_email']);
        $this->resetErrorBag();
    }

    public function update(): void
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $this->validate([
            'edit_name' => 'required|string|min:3|max:255',
            'edit_description' => 'nullable|string|max:1000',
            'edit_location' => 'nullable|string|max:255',
            'edit_invite_code' => 'required|string|min:3|max:50|unique:communities,invite_code,' . $this->editingCommunityId,
        ]);

        $community = Community::findOrFail($this->editingCommunityId);
        $community->update([
            'name' => trim($this->edit_name),
            'description' => trim($this->edit_description),
            'location' => trim($this->edit_location),
            'invite_code' => trim($this->edit_invite_code),
        ]);

        $this->closeEditModal();
        session()->flash('success', "Komunitas \"{$community->name}\" berhasil diperbarui.");
    }

    public function openDeleteModal(int $id): void
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $community = Community::findOrFail($id);
        $this->deletingCommunityId = $community->id;
        $this->deletingCommunityName = $community->name;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deletingCommunityId = null;
        $this->deletingCommunityName = '';
    }

    public function delete(): void
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $community = Community::findOrFail($this->deletingCommunityId);
        $name = $community->name;

        // Reset members community_id
        User::where('community_id', $community->id)->update(['community_id' => null]);
        $community->delete();

        $this->closeDeleteModal();
        session()->flash('success', "Komunitas \"{$name}\" berhasil dihapus.");
    }

    public function openMembersModal(int $id): void
    {
        $this->selectedCommunityId = $id;
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
            abort(403, 'Unauthorized.');
        }

        $member = User::findOrFail($userId);

        if ($member->id === auth()->id()) {
            $this->addError('kick_error', 'Anda tidak dapat mengeluarkan diri sendiri.');
            return;
        }

        if ($member->community_id === $this->selectedCommunityId) {
            $member->update(['community_id' => null]);
            session()->flash('success', "Anggota \"{$member->name}\" telah dikeluarkan dari komunitas.");
        }
    }

    public function render()
    {
        $query = Community::with('creator')
            ->withCount(['members', 'listings'])
            ->latest();

        if (! empty($this->search)) {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('description', 'like', $searchTerm)
                  ->orWhere('location', 'like', $searchTerm);
            });
        }

        $selectedCommunity = $this->selectedCommunityId
            ? Community::with('members')->find($this->selectedCommunityId)
            : null;

        $editingCommunity = $this->editingCommunityId
            ? Community::with('members')->find($this->editingCommunityId)
            : null;

        return view('livewire.community.index', [
            'communities' => $query->get(),
            'currentCommunityId' => auth()->user()?->community_id,
            'selectedCommunity' => $selectedCommunity,
            'editingCommunity' => $editingCommunity,
            'isAdmin' => auth()->user()?->isAdmin() ?? false,
        ])->title('Daftar Komunitas - WarKom');
    }
}

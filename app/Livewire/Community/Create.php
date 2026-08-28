<?php

namespace App\Livewire\Community;

use Livewire\Component;
use App\Models\Community;
use Illuminate\Support\Str;

class Create extends Component
{

    public string $name = '';
    public string $description = '';
    public string $location = '';
    public string $invite_code = '';

    protected array $rules = [
        'name' => 'required|string|min:3',
        'description' => 'required|string|min:3',
        'location' => 'required|string|min:3',
        'invite_code' => 'required|string|min:3|unique:communities,invite_code',
    ];

    protected array $messages = [
        'name.required' => 'Name is required',
        'name.min' => 'Name must be at least 3 characters long',
        'description.required' => 'Description is required',
        'description.min' => 'Description must be at least 3 characters long',
        'location.required' => 'Location is required',
        'location.min' => 'Location must be at least 3 characters long',
        'invite_code.required' => 'Invite code is required',
        'invite_code.min' => 'Invite code must be at least 3 characters long',
        'invite_code.unique' => 'Invite code has already been taken',
    ];

    public function mount(): void
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403, 'Unauthorized action. Only administrators can create communities.');
        }

        $this->invite_code = Str::random(6);
    }

    public function save(): void
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403, 'Unauthorized action. Only administrators can create communities.');
        }

        $validated = $this->validate();

        $community = Community::create($validated);
        auth()->user()->update(['community_id' => $community->id]);

        session()->flash('success', 'Community created successfully.');

        $this->redirectRoute('community.index');
    }

    public function render()
    {
        return view('livewire.community.create')->title('Create Community');
    }
}

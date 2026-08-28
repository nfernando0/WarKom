<div>
    <div class="bg-zinc-200 dark:bg-zinc-800 p-4 rounded-xl border border-zinc-300 dark:border-zinc-700">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item :href="route('dashboard')" wire:navigate>Home</flux:breadcrumbs.item>
            <flux:breadcrumbs.item :href="route('community.index')" wire:navigate>Community</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Create</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </div>

    <div class="mt-4">
        <form wire:submit="save">
            <flux:field class="mt-3">
                <flux:label>Name</flux:label>
                <flux:input wire:model="name" />
                <flux:error name="name" />
            </flux:field>

            <flux:field class="mt-3">
                <flux:label>Description</flux:label>
                <flux:input wire:model="description" />
                <flux:error name="description" />
            </flux:field>

            <flux:field class="mt-3">
                <flux:label>Location</flux:label>
                <flux:input wire:model="location" />
                <flux:error name="location" />
            </flux:field>

            <flux:field class="mt-3">
                <flux:label>Invite Code</flux:label>
                <flux:input wire:model="invite_code" disabled />
                <flux:error name="invite_code" />
            </flux:field>

            <flux:field class="mt-3">
                <flux:button variant="primary" wire:loading.flex wire:target="save">
                    Creating...
                </flux:button>
                <flux:button variant="primary" wire:loading.remove wire:target="save" type="submit">
                    Create Community
                </flux:button>
            </flux:field>
        </form>
    </div>
</div>

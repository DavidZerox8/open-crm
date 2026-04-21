<?php

use App\Actions\CRM\DeleteContact;
use App\Actions\CRM\UpdateContact;
use App\Models\CRM\Contact;
use Flux\Flux;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Contact')] class extends Component {
    use AuthorizesRequests;

    public Contact $contact;

    public bool $showEditModal = false;
    public bool $showDeleteModal = false;

    public string $edit_first_name = '';
    public string $edit_last_name = '';
    public string $edit_job_title = '';
    public string $edit_email = '';
    public string $edit_phone = '';
    public string $edit_mobile = '';
    public string $edit_notes = '';

    public function mount(Contact $contact): void
    {
        $this->contact = $contact->load(['owner', 'company']);

        $this->authorize('view', $this->contact);
    }

    #[Computed]
    public function deals(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->contact->deals()
            ->with(['stage', 'owner'])
            ->orderByDesc('id')
            ->limit(20)
            ->get();
    }

    #[Computed]
    public function activities(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->contact->activities()
            ->with('user')
            ->orderByDesc('occurred_at')
            ->limit(20)
            ->get();
    }

    public function editContact(): void
    {
        $this->authorize('update', $this->contact);

        $this->edit_first_name = $this->contact->first_name;
        $this->edit_last_name = $this->contact->last_name ?? '';
        $this->edit_job_title = $this->contact->job_title ?? '';
        $this->edit_email = $this->contact->email ?? '';
        $this->edit_phone = $this->contact->phone ?? '';
        $this->edit_mobile = $this->contact->mobile ?? '';
        $this->edit_notes = $this->contact->notes ?? '';

        $this->showEditModal = true;
    }

    public function updateContact(UpdateContact $action): void
    {
        $this->authorize('update', $this->contact);

        $validated = $this->validate([
            'edit_first_name' => ['required', 'string', 'max:255'],
            'edit_last_name' => ['nullable', 'string', 'max:255'],
            'edit_job_title' => ['nullable', 'string', 'max:255'],
            'edit_email' => ['nullable', 'email', 'max:255'],
            'edit_phone' => ['nullable', 'string', 'max:50'],
            'edit_mobile' => ['nullable', 'string', 'max:50'],
            'edit_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->contact = $action->execute($this->contact, [
            'first_name' => $validated['edit_first_name'],
            'last_name' => $validated['edit_last_name'] !== '' ? $validated['edit_last_name'] : null,
            'job_title' => $validated['edit_job_title'] !== '' ? $validated['edit_job_title'] : null,
            'email' => $validated['edit_email'] !== '' ? $validated['edit_email'] : null,
            'phone' => $validated['edit_phone'] !== '' ? $validated['edit_phone'] : null,
            'mobile' => $validated['edit_mobile'] !== '' ? $validated['edit_mobile'] : null,
            'notes' => $validated['edit_notes'] !== '' ? $validated['edit_notes'] : null,
        ]);

        $this->showEditModal = false;

        Flux::toast(variant: 'success', text: __('crm.actions.save'));
    }

    public function deleteContact(DeleteContact $action): void
    {
        $this->authorize('delete', $this->contact);

        $action->execute($this->contact);

        $this->redirectRoute('crm.contacts.index', navigate: true);
    }
}; ?>

<div class="w-full">
    <div class="mx-auto flex w-full max-w-7xl flex-col gap-6 p-4 sm:p-6 lg:p-8">
        <x-crm.entity-header
            :title="$contact->fullName()"
            :subtitle="$contact->job_title ?: __('crm.contacts.title')"
            data-tour="contact-header"
        >
            <x-slot:breadcrumbs>
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item icon="home" href="{{ route('crm.dashboard') }}" />
                    <flux:breadcrumbs.item href="{{ route('crm.contacts.index') }}" wire:navigate>{{ __('crm.nav.contacts') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item>{{ $contact->fullName() }}</flux:breadcrumbs.item>
                </flux:breadcrumbs>
            </x-slot:breadcrumbs>
            <x-slot:actions>
                @if ($contact->company)
                    <flux:button :href="route('crm.companies.show', $contact->company)" variant="ghost" wire:navigate>
                        {{ __('crm.labels.company') }}
                    </flux:button>
                @endif
                @can('update', $contact)
                    <flux:button variant="ghost" wire:click="editContact">
                        {{ __('crm.actions.edit') }}
                    </flux:button>
                @endcan
                @can('delete', $contact)
                    <flux:button variant="ghost" class="text-red-500 hover:text-red-600" wire:click="$set('showDeleteModal', true)">
                        {{ __('crm.actions.delete') }}
                    </flux:button>
                @endcan
                <flux:button :href="route('crm.contacts.index')" variant="ghost" wire:navigate>
                    {{ __('crm.nav.contacts') }}
                </flux:button>
            </x-slot:actions>
        </x-crm.entity-header>

        <div class="grid gap-4 xl:grid-cols-3">
            <article class="rounded-xl border border-neutral-200 bg-white p-4 xl:col-span-2 dark:border-neutral-700 dark:bg-zinc-900" data-tour="contact-details">
                <flux:heading size="lg">{{ __('crm.labels.contact') }}</flux:heading>

                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <div>
                        <flux:text size="sm" class="text-zinc-500">{{ __('crm.labels.email') }}</flux:text>
                        <flux:text>{{ $contact->email ?: '—' }}</flux:text>
                    </div>
                    <div>
                        <flux:text size="sm" class="text-zinc-500">{{ __('crm.labels.phone') }}</flux:text>
                        <flux:text>{{ $contact->phone ?: '—' }}</flux:text>
                    </div>
                    <div>
                        <flux:text size="sm" class="text-zinc-500">{{ __('crm.labels.mobile') }}</flux:text>
                        <flux:text>{{ $contact->mobile ?: '—' }}</flux:text>
                    </div>
                    <div>
                        <flux:text size="sm" class="text-zinc-500">{{ __('crm.labels.owner') }}</flux:text>
                        <flux:text>{{ $contact->owner?->name ?: '—' }}</flux:text>
                    </div>
                </div>

                @if ($contact->notes)
                    <div class="mt-4">
                        <flux:text size="sm" class="text-zinc-500">{{ __('crm.labels.notes') }}</flux:text>
                        <flux:text class="mt-1">{{ $contact->notes }}</flux:text>
                    </div>
                @endif
            </article>

            <aside class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-zinc-900" data-tour="contact-deals">
                <flux:heading size="lg">{{ __('crm.deals.title') }}</flux:heading>

                @if ($this->deals->isEmpty())
                    <x-crm.empty-state icon="currency-dollar" :heading="__('crm.deals.create')" class="mt-4 py-8" />
                @else
                    <div class="mt-4 space-y-3">
                        @foreach ($this->deals as $deal)
                            <a href="{{ route('crm.deals.show', $deal) }}" wire:navigate class="block rounded-lg border border-neutral-200 p-3 hover:bg-zinc-50 dark:border-neutral-700 dark:hover:bg-zinc-800/60">
                                <div class="flex items-center justify-between gap-2">
                                    <flux:heading size="sm">{{ $deal->title }}</flux:heading>
                                    <flux:badge :color="$deal->status->color()" size="sm">{{ $deal->status->label() }}</flux:badge>
                                </div>
                                <flux:text size="sm" class="mt-1 text-zinc-500">
                                    {{ number_format((float) $deal->amount, 2, ',', '.') }} {{ $deal->currency }}
                                </flux:text>
                            </a>
                        @endforeach
                    </div>
                @endif
            </aside>
        </div>

        <aside class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-zinc-900">
            <flux:heading size="lg">{{ __('crm.dashboard.recent_activity') }}</flux:heading>
            <div class="mt-4">
                <x-crm.activity-timeline :activities="$this->activities" />
            </div>
        </aside>
    </div>

    <flux:modal wire:model="showEditModal" class="max-w-2xl">
        <div class="space-y-4">
            <flux:heading>{{ __('crm.actions.edit') }}</flux:heading>

            <form wire:submit="updateContact" class="space-y-4">
                <div class="grid gap-3 md:grid-cols-2">
                    <flux:input wire:model="edit_first_name" :label="__('crm.labels.first_name')" required />
                    <flux:input wire:model="edit_last_name" :label="__('crm.labels.last_name')" />
                </div>

                <flux:input wire:model="edit_job_title" :label="__('crm.labels.job_title')" />

                <div class="grid gap-3 md:grid-cols-3">
                    <flux:input wire:model="edit_email" :label="__('crm.labels.email')" type="email" />
                    <flux:input wire:model="edit_phone" :label="__('crm.labels.phone')" />
                    <flux:input wire:model="edit_mobile" :label="__('crm.labels.mobile')" />
                </div>

                <flux:textarea wire:model="edit_notes" :label="__('crm.labels.notes')" rows="3" />

                <div class="flex justify-end gap-2">
                    <flux:button type="button" variant="ghost" wire:click="$set('showEditModal', false)">
                        {{ __('crm.actions.cancel') }}
                    </flux:button>
                    <flux:button type="submit" variant="primary">{{ __('crm.actions.save') }}</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <flux:modal wire:model="showDeleteModal" class="max-w-md">
        <div class="space-y-4">
            <flux:heading>{{ __('crm.actions.delete') }}</flux:heading>

            <flux:text>{{ __('Are you sure you want to delete this contact? This action cannot be undone.') }}</flux:text>

            <div class="flex justify-end gap-2">
                <flux:button type="button" variant="ghost" wire:click="$set('showDeleteModal', false)">
                    {{ __('crm.actions.cancel') }}
                </flux:button>
                <flux:button variant="danger" wire:click="deleteContact">{{ __('crm.actions.delete') }}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>

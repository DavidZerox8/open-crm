<?php

use App\Actions\CRM\CreateContact;
use App\Models\CRM\Company;
use App\Models\CRM\Contact;
use Flux\Flux;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Contacts')] class extends Component {
    use AuthorizesRequests;
    use WithPagination;

    public string $search = '';
    public bool $showCreateModal = false;

    public string $first_name = '';
    public string $last_name = '';
    public ?int $company_id = null;
    public string $job_title = '';
    public string $email = '';
    public string $phone = '';
    public string $mobile = '';
    public string $notes = '';

    public function mount(): void
    {
        $this->authorize('viewAny', Contact::class);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function companies(): \Illuminate\Database\Eloquent\Collection
    {
        return Company::query()
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    #[Computed]
    public function contacts(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Contact::query()
            ->with(['company', 'owner'])
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($inner): void {
                    $inner
                        ->where('first_name', 'like', '%'.$this->search.'%')
                        ->orWhere('last_name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%')
                        ->orWhere('job_title', 'like', '%'.$this->search.'%');
                });
            })
            ->orderByDesc('id')
            ->paginate(12);
    }

    public function createContact(CreateContact $action): void
    {
        $this->authorize('create', Contact::class);

        $validated = $this->validate([
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['nullable', 'string', 'max:120'],
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'job_title' => ['nullable', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:60'],
            'mobile' => ['nullable', 'string', 'max:60'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $action->execute(Auth::user(), [
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'] !== '' ? $validated['last_name'] : null,
            'company_id' => $validated['company_id'],
            'job_title' => $validated['job_title'] !== '' ? $validated['job_title'] : null,
            'email' => $validated['email'] !== '' ? $validated['email'] : null,
            'phone' => $validated['phone'] !== '' ? $validated['phone'] : null,
            'mobile' => $validated['mobile'] !== '' ? $validated['mobile'] : null,
            'notes' => $validated['notes'] !== '' ? $validated['notes'] : null,
        ]);

        $this->reset([
            'first_name',
            'last_name',
            'company_id',
            'job_title',
            'email',
            'phone',
            'mobile',
            'notes',
            'showCreateModal',
        ]);

        Flux::toast(variant: 'success', text: __('crm.contacts.create'));
    }
}; ?>

<div class="w-full">
    <div class="mx-auto flex w-full max-w-7xl flex-col gap-6 p-4 sm:p-6 lg:p-8">
        <x-crm.entity-header :title="__('crm.contacts.title')" :subtitle="__('crm.labels.contact')" data-tour="contacts-header">
            <x-slot:breadcrumbs>
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item icon="home" href="{{ route('crm.dashboard') }}" />
                    <flux:breadcrumbs.item>{{ __('crm.nav.contacts') }}</flux:breadcrumbs.item>
                </flux:breadcrumbs>
            </x-slot:breadcrumbs>
            <x-slot:actions>
                @can('create', \App\Models\CRM\Contact::class)
                    <flux:button variant="primary" wire:click="$set('showCreateModal', true)" data-tour="contacts-create">
                        {{ __('crm.contacts.create') }}
                    </flux:button>
                @endcan
            </x-slot:actions>
        </x-crm.entity-header>

        <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-zinc-900" data-tour="contacts-filters">
            <flux:input wire:model.live.debounce.300ms="search" :label="__('Search')" type="text" placeholder="Nombre, email o cargo" />
        </div>

        @if ($this->contacts->isEmpty())
            <x-crm.empty-state icon="users" :heading="__('crm.contacts.empty')" :subheading="__('crm.contacts.create')" />
        @else
            <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-zinc-900" data-tour="contacts-table">
                <table class="min-w-full divide-y divide-neutral-200 text-sm dark:divide-neutral-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/60">
                        <tr>
                            <th class="px-4 py-3 text-left">{{ __('crm.labels.contact') }}</th>
                            <th class="px-4 py-3 text-left">{{ __('crm.labels.company') }}</th>
                            <th class="px-4 py-3 text-left">{{ __('crm.labels.job_title') }}</th>
                            <th class="px-4 py-3 text-left">{{ __('crm.labels.owner') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                        @foreach ($this->contacts as $contact)
                            <tr class="hover:bg-zinc-50/70 dark:hover:bg-zinc-800/50">
                                <td class="px-4 py-3">
                                    <a href="{{ route('crm.contacts.show', $contact) }}" wire:navigate class="font-medium hover:underline">
                                        {{ $contact->fullName() }}
                                    </a>
                                    @if ($contact->email)
                                        <div class="text-xs text-zinc-500">{{ $contact->email }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3">{{ $contact->company?->name ?: '—' }}</td>
                                <td class="px-4 py-3">{{ $contact->job_title ?: '—' }}</td>
                                <td class="px-4 py-3">{{ $contact->owner?->name ?: '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="border-t border-neutral-200 px-4 py-3 dark:border-neutral-700">
                    {{ $this->contacts->links() }}
                </div>
            </div>
        @endif

        <flux:modal wire:model="showCreateModal" class="max-w-2xl">
            <div class="space-y-4">
                <flux:heading>{{ __('crm.contacts.create') }}</flux:heading>

                <form wire:submit="createContact" class="space-y-4">
                    <div class="grid gap-3 md:grid-cols-2">
                        <flux:input wire:model="first_name" :label="__('crm.labels.first_name')" required />
                        <flux:input wire:model="last_name" :label="__('crm.labels.last_name')" />
                    </div>

                    <div class="grid gap-3 md:grid-cols-2">
                        <flux:field>
                            <flux:label>{{ __('crm.labels.company') }}</flux:label>
                            <flux:select wire:model="company_id">
                                <option value="">—</option>
                                @foreach ($this->companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                                @endforeach
                            </flux:select>
                        </flux:field>
                        <flux:input wire:model="job_title" :label="__('crm.labels.job_title')" />
                    </div>

                    <div class="grid gap-3 md:grid-cols-3">
                        <flux:input wire:model="email" :label="__('crm.labels.email')" type="email" />
                        <flux:input wire:model="phone" :label="__('crm.labels.phone')" />
                        <flux:input wire:model="mobile" :label="__('crm.labels.mobile')" />
                    </div>

                    <flux:textarea wire:model="notes" :label="__('crm.labels.notes')" rows="3" />

                    <div class="flex justify-end gap-2">
                        <flux:button type="button" variant="ghost" wire:click="$set('showCreateModal', false)">
                            {{ __('crm.actions.cancel') }}
                        </flux:button>
                        <flux:button type="submit" variant="primary">{{ __('crm.actions.save') }}</flux:button>
                    </div>
                </form>
            </div>
        </flux:modal>
    </div>
</div>

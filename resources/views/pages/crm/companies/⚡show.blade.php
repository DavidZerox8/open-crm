<?php

use App\Actions\CRM\DeleteCompany;
use App\Actions\CRM\UpdateCompany;
use App\Models\CRM\Company;
use Flux\Flux;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Company')] class extends Component {
    use AuthorizesRequests;

    public Company $company;

    public bool $showEditModal = false;
    public bool $showDeleteModal = false;

    public string $edit_name = '';
    public string $edit_industry = '';
    public string $edit_website = '';
    public string $edit_email = '';
    public string $edit_phone = '';
    public string $edit_notes = '';

    public function mount(Company $company): void
    {
        $this->company = $company->load(['owner']);

        $this->authorize('view', $this->company);
    }

    #[Computed]
    public function contacts(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->company->contacts()
            ->with('owner')
            ->orderBy('first_name')
            ->limit(20)
            ->get();
    }

    #[Computed]
    public function deals(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->company->deals()
            ->with(['owner', 'stage'])
            ->orderByDesc('id')
            ->limit(20)
            ->get();
    }

    #[Computed]
    public function activities(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->company->activities()
            ->with('user')
            ->orderByDesc('occurred_at')
            ->limit(20)
            ->get();
    }

    public function editCompany(): void
    {
        $this->authorize('update', $this->company);

        $this->edit_name = $this->company->name;
        $this->edit_industry = $this->company->industry ?? '';
        $this->edit_website = $this->company->website ?? '';
        $this->edit_email = $this->company->email ?? '';
        $this->edit_phone = $this->company->phone ?? '';
        $this->edit_notes = $this->company->notes ?? '';

        $this->showEditModal = true;
    }

    public function updateCompany(UpdateCompany $action): void
    {
        $this->authorize('update', $this->company);

        $validated = $this->validate([
            'edit_name' => ['required', 'string', 'max:255'],
            'edit_industry' => ['nullable', 'string', 'max:255'],
            'edit_website' => ['nullable', 'url', 'max:255'],
            'edit_email' => ['nullable', 'email', 'max:255'],
            'edit_phone' => ['nullable', 'string', 'max:50'],
            'edit_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->company = $action->execute($this->company, [
            'name' => $validated['edit_name'],
            'industry' => $validated['edit_industry'] !== '' ? $validated['edit_industry'] : null,
            'website' => $validated['edit_website'] !== '' ? $validated['edit_website'] : null,
            'email' => $validated['edit_email'] !== '' ? $validated['edit_email'] : null,
            'phone' => $validated['edit_phone'] !== '' ? $validated['edit_phone'] : null,
            'notes' => $validated['edit_notes'] !== '' ? $validated['edit_notes'] : null,
        ]);

        $this->showEditModal = false;

        Flux::toast(variant: 'success', text: __('crm.actions.save'));
    }

    public function deleteCompany(DeleteCompany $action): void
    {
        $this->authorize('delete', $this->company);

        $action->execute($this->company);

        $this->redirectRoute('crm.companies.index', navigate: true);
    }
}; ?>

<section class="w-full">
    <div class="mx-auto flex w-full max-w-7xl flex-col gap-6 p-4 lg:p-6">
        <x-crm.entity-header
            :title="$company->name"
            :subtitle="$company->industry ?: __('crm.companies.title')"
            data-tour="company-header"
        >
            <x-slot:actions>
                @can('update', $company)
                    <flux:button variant="ghost" wire:click="editCompany">
                        {{ __('crm.actions.edit') }}
                    </flux:button>
                @endcan
                @can('delete', $company)
                    <flux:button variant="ghost" class="text-red-500 hover:text-red-600" wire:click="$set('showDeleteModal', true)">
                        {{ __('crm.actions.delete') }}
                    </flux:button>
                @endcan
                <flux:button :href="route('crm.companies.index')" variant="ghost" wire:navigate>
                    {{ __('crm.nav.companies') }}
                </flux:button>
            </x-slot:actions>
        </x-crm.entity-header>

        <div class="grid gap-4 xl:grid-cols-3">
            <section class="rounded-xl border border-neutral-200 bg-white p-4 xl:col-span-2 dark:border-neutral-700 dark:bg-zinc-900" data-tour="company-details">
                <flux:heading size="lg">{{ __('crm.labels.company') }}</flux:heading>

                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <div>
                        <flux:text size="sm" class="text-zinc-500">{{ __('crm.labels.website') }}</flux:text>
                        <flux:text>{{ $company->website ?: '—' }}</flux:text>
                    </div>
                    <div>
                        <flux:text size="sm" class="text-zinc-500">{{ __('crm.labels.email') }}</flux:text>
                        <flux:text>{{ $company->email ?: '—' }}</flux:text>
                    </div>
                    <div>
                        <flux:text size="sm" class="text-zinc-500">{{ __('crm.labels.phone') }}</flux:text>
                        <flux:text>{{ $company->phone ?: '—' }}</flux:text>
                    </div>
                    <div>
                        <flux:text size="sm" class="text-zinc-500">{{ __('crm.labels.owner') }}</flux:text>
                        <flux:text>{{ $company->owner?->name ?: '—' }}</flux:text>
                    </div>
                </div>

                @if ($company->notes)
                    <div class="mt-4">
                        <flux:text size="sm" class="text-zinc-500">{{ __('crm.labels.notes') }}</flux:text>
                        <flux:text class="mt-1">{{ $company->notes }}</flux:text>
                    </div>
                @endif
            </section>

            <section class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-zinc-900" data-tour="company-contacts">
                <flux:heading size="lg">{{ __('crm.labels.contact') }}</flux:heading>

                @if ($this->contacts->isEmpty())
                    <x-crm.empty-state icon="users" :heading="__('crm.contacts.empty')" class="mt-4 py-8" />
                @else
                    <div class="mt-4 space-y-3">
                        @foreach ($this->contacts as $contact)
                            <a href="{{ route('crm.contacts.show', $contact) }}" wire:navigate class="block rounded-lg border border-neutral-200 p-3 hover:bg-zinc-50 dark:border-neutral-700 dark:hover:bg-zinc-800/60">
                                <flux:heading size="sm">{{ $contact->fullName() }}</flux:heading>
                                <flux:text size="sm" class="text-zinc-500">{{ $contact->email ?: '—' }}</flux:text>
                            </a>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>

        <div class="grid gap-4 xl:grid-cols-5">
            <section class="rounded-xl border border-neutral-200 bg-white p-4 xl:col-span-2 dark:border-neutral-700 dark:bg-zinc-900" data-tour="company-deals">
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
            </section>

            <section class="rounded-xl border border-neutral-200 bg-white p-4 xl:col-span-3 dark:border-neutral-700 dark:bg-zinc-900">
                <flux:heading size="lg">{{ __('crm.dashboard.recent_activity') }}</flux:heading>
                <div class="mt-4">
                    <x-crm.activity-timeline :activities="$this->activities" />
                </div>
            </section>
        </div>
    </div>

    <flux:modal wire:model="showEditModal" class="max-w-2xl">
        <div class="space-y-4">
            <flux:heading>{{ __('crm.actions.edit') }}</flux:heading>

            <form wire:submit="updateCompany" class="space-y-4">
                <div class="grid gap-3 md:grid-cols-2">
                    <flux:input wire:model="edit_name" :label="__('crm.labels.company_name')" required />
                    <flux:input wire:model="edit_industry" :label="__('crm.labels.industry')" />
                </div>

                <div class="grid gap-3 md:grid-cols-2">
                    <flux:input wire:model="edit_email" :label="__('crm.labels.email')" type="email" />
                    <flux:input wire:model="edit_phone" :label="__('crm.labels.phone')" />
                </div>

                <flux:input wire:model="edit_website" :label="__('crm.labels.website')" type="url" />

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

            <flux:text>{{ __('Are you sure you want to delete this company? This action cannot be undone.') }}</flux:text>

            <div class="flex justify-end gap-2">
                <flux:button type="button" variant="ghost" wire:click="$set('showDeleteModal', false)">
                    {{ __('crm.actions.cancel') }}
                </flux:button>
                <flux:button variant="danger" wire:click="deleteCompany">{{ __('crm.actions.delete') }}</flux:button>
            </div>
        </div>
    </flux:modal>
</section>

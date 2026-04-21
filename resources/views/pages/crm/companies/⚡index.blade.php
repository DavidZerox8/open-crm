<?php

use App\Actions\CRM\CreateCompany;
use App\Models\CRM\Company;
use Flux\Flux;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Companies')] class extends Component {
    use AuthorizesRequests;
    use WithPagination;

    public string $search = '';
    public bool $showCreateModal = false;

    public string $name = '';
    public string $industry = '';
    public string $website = '';
    public string $email = '';
    public string $phone = '';
    public string $city = '';
    public string $country = '';
    public string $notes = '';

    public function mount(): void
    {
        $this->authorize('viewAny', Company::class);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function companies(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Company::query()
            ->with('owner')
            ->withCount(['contacts', 'deals'])
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($inner): void {
                    $inner
                        ->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('industry', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            })
            ->orderByDesc('id')
            ->paginate(12);
    }

    public function createCompany(CreateCompany $action): void
    {
        $this->authorize('create', Company::class);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'industry' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:60'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $action->execute(Auth::user(), [
            'name' => $validated['name'],
            'industry' => $validated['industry'] !== '' ? $validated['industry'] : null,
            'website' => $validated['website'] !== '' ? $validated['website'] : null,
            'email' => $validated['email'] !== '' ? $validated['email'] : null,
            'phone' => $validated['phone'] !== '' ? $validated['phone'] : null,
            'city' => $validated['city'] !== '' ? $validated['city'] : null,
            'country' => $validated['country'] !== '' ? $validated['country'] : null,
            'notes' => $validated['notes'] !== '' ? $validated['notes'] : null,
        ]);

        $this->reset(['name', 'industry', 'website', 'email', 'phone', 'city', 'country', 'notes', 'showCreateModal']);

        Flux::toast(variant: 'success', text: __('crm.companies.create'));
    }
}; ?>

<div class="w-full">
    <div class="mx-auto flex w-full max-w-7xl flex-col gap-6 p-4 sm:p-6 lg:p-8">
        <x-crm.entity-header :title="__('crm.companies.title')" :subtitle="__('crm.labels.company')" data-tour="companies-header">
            <x-slot:breadcrumbs>
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item icon="home" href="{{ route('crm.dashboard') }}" />
                    <flux:breadcrumbs.item>{{ __('crm.nav.companies') }}</flux:breadcrumbs.item>
                </flux:breadcrumbs>
            </x-slot:breadcrumbs>
            <x-slot:actions>
                @can('create', \App\Models\CRM\Company::class)
                    <flux:button variant="primary" wire:click="$set('showCreateModal', true)" data-tour="companies-create">
                        {{ __('crm.companies.create') }}
                    </flux:button>
                @endcan
            </x-slot:actions>
        </x-crm.entity-header>

        <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-zinc-900" data-tour="companies-filters">
            <flux:input wire:model.live.debounce.300ms="search" :label="__('Search')" type="text" placeholder="Nombre, industria o email" />
        </div>

        @if ($this->companies->isEmpty())
            <x-crm.empty-state icon="building-office-2" :heading="__('crm.companies.empty')" :subheading="__('crm.companies.create')" />
        @else
            <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-zinc-900" data-tour="companies-table">
                <table class="min-w-full divide-y divide-neutral-200 text-sm dark:divide-neutral-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/60">
                        <tr>
                            <th class="px-4 py-3 text-left">{{ __('crm.labels.name') }}</th>
                            <th class="px-4 py-3 text-left">{{ __('crm.labels.industry') }}</th>
                            <th class="px-4 py-3 text-left">{{ __('crm.labels.contact') }}</th>
                            <th class="px-4 py-3 text-left">{{ __('crm.labels.deal') }}</th>
                            <th class="px-4 py-3 text-left">{{ __('crm.labels.owner') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                        @foreach ($this->companies as $company)
                            <tr class="hover:bg-zinc-50/70 dark:hover:bg-zinc-800/50">
                                <td class="px-4 py-3">
                                    <a href="{{ route('crm.companies.show', $company) }}" wire:navigate class="font-medium hover:underline">
                                        {{ $company->name }}
                                    </a>
                                    @if ($company->email)
                                        <div class="text-xs text-zinc-500">{{ $company->email }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3">{{ $company->industry ?: '—' }}</td>
                                <td class="px-4 py-3">{{ $company->contacts_count }}</td>
                                <td class="px-4 py-3">{{ $company->deals_count }}</td>
                                <td class="px-4 py-3">
                                    @if ($company->owner)
                                        <div class="flex items-center gap-2">
                                            <x-crm.owner-avatar :user="$company->owner" size="xs" />
                                            <span>{{ $company->owner->name }}</span>
                                        </div>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="border-t border-neutral-200 px-4 py-3 dark:border-neutral-700">
                    {{ $this->companies->links() }}
                </div>
            </div>
        @endif

        <flux:modal wire:model="showCreateModal" class="max-w-2xl">
            <div class="space-y-4">
                <flux:heading>{{ __('crm.companies.create') }}</flux:heading>

                <form wire:submit="createCompany" class="space-y-4">
                    <div class="grid gap-3 md:grid-cols-2">
                        <flux:input wire:model="name" :label="__('crm.labels.name')" required />
                        <flux:input wire:model="industry" :label="__('crm.labels.industry')" />
                    </div>

                    <div class="grid gap-3 md:grid-cols-2">
                        <flux:input wire:model="website" :label="__('crm.labels.website')" />
                        <flux:input wire:model="email" :label="__('crm.labels.email')" type="email" />
                    </div>

                    <div class="grid gap-3 md:grid-cols-3">
                        <flux:input wire:model="phone" :label="__('crm.labels.phone')" />
                        <flux:input wire:model="city" :label="__('City')" />
                        <flux:input wire:model="country" :label="__('Country')" />
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

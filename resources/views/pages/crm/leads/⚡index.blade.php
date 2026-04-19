<?php

use App\Actions\CRM\CreateLead;
use App\Enums\LeadStatus;
use App\Models\CRM\Lead;
use Flux\Flux;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Leads')] class extends Component {
    use AuthorizesRequests;
    use WithPagination;

    public string $search = '';
    public string $status = '';
    public bool $showCreateModal = false;

    public string $contact_name = '';
    public string $company_name = '';
    public string $email = '';
    public string $phone = '';
    public string $source = '';
    public string $lead_status = 'new';
    public int $score = 0;
    public string $notes = '';

    public function mount(): void
    {
        $this->authorize('viewAny', Lead::class);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function leads(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Lead::query()
            ->with('owner')
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($inner): void {
                    $inner
                        ->where('contact_name', 'like', '%'.$this->search.'%')
                        ->orWhere('company_name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->orderByDesc('id')
            ->paginate(12);
    }

    public function createLead(CreateLead $action): void
    {
        $this->authorize('create', Lead::class);

        $validated = $this->validate([
            'contact_name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'source' => ['nullable', 'string', 'max:100'],
            'lead_status' => ['required', 'in:'.implode(',', LeadStatus::values())],
            'score' => ['required', 'integer', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $action->execute(Auth::user(), [
            'contact_name' => $validated['contact_name'],
            'company_name' => $validated['company_name'] !== '' ? $validated['company_name'] : null,
            'email' => $validated['email'] !== '' ? $validated['email'] : null,
            'phone' => $validated['phone'] !== '' ? $validated['phone'] : null,
            'source' => $validated['source'] !== '' ? $validated['source'] : null,
            'status' => LeadStatus::from($validated['lead_status']),
            'score' => $validated['score'],
            'notes' => $validated['notes'] !== '' ? $validated['notes'] : null,
        ]);

        $this->reset([
            'contact_name',
            'company_name',
            'email',
            'phone',
            'source',
            'lead_status',
            'score',
            'notes',
            'showCreateModal',
        ]);

        $this->lead_status = LeadStatus::New->value;

        Flux::toast(variant: 'success', text: __('crm.leads.create'));
    }
}; ?>

<section class="w-full">
    <div class="mx-auto flex w-full max-w-7xl flex-col gap-6 p-4 lg:p-6">
        <x-crm.entity-header :title="__('crm.leads.title')" :subtitle="__('crm.labels.status')" data-tour="leads-header">
            <x-slot:actions>
                @can('create', \App\Models\CRM\Lead::class)
                    <flux:button variant="primary" wire:click="$set('showCreateModal', true)" data-tour="leads-create">
                        {{ __('crm.leads.create') }}
                    </flux:button>
                @endcan
            </x-slot:actions>
        </x-crm.entity-header>

        <div class="grid gap-3 rounded-xl border border-neutral-200 bg-white p-4 md:grid-cols-3 dark:border-neutral-700 dark:bg-zinc-900" data-tour="leads-filters">
            <flux:input wire:model.live.debounce.300ms="search" :label="__('Search')" type="text" placeholder="Nombre, empresa o email" />

            <flux:field>
                <flux:label>{{ __('crm.labels.status') }}</flux:label>
                <flux:select wire:model.live="status">
                    <option value="">{{ __('All') }}</option>
                    @foreach (\App\Enums\LeadStatus::cases() as $leadStatus)
                        <option value="{{ $leadStatus->value }}">{{ $leadStatus->label() }}</option>
                    @endforeach
                </flux:select>
            </flux:field>
        </div>

        @if ($this->leads->isEmpty())
            <x-crm.empty-state icon="user-plus" :heading="__('crm.leads.empty')" :subheading="__('crm.leads.create')" />
        @else
            <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-zinc-900" data-tour="leads-table">
                <table class="min-w-full divide-y divide-neutral-200 text-sm dark:divide-neutral-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/60">
                        <tr>
                            <th class="px-4 py-3 text-left">{{ __('crm.labels.contact_name') }}</th>
                            <th class="px-4 py-3 text-left">{{ __('crm.labels.company_name') }}</th>
                            <th class="px-4 py-3 text-left">{{ __('crm.labels.status') }}</th>
                            <th class="px-4 py-3 text-left">{{ __('crm.labels.score') }}</th>
                            <th class="px-4 py-3 text-left">{{ __('crm.labels.owner') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                        @foreach ($this->leads as $lead)
                            <tr class="hover:bg-zinc-50/70 dark:hover:bg-zinc-800/50">
                                <td class="px-4 py-3">
                                    <a href="{{ route('crm.leads.show', $lead) }}" wire:navigate class="font-medium hover:underline">
                                        {{ $lead->contact_name }}
                                    </a>
                                    @if ($lead->email)
                                        <div class="text-xs text-zinc-500">{{ $lead->email }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3">{{ $lead->company_name ?: '—' }}</td>
                                <td class="px-4 py-3">
                                    <flux:badge :color="$lead->status->color()" size="sm">{{ $lead->status->label() }}</flux:badge>
                                </td>
                                <td class="px-4 py-3">{{ $lead->score }}</td>
                                <td class="px-4 py-3">
                                    @if ($lead->owner)
                                        <div class="flex items-center gap-2">
                                            <x-crm.owner-avatar :user="$lead->owner" size="xs" />
                                            <span>{{ $lead->owner->name }}</span>
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
                    {{ $this->leads->links() }}
                </div>
            </div>
        @endif

        <flux:modal wire:model="showCreateModal" class="max-w-2xl">
            <div class="space-y-4">
                <flux:heading>{{ __('crm.leads.create') }}</flux:heading>

                <form wire:submit="createLead" class="space-y-4">
                    <div class="grid gap-3 md:grid-cols-2">
                        <flux:input wire:model="contact_name" :label="__('crm.labels.contact_name')" required />
                        <flux:input wire:model="company_name" :label="__('crm.labels.company_name')" />
                    </div>

                    <div class="grid gap-3 md:grid-cols-2">
                        <flux:input wire:model="email" :label="__('crm.labels.email')" type="email" />
                        <flux:input wire:model="phone" :label="__('crm.labels.phone')" />
                    </div>

                    <div class="grid gap-3 md:grid-cols-3">
                        <flux:input wire:model="source" :label="__('crm.labels.source')" />
                        <flux:field>
                            <flux:label>{{ __('crm.labels.status') }}</flux:label>
                            <flux:select wire:model="lead_status">
                                @foreach (\App\Enums\LeadStatus::cases() as $leadStatus)
                                    <option value="{{ $leadStatus->value }}">{{ $leadStatus->label() }}</option>
                                @endforeach
                            </flux:select>
                        </flux:field>
                        <flux:input wire:model="score" :label="__('crm.labels.score')" type="number" min="0" max="100" />
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
</section>

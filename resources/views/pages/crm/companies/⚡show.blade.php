<?php

use App\Actions\CRM\CreateDeal;
use App\Actions\CRM\CreateTask;
use App\Actions\CRM\DeleteCompany;
use App\Actions\CRM\UpdateCompany;
use App\Models\CRM\Company;
use App\Models\CRM\Pipeline;
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
    public bool $showCreateDealModal = false;
    public bool $showCreateTaskModal = false;

    public string $edit_name = '';
    public string $edit_industry = '';
    public string $edit_website = '';
    public string $edit_email = '';
    public string $edit_phone = '';
    public string $edit_notes = '';

    public string $deal_title = '';
    public string $deal_amount = '';
    public string $deal_currency = 'EUR';
    public ?int $deal_pipeline_id = null;
    public ?int $deal_stage_id = null;
    public ?int $deal_contact_id = null;

    public string $task_title = '';
    public string $task_description = '';
    public ?string $task_due_at = null;
    public string $task_priority = 'medium';

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
    public function pipelines(): \Illuminate\Database\Eloquent\Collection
    {
        return Pipeline::query()
            ->with(['stages' => function ($query): void {
                $query->orderBy('position');
            }])
            ->orderByDesc('is_default')
            ->orderBy('position')
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

    public function createDeal(CreateDeal $action): void
    {
        $this->authorize('create', \App\Models\CRM\Deal::class);

        $validated = $this->validate([
            'deal_title' => ['required', 'string', 'max:255'],
            'deal_amount' => ['nullable', 'numeric', 'min:0'],
            'deal_currency' => ['required', 'string', 'max:3'],
            'deal_pipeline_id' => ['required', 'integer', 'exists:pipelines,id'],
            'deal_stage_id' => ['required', 'integer', 'exists:pipeline_stages,id'],
            'deal_contact_id' => ['nullable', 'integer', 'exists:contacts,id'],
        ]);

        $action->execute(auth()->user(), [
            'pipeline_id' => $validated['deal_pipeline_id'],
            'stage_id' => $validated['deal_stage_id'],
            'company_id' => $this->company->id,
            'contact_id' => $validated['deal_contact_id'],
            'title' => $validated['deal_title'],
            'amount' => $validated['deal_amount'] !== '' ? $validated['deal_amount'] : 0,
            'currency' => $validated['deal_currency'],
        ]);

        $this->reset([
            'deal_title', 'deal_amount', 'deal_currency', 'deal_pipeline_id', 
            'deal_stage_id', 'deal_contact_id', 'showCreateDealModal'
        ]);
        $this->deal_currency = 'EUR';

        Flux::toast(variant: 'success', text: __('crm.deals.create'));
    }

    public function createTask(CreateTask $action): void
    {
        $this->authorize('create', \App\Models\CRM\Task::class);

        $validated = $this->validate([
            'task_title' => ['required', 'string', 'max:255'],
            'task_description' => ['nullable', 'string', 'max:2000'],
            'task_due_at' => ['nullable', 'date'],
            'task_priority' => ['required', 'string'],
        ]);

        $action->execute(auth()->user(), [
            'title' => $validated['task_title'],
            'description' => $validated['task_description'] !== '' ? $validated['task_description'] : null,
            'due_at' => $validated['task_due_at'] !== '' ? $validated['task_due_at'] : null,
            'priority' => \App\Enums\TaskPriority::from($validated['task_priority']),
            'assigned_to' => auth()->user()->id,
        ], $this->company);

        $this->reset([
            'task_title', 'task_description', 'task_due_at', 'task_priority', 'showCreateTaskModal'
        ]);
        $this->task_priority = 'medium';

        Flux::toast(variant: 'success', text: __('crm.tasks.create'));
    }
}; ?>

<div class="w-full">
    <div class="mx-auto flex w-full max-w-7xl flex-col gap-6 p-4 sm:p-6 lg:p-8">
        <x-crm.entity-header
            :title="$company->name"
            :subtitle="$company->industry ?: __('crm.companies.title')"
            data-tour="company-header"
        >
            <x-slot:breadcrumbs>
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item icon="home" href="{{ route('crm.dashboard') }}" />
                    <flux:breadcrumbs.item href="{{ route('crm.companies.index') }}" wire:navigate>{{ __('crm.nav.companies') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item>{{ $company->name }}</flux:breadcrumbs.item>
                </flux:breadcrumbs>
            </x-slot:breadcrumbs>
            <x-slot:actions>
                @can('create', \App\Models\CRM\Task::class)
                    <flux:button variant="ghost" wire:click="$set('showCreateTaskModal', true)">
                        {{ __('crm.tasks.create') }}
                    </flux:button>
                @endcan
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
            <article class="space-y-4 rounded-xl border border-neutral-200 bg-white p-4 xl:col-span-2 dark:border-neutral-700 dark:bg-zinc-900" data-tour="company-details">
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
            </article>

            <aside class="space-y-4 rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-zinc-900" data-tour="company-contacts">
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
            </aside>
        </div>

        <div class="grid gap-4 xl:grid-cols-3">
            <aside class="rounded-xl border border-neutral-200 bg-white p-4 xl:col-span-2 dark:border-neutral-700 dark:bg-zinc-900" data-tour="company-deals">
                <div class="mb-3 flex items-center justify-between gap-2">
                    <flux:heading size="lg">{{ __('crm.deals.title') }}</flux:heading>
                    @can('create', \App\Models\CRM\Deal::class)
                        <flux:button size="sm" variant="ghost" icon="plus" class="h-8 w-8 !p-0" wire:click="$set('showCreateDealModal', true)" />
                    @endcan
                </div>

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

            <aside class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-zinc-900">
                <flux:heading size="lg">{{ __('crm.dashboard.recent_activity') }}</flux:heading>
                <div class="mt-4">
                    <x-crm.activity-timeline :activities="$this->activities" />
                </div>
            </aside>
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

    <flux:modal wire:model="showCreateDealModal" class="max-w-2xl">
        <div class="space-y-4">
            <flux:heading>{{ __('crm.deals.create') }}</flux:heading>

            <form wire:submit="createDeal" class="space-y-4">
                <flux:input wire:model="deal_title" :label="__('crm.labels.title')" required />

                <div class="grid gap-3 md:grid-cols-2">
                    <flux:input wire:model="deal_amount" :label="__('crm.labels.amount')" type="number" step="0.01" min="0" />
                    <flux:input wire:model="deal_currency" :label="__('crm.labels.currency')" required />
                </div>

                <div class="grid gap-3 md:grid-cols-2">
                    <flux:field>
                        <flux:label>{{ __('crm.labels.pipeline') }}</flux:label>
                        <flux:select wire:model.live="deal_pipeline_id" required>
                            <option value="">—</option>
                            @foreach ($this->pipelines as $pipeline)
                                <option value="{{ $pipeline->id }}">{{ $pipeline->name }}</option>
                            @endforeach
                        </flux:select>
                    </flux:field>
                    
                    <flux:field>
                        <flux:label>{{ __('crm.labels.stage') }}</flux:label>
                        <flux:select wire:model="deal_stage_id" required>
                            <option value="">—</option>
                            @if($deal_pipeline_id)
                                @foreach ($this->pipelines->find($deal_pipeline_id)?->stages ?? [] as $stage)
                                    <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                                @endforeach
                            @endif
                        </flux:select>
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>{{ __('crm.labels.contact') }}</flux:label>
                    <flux:select wire:model="deal_contact_id">
                        <option value="">—</option>
                        @foreach ($this->contacts as $contact)
                            <option value="{{ $contact->id }}">{{ $contact->fullName() }}</option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <div class="flex justify-end gap-2">
                    <flux:button type="button" variant="ghost" wire:click="$set('showCreateDealModal', false)">
                        {{ __('crm.actions.cancel') }}
                    </flux:button>
                    <flux:button type="submit" variant="primary">{{ __('crm.actions.save') }}</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <flux:modal wire:model="showCreateTaskModal" class="max-w-2xl">
        <div class="space-y-4">
            <flux:heading>{{ __('crm.tasks.create') }}</flux:heading>

            <form wire:submit="createTask" class="space-y-4">
                <flux:input wire:model="task_title" :label="__('crm.labels.title')" required />

                <div class="grid gap-3 md:grid-cols-2">
                    <flux:input wire:model="task_due_at" :label="__('crm.labels.due_at')" type="datetime-local" />
                    <flux:field>
                        <flux:label>{{ __('crm.labels.priority') }}</flux:label>
                        <flux:select wire:model="task_priority" required>
                            @foreach (\App\Enums\TaskPriority::cases() as $priority)
                                <option value="{{ $priority->value }}">{{ $priority->label() }}</option>
                            @endforeach
                        </flux:select>
                    </flux:field>
                </div>

                <flux:textarea wire:model="task_description" :label="__('crm.labels.description')" rows="3" />

                <div class="flex justify-end gap-2">
                    <flux:button type="button" variant="ghost" wire:click="$set('showCreateTaskModal', false)">
                        {{ __('crm.actions.cancel') }}
                    </flux:button>
                    <flux:button type="submit" variant="primary">{{ __('crm.actions.save') }}</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>

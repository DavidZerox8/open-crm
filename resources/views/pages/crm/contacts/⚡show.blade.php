<?php

use App\Actions\CRM\CreateDeal;
use App\Actions\CRM\CreateTask;
use App\Actions\CRM\DeleteContact;
use App\Actions\CRM\UpdateContact;
use App\Models\CRM\Company;
use App\Models\CRM\Contact;
use App\Models\CRM\Pipeline;
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
    public bool $showCreateDealModal = false;
    public bool $showCreateTaskModal = false;

    public string $edit_first_name = '';
    public string $edit_last_name = '';
    public string $edit_job_title = '';
    public string $edit_email = '';
    public string $edit_phone = '';
    public string $edit_mobile = '';
    public string $edit_notes = '';

    public string $deal_title = '';
    public string $deal_amount = '';
    public string $deal_currency = 'EUR';
    public ?int $deal_pipeline_id = null;
    public ?int $deal_stage_id = null;
    public ?int $deal_company_id = null;

    public string $task_title = '';
    public string $task_description = '';
    public ?string $task_due_at = null;
    public string $task_priority = 'medium';

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

    #[Computed]
    public function pipelines(): \Illuminate\Database\Eloquent\Collection
    {
        return Pipeline::query()
            ->with(['stages' => function ($query) {
                $query->orderBy('position');
            }])
            ->orderByDesc('is_default')
            ->orderBy('position')
            ->get();
    }

    #[Computed]
    public function companies(): \Illuminate\Database\Eloquent\Collection
    {
        return Company::query()
            ->orderBy('name')
            ->get(['id', 'name']);
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

    public function createDeal(CreateDeal $action): void
    {
        $this->authorize('create', \App\Models\CRM\Deal::class);

        $validated = $this->validate([
            'deal_title' => ['required', 'string', 'max:255'],
            'deal_amount' => ['nullable', 'numeric', 'min:0'],
            'deal_currency' => ['required', 'string', 'max:3'],
            'deal_pipeline_id' => ['required', 'integer', 'exists:pipelines,id'],
            'deal_stage_id' => ['required', 'integer', 'exists:pipeline_stages,id'],
            'deal_company_id' => ['nullable', 'integer', 'exists:companies,id'],
        ]);

        $action->execute(auth()->user(), [
            'pipeline_id' => $validated['deal_pipeline_id'],
            'stage_id' => $validated['deal_stage_id'],
            'company_id' => $validated['deal_company_id'] ?: $this->contact->company_id,
            'contact_id' => $this->contact->id,
            'title' => $validated['deal_title'],
            'amount' => $validated['deal_amount'] !== '' ? $validated['deal_amount'] : 0,
            'currency' => $validated['deal_currency'],
        ]);

        $this->reset([
            'deal_title', 'deal_amount', 'deal_currency', 'deal_pipeline_id', 
            'deal_stage_id', 'deal_company_id', 'showCreateDealModal'
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
        ], $this->contact);

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
                @can('create', \App\Models\CRM\Task::class)
                    <flux:button variant="ghost" wire:click="$set('showCreateTaskModal', true)">
                        {{ __('crm.tasks.create') }}
                    </flux:button>
                @endcan
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
                    <flux:label>{{ __('crm.labels.company') }}</flux:label>
                    <flux:select wire:model="deal_company_id">
                        <option value="">— {{ $contact->company ? __('Defaults to: :company', ['company' => $contact->company->name]) : '' }} —</option>
                        @foreach ($this->companies as $company)
                            <option value="{{ $company->id }}">{{ $company->name }}</option>
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

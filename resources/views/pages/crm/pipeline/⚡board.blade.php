<?php

use App\Actions\CRM\CreateDeal;
use App\Actions\CRM\MoveDealStage;
use App\Models\CRM\Company;
use App\Models\CRM\Contact;
use App\Models\CRM\Deal;
use App\Models\CRM\Pipeline;
use App\Models\CRM\PipelineStage;
use Flux\Flux;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Pipeline')] class extends Component {
    use AuthorizesRequests;

    public ?int $pipeline_id = null;
    public bool $showCreateModal = false;
    public string $deal_title = '';
    public string $deal_amount = '';
    public string $deal_currency = 'EUR';
    public ?int $deal_stage_id = null;
    public ?int $deal_company_id = null;
    public ?int $deal_contact_id = null;

    public function mount(): void
    {
        $this->authorize('viewAny', Pipeline::class);

        $this->pipeline_id = Pipeline::query()
            ->orderByDesc('is_default')
            ->orderBy('position')
            ->value('id');
    }

    #[Computed]
    public function pipelines(): \Illuminate\Database\Eloquent\Collection
    {
        return Pipeline::query()
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

    #[Computed]
    public function contacts(): \Illuminate\Database\Eloquent\Collection
    {
        return Contact::query()
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name']);
    }

    #[Computed]
    public function selectedPipeline(): ?Pipeline
    {
        if (! $this->pipeline_id) {
            return null;
        }

        return Pipeline::query()
            ->with(['stages' => function ($query): void {
                $query
                    ->orderBy('position')
                    ->with(['deals' => function ($dealQuery): void {
                        $dealQuery
                            ->with(['owner', 'company'])
                            ->orderByDesc('amount');
                    }]);
            }])
            ->find($this->pipeline_id);
    }

    public function moveDeal(int $dealId, int $stageId, MoveDealStage $action): void
    {
        $deal = Deal::query()->findOrFail($dealId);

        $this->authorize('move', $deal);

        $stage = PipelineStage::query()
            ->where('pipeline_id', $deal->pipeline_id)
            ->findOrFail($stageId);

        $action->execute($deal, $stage);

        Flux::toast(text: __('crm.deals.move_stage'));
    }

    public function createDeal(CreateDeal $action): void
    {
        $this->authorize('create', Deal::class);

        $validated = $this->validate([
            'deal_title' => ['required', 'string', 'max:255'],
            'deal_amount' => ['nullable', 'numeric', 'min:0'],
            'deal_currency' => ['required', 'string', 'max:3'],
            'deal_stage_id' => ['required', 'integer', 'exists:pipeline_stages,id'],
            'deal_company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'deal_contact_id' => ['nullable', 'integer', 'exists:contacts,id'],
        ]);

        $action->execute(Auth::user(), [
            'pipeline_id' => $this->pipeline_id,
            'stage_id' => $validated['deal_stage_id'],
            'company_id' => $validated['deal_company_id'],
            'contact_id' => $validated['deal_contact_id'],
            'title' => $validated['deal_title'],
            'amount' => $validated['deal_amount'] !== '' ? $validated['deal_amount'] : 0,
            'currency' => $validated['deal_currency'],
        ]);

        $this->reset([
            'deal_title', 'deal_amount', 'deal_currency', 'deal_stage_id', 
            'deal_company_id', 'deal_contact_id', 'showCreateModal'
        ]);
        $this->deal_currency = 'EUR';

        Flux::toast(variant: 'success', text: __('crm.deals.create'));
    }
}; ?>

<div class="w-full">
    <div class="mx-auto flex w-full max-w-[1600px] flex-col gap-6 p-4 sm:p-6 lg:p-8">
        <x-crm.entity-header :title="__('crm.pipeline.title')" :subtitle="__('crm.deals.title')" data-tour="pipeline-header">
            <x-slot:breadcrumbs>
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item icon="home" href="{{ route('crm.dashboard') }}" />
                    <flux:breadcrumbs.item>{{ __('crm.nav.pipeline') }}</flux:breadcrumbs.item>
                </flux:breadcrumbs>
            </x-slot:breadcrumbs>
            <x-slot:actions>
                @can('create', \App\Models\CRM\Deal::class)
                    <flux:button variant="primary" wire:click="$set('showCreateModal', true)" data-tour="pipeline-create-deal">
                        {{ __('crm.deals.create') }}
                    </flux:button>
                @endcan
            </x-slot:actions>
        </x-crm.entity-header>

        <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-zinc-900" data-tour="pipeline-selector">
            <flux:field>
                <flux:label>{{ __('crm.labels.pipeline') }}</flux:label>
                <flux:select wire:model.live="pipeline_id">
                    @foreach ($this->pipelines as $pipeline)
                        <option value="{{ $pipeline->id }}">{{ $pipeline->name }}</option>
                    @endforeach
                </flux:select>
            </flux:field>
        </div>

        @if (! $this->selectedPipeline)
            <x-crm.empty-state icon="view-columns" :heading="__('crm.pipeline.title')" />
        @else
            <div class="grid gap-4 lg:grid-cols-4" data-tour="pipeline-board">
                @foreach ($this->selectedPipeline->stages as $stage)
                    <section class="rounded-xl border border-neutral-200 bg-white p-3 dark:border-neutral-700 dark:bg-zinc-900">
                        <div class="mb-3 flex items-center justify-between gap-2">
                            <div>
                                <flux:heading size="sm">{{ $stage->name }}</flux:heading>
                                <flux:text size="xs" class="text-zinc-500">{{ $stage->deals->count() }} {{ __('crm.deals.title') }}</flux:text>
                            </div>
                            <flux:badge size="sm" color="zinc">
                                {{ number_format((float) $stage->deals->sum('amount'), 0, ',', '.') }}
                            </flux:badge>
                        </div>

                        <div class="space-y-3">
                            @forelse ($stage->deals as $deal)
                                <div class="space-y-2 rounded-lg border border-neutral-200 p-3 dark:border-neutral-700">
                                    <x-crm.kanban-card :deal="$deal" />

                                    @can('move', $deal)
                                        <flux:field>
                                            <flux:label>{{ __('crm.deals.move_stage') }}</flux:label>
                                            <flux:select wire:change="moveDeal({{ $deal->id }}, $event.target.value)">
                                                @foreach ($this->selectedPipeline->stages as $targetStage)
                                                    <option value="{{ $targetStage->id }}" @selected($targetStage->id === $deal->stage_id)>
                                                        {{ $targetStage->name }}
                                                    </option>
                                                @endforeach
                                            </flux:select>
                                        </flux:field>
                                    @endcan
                                </div>
                            @empty
                                <div class="rounded-lg border border-dashed border-neutral-300 px-3 py-6 text-center text-sm text-zinc-500 dark:border-neutral-600">
                                    {{ __('crm.deals.create') }}
                                </div>
                            @endforelse
                        </div>
                    </section>
                @endforeach
            </div>
        @endif

        <flux:modal wire:model="showCreateModal" class="max-w-2xl">
            <div class="space-y-4">
                <flux:heading>{{ __('crm.deals.create') }}</flux:heading>

                <form wire:submit="createDeal" class="space-y-4">
                    <flux:input wire:model="deal_title" :label="__('crm.labels.title')" required />

                    <div class="grid gap-3 md:grid-cols-2">
                        <flux:input wire:model="deal_amount" :label="__('crm.labels.amount')" type="number" step="0.01" min="0" />
                        <flux:input wire:model="deal_currency" :label="__('crm.labels.currency')" required />
                    </div>

                    @if ($this->selectedPipeline)
                        <flux:field>
                            <flux:label>{{ __('crm.labels.stage') }}</flux:label>
                            <flux:select wire:model="deal_stage_id">
                                <option value="">—</option>
                                @foreach ($this->selectedPipeline->stages as $stage)
                                    <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                                @endforeach
                            </flux:select>
                        </flux:field>
                    @endif

                    <div class="grid gap-3 md:grid-cols-2">
                        <flux:field>
                            <flux:label>{{ __('crm.labels.company') }}</flux:label>
                            <flux:select wire:model="deal_company_id">
                                <option value="">—</option>
                                @foreach ($this->companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                                @endforeach
                            </flux:select>
                        </flux:field>
                        
                        <flux:field>
                            <flux:label>{{ __('crm.labels.contact') }}</flux:label>
                            <flux:select wire:model="deal_contact_id">
                                <option value="">—</option>
                                @foreach ($this->contacts as $contact)
                                    <option value="{{ $contact->id }}">{{ $contact->fullName() }}</option>
                                @endforeach
                            </flux:select>
                        </flux:field>
                    </div>

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

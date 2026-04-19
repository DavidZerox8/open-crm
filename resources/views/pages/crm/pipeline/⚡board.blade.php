<?php

use App\Actions\CRM\MoveDealStage;
use App\Models\CRM\Deal;
use App\Models\CRM\Pipeline;
use App\Models\CRM\PipelineStage;
use Flux\Flux;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Pipeline')] class extends Component {
    use AuthorizesRequests;

    public ?int $pipeline_id = null;

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
}; ?>

<section class="w-full">
    <div class="mx-auto flex w-full max-w-[1600px] flex-col gap-6 p-4 lg:p-6">
        <x-crm.entity-header :title="__('crm.pipeline.title')" :subtitle="__('crm.deals.title')" />

        <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-zinc-900">
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
            <div class="grid gap-4 lg:grid-cols-4">
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
    </div>
</section>

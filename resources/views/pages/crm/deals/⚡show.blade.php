<?php

use App\Actions\CRM\CloseDeal;
use App\Actions\CRM\LogActivity;
use App\Actions\CRM\MoveDealStage;
use App\Enums\ActivityType;
use App\Enums\DealStatus;
use App\Models\CRM\Deal;
use App\Models\CRM\PipelineStage;
use Flux\Flux;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Deal')] class extends Component {
    use AuthorizesRequests;

    public Deal $deal;

    public ?int $stage_id = null;
    public string $lost_reason = '';

    public string $activity_type = 'note';
    public string $activity_title = '';
    public string $activity_body = '';

    public function mount(Deal $deal): void
    {
        $this->deal = $deal->load(['owner', 'company', 'contact', 'stage.pipeline']);

        $this->authorize('view', $this->deal);

        $this->stage_id = $this->deal->stage_id;
    }

    #[Computed]
    public function activities(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->deal->activities()
            ->with('user')
            ->orderByDesc('occurred_at')
            ->limit(20)
            ->get();
    }

    #[Computed]
    public function tasks(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->deal->tasks()
            ->with('assignee')
            ->orderBy('due_at')
            ->limit(20)
            ->get();
    }

    #[Computed]
    public function pipelineStages(): \Illuminate\Database\Eloquent\Collection
    {
        return PipelineStage::query()
            ->where('pipeline_id', $this->deal->pipeline_id)
            ->orderBy('position')
            ->get();
    }

    public function moveStage(MoveDealStage $action): void
    {
        $this->authorize('move', $this->deal);

        $validated = $this->validate([
            'stage_id' => ['required', 'integer', 'exists:pipeline_stages,id'],
        ]);

        $stage = PipelineStage::query()
            ->where('pipeline_id', $this->deal->pipeline_id)
            ->findOrFail($validated['stage_id']);

        $this->deal = $action->execute($this->deal, $stage)
            ->load(['owner', 'company', 'contact', 'stage.pipeline']);

        Flux::toast(text: __('crm.deals.move_stage'));
    }

    public function closeWon(CloseDeal $action): void
    {
        $this->authorize('close', $this->deal);

        $this->deal = $action->execute($this->deal, DealStatus::Won)
            ->load(['owner', 'company', 'contact', 'stage.pipeline']);

        Flux::toast(variant: 'success', text: __('crm.actions.close_won'));
    }

    public function closeLost(CloseDeal $action): void
    {
        $this->authorize('close', $this->deal);

        $validated = $this->validate([
            'lost_reason' => ['required', 'string', 'max:255'],
        ]);

        $this->deal = $action->execute($this->deal, DealStatus::Lost, $validated['lost_reason'])
            ->load(['owner', 'company', 'contact', 'stage.pipeline']);

        $this->lost_reason = '';

        Flux::toast(text: __('crm.actions.close_lost'));
    }

    public function createActivity(LogActivity $action): void
    {
        abort_unless(Auth::user()?->can('activities.create'), 403);

        $validated = $this->validate([
            'activity_type' => ['required', 'in:'.implode(',', ActivityType::values())],
            'activity_title' => ['required', 'string', 'max:255'],
            'activity_body' => ['nullable', 'string', 'max:2000'],
        ]);

        $action->execute(Auth::user(), $this->deal, [
            'type' => ActivityType::from($validated['activity_type']),
            'title' => $validated['activity_title'],
            'body' => $validated['activity_body'] !== '' ? $validated['activity_body'] : null,
        ]);

        $this->reset(['activity_type', 'activity_title', 'activity_body']);
        $this->activity_type = ActivityType::Note->value;

        Flux::toast(text: __('crm.actions.log_activity'));
    }
}; ?>

<section class="w-full">
    <div class="mx-auto flex w-full max-w-7xl flex-col gap-6 p-4 lg:p-6">
        <x-crm.entity-header
            :title="$deal->title"
            :subtitle="$deal->company?->name ?: __('crm.deals.title')"
            :badge="$deal->status->label()"
            :badge-color="$deal->status->color()"
        >
            <x-slot:actions>
                <flux:button :href="route('crm.pipeline.board')" variant="ghost" wire:navigate>
                    {{ __('crm.nav.pipeline') }}
                </flux:button>
                <flux:button :href="route('crm.companies.index')" variant="ghost" wire:navigate>
                    {{ __('crm.nav.companies') }}
                </flux:button>
            </x-slot:actions>
        </x-crm.entity-header>

        <div class="grid gap-4 xl:grid-cols-3">
            <section class="space-y-4 rounded-xl border border-neutral-200 bg-white p-4 xl:col-span-2 dark:border-neutral-700 dark:bg-zinc-900">
                <flux:heading size="lg">{{ __('crm.labels.deal') }}</flux:heading>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <flux:text size="sm" class="text-zinc-500">{{ __('crm.labels.amount') }}</flux:text>
                        <flux:text>{{ number_format((float) $deal->amount, 2, ',', '.') }} {{ $deal->currency }}</flux:text>
                    </div>
                    <div>
                        <flux:text size="sm" class="text-zinc-500">{{ __('crm.labels.owner') }}</flux:text>
                        <flux:text>{{ $deal->owner?->name ?: '—' }}</flux:text>
                    </div>
                    <div>
                        <flux:text size="sm" class="text-zinc-500">{{ __('crm.labels.company') }}</flux:text>
                        @if ($deal->company)
                            <a href="{{ route('crm.companies.show', $deal->company) }}" wire:navigate class="hover:underline">{{ $deal->company->name }}</a>
                        @else
                            <flux:text>—</flux:text>
                        @endif
                    </div>
                    <div>
                        <flux:text size="sm" class="text-zinc-500">{{ __('crm.labels.contact') }}</flux:text>
                        @if ($deal->contact)
                            <a href="{{ route('crm.contacts.show', $deal->contact) }}" wire:navigate class="hover:underline">{{ $deal->contact->fullName() }}</a>
                        @else
                            <flux:text>—</flux:text>
                        @endif
                    </div>
                </div>

                @if ($deal->description)
                    <div>
                        <flux:text size="sm" class="text-zinc-500">{{ __('crm.labels.description') }}</flux:text>
                        <flux:text class="mt-1">{{ $deal->description }}</flux:text>
                    </div>
                @endif

                @if ($deal->status === \App\Enums\DealStatus::Open)
                    <div class="space-y-3 rounded-lg border border-neutral-200 p-3 dark:border-neutral-700">
                        <flux:heading size="sm">{{ __('crm.deals.move_stage') }}</flux:heading>

                        <form wire:submit="moveStage" class="flex flex-wrap items-end gap-3">
                            <flux:field class="min-w-52 flex-1">
                                <flux:label>{{ __('crm.labels.stage') }}</flux:label>
                                <flux:select wire:model="stage_id">
                                    @foreach ($this->pipelineStages as $stage)
                                        <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                                    @endforeach
                                </flux:select>
                            </flux:field>
                            <flux:button type="submit" variant="primary">{{ __('crm.actions.save') }}</flux:button>
                        </form>

                        <div class="flex flex-wrap gap-2">
                            @can('close', $deal)
                                <flux:button wire:click="closeWon" variant="filled" color="emerald">
                                    {{ __('crm.actions.close_won') }}
                                </flux:button>
                            @endcan
                        </div>

                        @can('close', $deal)
                            <form wire:submit="closeLost" class="space-y-2">
                                <flux:input wire:model="lost_reason" :label="__('crm.deals.lost_reason')" required />
                                <flux:button type="submit" variant="ghost" color="rose">
                                    {{ __('crm.actions.close_lost') }}
                                </flux:button>
                            </form>
                        @endcan
                    </div>
                @endif
            </section>

            <section class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-zinc-900">
                <flux:heading size="lg">{{ __('crm.dashboard.upcoming_tasks') }}</flux:heading>

                @if ($this->tasks->isEmpty())
                    <x-crm.empty-state icon="clipboard-document" :heading="__('crm.tasks.title')" :subheading="__('crm.tasks.create')" class="mt-4 py-8" />
                @else
                    <div class="mt-4 space-y-3">
                        @foreach ($this->tasks as $task)
                            <div class="rounded-lg border border-neutral-200 p-3 dark:border-neutral-700">
                                <div class="flex items-center justify-between gap-2">
                                    <flux:heading size="sm" class="truncate">{{ $task->title }}</flux:heading>
                                    <flux:badge :color="$task->status->color()" size="sm">{{ $task->status->label() }}</flux:badge>
                                </div>
                                @if ($task->due_at)
                                    <flux:text size="sm" class="mt-1 text-zinc-500">{{ $task->due_at->format('d/m/Y H:i') }}</flux:text>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>

        <div class="grid gap-4 xl:grid-cols-3">
            <section class="rounded-xl border border-neutral-200 bg-white p-4 xl:col-span-2 dark:border-neutral-700 dark:bg-zinc-900">
                <flux:heading size="lg">{{ __('crm.dashboard.recent_activity') }}</flux:heading>
                <div class="mt-4">
                    <x-crm.activity-timeline :activities="$this->activities" />
                </div>
            </section>

            @if (auth()->user()->can('activities.create'))
                <section class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-zinc-900">
                    <flux:heading size="lg">{{ __('crm.actions.log_activity') }}</flux:heading>

                    <form wire:submit="createActivity" class="mt-4 space-y-3">
                        <flux:field>
                            <flux:label>{{ __('crm.labels.activity') }}</flux:label>
                            <flux:select wire:model="activity_type">
                                @foreach (\App\Enums\ActivityType::cases() as $type)
                                    <option value="{{ $type->value }}">{{ $type->label() }}</option>
                                @endforeach
                            </flux:select>
                        </flux:field>

                        <flux:input wire:model="activity_title" :label="__('crm.labels.title')" required />
                        <flux:textarea wire:model="activity_body" :label="__('crm.labels.description')" rows="4" />

                        <div class="flex justify-end">
                            <flux:button type="submit" variant="primary">{{ __('crm.actions.save') }}</flux:button>
                        </div>
                    </form>
                </section>
            @endif
        </div>
    </div>
</section>

<?php

use App\Enums\DealStatus;
use App\Enums\TaskStatus;
use App\Models\CRM\Activity;
use App\Models\CRM\Deal;
use App\Models\CRM\Task;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('CRM Dashboard')] class extends Component {
    public function mount(): void
    {
        abort_unless(Auth::user()?->canAny(['leads.view', 'deals.view', 'tasks.view', 'reports.view']), 403);
    }

    #[Computed]
    public function openDeals(): int
    {
        return Deal::query()
            ->where('status', DealStatus::Open)
            ->count();
    }

    #[Computed]
    public function wonDealsThisMonth(): int
    {
        return Deal::query()
            ->where('status', DealStatus::Won)
            ->whereBetween('closed_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();
    }

    #[Computed]
    public function pipelineValue(): float
    {
        return (float) Deal::query()
            ->where('status', DealStatus::Open)
            ->sum('amount');
    }

    #[Computed]
    public function dueTasks(): int
    {
        return Task::query()
            ->where('status', TaskStatus::Pending)
            ->whereNotNull('due_at')
            ->count();
    }

    #[Computed]
    public function recentActivities(): \Illuminate\Database\Eloquent\Collection
    {
        return Activity::query()
            ->with('user')
            ->orderByDesc('occurred_at')
            ->limit(8)
            ->get();
    }

    #[Computed]
    public function upcomingTasks(): \Illuminate\Database\Eloquent\Collection
    {
        return Task::query()
            ->with('assignee')
            ->where('status', TaskStatus::Pending)
            ->whereNotNull('due_at')
            ->orderBy('due_at')
            ->limit(8)
            ->get();
    }
}; ?>

<section class="w-full">
    <div class="mx-auto flex w-full max-w-7xl flex-col gap-6 p-4 lg:p-6">
        <x-crm.entity-header
            :title="__('crm.dashboard.title')"
            :subtitle="__('crm.dashboard.recent_activity')"
            data-tour="dashboard-header"
        >
            <x-slot:actions>
                <flux:button :href="route('crm.leads.index')" variant="primary" wire:navigate>
                    {{ __('crm.leads.create') }}
                </flux:button>
            </x-slot:actions>
        </x-crm.entity-header>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" data-tour="dashboard-kpis">
            <x-crm.stat-card
                :label="__('crm.dashboard.kpi_open_deals')"
                :value="$this->openDeals"
                icon="currency-dollar"
            />
            <x-crm.stat-card
                :label="__('crm.dashboard.kpi_won_deals_month')"
                :value="$this->wonDealsThisMonth"
                icon="trophy"
            />
            <x-crm.stat-card
                :label="__('crm.dashboard.kpi_pipeline_value')"
                :value="number_format($this->pipelineValue, 2, ',', '.') . ' EUR'"
                icon="chart-bar"
            />
            <x-crm.stat-card
                :label="__('crm.dashboard.kpi_tasks_due')"
                :value="$this->dueTasks"
                icon="clipboard-document-check"
            />
        </div>

        <div class="grid gap-4 xl:grid-cols-5">
            <section class="rounded-xl border border-neutral-200 bg-white p-4 xl:col-span-3 dark:border-neutral-700 dark:bg-zinc-900" data-tour="dashboard-activity">
                <div class="mb-4 flex items-center justify-between">
                    <flux:heading size="lg">{{ __('crm.dashboard.recent_activity') }}</flux:heading>
                    <flux:button :href="route('crm.reports.index')" variant="ghost" size="sm" wire:navigate>
                        {{ __('crm.nav.reports') }}
                    </flux:button>
                </div>

                <x-crm.activity-timeline :activities="$this->recentActivities" />
            </section>

            <section class="rounded-xl border border-neutral-200 bg-white p-4 xl:col-span-2 dark:border-neutral-700 dark:bg-zinc-900" data-tour="dashboard-tasks">
                <div class="mb-4 flex items-center justify-between">
                    <flux:heading size="lg">{{ __('crm.dashboard.upcoming_tasks') }}</flux:heading>
                    <flux:button :href="route('crm.tasks.index')" variant="ghost" size="sm" wire:navigate>
                        {{ __('crm.nav.tasks') }}
                    </flux:button>
                </div>

                @if ($this->upcomingTasks->isEmpty())
                    <x-crm.empty-state
                        icon="clipboard-document"
                        :heading="__('crm.tasks.title')"
                        :subheading="__('crm.tasks.create')"
                    />
                @else
                    <div class="space-y-3">
                        @foreach ($this->upcomingTasks as $task)
                            <div class="rounded-lg border border-neutral-200 px-3 py-2 dark:border-neutral-700">
                                <div class="flex items-center justify-between gap-2">
                                    <flux:heading size="sm" class="truncate">{{ $task->title }}</flux:heading>
                                    <flux:badge :color="$task->priority->color()" size="sm">{{ $task->priority->label() }}</flux:badge>
                                </div>
                                <div class="mt-1 flex items-center justify-between gap-2">
                                    <flux:text size="sm" class="text-zinc-500">
                                        {{ $task->due_at?->format('d/m/Y H:i') }}
                                    </flux:text>
                                    @if ($task->assignee)
                                        <x-crm.owner-avatar :user="$task->assignee" size="xs" />
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>
    </div>
</section>

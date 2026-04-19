<?php

use App\Enums\DealStatus;
use App\Models\CRM\Activity;
use App\Models\CRM\Deal;
use App\Models\CRM\Lead;
use App\Models\CRM\PipelineStage;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Reports')] class extends Component {
    public function mount(): void
    {
        abort_unless(Auth::user()?->can('reports.view'), 403);
    }

    #[Computed]
    public function totalLeads(): int
    {
        return Lead::query()->count();
    }

    #[Computed]
    public function convertedLeads(): int
    {
        return Lead::query()->whereNotNull('converted_at')->count();
    }

    #[Computed]
    public function conversionRate(): float
    {
        if ($this->totalLeads === 0) {
            return 0;
        }

        return round(($this->convertedLeads / $this->totalLeads) * 100, 1);
    }

    #[Computed]
    public function wonDealsValue(): float
    {
        return (float) Deal::query()->where('status', DealStatus::Won)->sum('amount');
    }

    #[Computed]
    public function openDealsValue(): float
    {
        return (float) Deal::query()->where('status', DealStatus::Open)->sum('amount');
    }

    #[Computed]
    public function stages(): \Illuminate\Database\Eloquent\Collection
    {
        return PipelineStage::query()
            ->with('pipeline')
            ->withCount('deals')
            ->withSum('deals', 'amount')
            ->orderBy('position')
            ->get();
    }

    #[Computed]
    public function activityLeaders(): \Illuminate\Support\Collection
    {
        $rows = Activity::query()
            ->selectRaw('user_id, COUNT(*) as total')
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $users = \App\Models\User::query()
            ->whereIn('id', $rows->pluck('user_id'))
            ->get(['id', 'name'])
            ->keyBy('id');

        return $rows->map(function ($row) use ($users): array {
            return [
                'name' => $users->get($row->user_id)?->name ?? 'N/A',
                'total' => (int) $row->total,
            ];
        });
    }
}; ?>

<section class="w-full">
    <div class="mx-auto flex w-full max-w-7xl flex-col gap-6 p-4 lg:p-6">
        <x-crm.entity-header :title="__('crm.reports.title')" :subtitle="__('crm.dashboard.title')" />

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-crm.stat-card :label="__('crm.reports.kpi_conversion_rate')" :value="$this->conversionRate . '%'" icon="arrows-right-left" />
            <x-crm.stat-card :label="__('crm.reports.kpi_won_value')" :value="number_format($this->wonDealsValue, 2, ',', '.') . ' EUR'" icon="trophy" />
            <x-crm.stat-card :label="__('crm.reports.kpi_open_value')" :value="number_format($this->openDealsValue, 2, ',', '.') . ' EUR'" icon="chart-bar" />
            <x-crm.stat-card :label="__('crm.reports.kpi_total_leads')" :value="$this->totalLeads" icon="user-plus" />
        </div>

        <div class="grid gap-4 xl:grid-cols-5">
            <section class="rounded-xl border border-neutral-200 bg-white p-4 xl:col-span-3 dark:border-neutral-700 dark:bg-zinc-900">
                <flux:heading size="lg">{{ __('crm.reports.funnel_by_stage') }}</flux:heading>

                @if ($this->stages->isEmpty())
                    <x-crm.empty-state icon="chart-bar" :heading="__('crm.pipeline.title')" class="mt-4 py-8" />
                @else
                    <div class="mt-4 overflow-hidden rounded-lg border border-neutral-200 dark:border-neutral-700">
                        <table class="min-w-full divide-y divide-neutral-200 text-sm dark:divide-neutral-700">
                            <thead class="bg-zinc-50 dark:bg-zinc-800/60">
                                <tr>
                                    <th class="px-4 py-3 text-left">{{ __('crm.labels.pipeline') }}</th>
                                    <th class="px-4 py-3 text-left">{{ __('crm.labels.stage') }}</th>
                                    <th class="px-4 py-3 text-left">{{ __('crm.deals.title') }}</th>
                                    <th class="px-4 py-3 text-left">{{ __('crm.labels.amount') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                                @foreach ($this->stages as $stage)
                                    <tr>
                                        <td class="px-4 py-3">{{ $stage->pipeline?->name ?: '—' }}</td>
                                        <td class="px-4 py-3">{{ $stage->name }}</td>
                                        <td class="px-4 py-3">{{ $stage->deals_count }}</td>
                                        <td class="px-4 py-3">{{ number_format((float) $stage->deals_sum_amount, 2, ',', '.') }} EUR</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            <section class="rounded-xl border border-neutral-200 bg-white p-4 xl:col-span-2 dark:border-neutral-700 dark:bg-zinc-900">
                <flux:heading size="lg">{{ __('crm.reports.activity_by_user') }}</flux:heading>

                @if ($this->activityLeaders->isEmpty())
                    <x-crm.empty-state icon="clock" :heading="__('crm.dashboard.recent_activity')" class="mt-4 py-8" />
                @else
                    <div class="mt-4 space-y-3">
                        @foreach ($this->activityLeaders as $row)
                            <div class="flex items-center justify-between rounded-lg border border-neutral-200 px-3 py-2 dark:border-neutral-700">
                                <flux:text>{{ $row['name'] }}</flux:text>
                                <flux:badge color="zinc" size="sm">{{ $row['total'] }}</flux:badge>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>
    </div>
</section>

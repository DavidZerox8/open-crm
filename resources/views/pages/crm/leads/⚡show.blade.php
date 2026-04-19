<?php

use App\Actions\CRM\ConvertLead;
use App\Actions\CRM\LogActivity;
use App\Enums\ActivityType;
use App\Models\CRM\Lead;
use Flux\Flux;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Lead')] class extends Component {
    use AuthorizesRequests;

    public Lead $lead;

    public string $activity_type = 'note';
    public string $activity_title = '';
    public string $activity_body = '';

    public function mount(Lead $lead): void
    {
        $this->lead = $lead->load(['owner', 'convertedCompany', 'convertedContact', 'convertedDeal']);

        $this->authorize('view', $this->lead);
    }

    #[Computed]
    public function activities(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->lead->activities()
            ->with('user')
            ->orderByDesc('occurred_at')
            ->limit(20)
            ->get();
    }

    #[Computed]
    public function tasks(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->lead->tasks()
            ->with('assignee')
            ->orderBy('due_at')
            ->limit(20)
            ->get();
    }

    public function convert(ConvertLead $action): void
    {
        $this->authorize('convert', $this->lead);

        $this->lead = $action->execute(Auth::user(), $this->lead);

        Flux::toast(variant: 'success', text: __('crm.actions.convert'));
    }

    public function createActivity(LogActivity $action): void
    {
        abort_unless(Auth::user()?->can('activities.create'), 403);

        $validated = $this->validate([
            'activity_type' => ['required', 'in:'.implode(',', ActivityType::values())],
            'activity_title' => ['required', 'string', 'max:255'],
            'activity_body' => ['nullable', 'string', 'max:2000'],
        ]);

        $action->execute(Auth::user(), $this->lead, [
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
            :title="$lead->contact_name"
            :subtitle="$lead->company_name ?: __('crm.leads.title')"
            :badge="$lead->status->label()"
            :badge-color="$lead->status->color()"
            data-tour="lead-header"
        >
            <x-slot:actions>
                @can('convert', $lead)
                    <flux:button variant="primary" wire:click="convert" data-tour="lead-convert">
                        {{ __('crm.actions.convert') }}
                    </flux:button>
                @endcan
                <flux:button :href="route('crm.leads.index')" variant="ghost" wire:navigate>
                    {{ __('crm.nav.leads') }}
                </flux:button>
            </x-slot:actions>
        </x-crm.entity-header>

        <div class="grid gap-4 xl:grid-cols-3">
            <section class="space-y-4 rounded-xl border border-neutral-200 bg-white p-4 xl:col-span-2 dark:border-neutral-700 dark:bg-zinc-900" data-tour="lead-details">
                <flux:heading size="lg">{{ __('crm.labels.contact') }}</flux:heading>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <flux:text size="sm" class="text-zinc-500">{{ __('crm.labels.email') }}</flux:text>
                        <flux:text>{{ $lead->email ?: '—' }}</flux:text>
                    </div>
                    <div>
                        <flux:text size="sm" class="text-zinc-500">{{ __('crm.labels.phone') }}</flux:text>
                        <flux:text>{{ $lead->phone ?: '—' }}</flux:text>
                    </div>
                    <div>
                        <flux:text size="sm" class="text-zinc-500">{{ __('crm.labels.source') }}</flux:text>
                        <flux:text>{{ $lead->source ?: '—' }}</flux:text>
                    </div>
                    <div>
                        <flux:text size="sm" class="text-zinc-500">{{ __('crm.labels.score') }}</flux:text>
                        <flux:text>{{ $lead->score }}</flux:text>
                    </div>
                </div>

                @if ($lead->notes)
                    <div>
                        <flux:text size="sm" class="text-zinc-500">{{ __('crm.labels.notes') }}</flux:text>
                        <flux:text class="mt-1">{{ $lead->notes }}</flux:text>
                    </div>
                @endif

                @if ($lead->converted_at)
                    <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-3 dark:border-emerald-500/40 dark:bg-emerald-500/10">
                        <flux:text size="sm" class="text-emerald-700 dark:text-emerald-300">
                            {{ __('crm.leads.converted_at') }}: {{ $lead->converted_at->format('d/m/Y H:i') }}
                        </flux:text>

                        <div class="mt-2 flex flex-wrap gap-2">
                            @if ($lead->converted_company_id)
                                <flux:button :href="route('crm.companies.show', $lead->converted_company_id)" size="sm" variant="ghost" wire:navigate>
                                    {{ __('crm.labels.company') }}
                                </flux:button>
                            @endif
                            @if ($lead->converted_contact_id)
                                <flux:button :href="route('crm.contacts.show', $lead->converted_contact_id)" size="sm" variant="ghost" wire:navigate>
                                    {{ __('crm.labels.contact') }}
                                </flux:button>
                            @endif
                            @if ($lead->converted_deal_id)
                                <flux:button :href="route('crm.deals.show', $lead->converted_deal_id)" size="sm" variant="ghost" wire:navigate>
                                    {{ __('crm.labels.deal') }}
                                </flux:button>
                            @endif
                        </div>
                    </div>
                @endif
            </section>

            <section class="space-y-4 rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-zinc-900">
                <flux:heading size="lg">{{ __('crm.dashboard.upcoming_tasks') }}</flux:heading>

                @if ($this->tasks->isEmpty())
                    <x-crm.empty-state icon="clipboard-document" :heading="__('crm.tasks.title')" :subheading="__('crm.tasks.create')" class="py-8" />
                @else
                    <div class="space-y-3">
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
                <div class="mb-4 flex items-center justify-between">
                    <flux:heading size="lg">{{ __('crm.dashboard.recent_activity') }}</flux:heading>
                </div>

                <x-crm.activity-timeline :activities="$this->activities" />
            </section>

            @if (auth()->user()->can('activities.create'))
                <section class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-zinc-900" data-tour="lead-activity-form">
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

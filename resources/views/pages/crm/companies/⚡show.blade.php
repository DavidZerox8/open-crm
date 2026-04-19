<?php

use App\Models\CRM\Company;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Company')] class extends Component {
    use AuthorizesRequests;

    public Company $company;

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
    public function activities(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->company->activities()
            ->with('user')
            ->orderByDesc('occurred_at')
            ->limit(20)
            ->get();
    }
}; ?>

<section class="w-full">
    <div class="mx-auto flex w-full max-w-7xl flex-col gap-6 p-4 lg:p-6">
        <x-crm.entity-header
            :title="$company->name"
            :subtitle="$company->industry ?: __('crm.companies.title')"
            data-tour="company-header"
        >
            <x-slot:actions>
                <flux:button :href="route('crm.companies.index')" variant="ghost" wire:navigate>
                    {{ __('crm.nav.companies') }}
                </flux:button>
            </x-slot:actions>
        </x-crm.entity-header>

        <div class="grid gap-4 xl:grid-cols-3">
            <section class="rounded-xl border border-neutral-200 bg-white p-4 xl:col-span-2 dark:border-neutral-700 dark:bg-zinc-900" data-tour="company-details">
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
            </section>

            <section class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-zinc-900" data-tour="company-contacts">
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
            </section>
        </div>

        <div class="grid gap-4 xl:grid-cols-5">
            <section class="rounded-xl border border-neutral-200 bg-white p-4 xl:col-span-2 dark:border-neutral-700 dark:bg-zinc-900" data-tour="company-deals">
                <flux:heading size="lg">{{ __('crm.deals.title') }}</flux:heading>

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
            </section>

            <section class="rounded-xl border border-neutral-200 bg-white p-4 xl:col-span-3 dark:border-neutral-700 dark:bg-zinc-900">
                <flux:heading size="lg">{{ __('crm.dashboard.recent_activity') }}</flux:heading>
                <div class="mt-4">
                    <x-crm.activity-timeline :activities="$this->activities" />
                </div>
            </section>
        </div>
    </div>
</section>

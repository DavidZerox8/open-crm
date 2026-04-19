<?php

use App\Models\CRM\Contact;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Contact')] class extends Component {
    use AuthorizesRequests;

    public Contact $contact;

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
}; ?>

<section class="w-full">
    <div class="mx-auto flex w-full max-w-7xl flex-col gap-6 p-4 lg:p-6">
        <x-crm.entity-header
            :title="$contact->fullName()"
            :subtitle="$contact->job_title ?: __('crm.contacts.title')"
        >
            <x-slot:actions>
                @if ($contact->company)
                    <flux:button :href="route('crm.companies.show', $contact->company)" variant="ghost" wire:navigate>
                        {{ __('crm.labels.company') }}
                    </flux:button>
                @endif
                <flux:button :href="route('crm.contacts.index')" variant="ghost" wire:navigate>
                    {{ __('crm.nav.contacts') }}
                </flux:button>
            </x-slot:actions>
        </x-crm.entity-header>

        <div class="grid gap-4 xl:grid-cols-3">
            <section class="rounded-xl border border-neutral-200 bg-white p-4 xl:col-span-2 dark:border-neutral-700 dark:bg-zinc-900">
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
            </section>

            <section class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-zinc-900">
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
        </div>

        <section class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-zinc-900">
            <flux:heading size="lg">{{ __('crm.dashboard.recent_activity') }}</flux:heading>
            <div class="mt-4">
                <x-crm.activity-timeline :activities="$this->activities" />
            </div>
        </section>
    </div>
</section>

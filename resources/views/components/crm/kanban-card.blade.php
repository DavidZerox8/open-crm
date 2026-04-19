@props([
    'deal' => null,
])

<div
    data-deal-id="{{ $deal->id }}"
    class="group cursor-grab rounded-lg border border-neutral-200 bg-white p-3 shadow-xs transition hover:shadow-md active:cursor-grabbing dark:border-neutral-700 dark:bg-zinc-800"
>
    <div class="flex items-start justify-between gap-2">
        <a href="{{ route('crm.deals.show', $deal) }}" wire:navigate class="min-w-0 flex-1">
            <flux:heading size="sm" class="truncate">{{ $deal->title }}</flux:heading>
        </a>
        <x-crm.owner-avatar :user="$deal->owner" size="xs" />
    </div>

    <div class="mt-2 flex items-center justify-between">
        <flux:text size="sm" class="font-semibold text-zinc-900 dark:text-white">
            {{ number_format((float) $deal->amount, 0, ',', '.') }} {{ $deal->currency }}
        </flux:text>
        <flux:text size="xs" class="text-zinc-500">{{ $deal->probability }}%</flux:text>
    </div>

    @if ($deal->company)
        <flux:text size="xs" class="mt-1 truncate text-zinc-500">{{ $deal->company->name }}</flux:text>
    @endif

    @if ($deal->expected_close_date)
        <div class="mt-2 flex items-center gap-1 text-zinc-400">
            <flux:icon name="calendar" class="size-3" />
            <flux:text size="xs">{{ $deal->expected_close_date->format('d M') }}</flux:text>
        </div>
    @endif
</div>

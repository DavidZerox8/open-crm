@props([
    'label' => '',
    'value' => '',
    'icon' => null,
    'hint' => null,
])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-neutral-200 bg-white p-5 shadow-xs dark:border-neutral-700 dark:bg-zinc-900']) }}>
    <div class="flex items-center justify-between">
        <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ $label }}</flux:text>
        @if ($icon)
            <flux:icon :name="$icon" class="size-4 text-zinc-400" />
        @endif
    </div>
    <div class="mt-2">
        <flux:heading size="xl" class="font-semibold text-zinc-900 dark:text-white">{{ $value }}</flux:heading>
        @if ($hint)
            <flux:text size="sm" class="mt-1 text-zinc-500 dark:text-zinc-400">{{ $hint }}</flux:text>
        @endif
    </div>
</div>

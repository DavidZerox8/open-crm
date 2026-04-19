@props([
    'icon' => 'inbox',
    'heading' => '',
    'subheading' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center gap-3 rounded-xl border border-dashed border-neutral-300 bg-white px-6 py-16 text-center dark:border-neutral-700 dark:bg-zinc-900']) }}>
    <div class="flex size-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
        <flux:icon :name="$icon" class="size-6 text-zinc-500" />
    </div>
    <flux:heading size="lg">{{ $heading }}</flux:heading>
    @if ($subheading)
        <flux:text class="max-w-md text-zinc-500 dark:text-zinc-400">{{ $subheading }}</flux:text>
    @endif
    @isset($action)
        <div class="mt-2">{{ $action }}</div>
    @endisset
</div>

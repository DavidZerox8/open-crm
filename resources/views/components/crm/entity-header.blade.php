@props([
    'title' => '',
    'subtitle' => null,
    'badge' => null,
    'badgeColor' => 'zinc',
])

@php($showTutorialLauncher = request()->routeIs('crm.*'))

<header {{ $attributes->merge(['class' => 'flex flex-wrap items-start justify-between gap-4 border-b border-neutral-200 pb-5 dark:border-neutral-700']) }}>
    <div class="min-w-0">
        <div class="flex items-center gap-3">
            <flux:heading size="xl" class="truncate">{{ $title }}</flux:heading>
            @if ($badge)
                <flux:badge :color="$badgeColor" size="sm">{{ $badge }}</flux:badge>
            @endif
        </div>
        @if ($subtitle)
            <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">{{ $subtitle }}</flux:text>
        @endif
    </div>

    @if (isset($actions) || $showTutorialLauncher)
        <div class="flex flex-wrap items-center gap-2">
            @isset($actions)
                {{ $actions }}
            @endisset

            @if ($showTutorialLauncher)
                <flux:button
                    type="button"
                    variant="ghost"
                    size="sm"
                    icon="sparkles"
                    data-tour-launcher
                    data-tour="crm-tour-launcher"
                >
                    {{ __('crm.nav.tutorial') }}
                </flux:button>
            @endif
        </div>
    @endif
</header>

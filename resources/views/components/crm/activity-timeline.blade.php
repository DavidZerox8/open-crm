@props([
    'activities' => collect(),
])

<ol class="relative space-y-6 border-s border-neutral-200 ps-5 dark:border-neutral-700">
    @forelse ($activities as $activity)
        <li class="relative">
            <span class="absolute -start-[29px] top-1 flex size-5 items-center justify-center rounded-full bg-{{ $activity->type->color() }}-100 ring-2 ring-white dark:bg-{{ $activity->type->color() }}-500/20 dark:ring-zinc-900">
                <flux:icon :name="$activity->type->icon()" class="size-3 text-{{ $activity->type->color() }}-600 dark:text-{{ $activity->type->color() }}-300" />
            </span>
            <div class="flex items-center gap-2">
                <flux:badge :color="$activity->type->color()" size="sm">{{ $activity->type->label() }}</flux:badge>
                <flux:text size="sm" class="text-zinc-500">{{ $activity->occurred_at?->diffForHumans() }}</flux:text>
            </div>
            <flux:heading size="sm" class="mt-1">{{ $activity->title }}</flux:heading>
            @if ($activity->body)
                <flux:text size="sm" class="mt-1 text-zinc-600 dark:text-zinc-400">{{ $activity->body }}</flux:text>
            @endif
            @if ($activity->user)
                <flux:text size="xs" class="mt-1 text-zinc-400">{{ $activity->user->name }}</flux:text>
            @endif
        </li>
    @empty
        <li class="-ms-5 list-none">
            <flux:text class="text-zinc-500">{{ __('crm.leads.empty') }}</flux:text>
        </li>
    @endforelse
</ol>

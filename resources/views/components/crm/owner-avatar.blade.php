@props([
    'user' => null,
    'size' => 'sm',
])

@if ($user)
    <flux:avatar
        :name="$user->name"
        :initials="\Illuminate\Support\Str::of($user->name)->explode(' ')->take(2)->map(fn ($p) => \Illuminate\Support\Str::substr($p, 0, 1))->implode('')"
        :size="$size"
    />
@else
    <flux:avatar icon="user" :size="$size" />
@endif

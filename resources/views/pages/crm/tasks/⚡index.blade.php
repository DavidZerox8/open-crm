<?php

use App\Actions\CRM\CompleteTask;
use App\Actions\CRM\CreateTask;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\CRM\Task;
use App\Models\User;
use Flux\Flux;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Tasks')] class extends Component {
    use AuthorizesRequests;
    use WithPagination;

    public string $search = '';
    public string $status = '';
    public string $priority = '';
    public bool $showCreateModal = false;

    public string $title = '';
    public string $description = '';
    public string $task_priority = 'medium';
    public ?int $assigned_to = null;
    public string $due_at = '';

    public function mount(): void
    {
        $this->authorize('viewAny', Task::class);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedPriority(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function users(): \Illuminate\Database\Eloquent\Collection
    {
        $currentAccountId = Auth::user()?->current_account_id;

        return User::query()
            ->whereHas('accounts', fn ($query) => $query->where('accounts.id', $currentAccountId))
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    #[Computed]
    public function tasks(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Task::query()
            ->with(['assignee', 'subject'])
            ->when($this->search !== '', fn ($query) => $query->where('title', 'like', '%'.$this->search.'%'))
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->when($this->priority !== '', fn ($query) => $query->where('priority', $this->priority))
            ->orderByRaw('CASE WHEN due_at IS NULL THEN 1 ELSE 0 END')
            ->orderBy('due_at')
            ->orderByDesc('id')
            ->paginate(15);
    }

    public function createTask(CreateTask $action): void
    {
        $this->authorize('create', Task::class);

        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'task_priority' => ['required', 'in:'.implode(',', TaskPriority::values())],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'due_at' => ['nullable', 'date'],
        ]);

        $action->execute(Auth::user(), [
            'title' => $validated['title'],
            'description' => $validated['description'] !== '' ? $validated['description'] : null,
            'priority' => TaskPriority::from($validated['task_priority']),
            'status' => TaskStatus::Pending,
            'assigned_to' => $validated['assigned_to'],
            'due_at' => $validated['due_at'] !== '' ? Carbon::parse($validated['due_at']) : null,
        ]);

        $this->reset(['title', 'description', 'task_priority', 'assigned_to', 'due_at', 'showCreateModal']);
        $this->task_priority = TaskPriority::Medium->value;

        Flux::toast(variant: 'success', text: __('crm.tasks.create'));
    }

    public function completeTask(int $taskId, CompleteTask $action): void
    {
        $task = Task::query()->findOrFail($taskId);

        $this->authorize('complete', $task);

        $action->execute($task);

        Flux::toast(text: __('crm.actions.complete'));
    }
}; ?>

<section class="w-full">
    <div class="mx-auto flex w-full max-w-7xl flex-col gap-6 p-4 lg:p-6">
        <x-crm.entity-header :title="__('crm.tasks.title')" :subtitle="__('crm.labels.task')">
            <x-slot:actions>
                @can('create', \App\Models\CRM\Task::class)
                    <flux:button variant="primary" wire:click="$set('showCreateModal', true)">
                        {{ __('crm.tasks.create') }}
                    </flux:button>
                @endcan
            </x-slot:actions>
        </x-crm.entity-header>

        <div class="grid gap-3 rounded-xl border border-neutral-200 bg-white p-4 md:grid-cols-3 dark:border-neutral-700 dark:bg-zinc-900">
            <flux:input wire:model.live.debounce.300ms="search" :label="__('Search')" type="text" placeholder="Título" />

            <flux:field>
                <flux:label>{{ __('crm.labels.status') }}</flux:label>
                <flux:select wire:model.live="status">
                    <option value="">{{ __('All') }}</option>
                    @foreach (\App\Enums\TaskStatus::cases() as $taskStatus)
                        <option value="{{ $taskStatus->value }}">{{ $taskStatus->label() }}</option>
                    @endforeach
                </flux:select>
            </flux:field>

            <flux:field>
                <flux:label>{{ __('crm.labels.priority') }}</flux:label>
                <flux:select wire:model.live="priority">
                    <option value="">{{ __('All') }}</option>
                    @foreach (\App\Enums\TaskPriority::cases() as $taskPriority)
                        <option value="{{ $taskPriority->value }}">{{ $taskPriority->label() }}</option>
                    @endforeach
                </flux:select>
            </flux:field>
        </div>

        @if ($this->tasks->isEmpty())
            <x-crm.empty-state icon="clipboard-document-check" :heading="__('crm.tasks.empty')" :subheading="__('crm.tasks.create')" />
        @else
            <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-zinc-900">
                <table class="min-w-full divide-y divide-neutral-200 text-sm dark:divide-neutral-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/60">
                        <tr>
                            <th class="px-4 py-3 text-left">{{ __('crm.labels.task') }}</th>
                            <th class="px-4 py-3 text-left">{{ __('crm.labels.status') }}</th>
                            <th class="px-4 py-3 text-left">{{ __('crm.labels.priority') }}</th>
                            <th class="px-4 py-3 text-left">{{ __('crm.labels.assignee') }}</th>
                            <th class="px-4 py-3 text-left">{{ __('crm.labels.due_at') }}</th>
                            <th class="px-4 py-3 text-left"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                        @foreach ($this->tasks as $task)
                            <tr class="hover:bg-zinc-50/70 dark:hover:bg-zinc-800/50">
                                <td class="px-4 py-3">
                                    <div class="font-medium">{{ $task->title }}</div>
                                    @if ($task->subject)
                                        <div class="text-xs text-zinc-500">{{ class_basename($task->subject_type) }} #{{ $task->subject_id }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <flux:badge :color="$task->status->color()" size="sm">{{ $task->status->label() }}</flux:badge>
                                </td>
                                <td class="px-4 py-3">
                                    <flux:badge :color="$task->priority->color()" size="sm">{{ $task->priority->label() }}</flux:badge>
                                </td>
                                <td class="px-4 py-3">{{ $task->assignee?->name ?: '—' }}</td>
                                <td class="px-4 py-3">{{ $task->due_at?->format('d/m/Y H:i') ?: '—' }}</td>
                                <td class="px-4 py-3 text-right">
                                    @if ($task->status === \App\Enums\TaskStatus::Pending)
                                        @can('complete', $task)
                                            <flux:button size="sm" variant="ghost" wire:click="completeTask({{ $task->id }})">
                                                {{ __('crm.actions.complete') }}
                                            </flux:button>
                                        @endcan
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="border-t border-neutral-200 px-4 py-3 dark:border-neutral-700">
                    {{ $this->tasks->links() }}
                </div>
            </div>
        @endif

        <flux:modal wire:model="showCreateModal" class="max-w-2xl">
            <div class="space-y-4">
                <flux:heading>{{ __('crm.tasks.create') }}</flux:heading>

                <form wire:submit="createTask" class="space-y-4">
                    <flux:input wire:model="title" :label="__('crm.labels.title')" required />
                    <flux:textarea wire:model="description" :label="__('crm.labels.description')" rows="3" />

                    <div class="grid gap-3 md:grid-cols-3">
                        <flux:field>
                            <flux:label>{{ __('crm.labels.priority') }}</flux:label>
                            <flux:select wire:model="task_priority">
                                @foreach (\App\Enums\TaskPriority::cases() as $taskPriority)
                                    <option value="{{ $taskPriority->value }}">{{ $taskPriority->label() }}</option>
                                @endforeach
                            </flux:select>
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('crm.labels.assignee') }}</flux:label>
                            <flux:select wire:model="assigned_to">
                                <option value="">—</option>
                                @foreach ($this->users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </flux:select>
                        </flux:field>

                        <flux:input wire:model="due_at" :label="__('crm.labels.due_at')" type="datetime-local" />
                    </div>

                    <div class="flex justify-end gap-2">
                        <flux:button type="button" variant="ghost" wire:click="$set('showCreateModal', false)">
                            {{ __('crm.actions.cancel') }}
                        </flux:button>
                        <flux:button type="submit" variant="primary">{{ __('crm.actions.save') }}</flux:button>
                    </div>
                </form>
            </div>
        </flux:modal>
    </div>
</section>

<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CrmTutorialState;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TutorialStateController extends Controller
{
    private const TutorialKey = 'crm-onboarding';

    private const TutorialVersion = 1;

    private const ModuleKeys = [
        'dashboard',
        'leads',
        'companies',
        'contacts',
        'pipeline',
        'deals',
        'tasks',
        'reports',
    ];

    public function show(Request $request): JsonResponse
    {
        $state = $this->resolveState($request);

        return response()->json($this->payload($state));
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'string', Rule::in(['complete-module', 'skip'])],
            'module' => [
                Rule::requiredIf(fn (): bool => $request->string('action')->value() === 'complete-module'),
                'nullable',
                'string',
                Rule::in(self::ModuleKeys),
            ],
        ]);

        $state = $this->resolveState($request);

        if ($validated['action'] === 'skip') {
            $state->forceFill([
                'dismissed_at' => now(),
                'completed_at' => null,
            ])->save();

            return response()->json($this->payload($state->fresh()));
        }

        $completedModules = $this->normalizeModules([
            ...($state->completed_modules ?? []),
            $validated['module'],
        ]);

        $isComplete = count($completedModules) === count(self::ModuleKeys);

        $state->forceFill([
            'completed_modules' => $completedModules,
            'dismissed_at' => null,
            'completed_at' => $isComplete ? now() : null,
        ])->save();

        return response()->json($this->payload($state->fresh()));
    }

    public function restart(Request $request): JsonResponse
    {
        $state = $this->resolveState($request);

        $state->forceFill([
            'tutorial_version' => self::TutorialVersion,
            'completed_modules' => [],
            'dismissed_at' => null,
            'completed_at' => null,
        ])->save();

        return response()->json($this->payload($state->fresh()));
    }

    protected function resolveState(Request $request): CrmTutorialState
    {
        $user = $request->user();
        abort_unless($user !== null, 401);

        $currentAccountId = $user->current_account_id;
        abort_unless($currentAccountId !== null, 403);
        abort_unless($user->accounts()->whereKey($currentAccountId)->exists(), 403);

        $state = CrmTutorialState::query()->firstOrCreate(
            [
                'account_id' => $currentAccountId,
                'user_id' => $user->id,
                'tutorial_key' => self::TutorialKey,
            ],
            [
                'tutorial_version' => self::TutorialVersion,
                'completed_modules' => [],
            ],
        );

        if ((int) $state->tutorial_version !== self::TutorialVersion) {
            $state->forceFill([
                'tutorial_version' => self::TutorialVersion,
                'completed_modules' => [],
                'dismissed_at' => null,
                'completed_at' => null,
            ])->save();
        }

        return $state;
    }

    /**
     * @param  array<int, mixed>  $modules
     * @return array<int, string>
     */
    protected function normalizeModules(array $modules): array
    {
        return array_values(
            array_unique(
                array_values(
                    array_intersect(
                        array_filter($modules, fn ($module): bool => is_string($module) && $module !== ''),
                        self::ModuleKeys,
                    ),
                ),
            ),
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function payload(CrmTutorialState $state): array
    {
        $completedModules = $this->normalizeModules($state->completed_modules ?? []);
        $isComplete = $state->completed_at !== null || count($completedModules) === count(self::ModuleKeys);

        return [
            'tutorial_key' => self::TutorialKey,
            'tutorial_version' => self::TutorialVersion,
            'modules' => self::ModuleKeys,
            'completed_modules' => $completedModules,
            'dismissed' => $state->dismissed_at !== null,
            'completed' => $isComplete,
            'should_start' => $state->dismissed_at === null && ! $isComplete,
        ];
    }
}

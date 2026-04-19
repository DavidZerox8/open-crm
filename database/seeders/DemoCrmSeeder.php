<?php

namespace Database\Seeders;

use App\Enums\ActivityType;
use App\Enums\DealStatus;
use App\Enums\LeadStatus;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Account;
use App\Models\CRM\Activity;
use App\Models\CRM\Company;
use App\Models\CRM\Contact;
use App\Models\CRM\Deal;
use App\Models\CRM\Lead;
use App\Models\CRM\Pipeline;
use App\Models\CRM\PipelineStage;
use App\Models\CRM\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DemoCrmSeeder extends Seeder
{
    public function run(?Account $account = null, ?User $user = null): void
    {
        $account ??= Account::query()->first();
        $user ??= User::query()->where('current_account_id', $account->id)->first();

        if ($account === null || $user === null) {
            return;
        }

        $pipelines = $this->createPipelines($account);
        $companies = $this->createCompanies($account, $user, 20);
        $contacts = $this->createContacts($account, $user, $companies, 50);
        $this->createLeads($account, $user, 30);
        $deals = $this->createDeals($account, $user, $pipelines, $companies, $contacts, 40);
        $this->createActivities($account, $user, $companies, $contacts, $deals, 100);
        $this->createTasks($account, $user, $companies, $contacts, $deals, 60);
    }

    /**
     * @return array<string, Pipeline>
     */
    protected function createPipelines(Account $account): array
    {
        $ventas = Pipeline::factory()->create([
            'account_id' => $account->id,
            'name' => 'Ventas',
            'slug' => 'ventas-'.Str::lower(Str::random(6)),
            'is_default' => true,
            'position' => 0,
        ]);

        $onboarding = Pipeline::factory()->create([
            'account_id' => $account->id,
            'name' => 'Onboarding',
            'slug' => 'onboarding-'.Str::lower(Str::random(6)),
            'is_default' => false,
            'position' => 1,
        ]);

        $this->createStages($account, $ventas, [
            ['Nuevo', 10, '#94a3b8', false, false],
            ['Calificando', 25, '#60a5fa', false, false],
            ['Propuesta', 50, '#a78bfa', false, false],
            ['Negociacion', 75, '#f59e0b', false, false],
            ['Ganado', 100, '#10b981', true, false],
            ['Perdido', 0, '#ef4444', false, true],
        ]);

        $this->createStages($account, $onboarding, [
            ['Kickoff', 20, '#0ea5e9', false, false],
            ['Configuracion', 50, '#6366f1', false, false],
            ['Capacitacion', 75, '#8b5cf6', false, false],
            ['Completado', 100, '#10b981', true, false],
            ['Cancelado', 0, '#ef4444', false, true],
        ]);

        return ['ventas' => $ventas, 'onboarding' => $onboarding];
    }

    /**
     * @param  array<int, array{0: string, 1: int, 2: string, 3: bool, 4: bool}>  $stages
     */
    protected function createStages(Account $account, Pipeline $pipeline, array $stages): void
    {
        foreach ($stages as $index => [$name, $probability, $color, $isWon, $isLost]) {
            PipelineStage::factory()->create([
                'account_id' => $account->id,
                'pipeline_id' => $pipeline->id,
                'name' => $name,
                'slug' => Str::slug($name).'-'.Str::lower(Str::random(6)),
                'position' => $index,
                'probability' => $probability,
                'color' => $color,
                'is_won' => $isWon,
                'is_lost' => $isLost,
            ]);
        }
    }

    /**
     * @return Collection<int, Company>
     */
    protected function createCompanies(Account $account, User $user, int $count): Collection
    {
        return Company::factory()
            ->count($count)
            ->create([
                'account_id' => $account->id,
                'owner_id' => $user->id,
            ]);
    }

    /**
     * @param  Collection<int, Company>  $companies
     * @return Collection<int, Contact>
     */
    protected function createContacts(Account $account, User $user, Collection $companies, int $count): Collection
    {
        return Contact::factory()
            ->count($count)
            ->state(fn () => [
                'account_id' => $account->id,
                'owner_id' => $user->id,
                'company_id' => $companies->random()->id,
            ])
            ->create();
    }

    protected function createLeads(Account $account, User $user, int $count): void
    {
        Lead::factory()
            ->count($count)
            ->state(fn () => [
                'account_id' => $account->id,
                'owner_id' => $user->id,
                'status' => fake()->randomElement([
                    LeadStatus::New,
                    LeadStatus::Contacted,
                    LeadStatus::Qualified,
                    LeadStatus::Unqualified,
                ]),
            ])
            ->create();
    }

    /**
     * @param  array<string, Pipeline>  $pipelines
     * @param  Collection<int, Company>  $companies
     * @param  Collection<int, Contact>  $contacts
     * @return Collection<int, Deal>
     */
    protected function createDeals(Account $account, User $user, array $pipelines, Collection $companies, Collection $contacts, int $count): Collection
    {
        $ventas = $pipelines['ventas'];
        $stages = $ventas->stages()->get();

        return Deal::factory()
            ->count($count)
            ->state(function () use ($account, $user, $ventas, $stages, $companies, $contacts) {
                $stage = $stages->random();
                $status = DealStatus::Open;
                $closedAt = null;

                if ($stage->is_won) {
                    $status = DealStatus::Won;
                    $closedAt = now()->subDays(fake()->numberBetween(0, 60));
                } elseif ($stage->is_lost) {
                    $status = DealStatus::Lost;
                    $closedAt = now()->subDays(fake()->numberBetween(0, 60));
                }

                return [
                    'account_id' => $account->id,
                    'pipeline_id' => $ventas->id,
                    'stage_id' => $stage->id,
                    'company_id' => $companies->random()->id,
                    'contact_id' => $contacts->random()->id,
                    'owner_id' => $user->id,
                    'probability' => $stage->probability,
                    'status' => $status,
                    'closed_at' => $closedAt,
                ];
            })
            ->create();
    }

    /**
     * @param  Collection<int, Company>  $companies
     * @param  Collection<int, Contact>  $contacts
     * @param  Collection<int, Deal>  $deals
     */
    protected function createActivities(Account $account, User $user, Collection $companies, Collection $contacts, Collection $deals, int $count): void
    {
        $subjects = collect()
            ->concat($companies->map(fn (Company $c) => [Company::class, $c->id]))
            ->concat($contacts->map(fn (Contact $c) => [Contact::class, $c->id]))
            ->concat($deals->map(fn (Deal $d) => [Deal::class, $d->id]));

        Activity::factory()
            ->count($count)
            ->state(function () use ($account, $user, $subjects) {
                [$type, $id] = $subjects->random();

                return [
                    'account_id' => $account->id,
                    'user_id' => $user->id,
                    'subject_type' => $type,
                    'subject_id' => $id,
                    'type' => fake()->randomElement(ActivityType::cases()),
                ];
            })
            ->create();
    }

    /**
     * @param  Collection<int, Company>  $companies
     * @param  Collection<int, Contact>  $contacts
     * @param  Collection<int, Deal>  $deals
     */
    protected function createTasks(Account $account, User $user, Collection $companies, Collection $contacts, Collection $deals, int $count): void
    {
        $subjects = collect()
            ->concat($companies->map(fn (Company $c) => [Company::class, $c->id]))
            ->concat($contacts->map(fn (Contact $c) => [Contact::class, $c->id]))
            ->concat($deals->map(fn (Deal $d) => [Deal::class, $d->id]));

        Task::factory()
            ->count($count)
            ->state(function () use ($account, $user, $subjects) {
                [$type, $id] = $subjects->random();
                $status = fake()->randomElement([TaskStatus::Pending, TaskStatus::Pending, TaskStatus::Completed]);

                return [
                    'account_id' => $account->id,
                    'assigned_to' => $user->id,
                    'created_by' => $user->id,
                    'subject_type' => $type,
                    'subject_id' => $id,
                    'priority' => fake()->randomElement(TaskPriority::cases()),
                    'status' => $status,
                    'completed_at' => $status === TaskStatus::Completed ? now()->subDays(fake()->numberBetween(0, 10)) : null,
                ];
            })
            ->create();
    }
}

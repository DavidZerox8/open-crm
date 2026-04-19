<?php

namespace App\Providers;

use App\Listeners\LogAuthenticationActivity;
use App\Models\CRM\Activity;
use App\Models\CRM\Company;
use App\Models\CRM\Contact;
use App\Models\CRM\Deal;
use App\Models\CRM\Lead;
use App\Models\CRM\Pipeline;
use App\Models\CRM\Task;
use App\Models\User;
use App\Policies\ActivityPolicy;
use App\Policies\CompanyPolicy;
use App\Policies\ContactPolicy;
use App\Policies\DealPolicy;
use App\Policies\LeadPolicy;
use App\Policies\PipelinePolicy;
use App\Policies\TaskPolicy;
use App\Support\Authorization\RolePermissionMatrix;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureAuthorization();
        $this->configureActivitySubscribers();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    /**
     * Configure authorization defaults.
     */
    protected function configureAuthorization(): void
    {
        Gate::before(function (User $user): ?bool {
            return $user->hasRole(RolePermissionMatrix::AdministratorRole) ? true : null;
        });

        Gate::policy(Company::class, CompanyPolicy::class);
        Gate::policy(Contact::class, ContactPolicy::class);
        Gate::policy(Lead::class, LeadPolicy::class);
        Gate::policy(Deal::class, DealPolicy::class);
        Gate::policy(Pipeline::class, PipelinePolicy::class);
        Gate::policy(Activity::class, ActivityPolicy::class);
        Gate::policy(Task::class, TaskPolicy::class);
    }

    /**
     * Configure activity subscribers.
     */
    protected function configureActivitySubscribers(): void
    {
        Event::subscribe(LogAuthenticationActivity::class);
    }
}

<?php

namespace App\Providers;

use App\Domains\Calendar\Models\Event;
use App\Domains\Calendar\Policies\EventPolicy;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Policies\ProjectPolicy;
use App\Domains\Tasks\Models\Task;
use App\Domains\Tasks\Policies\TaskPolicy;
use App\Domains\Teams\Listeners\ConsumePendingInvitationOnLogin;
use App\Domains\Teams\Listeners\ConsumePendingInvitationOnRegister;
use App\Domains\Teams\Models\Team;
use App\Domains\Teams\Policies\TeamPolicy;
use App\Domains\TimeTracking\Models\TimeEntry;
use App\Domains\TimeTracking\Policies\TimeEntryPolicy;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event as EventFacade;
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
        $this->registerPolicies();
        $this->registerListeners();
    }

    protected function registerPolicies(): void
    {
        Gate::policy(Team::class, TeamPolicy::class);
        Gate::policy(Project::class, ProjectPolicy::class);
        Gate::policy(Task::class, TaskPolicy::class);
        Gate::policy(TimeEntry::class, TimeEntryPolicy::class);
        Gate::policy(Event::class, EventPolicy::class);
    }

    protected function registerListeners(): void
    {
        EventFacade::listen(Registered::class, ConsumePendingInvitationOnRegister::class);
        EventFacade::listen(Login::class, ConsumePendingInvitationOnLogin::class);
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
}

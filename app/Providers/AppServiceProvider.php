<?php

namespace App\Providers;

use App\Models\Event;
use App\Models\ParticipantRegistration;
use App\Models\RegistrationForm;
use App\Models\User;
use App\Policies\EventPolicy;
use App\Policies\ParticipantRegistrationPolicy;
use App\Policies\RegistrationFormPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        Gate::policy(Event::class, EventPolicy::class);
        Gate::policy(ParticipantRegistration::class, ParticipantRegistrationPolicy::class);
        Gate::policy(RegistrationForm::class, RegistrationFormPolicy::class);

        Gate::define('attendance.view', fn (User $user, Event $event) => $user->hasPermission('attendance.view')
            || ($user->hasPermission('events.view') && $event->organizer_id === $user->id));
        Gate::define('attendance.scan', fn (User $user, Event $event) => $user->hasPermission('attendance.scan')
            || ($user->hasPermission('events.update') && $event->organizer_id === $user->id));
        Gate::define('attendance.override', fn (User $user, Event $event) => $user->hasPermission('attendance.override'));
        Gate::define('attendance.export', fn (User $user, Event $event) => $user->hasPermission('attendance.export'));
    }
}

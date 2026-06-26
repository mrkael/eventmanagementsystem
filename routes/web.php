<?php

use App\Http\Controllers\Admin\Attendance\AttendanceController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\Core\AttendeeController as CoreAttendeeController;
use App\Http\Controllers\Admin\Core\CheckInController as CoreCheckInController;
use App\Http\Controllers\Admin\Core\ContactController as CoreContactController;
use App\Http\Controllers\Admin\Core\EmailController as CoreEmailController;
use App\Http\Controllers\Admin\Core\EventAgendaController as CoreEventAgendaController;
use App\Http\Controllers\Admin\Core\EventController as CoreEventController;
use App\Http\Controllers\Admin\Core\EventEmailController as CoreEventEmailController;
use App\Http\Controllers\Admin\Core\FoundationController;
use App\Http\Controllers\Admin\Core\MicrositeController as CoreMicrositeController;
use App\Http\Controllers\Admin\Core\OrganiserProfileController as CoreOrganiserProfileController;
use App\Http\Controllers\Admin\Core\RegistrationFormController as CoreRegistrationFormController;
use App\Http\Controllers\Admin\Core\ReportController as CoreReportController;
use App\Http\Controllers\Admin\Core\SessionController as CoreSessionController;
use App\Http\Controllers\Admin\Core\TicketController as CoreTicketController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\Events\EventController;
use App\Http\Controllers\Admin\Events\EventPageBuilderController;
use App\Http\Controllers\Admin\EventSetup\EventCategoryController;
use App\Http\Controllers\Admin\EventSetup\EventConfigurationController;
use App\Http\Controllers\Admin\EventSetup\EventStatusController;
use App\Http\Controllers\Admin\EventSetup\EventTypeController;
use App\Http\Controllers\Admin\EventSetup\VenueController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\Registrations\ParticipantRegistrationController;
use App\Http\Controllers\Admin\Registrations\RegistrationFormBuilderController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\CoreEventController as PublicCoreEventController;
use App\Http\Controllers\Public\EventPageController;
use App\Http\Controllers\Public\RegistrationController;
use Illuminate\Support\Facades\Route;

Route::get('/pagebuilder-assets/pagebuilder/{file}', function (string $file) {
    $base = realpath(base_path('vendor/hansschouten/phpagebuilder/dist/pagebuilder'));
    $path = realpath($base.DIRECTORY_SEPARATOR.$file);

    abort_unless($base && $path && str_starts_with($path, $base), 404);

    return response()->file($path);
})->where('file', '.*')->name('pagebuilder.assets');

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:5,1');

    if (config('event_management.self_registration_enabled')) {
        Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
        Route::post('/register', [RegisteredUserController::class, 'store'])->middleware('throttle:3,1');
    }
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', [FoundationController::class, 'dashboard'])
        ->middleware('permission:dashboard.view')
        ->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->middleware('permission:profile.update')
        ->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])
        ->middleware('permission:profile.update')
        ->name('profile.update');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('departments', [DepartmentController::class, 'index'])->middleware('permission:departments.view')->name('departments.index');
        Route::get('departments/create', [DepartmentController::class, 'create'])->middleware('permission:departments.create')->name('departments.create');
        Route::post('departments', [DepartmentController::class, 'store'])->middleware('permission:departments.create')->name('departments.store');
        Route::get('departments/{department}', [DepartmentController::class, 'show'])->middleware('permission:departments.view')->name('departments.show');
        Route::get('departments/{department}/edit', [DepartmentController::class, 'edit'])->middleware('permission:departments.update')->name('departments.edit');
        Route::put('departments/{department}', [DepartmentController::class, 'update'])->middleware('permission:departments.update')->name('departments.update');
        Route::delete('departments/{department}', [DepartmentController::class, 'destroy'])->middleware('permission:departments.delete')->name('departments.destroy');

        Route::get('roles', [RoleController::class, 'index'])->middleware('permission:roles.view')->name('roles.index');
        Route::get('roles/create', [RoleController::class, 'create'])->middleware('permission:roles.create')->name('roles.create');
        Route::post('roles', [RoleController::class, 'store'])->middleware('permission:roles.create')->name('roles.store');
        Route::get('roles/{role}', [RoleController::class, 'show'])->middleware('permission:roles.view')->name('roles.show');
        Route::get('roles/{role}/edit', [RoleController::class, 'edit'])->middleware('permission:roles.update')->name('roles.edit');
        Route::put('roles/{role}', [RoleController::class, 'update'])->middleware('permission:roles.update')->name('roles.update');
        Route::delete('roles/{role}', [RoleController::class, 'destroy'])->middleware('permission:roles.delete')->name('roles.destroy');

        Route::get('permissions', [PermissionController::class, 'index'])->middleware('permission:permissions.view')->name('permissions.index');
        Route::get('permissions/create', [PermissionController::class, 'create'])->middleware('permission:permissions.create')->name('permissions.create');
        Route::post('permissions', [PermissionController::class, 'store'])->middleware('permission:permissions.create')->name('permissions.store');
        Route::get('permissions/{permission}/edit', [PermissionController::class, 'edit'])->middleware('permission:permissions.update')->name('permissions.edit');
        Route::put('permissions/{permission}', [PermissionController::class, 'update'])->middleware('permission:permissions.update')->name('permissions.update');
        Route::delete('permissions/{permission}', [PermissionController::class, 'destroy'])->middleware('permission:permissions.delete')->name('permissions.destroy');

        Route::get('audit-logs', [AuditLogController::class, 'index'])->middleware('permission:audit_logs.view')->name('audit-logs.index');

        Route::get('users', [UserController::class, 'index'])->middleware('permission:users.view')->name('users.index');
        Route::get('users/create', [UserController::class, 'create'])->middleware('permission:users.create')->name('users.create');
        Route::post('users', [UserController::class, 'store'])->middleware('permission:users.create')->name('users.store');
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->middleware('permission:users.update')->name('users.edit');
        Route::put('users/{user}', [UserController::class, 'update'])->middleware('permission:users.update')->name('users.update');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->middleware('permission:users.delete')->name('users.destroy');

        Route::get('event-categories', [EventCategoryController::class, 'index'])->middleware('permission:event_categories.view')->name('event-categories.index');
        Route::get('event-categories/create', [EventCategoryController::class, 'create'])->middleware('permission:event_categories.create')->name('event-categories.create');
        Route::post('event-categories', [EventCategoryController::class, 'store'])->middleware('permission:event_categories.create')->name('event-categories.store');
        Route::get('event-categories/{eventCategory}', [EventCategoryController::class, 'show'])->middleware('permission:event_categories.view')->name('event-categories.show');
        Route::get('event-categories/{eventCategory}/edit', [EventCategoryController::class, 'edit'])->middleware('permission:event_categories.update')->name('event-categories.edit');
        Route::put('event-categories/{eventCategory}', [EventCategoryController::class, 'update'])->middleware('permission:event_categories.update')->name('event-categories.update');
        Route::delete('event-categories/{eventCategory}', [EventCategoryController::class, 'destroy'])->middleware('permission:event_categories.delete')->name('event-categories.destroy');

        Route::get('event-types', [EventTypeController::class, 'index'])->middleware('permission:event_types.view')->name('event-types.index');
        Route::get('event-types/create', [EventTypeController::class, 'create'])->middleware('permission:event_types.create')->name('event-types.create');
        Route::post('event-types', [EventTypeController::class, 'store'])->middleware('permission:event_types.create')->name('event-types.store');
        Route::get('event-types/{eventType}', [EventTypeController::class, 'show'])->middleware('permission:event_types.view')->name('event-types.show');
        Route::get('event-types/{eventType}/edit', [EventTypeController::class, 'edit'])->middleware('permission:event_types.update')->name('event-types.edit');
        Route::put('event-types/{eventType}', [EventTypeController::class, 'update'])->middleware('permission:event_types.update')->name('event-types.update');
        Route::delete('event-types/{eventType}', [EventTypeController::class, 'destroy'])->middleware('permission:event_types.delete')->name('event-types.destroy');

        Route::get('venues', [VenueController::class, 'index'])->middleware('permission:venues.view')->name('venues.index');
        Route::get('venues/create', [VenueController::class, 'create'])->middleware('permission:venues.create')->name('venues.create');
        Route::post('venues', [VenueController::class, 'store'])->middleware('permission:venues.create')->name('venues.store');
        Route::get('venues/{venue}', [VenueController::class, 'show'])->middleware('permission:venues.view')->name('venues.show');
        Route::get('venues/{venue}/edit', [VenueController::class, 'edit'])->middleware('permission:venues.update')->name('venues.edit');
        Route::put('venues/{venue}', [VenueController::class, 'update'])->middleware('permission:venues.update')->name('venues.update');
        Route::delete('venues/{venue}', [VenueController::class, 'destroy'])->middleware('permission:venues.delete')->name('venues.destroy');

        Route::get('event-statuses', [EventStatusController::class, 'index'])->middleware('permission:event_statuses.view')->name('event-statuses.index');
        Route::get('event-statuses/create', [EventStatusController::class, 'create'])->middleware('permission:event_statuses.create')->name('event-statuses.create');
        Route::post('event-statuses', [EventStatusController::class, 'store'])->middleware('permission:event_statuses.create')->name('event-statuses.store');
        Route::get('event-statuses/{eventStatus}', [EventStatusController::class, 'show'])->middleware('permission:event_statuses.view')->name('event-statuses.show');
        Route::get('event-statuses/{eventStatus}/edit', [EventStatusController::class, 'edit'])->middleware('permission:event_statuses.update')->name('event-statuses.edit');
        Route::put('event-statuses/{eventStatus}', [EventStatusController::class, 'update'])->middleware('permission:event_statuses.update')->name('event-statuses.update');
        Route::delete('event-statuses/{eventStatus}', [EventStatusController::class, 'destroy'])->middleware('permission:event_statuses.delete')->name('event-statuses.destroy');

        Route::get('event-configurations', [EventConfigurationController::class, 'index'])->middleware('permission:event_configurations.view')->name('event-configurations.index');
        Route::get('event-configurations/create', [EventConfigurationController::class, 'create'])->middleware('permission:event_configurations.create')->name('event-configurations.create');
        Route::post('event-configurations', [EventConfigurationController::class, 'store'])->middleware('permission:event_configurations.create')->name('event-configurations.store');
        Route::get('event-configurations/{eventConfiguration}/edit', [EventConfigurationController::class, 'edit'])->middleware('permission:event_configurations.update')->name('event-configurations.edit');
        Route::put('event-configurations/{eventConfiguration}', [EventConfigurationController::class, 'update'])->middleware('permission:event_configurations.update')->name('event-configurations.update');
        Route::delete('event-configurations/{eventConfiguration}', [EventConfigurationController::class, 'destroy'])->middleware('permission:event_configurations.delete')->name('event-configurations.destroy');

        Route::get('events', [EventController::class, 'index'])->middleware('permission:events.view')->name('events.index');
        Route::get('events/create', [EventController::class, 'create'])->middleware('permission:events.create')->name('events.create');
        Route::post('events', [EventController::class, 'store'])->middleware('permission:events.create')->name('events.store');
        Route::get('events/{event}', [EventController::class, 'show'])->middleware('permission:events.view')->name('events.show');
        Route::get('events/{event}/edit', [EventController::class, 'edit'])->middleware('permission:events.update')->name('events.edit');
        Route::put('events/{event}', [EventController::class, 'update'])->middleware('permission:events.update')->name('events.update');
        Route::delete('events/{event}', [EventController::class, 'destroy'])->middleware('permission:events.delete')->name('events.destroy');
        Route::post('events/{event}/submit', [EventController::class, 'submit'])->middleware('permission:events.submit')->name('events.submit');
        Route::post('events/{event}/publish', [EventController::class, 'publish'])->middleware('permission:events.publish')->name('events.publish');
        Route::delete('events/{event}/documents/{document}', [EventController::class, 'destroyDocument'])->middleware('permission:events.update')->name('events.documents.destroy');
        Route::get('events/{event}/builder', [EventPageBuilderController::class, 'edit'])->middleware('permission:events.update')->name('events.builder.edit');
        Route::put('events/{event}/builder', [EventPageBuilderController::class, 'update'])->middleware('permission:events.update')->name('events.builder.update');
        Route::post('events/{event}/builder/publish', [EventPageBuilderController::class, 'publish'])->middleware('permission:events.publish')->name('events.builder.publish');
        Route::get('events/{event}/preview', [EventPageBuilderController::class, 'preview'])->middleware('permission:events.view')->name('events.preview');

        Route::get('events/{event}/registration-builder', [RegistrationFormBuilderController::class, 'edit'])->middleware('permission:registration_forms.manage')->name('events.registrations.builder.edit');
        Route::put('events/{event}/registration-builder', [RegistrationFormBuilderController::class, 'update'])->middleware('permission:registration_forms.manage')->name('events.registrations.builder.update');
        Route::get('events/{event}/registrations', [ParticipantRegistrationController::class, 'index'])->middleware('permission:registrations.view')->name('events.registrations.index');
        Route::get('events/{event}/registrations/create', [ParticipantRegistrationController::class, 'create'])->middleware('permission:registrations.create')->name('events.registrations.create');
        Route::post('events/{event}/registrations', [ParticipantRegistrationController::class, 'store'])->middleware('permission:registrations.create')->name('events.registrations.store');
        Route::post('events/{event}/registrations/bulk', [ParticipantRegistrationController::class, 'bulk'])->middleware('permission:registrations.create')->name('events.registrations.bulk');
        Route::post('events/{event}/registrations/invite', [ParticipantRegistrationController::class, 'invite'])->middleware('permission:registrations.invite')->name('events.registrations.invite');
        Route::get('events/{event}/registrations/{registration}', [ParticipantRegistrationController::class, 'show'])->middleware('permission:registrations.view')->name('events.registrations.show');
        Route::patch('events/{event}/registrations/{registration}/status', [ParticipantRegistrationController::class, 'updateStatus'])->middleware('permission:registrations.update')->name('events.registrations.status');
        Route::post('events/{event}/registrations/{registration}/approve', [ParticipantRegistrationController::class, 'approve'])->middleware('permission:registrations.approve')->name('events.registrations.approve');

        Route::get('events/{event}/attendance', [AttendanceController::class, 'index'])->middleware('permission:attendance.view')->name('events.attendance.index');
        Route::get('events/{event}/attendance/scanner', [AttendanceController::class, 'scanner'])->middleware('permission:attendance.scan')->name('events.attendance.scanner');
        Route::post('events/{event}/attendance/check-in', [AttendanceController::class, 'checkIn'])->middleware('permission:attendance.scan')->name('events.attendance.check-in');
        Route::post('events/{event}/attendance/check-out', [AttendanceController::class, 'checkOut'])->middleware('permission:attendance.scan')->name('events.attendance.check-out');
        Route::post('events/{event}/attendance/{registration}/qr', [AttendanceController::class, 'generate'])->middleware('permission:attendance.scan')->name('events.attendance.qr');
        Route::post('events/{event}/attendance/{registration}/override', [AttendanceController::class, 'override'])->middleware('permission:attendance.override')->name('events.attendance.override');
        Route::get('events/{event}/attendance/export/{format}', [AttendanceController::class, 'export'])->middleware('permission:attendance.export')->name('events.attendance.export');
    });

    Route::prefix('admin/core')->name('core.')->group(function () {
        Route::get('organisers', [CoreOrganiserProfileController::class, 'index'])->middleware('permission:organisers.view')->name('organisers.index');
        Route::get('organisers/create', [CoreOrganiserProfileController::class, 'create'])->middleware('permission:organisers.create')->name('organisers.create');
        Route::post('organisers', [CoreOrganiserProfileController::class, 'store'])->middleware('permission:organisers.create')->name('organisers.store');
        Route::get('organisers/{organiser}', [CoreOrganiserProfileController::class, 'show'])->middleware('permission:organisers.view')->name('organisers.show');
        Route::get('organisers/{organiser}/edit', [CoreOrganiserProfileController::class, 'edit'])->middleware('permission:organisers.update')->name('organisers.edit');
        Route::put('organisers/{organiser}', [CoreOrganiserProfileController::class, 'update'])->middleware('permission:organisers.update')->name('organisers.update');
        Route::post('organisers/{organiser}/resend-login', [CoreOrganiserProfileController::class, 'resendLogin'])->middleware('permission:organisers.update')->name('organisers.resend-login');
        Route::delete('organisers/{organiser}', [CoreOrganiserProfileController::class, 'destroy'])->middleware('permission:organisers.delete')->name('organisers.destroy');

        Route::get('contacts', [CoreContactController::class, 'index'])->middleware('permission:contacts.view')->name('contacts.index');
        Route::post('contacts', [CoreContactController::class, 'store'])->middleware('permission:contacts.create')->name('contacts.store');
        Route::post('contacts/groups', [CoreContactController::class, 'storeGroup'])->middleware('permission:contacts.create')->name('contacts.groups.store');
        Route::post('contacts/import', [CoreContactController::class, 'import'])->middleware('permission:contacts.create')->name('contacts.import');
        Route::get('contacts/export', [CoreContactController::class, 'export'])->middleware('permission:contacts.export')->name('contacts.export');

        Route::get('events', [CoreEventController::class, 'index'])->middleware('permission:events.view')->name('events.index');
        Route::get('events/create', [CoreEventController::class, 'create'])->middleware('permission:events.create')->name('events.create');
        Route::get('events/foundation/detail', [FoundationController::class, 'eventShow'])->middleware('permission:events.view')->name('events.foundation.show');
        Route::get('events/foundation/edit', [FoundationController::class, 'eventEdit'])->middleware('permission:events.update')->name('events.foundation.edit');
        Route::get('events/foundation/microsite', [FoundationController::class, 'microsite'])->middleware('permission:events.update')->name('events.foundation.microsite');
        Route::post('events', [CoreEventController::class, 'store'])->middleware('permission:events.create')->name('events.store');
        Route::get('events/{event}', [CoreEventController::class, 'show'])->middleware('permission:events.view')->name('events.show');
        Route::get('events/{event}/edit', [CoreEventController::class, 'edit'])->middleware('permission:events.update')->name('events.edit');
        Route::put('events/{event}', [CoreEventController::class, 'update'])->middleware('permission:events.update')->name('events.update');

        Route::get('events/{event}/microsite', [CoreMicrositeController::class, 'edit'])->middleware('permission:events.update')->name('events.microsite.edit');
        Route::get('events/{event}/microsite/preview', [CoreMicrositeController::class, 'preview'])->middleware('permission:events.update')->name('events.microsite.preview');
        Route::put('events/{event}/microsite', [CoreMicrositeController::class, 'update'])->middleware('permission:events.update')->name('events.microsite.update');
        Route::post('events/{event}/microsite/assets', [CoreMicrositeController::class, 'upload'])->middleware('permission:events.update')->name('events.microsite.assets');
        Route::post('events/{event}/microsite/publish', [CoreMicrositeController::class, 'publish'])->middleware('permission:events.publish')->name('events.microsite.publish');
        Route::get('events/{event}/email', [CoreEventEmailController::class, 'edit'])->middleware('permission:emails.view')->name('events.email.edit');
        Route::put('events/{event}/email', [CoreEventEmailController::class, 'update'])->middleware('permission:emails.create')->name('events.email.update');
        Route::get('events/{event}/email/preview', [CoreEventEmailController::class, 'preview'])->middleware('permission:emails.view')->name('events.email.preview');
        Route::post('events/{event}/email/test', [CoreEventEmailController::class, 'sendTest'])->middleware('permission:emails.send')->name('events.email.test');

        Route::get('events/{event}/attendees', [CoreAttendeeController::class, 'index'])->middleware('permission:registrations.view')->name('events.attendees.index');
        Route::get('events/{event}/attendees/create', [CoreAttendeeController::class, 'create'])->middleware('permission:registrations.create')->name('events.attendees.create');
        Route::get('events/{event}/attendees/tickets/{ticket}/register', [CoreAttendeeController::class, 'register'])->middleware('permission:registrations.create')->name('events.attendees.register');
        Route::post('events/{event}/attendees/tickets/{ticket}', [CoreAttendeeController::class, 'store'])->middleware('permission:registrations.create')->name('events.attendees.store');
        Route::get('events/{event}/attendees/export', [CoreAttendeeController::class, 'export'])->middleware('permission:registrations.view')->name('events.attendees.export');
        Route::get('events/{event}/attendees/{registration}', [CoreAttendeeController::class, 'show'])->middleware('permission:registrations.view')->name('events.attendees.show');
        Route::get('events/{event}/attendees/{registration}/edit', [CoreAttendeeController::class, 'edit'])->middleware('permission:registrations.update')->name('events.attendees.edit');
        Route::put('events/{event}/attendees/{registration}', [CoreAttendeeController::class, 'update'])->middleware('permission:registrations.update')->name('events.attendees.update');
        Route::post('events/{event}/attendees/{registration}/resend', [CoreAttendeeController::class, 'resend'])->middleware('permission:registrations.update')->name('events.attendees.resend');
        Route::patch('events/{event}/attendees/{registration}/cancel', [CoreAttendeeController::class, 'cancel'])->middleware('permission:registrations.update')->name('events.attendees.cancel');

        Route::get('events/{event}/agendas', [CoreEventAgendaController::class, 'index'])->middleware('permission:events.update')->name('events.agendas.index');
        Route::get('events/{event}/agendas/create', [CoreEventAgendaController::class, 'create'])->middleware('permission:events.update')->name('events.agendas.create');
        Route::post('events/{event}/agendas', [CoreEventAgendaController::class, 'store'])->middleware('permission:events.update')->name('events.agendas.store');
        Route::get('events/{event}/agendas/{agenda}', [CoreEventAgendaController::class, 'show'])->middleware('permission:events.update')->name('events.agendas.show');
        Route::get('events/{event}/agendas/{agenda}/edit', [CoreEventAgendaController::class, 'edit'])->middleware('permission:events.update')->name('events.agendas.edit');
        Route::put('events/{event}/agendas/{agenda}', [CoreEventAgendaController::class, 'update'])->middleware('permission:events.update')->name('events.agendas.update');
        Route::delete('events/{event}/agendas/{agenda}', [CoreEventAgendaController::class, 'destroy'])->middleware('permission:events.update')->name('events.agendas.destroy');
        Route::post('events/{event}/agendas/{agenda}/sessions', [CoreEventAgendaController::class, 'storeSession'])->middleware('permission:events.update')->name('events.agendas.sessions.store');
        Route::get('events/{event}/agendas/{agenda}/sessions/{session}/edit', [CoreEventAgendaController::class, 'editSession'])->middleware('permission:events.update')->name('events.agendas.sessions.edit');
        Route::put('events/{event}/agendas/{agenda}/sessions/{session}', [CoreEventAgendaController::class, 'updateSession'])->middleware('permission:events.update')->name('events.agendas.sessions.update');
        Route::delete('events/{event}/agendas/{agenda}/sessions/{session}', [CoreEventAgendaController::class, 'destroySession'])->middleware('permission:events.update')->name('events.agendas.sessions.destroy');

        Route::get('events/{event}/check-in', [CoreCheckInController::class, 'index'])->middleware('permission:attendance.scan')->name('events.check-in.index');
        Route::post('events/{event}/check-in/scan', [CoreCheckInController::class, 'scan'])->middleware('permission:attendance.scan')->name('events.check-in.scan');

        Route::get('events/{event}/forms', [CoreRegistrationFormController::class, 'index'])->middleware('permission:registration_forms.manage')->name('events.forms.index');
        Route::get('events/{event}/forms/create', [CoreRegistrationFormController::class, 'create'])->middleware('permission:registration_forms.manage')->name('events.forms.create');
        Route::post('events/{event}/forms', [CoreRegistrationFormController::class, 'store'])->middleware('permission:registration_forms.manage')->name('events.forms.store');
        Route::get('events/{event}/forms/{form}/edit', [CoreRegistrationFormController::class, 'edit'])->middleware('permission:registration_forms.manage')->name('events.forms.edit');
        Route::get('events/{event}/forms/{form}/preview', [CoreRegistrationFormController::class, 'preview'])->middleware('permission:registration_forms.manage')->name('events.forms.preview');
        Route::put('events/{event}/forms/{form}', [CoreRegistrationFormController::class, 'update'])->middleware('permission:registration_forms.manage')->name('events.forms.update');
        Route::delete('events/{event}/forms/{form}', [CoreRegistrationFormController::class, 'destroy'])->middleware('permission:registration_forms.manage')->name('events.forms.destroy');

        Route::get('events/{event}/tickets', [CoreTicketController::class, 'index'])->middleware('permission:events.update')->name('events.tickets.index');
        Route::get('events/{event}/tickets/create', [CoreTicketController::class, 'create'])->middleware('permission:events.update')->name('events.tickets.create');
        Route::post('events/{event}/tickets', [CoreTicketController::class, 'store'])->middleware('permission:events.update')->name('events.tickets.store');
        Route::get('events/{event}/tickets/{ticket}/edit', [CoreTicketController::class, 'edit'])->middleware('permission:events.update')->name('events.tickets.edit');
        Route::put('events/{event}/tickets/{ticket}', [CoreTicketController::class, 'update'])->middleware('permission:events.update')->name('events.tickets.update');
        Route::delete('events/{event}/tickets/{ticket}', [CoreTicketController::class, 'destroy'])->middleware('permission:events.update')->name('events.tickets.destroy');
        Route::post('events/{event}/promos', [CoreTicketController::class, 'storePromo'])->middleware('permission:events.update')->name('events.promos.store');
        Route::put('events/{event}/promos/{promoCode}', [CoreTicketController::class, 'updatePromo'])->middleware('permission:events.update')->name('events.promos.update');

        Route::get('events/{event}/sessions', [CoreSessionController::class, 'index'])->middleware('permission:events.update')->name('events.sessions.index');
        Route::post('events/{event}/sessions', [CoreSessionController::class, 'store'])->middleware('permission:events.update')->name('events.sessions.store');
        Route::put('events/{event}/sessions/{session}', [CoreSessionController::class, 'update'])->middleware('permission:events.update')->name('events.sessions.update');
        Route::get('events/{event}/sessions/{session}/scanner', [CoreSessionController::class, 'scanner'])->middleware('permission:attendance.scan')->name('events.sessions.scanner');
        Route::get('events/{event}/sessions/{session}/counter', [CoreSessionController::class, 'counter'])->middleware('permission:attendance.view')->name('events.sessions.counter');
        Route::post('events/{event}/sessions/{session}/scan', CoreCheckInController::class)->middleware('permission:attendance.scan')->name('events.sessions.scan');

        Route::get('events/{event}/reports', [CoreReportController::class, 'index'])->middleware('permission:reports.view')->name('events.reports.index');
        Route::post('events/{event}/reports', [CoreReportController::class, 'store'])->middleware('permission:reports.create')->name('events.reports.store');
        Route::get('events/{event}/reports/{report}/export', [CoreReportController::class, 'export'])->middleware('permission:reports.export')->name('events.reports.export');

        Route::get('attendees', [FoundationController::class, 'attendees'])->middleware('permission:registrations.view')->name('attendees.index');

        Route::get('emails', [FoundationController::class, 'emails'])->middleware('permission:emails.view')->name('emails.index');
        Route::post('emails/templates', [CoreEmailController::class, 'storeTemplate'])->middleware('permission:emails.create')->name('emails.templates.store');
        Route::post('emails/send', [CoreEmailController::class, 'send'])->middleware('permission:emails.send')->name('emails.send');
    });
});

Route::get('/e/{event:custom_url}/{referral?}', [PublicCoreEventController::class, 'show'])->where('referral', '[^/]+')->name('core.public.events.show');
Route::get('/e/{event:custom_url}/tickets/{ticket}/register', [PublicCoreEventController::class, 'register'])->name('core.public.register');
Route::post('/e/{event:custom_url}/tickets/{ticket}/register', [PublicCoreEventController::class, 'submit'])->middleware('throttle:10,1')->name('core.public.submit');
Route::get('/registration/{registration}/success', [PublicCoreEventController::class, 'success'])->middleware('signed')->name('core.public.success');

Route::get('/events/{event:slug}', [EventPageController::class, 'show'])->name('public.events.show');
Route::get('/events/{event:slug}/register', [RegistrationController::class, 'show'])->name('public.registrations.show');
Route::post('/events/{event:slug}/register', [RegistrationController::class, 'store'])->middleware('throttle:10,1')->name('public.registrations.store');
Route::middleware('auth')->group(function () {
    Route::get('/events/{event:slug}/private-register', [RegistrationController::class, 'private'])->name('public.registrations.private.show');
    Route::post('/events/{event:slug}/private-register', [RegistrationController::class, 'storePrivate'])->middleware('throttle:10,1')->name('public.registrations.private.store');
});
Route::get('/registration-invites/{token}', [RegistrationController::class, 'invite'])->name('public.registrations.invite.show');
Route::post('/registration-invites/{token}', [RegistrationController::class, 'storeInvite'])->middleware('throttle:10,1')->name('public.registrations.invite.store');

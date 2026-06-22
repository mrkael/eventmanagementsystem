<?php

namespace App\Models;

use App\Enums\EventLifecycleStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'organizer_id', 'organiser_profile_id', 'event_category_id', 'event_type_id', 'venue_id', 'event_status_id',
    'event_configuration_id', 'title', 'slug', 'summary', 'description', 'starts_at',
    'ends_at', 'capacity', 'is_registration_enabled', 'is_public', 'status_key',
    'banner_path', 'submitted_at', 'published_at', 'published_page_version_id',
    'custom_url', 'location', 'logo_path', 'brand_color', 'registration_opens_at', 'registration_closes_at',
    'payment_tax_percentage', 'allow_promo_code', 'allow_duplicate_email', 'sender_name', 'sender_email',
])]
class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'submitted_at' => 'datetime',
            'published_at' => 'datetime',
            'registration_opens_at' => 'datetime',
            'registration_closes_at' => 'datetime',
            'capacity' => 'integer',
            'payment_tax_percentage' => 'decimal:2',
            'allow_promo_code' => 'boolean',
            'allow_duplicate_email' => 'boolean',
            'is_registration_enabled' => 'boolean',
            'is_public' => 'boolean',
            'status_key' => EventLifecycleStatus::class,
        ];
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function organiserProfile(): BelongsTo
    {
        return $this->belongsTo(OrganiserProfile::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(EventCategory::class, 'event_category_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(EventType::class, 'event_type_id');
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(EventStatus::class, 'event_status_id');
    }

    public function configuration(): BelongsTo
    {
        return $this->belongsTo(EventConfiguration::class, 'event_configuration_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(EventSession::class)->orderBy('sort_order')->orderBy('starts_at');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EventDocument::class);
    }

    public function pageVersions(): HasMany
    {
        return $this->hasMany(EventPageVersion::class);
    }

    public function publishedPageVersion(): BelongsTo
    {
        return $this->belongsTo(EventPageVersion::class, 'published_page_version_id');
    }

    public function registrationForm(): HasOne
    {
        return $this->hasOne(RegistrationForm::class);
    }

    public function registrationForms(): HasMany
    {
        return $this->hasMany(RegistrationForm::class);
    }

    public function participantRegistrations(): HasMany
    {
        return $this->hasMany(ParticipantRegistration::class);
    }

    public function registrationInvites(): HasMany
    {
        return $this->hasMany(RegistrationInvite::class);
    }

    public function attendanceQrTokens(): HasMany
    {
        return $this->hasMany(AttendanceQrToken::class);
    }

    public function attendanceLogs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class);
    }

    public function pages(): HasMany
    {
        return $this->hasMany(EventPage::class);
    }

    public function publishedPage(): HasOne
    {
        return $this->hasOne(EventPage::class)->where('status', 'published');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function promoCodes(): HasMany
    {
        return $this->hasMany(PromoCode::class);
    }

    public function coreRegistrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function emailTemplates(): HasMany
    {
        return $this->hasMany(EmailTemplate::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(EventReport::class);
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }
}

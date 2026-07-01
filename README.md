# EventOS — QR Management System

A Laravel 13 event management platform with QR-based check-in, organiser workspaces, and a microsite builder.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 13, PHP 8.2+ |
| Frontend | Tailwind CSS v4 (via `@tailwindcss/vite`), Vite |
| Database | MySQL 8 |
| Auth / RBAC | Custom roles & permissions (no Spatie) |
| QR scanning | `html5-qrcode` (camera), `endroid/qr-code` (generation) |
| Page builder | `hansschouten/laravel-pagebuilder` (custom-served) |
| JS utilities | TomSelect, SortableJS, EditorJS |

---

## Getting Started

```bash
# Install dependencies
composer install && npm install

# Copy environment file and configure DB credentials
cp .env.example .env
php artisan key:generate

# Create databases
# Main:    qr_management_system
# Testing: qr_management_system_testing

# Run migrations and seed demo data
php artisan migrate --seed

# Start all dev servers concurrently (Laravel + Vite + queue + log tail)
composer dev
```

Default admin credentials (after seeding):

```
Email:    admin@example.com
Password: password
```

---

## Commands

```bash
composer test                     # Run full test suite
php artisan test                  # Alternative
php artisan test --filter=name    # Single test method

vendor/bin/pint --dirty           # Lint changed files only
php artisan migrate:fresh --seed  # Reset DB with seed data
npm run build                     # Production asset build
```

---

## Architecture

### Route namespaces

Two parallel admin worlds share the same layout and RBAC:

| URL prefix | Route prefix | Purpose |
|---|---|---|
| `/admin` | `admin.` | Platform-level admin (Events, Venues, Registrations, Attendance) |
| `/admin/core` | `core.` | Organiser-scoped workspace (Dashboard, Events, Check-In, Reports, Emails) |

Public registration: `/events/{slug}/...` and `/e/{custom_url}/...`

### RBAC

- `Role` → has many `Permission` via pivot
- `User` → belongs to `Role`
- Permissions follow `resource.action` naming (e.g. `events.publish`, `attendance.scan`)
- `EnsureUserHasPermission` middleware resolves the permission string from the route definition
- `User::isPlatformAdmin()` — true for `super-admin` / `admin` roles

### Service layer (`app/Services/`)

| Service | Responsibility |
|---|---|
| `Core/` | Organiser profiles, login access |
| `Events/` | Event lifecycle, microsite/page builder |
| `EventSetup/` | Categories, types, venues, statuses |
| `Registrations/` | Form builder, registration processing |
| `Attendance/` | QR check-in/check-out, attendance records |
| `QrCodeImageService` | Wraps `endroid/qr-code` to generate QR PNGs |
| `AuditLogger` | Writes to `audit_logs` for all significant actions |

### Key model relationships

- `Event` → belongs to `OrganiserProfile`, `EventCategory`, `EventType`, `Venue`, `EventStatus`
- `Event` → has many `Registration`, `Ticket`, `PromoCode`, `EventSession`, `EventAgenda`
- `Event` → has one `EventPage` with versioned `EventPageSection` / `EventPageVersion`
- `Registration` → has many `RegistrationAnswer`, `AttendanceRecord`, `AttendanceLog`
- `Event` uses `EventLifecycleStatus` enum: `draft → submitted → published`

### Design system

Tokens defined in `resources/css/app.css`:

| Token | Usage |
|---|---|
| `ds-button-primary` | Primary CTA (brand fill `#002169`) |
| `ds-button-secondary` | Outline action button |
| `ds-input` | Form inputs and selects |
| `ds-label` | Form field labels |
| `ds-card` | Surface card with border + shadow |
| `ds-page-enter` | 280ms fade + slide-up on page load |
| `ds-page-exit` | 200ms fade + slide-up-out on tab navigation |

---

## Modules

### Dashboard (`/admin/core/dashboard`)

Organiser-scoped overview. Displays:
- **KPI cards** — total registrations, this week's registrations, total check-ins, today's check-ins for a selected event
- **Event selector** — dropdown to switch the event context; KPIs update via AJAX (`GET /admin/core/dashboard/stats`)
- **Live Now banner** — shown when the selected event is running today; links directly to the check-in scanner
- **Events list** — all organiser events with registration counts
- **Recent Registrations** — last 10 registrations for the selected event

Platform admins see all events across all organisers.

### Events workspace (`/admin/core/events`)

Listing columns: No., Event Name, Event Date, Status, Open Details. Pagination at 12 per page with search and status filter.

**Event detail tab navigation** (animated glider):

| Tab | Purpose |
|---|---|
| Settings | Edit core event fields |
| Tickets | Manage ticket types |
| Forms | Registration form builder |
| Site | Drag-and-drop microsite builder |
| Email | Confirmation email template |
| Attendees | Registrant list and exports |
| Agenda | Session and agenda management |
| Check-In | Live QR check-in console |

Tab transitions use an absolutely-positioned glider pill with `cubic-bezier(0.37, 1.95, 0.66, 0.56)` bounce (380ms) paired with a `ds-page-exit` content fade, making navigation feel seamless at ~1200ms server response times.

### QR Check-In

Two parallel check-in systems:

**Core check-in** (`/admin/core/events/{event}/check-in`)
- Live participant table, updates on each scan
- Stores `AttendanceRecord` with optional GPS coordinates and resolved street name

**Attendance module** (`/admin/events/{event}/attendance`)
- Camera scanner (`html5-qrcode`) + manual token entry
- Stores `AttendanceLog` with optional GPS coordinates and resolved street name

**GPS location capture (both systems):**
- Browser Web Geolocation API with `enableHighAccuracy: true` — free, no API key; requires HTTPS or localhost
- Street/location name resolved via Nominatim OpenStreetMap reverse geocoding at `zoom=18` (building/street level) — free, no API key
- Non-blocking: check-in completes even if the user denies location permission or GPS is unavailable

### Microsite / Page builder

Each event can have a public microsite built with a drag-and-drop section editor. `hansschouten/laravel-pagebuilder` is excluded from auto-discovery; assets served via `/pagebuilder-assets/...`.

---

## Database

### Testing database

Tests run against a separate MySQL database (not SQLite). Create it before running tests:

```sql
CREATE DATABASE qr_management_system_testing
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Seeding order

1. `AccessControlSeeder` — admin user, roles, permissions
2. `EventMasterSetupSeeder` — categories, types, venues, statuses
3. `SampleEventSeeder` — demo event with registrations

---

## Changelog

### 2026-07-01
- GPS location capture on check-in — lat/lng + Nominatim street name stored on `attendance_records` and `attendance_logs`
- Two new migrations: `add_location_to_attendance_tables`, `add_location_name_to_attendance_tables`
- Organiser-scoped dashboard with AJAX KPI cards, event selector, live-now banner, and recent registrations feed
- New `DashboardController` with `/dashboard/stats` JSON endpoint; replaces `FoundationController::dashboard`
- Admin layout: removed sticky page-title header bar; floating mobile hamburger button replaces it
- Events listing: refined to 5 columns (No., Event Name, Event Date, Status, Action) with elastic Event Name column, `ds-button-secondary` action button, and pagination inside the card
- Event detail tab nav: brand-primary (`#002169`) glider with cubic-bezier bounce transition; `ds-page-exit` animation synced to navigation; tab text colour swapped via `classList` on click to prevent invisible-text regression
- Status badges colour-coded per lifecycle state: published (emerald), submitted (blue), draft (slate)

### Prior releases
- Multi-ticket registration, premium sidebar, pagination, email fixes, dev config
- Referral tracking, site builder bug fixes, organiser publish access
- Event management modules: Settings, Tickets, Forms, Site, Email, Attendees, Agenda, Check-In
- Core event management foundation: RBAC, QR generation, QR scanning, registration flow

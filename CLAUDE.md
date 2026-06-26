# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
# Install all dependencies
composer install && npm install

# Run dev servers (Laravel + Vite + queue + log tail, all concurrently)
composer dev

# Build frontend assets
npm run build

# Run all tests
composer test
# or
php artisan test

# Run a single test file
php artisan test tests/Feature/EventManagementTest.php

# Run a specific test method
php artisan test --filter=test_method_name

# Lint (only dirty files)
vendor/bin/pint --dirty

# Fresh database with seed data
php artisan migrate:fresh --seed
```

## Testing database

Tests run against a separate MySQL database: `qr_management_system_testing` (not SQLite). Create it before running tests:

```sql
CREATE DATABASE qr_management_system_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

## Architecture

**Laravel 13** app with a custom RBAC system (no Spatie/permissions package). Every route carries a `permission:` middleware string that is checked by `EnsureUserHasPermission` middleware against the `permissions` and pivot tables.

### Route namespaces

There are two parallel admin "worlds":

| Prefix | Route name prefix | Purpose |
|--------|-------------------|---------|
| `/admin` | `admin.` | Traditional multi-organiser admin (Events, Venues, Registrations, Attendance) |
| `/admin/core` | `core.` | Organiser-scoped "core" flow (Organiser Profiles, Contacts, Events, Check-In, Reports, Emails) |

Public registration lives under `/events/{slug}/...` and `/e/{custom_url}/...` (two separate public flows).

### Service layer

Business logic lives in `app/Services/` mirroring the controller namespaces:

- `Core/` — organiser profiles, login access
- `Events/` — event lifecycle, microsite/page builder
- `EventSetup/` — event master usage (categories, types, venues, statuses)
- `Registrations/` — registration form builder, registration processing
- `Attendance/` — QR check-in/check-out, attendance records
- `QrCodeImageService` — wraps `endroid/qr-code` to generate QR PNGs
- `AuditLogger` — writes to `audit_logs` table for all significant actions

### Key model relationships

- `Event` → belongs to `OrganiserProfile`, `EventCategory`, `EventType`, `Venue`, `EventStatus`
- `Event` → has many `RegistrationForm`, `Ticket`, `PromoCode`, `EventSession`, `EventAgenda`, `Registration`
- `Event` → has one `EventPage` (microsite) with versioned `EventPageSection`/`EventPageVersion`
- `Registration` → has many `RegistrationAnswer`, `AttendanceRecord`, `AttendanceLog`
- `Event` uses `EventLifecycleStatus` enum for `status_key` (draft → submitted → published)

### Frontend

- **Tailwind CSS v4** via `@tailwindcss/vite` plugin (no `tailwind.config.js`; config lives in CSS)
- **Vite** bundles `resources/js/app.js` + `resources/css/app.css`
- JS dependencies: `html5-qrcode` (camera QR scanner), `qrcode` (client-side QR generation), `tom-select` (dropdowns), `sortablejs` (drag-sort), `@editorjs/editorjs` (rich text)
- `hansschouten/laravel-pagebuilder` is manually excluded from auto-discovery and served via a custom route (`/pagebuilder-assets/...`)

### RBAC

- `Role` → has many `Permission` via pivot
- `User` → belongs to `Role`
- Permissions follow `resource.action` naming (e.g. `events.publish`, `attendance.scan`)
- The `EnsureUserHasPermission` middleware resolves the permission string directly from the route middleware definition

### Seeding

`DatabaseSeeder` calls three seeders in order:
1. `AccessControlSeeder` — creates admin user (`admin@example.com` / `password`), roles, permissions
2. `EventMasterSetupSeeder` — populates categories, types, venues, statuses
3. `SampleEventSeeder` — creates a demo event with registrations

### QR check-in flow

Browser camera (via `html5-qrcode`) scans a QR code containing a signed token → POST to `/admin/core/events/{event}/check-in/scan` or `/admin/events/{event}/attendance/check-in` → `AttendanceService`/`CoreAttendanceService` validates token, logs to `attendance_logs` and upserts `attendance_records`. Requires HTTPS or localhost (browser camera restriction).

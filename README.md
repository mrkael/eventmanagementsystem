# QR Management System

Laravel event management system inspired by the Eevee/Evenesis starter flow.

## Core Modules

- Organiser Profile
- Team Management and roles
- Contacts, Groups, and Master List
- Event setup and microsite CMS
- Registration form builder
- Tickets and promo codes
- Public registration with QR e-ticket
- Confirmation email templates
- Attendee management
- Session check-in/check-out using browser camera QR scanner
- Event reports and CSV exports

## Local Requirements

- PHP 8.3+
- Composer
- Node.js and npm
- MySQL 8 or MariaDB via Laragon
- Git

## Fresh Local Setup

Clone the repository:

```bash
git clone <your-repository-url> QRManagementSystem
cd QRManagementSystem
```

Install dependencies:

```bash
composer install
npm install
```

Create the environment file:

```bash
copy .env.example .env
php artisan key:generate
```

Create a MySQL database named:

```sql
CREATE DATABASE qr_management_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Update `.env` if needed:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=qr_management_system
DB_USERNAME=root
DB_PASSWORD=
```

Run migrations and seed the admin account:

```bash
php artisan migrate:fresh --seed
```

Create the storage symlink:

```bash
php artisan storage:link
```

Build frontend assets:

```bash
npm run build
```

Start the app:

```bash
php artisan serve
```

Default login after seeding:

```text
Email: admin@example.com
Password: password
```

## Development Checks

```bash
php artisan test
npm run build
vendor\bin\pint --dirty
```

## Notes

- The project is configured for local MySQL, not SQLite.
- `.env`, `vendor`, `node_modules`, built assets, storage symlinks, and SQLite files are intentionally ignored by Git.
- Browser camera QR scanning requires HTTPS or localhost/127.0.0.1 in modern browsers.

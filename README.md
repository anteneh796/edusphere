# EduSphere School Management System

EduSphere is a Laravel-based school management system designed for a **single school serving KG through Grade 8**.

## Product scope

EduSphere covers the operational areas needed to run the school:

- Student management
- Academic/class management
- Admissions
- Attendance
- Examinations and report cards
- Teacher and staff management
- Human resources
- Parent portal
- Student portal
- Teacher portal
- Notifications and communication
- Documents and official records
- School administration
- Public school website/CMS
- Reports, audit, security and system administration

### Explicitly out of scope

The following are intentionally removed from EduSphere:

- Finance portal
- Hostel management
- Library management
- Inventory management
- Multi-school/tenant architecture
- Standalone Guardian role or portal

Legacy Guardian database/model names may remain internally where required for backward compatibility. User-facing family functionality is **Parent**.

## Technology

- Laravel 12
- PHP 8.3+
- MySQL 8+
- Blade
- Alpine.js
- Tailwind CSS
- Vite
- Laravel Sanctum where API authentication is required
- PHPUnit/Pest feature testing
- GitHub Actions CI

## Development

Install dependencies:

```powershell
composer install
npm install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
php artisan serve
```

Run the test suite:

```powershell
php artisan test
```

Build frontend assets:

```powershell
npm run build
```

## Architecture

The application is organized by domain under `app/Domains`, with shared support code under `app/Support`.

Core domains include:

- Academics
- Students
- Admissions
- Attendance
- Exams
- Human Resources
- Accounts
- Notifications
- CMS
- Reports
- Portals
- Settings
- Security/Audit

Authorization is permission-driven for staff modules and role-driven for the Student, Parent, and Teacher portals.

## Quality gates

Before a change is considered complete:

1. Routes reference existing controllers and methods.
2. Route permissions exist in the RBAC source of truth.
3. Navigation routes exist.
4. Roles remain synchronized between enum and configuration.
5. Removed modules do not regain registered routes.
6. Feature tests cover authorization and important lifecycle rules.
7. Frontend assets build successfully.
8. The complete test suite passes.

See `docs/SPRINT-7-COMPLETION.md` for the current product completion scope and quality gates.

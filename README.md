# Alumni Influencers

CodeIgniter 3 coursework application for alumni profile management, blind bidding, API key administration, and a public featured-alumnus API.

## Tech Stack

- PHP 8.1 on XAMPP
- CodeIgniter 3
- MySQL / MariaDB
- Swagger UI for API documentation

## What This Project Covers

- university-email authentication and account verification
- API-driven profile management with secure media upload
- education, certification, licence, course, and employment records
- blind bidding with automated featured alumnus selection
- API key lifecycle management and usage logging
- public developer API plus OpenAPI/Swagger docs
- coursework 2 analytics dashboard for alumni insights

## Quick Start

1. Place the project in `xampp/htdocs/alumni-influencers`.
2. Start Apache and MySQL in XAMPP.
3. Create a `.env` file from `.env.example` and set:
   - `APP_BASE_URL`
   - database credentials
   - `APP_ENCRYPTION_KEY`
4. Import `database/schema.sql` into MySQL.
5. Make sure the `ci_sessions` table is present from the schema.
6. Open the application at `http://localhost/alumni-influencers/`.

## Main URLs

- Health check: `/ping`
- Public featured profile: `/api/featured-today`
- Profile API:
  - `/api/profile`
  - `/api/profile/basic`
  - `/api/profile/save-basic`
  - `/api/profile/degrees`
  - `/api/profile/degrees/add`
  - `/api/profile/degrees/update/{id}`
  - `/api/profile/degrees/delete/{id}`
  - `/api/profile/certifications`
  - `/api/profile/licences`
  - `/api/profile/courses`
  - `/api/profile/employment`
- Bidding:
  - `/bids/store`
  - `/bids/status`
  - `/bids/history`
  - `/bids/run-daily-winner`
- Admin:
  - `/admin/api_keys`
  - `/admin/create_api_key`
  - `/admin/revoke_api_key/{id}`
  - `/admin/usage_logs`
- Dashboard:
  - `/dashboard`
  - `/dashboard/graphs`
  - `/dashboard/alumni`
- API docs:
  - `/api-docs`
  - `/api-docs/openapi.yaml`

## Documentation

- OpenAPI spec: `docs/openapi.yaml`
- Application guide: `docs/APPLICATION_GUIDE.md`

## Security Highlights

- password hashing with `password_hash()` and `password_verify()`
- verification and reset tokens stored as SHA-256 hashes only
- CSRF protection enabled
- session hardening and regeneration
- rate limiting for login, reset, register, and public API endpoints
- security headers applied through CodeIgniter hooks
- API key hash-only storage, revocation, and usage audit logging

## Notes for Assessors

- Controllers orchestrate request flow; models contain data and business rules.
- Swagger UI is the retained server-rendered surface for API documentation.
- Critical security and business events are written through CodeIgniter `log_message()`.

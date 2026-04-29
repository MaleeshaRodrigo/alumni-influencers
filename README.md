# Alumni Influencers (CodeIgniter 3 Coursework)

PHP + CodeIgniter 3 + MySQL coursework application covering:
- university-email authentication and account verification,
- API-based profile management and secure media upload,
- blind bidding + automated featured alumnus selection,
- API key lifecycle + usage logging,
- public developer API + Swagger/OpenAPI docs.

## Tech Stack

- PHP (XAMPP runtime)
- CodeIgniter 3
- MySQL / MariaDB
- Swagger UI (CDN-hosted) for API documentation

## Quick Start (Local XAMPP)

1. Place project in `xampp/htdocs/alumni-influencers`.
2. Start Apache and MySQL from XAMPP.
3. Create `.env` from `.env.example` and set:
   - `APP_BASE_URL`
   - DB credentials
   - `APP_ENCRYPTION_KEY`
4. Import schema:
  - `database/schema.sql`
5. Ensure `ci_sessions` table exists (included in schema).
6. Open app:
   - `http://localhost/alumni-influencers/`

## Main URLs

- Health:
  - `/ping`
- Public API:
  - `/api/featured-today`
- Profile API:
  - `/api/profile`
  - `/api/profile/basic`
  - `/api/profile/save-basic`
  - `/api/profile/degrees`
  - `/api/profile/certifications`
  - `/api/profile/licences`
  - `/api/profile/courses`
  - `/api/profile/employment`
- Bidding API:
  - `/bids/store`
  - `/bids/status`
  - `/bids/history`
- Admin API:
  - `/admin/api_keys`
  - `/admin/create_api_key`
  - `/admin/revoke_api_key/{id}`
  - `/admin/usage_logs`
- Swagger UI:
  - `/api-docs`
- OpenAPI spec:
  - `/api-docs/openapi.yaml`
- Winner automation:
  - `/bids/run-daily-winner`

## Documentation Index

- OpenAPI spec: `docs/openapi.yaml`

## Security Highlights

- Password hashing via `password_hash()` / `password_verify()`
- Verification and reset tokens stored as SHA-256 hashes only
- CSRF protection enabled
- Session hardening and regeneration
- Rate limiting for login/reset/register/public API
- Security headers via CI hooks
- API key hash-only storage + revocation + usage audit logging

## Notes for Assessors

- This project intentionally keeps CI3 MVC layering clear:
  - controllers orchestrate flow,
  - models hold data/business rules,
  - Swagger UI is the only retained server-rendered view for API docs.
- Logs are written through CodeIgniter `log_message()` for critical security and business events.

# Alumni Influencers (CodeIgniter 3 Coursework)

PHP + CodeIgniter 3 + MySQL coursework application covering:
- university-email authentication and account verification,
- profile management and secure media upload,
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

- App home: `/`
- Auth:
  - `/register`
  - `/auth/login`
- Profile dashboard: `/profile/dashboard`
- Blind bidding:
  - `/bids/place`
  - `/bids/status`
- Public API:
  - `/api/featured-today`
- Swagger UI:
  - `/api-docs`
- OpenAPI spec:
  - `/api-docs/openapi.yaml`

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
  - views remain presentation-focused.
- Logs are written through CodeIgniter `log_message()` for critical security and business events.

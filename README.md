# Alumni Influencers — Coursework Project

## Overview

This repository contains the CodeIgniter-based web application developed for the coursework assignment "Alumni Influencers". The application demonstrates a small MVC web app built using the CodeIgniter framework, focused on routing, controllers, views, configuration, and integration with PHP components such as caching and database access.

## Coursework Goal

The objective of the coursework was to design and implement a simple, well-structured server-side web application that follows MVC principles, applies framework configuration best practices, and demonstrates knowledge of routing, controllers, models, views, and basic performance/security considerations.

## How I achieved this

- Used the CodeIgniter framework to enforce MVC separation and speed development.
- Implemented routing and controller logic under `application/controllers/` to handle site endpoints.
- Kept presentation in `application/views/` and configuration in `application/config/`.
- Connected the app to a database via `application/config/database.php` and used the framework's DB utilities.
- Added performance features like caching (see `application/config/memcached.php`) and profiling (`application/config/profiler.php`).
- Followed security best practices available in CodeIgniter: input filtering, CSRF/validation hooks, and secure config defaults.

## Architecture (high level)

- Framework: CodeIgniter (lightweight MVC PHP framework).
- Layout: standard CodeIgniter structure with `application/` (app code) and `system/` (framework core).
- MVC: Controllers live in `application/controllers/`, models in `application/models/`, views in `application/views/`.
- Config: All environment- and framework-level settings live in `application/config/`.

Key files and locations

- Front controller: [index.php](index.php)
- Application config: [application/config/config.php](application/config/config.php)
- Database config: [application/config/database.php](application/config/database.php)
- Routes: [application/config/routes.php](application/config/routes.php)
- Example controller: [application/controllers/Welcome.php](application/controllers/Welcome.php)
- Example view: [application/views/welcome_message.php](application/views/welcome_message.php)
- Composer manifest: [composer.json](composer.json)

## Setup & Installation

Requirements:

- PHP 7.2+ (or the version required by the provided CodeIgniter in `system/`).
- A web server (Apache, Nginx) or PHP built-in server for development.
- Composer (optional) to manage any additional dependencies declared in `composer.json`.

Quick start (development):

1. Install dependencies (if any):

```bash
composer install
```

2. Configure the application:

- Copy and update `application/config/config.php` to set `base_url` and other environment settings.
- Edit `application/config/database.php` to configure your database connection.
- Ensure `application/cache/` and `application/logs/` are writable by the web server.

3. Run locally using PHP built-in server (from the project root):

```bash
php -S localhost:8000
```

Then open http://localhost:8000/ in your browser.

Note: For production use, configure a proper virtual host in Apache/Nginx and set production-safe PHP settings.

## Development Notes

- Caching: memcached configuration is located at `application/config/memcached.php` and can be enabled for improved performance.
- Migrations: check `application/config/migration.php` for migration settings.
- Hooks: application-level hooks are configured in `application/config/hooks.php`.
- Profiling and debugging: `application/config/profiler.php` controls the built-in profiler.

## Testing & Verification

There are no automated tests included by default; manual verification steps:

- Start the server and browse to the default route to confirm the `Welcome` controller and view render correctly.
- Check database connectivity by exercising any data-driven endpoints (ensure `application/config/database.php` is correct).

## Deployment

- Use a supported web server and PHP version. Copy the project to your server, configure document root to point to the project root (or a public folder if you create one).
- Secure `application/config/` settings and ensure directories for caches and logs are writable only by the web server user.

## License

This project includes a `license.txt` in the repository root. See that file for licensing details.

## Contact / Course Submission

If this README is part of a coursework submission, include your student details, assignment brief, and any additional notes requested by the instructor here.

# Alumni Influencers Project

This repository is prepared for a PHP + CodeIgniter 3 + MySQL setup on XAMPP with strict MVC structure.

## Step 1 Scope Completed

- Project foundation and configuration
- Local XAMPP-ready CodeIgniter configuration
- Database-backed sessions
- Base controller and starter layout
- Home page route and health check endpoint

## Recommended Project Structure

```text
alumni-influencers/
├── application/
│   ├── config/
│   │   ├── autoload.php
│   │   ├── config.php
│   │   ├── database.php
│   │   └── routes.php
│   ├── controllers/
│   │   └── Home.php
│   ├── core/
│   │   └── MY_Controller.php
│   ├── models/
│   │   ├── Auth_model.php            (future)
│   │   ├── User_model.php            (future)
│   │   ├── Bid_model.php             (future)
│   │   └── ApiKey_model.php          (future)
│   ├── views/
│   │   ├── home/
│   │   │   └── index.php
│   │   └── layouts/
│   │       ├── footer.php
│   │       └── header.php
│   ├── logs/
│   └── helpers/                      (future shared helpers)
├── system/
├── .env                              (local secrets, ignored)
├── .env.example
├── index.php
└── README.md
```

## Local XAMPP Setup

1. Put this project under your XAMPP `htdocs` directory.
2. Start **Apache** and **MySQL** from XAMPP Control Panel.
3. Create your environment file:
   - Copy `.env.example` to `.env`
   - Update values if needed (especially `APP_BASE_URL`, DB values, and `APP_ENCRYPTION_KEY`)
4. Create database:
   - DB name from `.env` (default: `alumni-influencers`)
5. Create session table (`ci_sessions`) using SQL below.
6. Open in browser:
   - `http://localhost/alumni-influencers/`

## Session Table SQL (Required)

```sql
CREATE TABLE IF NOT EXISTS `ci_sessions` (
  `id` varchar(128) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `timestamp` int(10) unsigned NOT NULL DEFAULT 0,
  `data` blob NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ci_sessions_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

## Routes Available

- `/` -> `Home::index`
- `/home` -> `Home::index`
- `/ping` -> JSON health check

## Notes

- Strict MVC is maintained:
  - Controllers stay thin
  - Business/data logic belongs in models (to be added in next steps)
  - Views remain simple presentation templates
- `.env` values are loaded at runtime in `index.php`
- Logging is enabled through `LOG_THRESHOLD` (default 1)

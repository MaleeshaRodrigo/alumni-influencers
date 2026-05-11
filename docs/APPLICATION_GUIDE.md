# Alumni Influencers Application Guide

**Last Updated**: April 30, 2026  
**Framework**: CodeIgniter 3 (PHP MVC)  
**Database**: MySQL/MariaDB  
**Purpose**: Alumni profile platform with university analytics dashboard

---

## Table of Contents

1. [Application Overview](#application-overview)
2. [Architecture](#architecture)
3. [Database Structure](#database-structure)
4. [Core Features](#core-features)
5. [API Security & Scoping](#api-security--scoping)
6. [Request Flow](#request-flow)
7. [Controllers & Models](#controllers--models)
8. [User Journeys](#user-journeys)
9. [Configuration](#configuration)
10. [Security Highlights](#security-highlights)

---

## Application Overview

The Alumni Influencers application is a two-part system:

### **Part 1: Alumni Profile Platform**
- Alumni create and maintain detailed professional profiles
- Track education, certifications, licenses, courses, and employment history
- Participate in blind bidding system for featured profile placement
- Public profiles showcase alumni achievements

### **Part 2: University Analytics Dashboard** (Coursework 2)
- Real-time insights into graduate outcomes
- Identify skills gaps vs. curriculum
- Track emerging career pathways
- Monitor professional development trends
- Analyze industry distribution and geographic spread
- Generate data-driven insights for curriculum development

---

## Architecture

### High-Level Design

```
┌─────────────────────────────────────────────────────────────┐
│                    Frontend Layer                           │
│  (HTML/CSS/JS Views - Dashboard, Graphs, Alumni List)      │
└────────────┬────────────────────────────────┬───────────────┘
             │                                │
             ▼                                ▼
┌──────────────────────┐        ┌────────────────────────┐
│  Dashboard           │        │  Analytics API         │
│  Controllers         │        │  Controllers           │
│  - Home              │        │  - AnalyticsApi        │
│  - Dashboard         │        │  - PublicApi           │
│  - Auth              │        │  - Bids                │
│  - ProfileApi        │        │  - Admin               │
└──────────┬───────────┘        └──────────┬─────────────┘
           │                              │
           └──────────────┬───────────────┘
                          ▼
        ┌─────────────────────────────────────┐
        │      Libraries & Middleware         │
        │  - BearerTokenAuth (API Key check)  │
        │  - RateLimiter (Rate limiting)      │
        │  - Security Headers Hook            │
        │  - CSRF Protection                  │
        └─────────────┬───────────────────────┘
                      ▼
        ┌─────────────────────────────────────┐
        │         Models (Business Logic)     │
        │  - User_model                       │
        │  - Profile_model                    │
        │  - Analytics_model                  │
        │  - ApiKey_model                     │
        │  - Bid_model                        │
        │  - UsageLog_model                   │
        └─────────────┬───────────────────────┘
                      ▼
        ┌─────────────────────────────────────┐
        │     MySQL/MariaDB Database          │
        │  - users, profiles, degrees, etc.   │
        │  - api_keys, api_usage_logs         │
        │  - bids, featured_alumni            │
        └─────────────────────────────────────┘
```

---

## Database Structure

### Core Tables

#### **users** - Authentication & Account Security
```sql
CREATE TABLE users (
  id BIGINT PRIMARY KEY,
  email VARCHAR(255) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','alumni','viewer') DEFAULT 'alumni',
  status ENUM('pending_verification','active','suspended','deleted'),
  email_verified_at DATETIME,
  email_verify_token_hash CHAR(64),
  password_reset_token_hash CHAR(64),
  failed_login_count TINYINT DEFAULT 0,
  locked_until DATETIME,
  last_login_at DATETIME,
  created_at DATETIME,
  updated_at DATETIME
);
```

**Key Concepts**:
- Passwords stored as bcrypt hashes only
- Verification & reset tokens stored as SHA-256 hashes
- Account lockout after failed login attempts
- Status tracks account lifecycle

#### **profiles** - Public Alumni CV Data (1:1 with users)
```sql
CREATE TABLE profiles (
  id BIGINT PRIMARY KEY,
  user_id BIGINT UNIQUE NOT NULL,
  display_name VARCHAR(150) NOT NULL,
  headline VARCHAR(255),
  bio TEXT,
  photo_path VARCHAR(512),
  linkedin_url VARCHAR(512),
  is_public TINYINT DEFAULT 1,
  created_at DATETIME,
  updated_at DATETIME,
  FOREIGN KEY (user_id) REFERENCES users(id)
);
```

#### **Repeatable Profile Sections** (Many:1 with profiles)
- **degrees**: University qualifications (institution, field_of_study, graduation date)
- **certifications**: Professional certifications (name, issuer, date, expiry)
- **licences**: Professional licenses (title, jurisdiction, validity)
- **short_courses**: Training courses completed (title, provider, hours)
- **employment_history**: Job positions (employer, job_title, dates, current flag)

All support `sort_order` for custom ordering and `created_at`/`updated_at` for tracking.

#### **bids** - Blind Bidding System
```sql
CREATE TABLE bids (
  id BIGINT PRIMARY KEY,
  user_id BIGINT NOT NULL,
  cycle_id INT NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  currency CHAR(3) DEFAULT 'GBP',
  status ENUM('draft','submitted','withdrawn','won','lost','disqualified'),
  submitted_at DATETIME,
  revealed_at DATETIME,
  admin_notes VARCHAR(512),
  created_at DATETIME,
  updated_at DATETIME,
  UNIQUE KEY (user_id, cycle_id),
  FOREIGN KEY (user_id) REFERENCES users(id)
);
```

**Key Concepts**:
- One bid per user per cycle (bidding round)
- Bids are "blind" - amounts hidden from other bidders
- Status tracks bid lifecycle
- cycle_id groups bids into monthly rounds

#### **featured_alumni** - Homepage Spotlight Rows
```sql
CREATE TABLE featured_alumni (
  id BIGINT PRIMARY KEY,
  profile_id BIGINT NOT NULL,
  cycle_id INT NOT NULL,
  winning_bid_id BIGINT,
  featured_from DATETIME NOT NULL,
  featured_until DATETIME NOT NULL,
  sort_order SMALLINT DEFAULT 0,
  is_active TINYINT DEFAULT 1,
  created_at DATETIME,
  updated_at DATETIME,
  FOREIGN KEY (profile_id) REFERENCES profiles(id),
  FOREIGN KEY (winning_bid_id) REFERENCES bids(id)
);
```

**Key Concepts**:
- Records which alumni is featured and for how long
- Optional link to winning bid (paid feature)
- sort_order for display priority

#### **api_keys** - Scoped API Access Tokens
```sql
CREATE TABLE api_keys (
  id BIGINT PRIMARY KEY,
  user_id BIGINT NOT NULL,
  name VARCHAR(100) NOT NULL,
  key_prefix VARCHAR(16) NOT NULL,
  key_hash CHAR(64) NOT NULL UNIQUE,
  scopes VARCHAR(512) DEFAULT '',
  is_revoked TINYINT DEFAULT 0,
  revoked_at DATETIME,
  revoked_reason VARCHAR(255),
  expires_at DATETIME,
  last_used_at DATETIME,
  created_at DATETIME,
  updated_at DATETIME,
  FOREIGN KEY (user_id) REFERENCES users(id)
);
```

**Key Concepts**:
- `key_prefix`: First 16 chars of key (for UI display, safe to show)
- `key_hash`: Full key hashed with SHA-256 (never expose full key)
- `scopes`: Comma-separated permissions (e.g., "read:alumni,read:analytics")
- Revocation tracking for audit purposes

#### **api_usage_logs** - Request Audit Trail
```sql
CREATE TABLE api_usage_logs (
  id BIGINT PRIMARY KEY,
  api_key_id BIGINT,
  route VARCHAR(255) NOT NULL,
  http_method VARCHAR(10) NOT NULL,
  ip_address VARCHAR(45) NOT NULL,
  user_agent VARCHAR(512),
  response_code SMALLINT NOT NULL,
  duration_ms INT,
  created_at DATETIME,
  FOREIGN KEY (api_key_id) REFERENCES api_keys(id)
);
```

**Key Concepts**:
- Comprehensive audit trail of all API usage
- Tracks response time (duration_ms)
- Null api_key_id for public endpoints
- Enable-ability to audit security events

---

## Core Features

### 1. Registration & Authentication

#### Registration Flow
```
1. User enters email + password + name
2. Input validation
   - Email format validation
   - Email must be from university domain
   - Password strength validation (min 8 chars, complexity)
3. Check if email already registered
4. Hash password with bcrypt (10+ rounds)
5. Create user with status = 'pending_verification'
6. Generate verification token (random 32 bytes)
7. Store token hash (SHA-256) in db
8. Send verification email with token link
9. User clicks link: /auth/verify-email/{token}
10. Server hashes received token, matches against db
11. If valid: update status to 'active'
12. User can now login
```

#### Login Flow
```
1. User enters email + password
2. Fetch user record by email
3. If not found → return error
4. If account status != 'active' → return error
5. If locked_until > NOW → return "too many attempts"
6. Compare submitted password with password_hash
7. If match → create session, update last_login_at
8. If no match → increment failed_login_count, check lockout
```

### 2. Profile Management

Alumni can maintain detailed professional profiles:

- **Basic Info**: Display name, headline, bio, photo, LinkedIn URL
- **Education**: Multiple degrees with field of study, grades, dates
- **Certifications**: Professional certifications with issuer and expiry
- **Licenses**: Professional licenses with jurisdiction and validity
- **Courses**: Short courses and training completed
- **Employment**: Current and past jobs with descriptions

All changes are tracked with `updated_at` timestamps.

### 3. Blind Bidding System

#### How It Works
```
1. Alumni visits /bids/place (requires login)
2. Enters bid amount (e.g., £50)
3. System checks: Is this their 4th bid this month?
   - If YES → reject (limit is 3 per month)
   - If NO → proceed
4. Bid stored with status = 'submitted'
5. Amount is SECRET - not shown in UI to other users
6. Alumni can see their own bid status and amount
7. At midnight, automated task runs: /bids/run-daily-winner
   - Finds highest bid for current cycle
   - Updates status to 'won' or 'lost'
   - Creates featured_alumni record for winner
   - Winner's profile featured on homepage for next 24 hours
```

#### Bid Status States
- **draft**: Bid created but not submitted
- **submitted**: Bid placed, waiting for midnight selection
- **won**: Bid was highest, profile featured
- **lost**: Bid was not highest
- **withdrawn**: User cancelled bid
- **disqualified**: Admin marked as invalid

### 4. Analytics Dashboard

Real-time insights into graduate outcomes:

#### Alumni Distribution
- By program/degree field
- By graduation year
- By industry/employer sector

```
Example Output:
{
  by_degree: [
    { programme: "Computer Science", count: 87 },
    { programme: "Business Management", count: 56 }
  ],
  by_year: [
    { year: 2023, count: 92 },
    { year: 2022, count: 78 }
  ],
  by_industry: [
    { sector: "Technology", count: 45 },
    { sector: "Finance", count: 32 }
  ]
}
```

#### Skills Gap Detection
Identifies certifications acquired AFTER graduation:
```
Example Output:
{
  data: [
    { skill: "Docker/Kubernetes", count: 63 },  // 73% of CS alumni
    { skill: "AWS Solutions Architect", count: 58 },
    { skill: "Python Advanced", count: 45 }
  ]
}

Interpretation: CS curriculum missing containerization skills
```

#### Career Pathways
Maps degree → current job titles:
```
Example Output:
{
  data: [
    { degree: "Business Management", job_title: "Data Analyst", count: 12 },
    { degree: "Business Management", job_title: "Product Manager", count: 8 },
    { degree: "CS", job_title: "Software Engineer", count: 34 }
  ]
}

Interpretation: 23% of Business grads now in Data Analytics (emerging path)
```

#### Professional Development Trends
```
Certification Trends:
{
  year: 2026,
  certification: "AWS Certified Solutions Architect",
  count: 15  // 156% increase from 6 months ago
}

Top Short Courses:
{
  title: "Agile/Scrum Certification",
  count: 89  // 31% of all graduates
}
```

### 5. API Key Management

Admins can create, revoke, and monitor API keys:

#### Create API Key
```
POST /admin/create_api_key
{
  name: "Analytics Dashboard v1",
  scopes: ["read:alumni", "read:analytics"],
  expires_at: "2027-12-31"
}

Response:
{
  id: 1,
  name: "Analytics Dashboard v1",
  key_prefix: "algo_",
  key: "algo_abc123xyz789...",  // Shown ONLY once
  scopes: ["read:alumni", "read:analytics"],
  created_at: "2026-04-30T12:00:00Z"
}
```

Note: Full key shown only once. Store it securely. If lost, must revoke and create new.

#### Revoke API Key
```
POST /admin/revoke_api_key/{id}
{
  reason: "Quarterly rotation"
}

Response:
{
  ok: true,
  revoked_at: "2026-04-30T15:00:00Z"
}
```

### 6. Usage Logging & Audit Trail

Every API call logged:
```sql
INSERT INTO api_usage_logs (
  api_key_id,
  route,
  http_method,
  ip_address,
  user_agent,
  response_code,
  duration_ms,
  created_at
) VALUES (1, '/api/analytics/alumni_distribution', 'GET', '192.168.1.1', '...', 200, 45, NOW());
```

Enables:
- Security audit: Who accessed what and when?
- Performance monitoring: Which endpoints are slow?
- Quota enforcement: How many API calls used this month?

---

## API Security & Scoping

### Bearer Token Authentication

All analytics and protected endpoints require bearer token (API key):

```bash
curl -H "Authorization: Bearer algo_abc123xyz789" \
  http://localhost/alumni-influencers/api/analytics/alumni_distribution
```

### Scope-Based Access Control

Different clients get different permissions:

#### Analytics Dashboard Client
```
Scopes: ["read:alumni", "read:analytics"]

Allowed Endpoints:
  ✓ GET /api/analytics/alumni_distribution
  ✓ GET /api/analytics/skills_gap
  ✓ GET /api/analytics/career_pathways
  ✓ GET /api/analytics/trends
  ✓ GET /api/analytics/alumni_list
  ✓ GET /api/analytics/usage_stats

Denied Endpoints:
  ✗ GET /api/alumni_of_day (requires read:alumni_of_day)
  ✗ POST /api/bids/store (requires write:bids)
```

#### Mobile AR App Client
```
Scopes: ["read:alumni_of_day"]

Allowed Endpoints:
  ✓ GET /api/featured-today

Denied Endpoints:
  ✗ GET /api/analytics/* (requires read:analytics)
```

### Key Generation & Security

#### How Keys Are Stored
```
Original Key (shown once on creation):
algo_abc123xyz789def456ghi789jk

Stored in Database (SHA-256 hash):
key_hash = SHA256("algo_abc123xyz789def456ghi789jk")
         = "7f8a9b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6e7f8a9b0c1d2e3f4a5b6c7d8e9f"

Displayed in UI (prefix only):
key_prefix = "algo_" (safe to display)
```

#### Authentication Process
```
1. Client makes request:
   GET /api/analytics/alumni_distribution
   Authorization: Bearer algo_abc123xyz789def456ghi789jk

2. Server extracts token from header

3. Server hashes token:
   token_hash = SHA256("algo_abc123xyz789def456ghi789jk")

4. Server queries database:
   SELECT * FROM api_keys 
   WHERE key_hash = '7f8a...' 
   AND is_revoked = 0 
   AND (expires_at IS NULL OR expires_at > NOW())

5. If found:
   - Extract scopes from api_key.scopes
   - Verify endpoint's required scopes are in key's scopes
   - If YES → process request, log usage
   - If NO → return 403 Forbidden

6. If not found:
   - Return 401 Unauthorized
```

### Rate Limiting

Prevents abuse. Different limits for different operations:

```
Login attempts: 5 per 15 minutes per IP
Registration: 3 per hour per IP
Public API (/api/featured-today): 100 per hour per IP
Analytics API: 500 per hour per API key
```

Exceeded limit response:
```json
{
  "ok": false,
  "message": "Too many requests. Try again in 10 minutes.",
  "code": 429
}
```

---

## Request Flow

### Example: Analytics Distribution Request

```
┌─────────────────────────────────────────────────────────────┐
│ 1. Client Request                                           │
│                                                             │
│ GET /api/analytics/alumni_distribution                     │
│ Authorization: Bearer algo_xyz123abc...                    │
│ User-Agent: Mozilla/5.0...                                 │
└───────────────────┬─────────────────────────────────────────┘
                    ▼
┌─────────────────────────────────────────────────────────────┐
│ 2. Router (routes.php)                                      │
│                                                             │
│ Maps URL to controller:                                    │
│ /api/analytics/alumni_distribution → AnalyticsApi::alumni_distribution()
└───────────────────┬─────────────────────────────────────────┘
                    ▼
┌─────────────────────────────────────────────────────────────┐
│ 3. Security Checks (begin_request method)                  │
│                                                             │
│ ✓ Check HTTP method is GET                                 │
│ ✓ Check rate limit (500 per hour)                          │
│ ✓ Extract bearer token                                     │
│ ✓ Hash token (SHA-256)                                     │
│ ✓ Query api_keys table for match                           │
│ ✓ Verify key not revoked                                   │
│ ✓ Verify key not expired                                   │
│ ✓ Verify scopes include "read:analytics"                   │
│ ✓ Update api_keys.last_used_at                             │
└───────────────────┬─────────────────────────────────────────┘
                    ▼
┌─────────────────────────────────────────────────────────────┐
│ 4. Business Logic (Analytics_model)                         │
│                                                             │
│ $this->analytics_model->get_alumni_distribution_by_degree()
│   → SELECT field_of_study as programme, COUNT(*) as count  │
│     FROM degrees GROUP BY field_of_study                   │
│                                                             │
│ $this->analytics_model->get_alumni_distribution_by_graduation_year()
│   → SELECT YEAR(completed_on) as year, COUNT(*) as count   │
│     FROM degrees WHERE completed_on IS NOT NULL            │
│     GROUP BY year ORDER BY year DESC                       │
│                                                             │
│ $this->analytics_model->get_industry_distribution()        │
│   → SELECT employer as sector, COUNT(*) as count           │
│     FROM employment_history GROUP BY employer              │
│     ORDER BY count DESC LIMIT 10                           │
└───────────────────┬─────────────────────────────────────────┘
                    ▼
┌─────────────────────────────────────────────────────────────┐
│ 5. Response Assembly                                        │
│                                                             │
│ {                                                           │
│   "ok": true,                                               │
│   "data": {                                                 │
│     "by_degree": [                                          │
│       {"programme": "Computer Science", "count": 87},       │
│       {"programme": "Business Management", "count": 56}     │
│     ],                                                      │
│     "by_year": [                                            │
│       {"year": 2023, "count": 92},                          │
│       {"year": 2022, "count": 78}                           │
│     ],                                                      │
│     "by_industry": [                                        │
│       {"sector": "Technology", "count": 45}                 │
│     ]                                                       │
│   }                                                         │
│ }                                                           │
└───────────────────┬─────────────────────────────────────────┘
                    ▼
┌─────────────────────────────────────────────────────────────┐
│ 6. Usage Logging                                            │
│                                                             │
│ INSERT INTO api_usage_logs (                                │
│   api_key_id: 1,                                            │
│   route: "/api/analytics/alumni_distribution",              │
│   http_method: "GET",                                       │
│   ip_address: "192.168.1.100",                              │
│   user_agent: "Mozilla/5.0...",                             │
│   response_code: 200,                                       │
│   duration_ms: 45,                                          │
│   created_at: NOW()                                         │
│ )                                                           │
└───────────────────┬─────────────────────────────────────────┘
                    ▼
┌─────────────────────────────────────────────────────────────┐
│ 7. Response Sent to Client                                  │
│                                                             │
│ HTTP/1.1 200 OK                                             │
│ Content-Type: application/json                              │
│                                                             │
│ {                                                           │
│   "ok": true,                                               │
│   "data": { ... }                                           │
│ }                                                           │
└─────────────────────────────────────────────────────────────┘
```

---

## Controllers & Models

### Controllers

| Controller | Purpose | Key Methods |
|-----------|---------|------------|
| **Dashboard** | Serves HTML views | index(), graphs(), alumni(), security(), login(), register() |
| **Auth** | Authentication | do_register(), do_login(), logout(), send_reset(), verify_email() |
| **ProfileApi** | Alumni profile API | profile(), save_basic(), add_degree(), delete_certification() |
| **Bids** | Blind bidding | place(), store(), status(), history(), run_daily_winner() |
| **AnalyticsApi** | Analytics endpoints | alumni_distribution(), skills_gap(), career_pathways(), trends(), alumni_list(), usage_stats() |
| **Admin** | Admin functions | api_keys(), create_api_key(), revoke_api_key(), usage_logs() |
| **PublicApi** | Public endpoints | featured_today() |

### Models

| Model | Responsibility |
|-------|-----------------|
| **User_model** | Authentication, password reset, email verification |
| **Profile_model** | Alumni profile CRUD operations |
| **Degree_model** | Education history management |
| **Certification_model** | Professional certifications |
| **Licence_model** | Professional licenses |
| **Course_model** | Short courses tracking |
| **Employment_model** | Employment history |
| **Analytics_model** | Analytics queries and data aggregation |
| **Bid_model** | Bidding logic and status management |
| **ApiKey_model** | API key creation, validation, revocation |
| **UsageLog_model** | API usage logging and statistics |
| **Feature_model** | Featured alumni records |

---

## User Journeys

### Journey 1: Alumni Registers & Updates Profile

```
1. Alumni visits http://localhost/alumni-influencers/
2. Clicks "Register"
3. Enters:
   - Email: student@university.ac.uk (must be university domain)
   - Password: SecureP@ss123 (validated for complexity)
   - Display Name: John Doe
4. System:
   - Validates input
   - Hashes password with bcrypt
   - Creates user (status=pending_verification)
   - Generates verification token
   - Sends verification email
5. Alumni clicks email link
6. System hashes token, verifies it matches, updates status to active
7. Alumni logs in with email/password
8. Alumni navigates to profile
9. Alumni updates:
   - Headline: "Software Engineer at Tech Corp"
   - Education: Added CS degree from University
   - Certifications: Added AWS Developer certification
   - Employment: Added current job at Tech Corp
10. Changes saved and timestamped

Next Step: Alumni can now participate in bidding or be viewed by university
```

### Journey 2: University Admin Views Analytics

```
1. Admin logs into system
2. Navigates to Admin Panel
3. Creates API Key for Analytics Dashboard:
   - Name: "Analytics Dashboard v1"
   - Scopes: ["read:alumni", "read:analytics"]
   - Expiry: 1 year from now
4. System generates key: algo_abc123xyz789def456ghi789jk
5. Admin copies key and provides to developer
6. Developer embeds key in Analytics Dashboard frontend:
   Authorization: Bearer algo_abc123xyz789def456ghi789jk
7. Dashboard makes requests:
   GET /api/analytics/alumni_distribution
   GET /api/analytics/skills_gap
   GET /api/analytics/career_pathways
   GET /api/analytics/trends
8. Server validates key, checks scopes, returns data
9. Dashboard visualizes:
   - Bar chart: Alumni by program
   - Line chart: Trends over time
   - Heatmap: Skills gaps
   - Scatter plot: Career pathways
10. Admin identifies insight: "73% of CS alumni got Docker certs post-grad"
11. Curriculum committee updates CS program to include Docker
```

### Journey 3: Alumni Places Blind Bid

```
1. Alumni logs in
2. Sees bid opportunity: "Feature your profile for 24 hours"
3. Visits /bids/place
4. Sees bid limit: "3 bids per month remaining: 2"
5. Enters bid amount: £50
6. Clicks "Place Bid"
7. System:
   - Checks monthly limit (not exceeded) ✓
   - Stores bid with status=submitted
   - Amount is hidden from other bidders
8. Alumni sees confirmation: "Bid placed. Result at midnight."
9. At midnight, automated task runs:
   - Finds all bids for current cycle
   - Identifies highest bid: £50 (John's)
   - Updates John's bid status to "won"
   - Creates featured_alumni record
   - Sets featured_from=now, featured_until=now+24h
10. Next day, John's profile shown at top of featured alumni
11. John can see in bid history: "Won - Featured 24 hours"
12. Other bidders see: "Lost - bid not highest"
```

---

## Configuration

### Environment Variables (.env)

```env
# ==============================================================================
# APPLICATION CONFIGURATION
# ==============================================================================
CI_ENV=development
APP_BASE_URL=http://localhost/alumni-influencers/
APP_ENCRYPTION_KEY=your-encryption-key-here
APP_COOKIE_SECURE=false

# ==============================================================================
# LOGGING CONFIGURATION
# ==============================================================================
LOG_THRESHOLD=1

# ==============================================================================
# DATABASE CONFIGURATION
# ==============================================================================
DB_HOST=localhost:3307
DB_USERNAME=root
DB_PASSWORD=
DB_DATABASE=alumni_influencers_db
DB_PREFIX=

# ==============================================================================
# EMAIL/MAIL CONFIGURATION
# ==============================================================================
MAIL_PROTOCOL=mail
MAIL_HOST=smtp.gmail.com
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_FROM=no-reply@alumni-influencers.local
MAIL_FROM_NAME=Alumni Influencers

# ==============================================================================
# API/SECURITY CONFIGURATION
# ==============================================================================
BIDS_ADMIN_KEY=dev-bids-admin-key
```

### Configuration Files

| File | Purpose |
|------|---------|
| `config/config.php` | Base URL, encryption, cookie settings |
| `config/database.php` | Database connection |
| `config/email.php` | Email service configuration |
| `config/routes.php` | URL routing |
| `config/security_hardening.php` | Security settings (CORS, rate limits, etc.) |

---

## Security Highlights

### 1. Password Security
- **Algorithm**: bcrypt (PHP's password_hash function)
- **Salt Rounds**: 10+ (automatically salted)
- **Validation**: Minimum 8 characters, complexity requirements
- **Storage**: Hash only, never plaintext
- **Verification**: password_verify() for comparison

### 2. Token Security
- **Generation**: Cryptographically random (random_bytes 32)
- **Storage**: SHA-256 hash only (never store plaintext tokens)
- **Expiry**: Tokens expire after set period
- **Single-Use**: Email verification tokens are single-use

### 3. API Key Security
- **Generation**: Random prefix + random hash
- **Display**: Only prefix shown in UI (safe)
- **Storage**: Key hash (SHA-256), never full key
- **Scopes**: Granular permissions limit damage if compromised
- **Revocation**: Can instantly revoke if suspected compromise
- **Audit**: Every usage logged with timestamp and IP

### 4. Input Validation & Sanitization
- **Server-Side**: All input validated
- **Types**: Email format, numbers, strings
- **Length**: Max lengths enforced
- **Encoding**: UTF-8 forced
- **Injection Prevention**: Prepared statements for all SQL queries

### 5. CSRF Protection
- **Mechanism**: CodeIgniter's built-in CSRF token
- **Token**: Unique per session
- **Validation**: All POST/PUT/DELETE requests require token
- **Mismatch**: Returns 403 Forbidden

### 6. SQL Injection Prevention
- **Prepared Statements**: All queries use parameterized bindings
- **Query Builder**: Avoids string concatenation
- **Example**:
  ```php
  // GOOD (prepared statement)
  $this->db->where('email', $email)->get('users');
  
  // BAD (vulnerable)
  $this->db->query("SELECT * FROM users WHERE email = '".$email."'");
  ```

### 7. XSS (Cross-Site Scripting) Prevention
- **Input**: All user input sanitized
- **Output**: htmlspecialchars() on display
- **JSON**: Responses encoded as JSON (not HTML)
- **CSP**: Content Security Policy headers set

### 8. Security Headers
- **X-Frame-Options**: SAMEORIGIN (prevents clickjacking)
- **X-Content-Type-Options**: nosniff (prevents MIME sniffing)
- **X-XSS-Protection**: 1; mode=block (legacy XSS protection)
- **Strict-Transport-Security**: HSTS enforced
- **CORS**: Configured to allow only specific origins

### 9. Rate Limiting
- **Login**: 5 attempts per 15 minutes per IP
- **Registration**: 3 attempts per hour per IP
- **Public API**: 100 requests per hour per IP
- **Analytics API**: 500 requests per hour per API key
- **Enforcement**: Returns 429 Too Many Requests

### 10. Audit Logging
- **Events Logged**:
  - Login attempts (success/failure)
  - Password resets
  - API key creation/revocation
  - All API requests
  - Admin actions
- **Retention**: Logs stored indefinitely for audit trail
- **Access**: Only admins can view logs

### 11. Account Lockout
- **Threshold**: 5 failed login attempts
- **Duration**: 15 minutes lock
- **Reset**: Successful login resets counter
- **Notification**: Email sent on suspicious activity

### 12. HTTPS Enforcement (Production)
- **Protocol**: Must use HTTPS in production
- **Setting**: APP_COOKIE_SECURE=true
- **HSTS**: Redirect all HTTP to HTTPS
- **Certificates**: Valid SSL/TLS certificate required

---

## Deployment Checklist

- [ ] Copy `.env.example` to `.env`
- [ ] Update `.env` with production values
- [ ] Generate strong `APP_ENCRYPTION_KEY` (minimum 32 chars)
- [ ] Set `CI_ENV=production` in `.env`
- [ ] Set `APP_COOKIE_SECURE=true` for HTTPS
- [ ] Import database schema from `database/schema.sql`
- [ ] Ensure `ci_sessions` table created
- [ ] Configure email service (MAIL_HOST, credentials)
- [ ] Set up HTTPS/SSL certificate
- [ ] Enable security headers in nginx/Apache
- [ ] Configure rate limiting based on expected traffic
- [ ] Test API key creation and usage
- [ ] Test email verification flow
- [ ] Review logs directory permissions (writable)
- [ ] Set up automated backups of database
- [ ] Set up monitoring for failed logins and API errors

---

## Troubleshooting

### "Database connection failed"
- Check `DB_HOST`, `DB_USERNAME`, `DB_PASSWORD`, `DB_DATABASE` in `.env`
- Verify MySQL is running
- Verify `ci_sessions` table exists in database

### "Email not sending"
- Check `MAIL_PROTOCOL` (should be 'smtp' for Gmail)
- Verify `MAIL_HOST`, `MAIL_USERNAME`, `MAIL_PASSWORD`
- For Gmail: Enable "Less secure app access" or use App Passwords
- Check application logs in `application/logs/`

### "API key returns 401 Unauthorized"
- Verify API key not revoked
- Verify API key not expired
- Verify `Authorization` header format: `Bearer <key>`
- Check server logs for token mismatch details

### "API returns 403 Forbidden"
- Verify API key has required scope
- Check endpoint's required scopes in controller code
- Example: endpoint requires `read:analytics` but key only has `read:alumni_of_day`

### "Rate limit exceeded (429)"
- Wait for rate limit window to expire
- Check rate limit settings in `config/security_hardening.php`
- For high-traffic scenarios, adjust limits accordingly

---

## Additional Resources

- **CodeIgniter 3 Documentation**: https://codeigniter.com/userguide3/
- **MySQL Documentation**: https://dev.mysql.com/doc/
- **Password Hashing Best Practices**: https://cheatsheetseries.owasp.org/cheatsheets/Password_Storage_Cheat_Sheet.html
- **API Security**: https://owasp.org/www-project-api-security/

---

**Document Version**: 1.0  
**Last Modified**: April 30, 2026  
**Author**: Development Team

# Coursework 2: University Analytics & Intelligence Dashboard - Implementation Report

## 1. Executive Summary
This document details the implementation of Part 2 of the Alumni Influencers project: the University Analytics Dashboard. This extension transforms alumni professional data into actionable intelligence for curriculum strategic planning.

## 2. Core Features Implemented

### 2.1 Registration & Authentication
- **University Domain Restriction**: Registration is restricted to authorized domains (`my.westminster.ac.uk`, `westminster.ac.uk`, `iit.ac.lk`) via the `Auth` controller.
- **Secure Authentication**: Implemented using session-based login with Bcrypt password hashing (12 rounds) and complex password requirements (min 12 chars, alphanumeric + symbols).
- **Email Verification**: Integrated system for verifying university identity.

### 2.2 Analytics Dashboard Interface
- **Responsive Design**: Built using Bootstrap 5, featuring a professional sidebar navigation and mobile-friendly layout.
- **Key Metrics Overview**: Real-time cards displaying total alumni, skills acquired, and identified curriculum gaps.
- **Alumni Explorer**: Advanced filtering by programme, graduation year, and industry sector with direct CSV export capabilities.

### 2.3 Data Visualization (Analytics)
Implemented 8 distinct interactive chart types using **Chart.js**:
1. **Line Chart**: Alumni growth trends over graduation years.
2. **Doughnut Chart**: Distribution of alumni across different degree programmes.
3. **Bar Chart**: Top 10 industry sectors where graduates are employed.
4. **Radar Chart**: Comparison of skills acquired post-graduation vs. curriculum.
5. **Polar Area Chart**: Certification acquisition intensity.
6. **Bubble Chart**: Career pathways mapping (Degree vs. Current Role).
7. **Stacked Bar**: Skill demand trends over time.
8. **Summary Table**: Critical skills gap detection (e.g., Docker/Kubernetes trends).

### 2.4 Blind Bidding System (Automated)
- **Place/Update Bids**: Alumni can place blind bids to increase visibility.
- **Midnight Winner Selection**: Automated logic in `Bid_model::run_daily_winner` selects the highest eligible bidder daily.
- **Monthly Limit**: Enforces a limit of 3 successful features per month per alumnus.
- **Mark for Display**: Winners are automatically flagged in the `features` table for homepage display.

### 2.5 Security & Usage Monitoring
- **Real-time Audit Logs**: University staff can view the last 50 API requests, including client name, endpoint, IP address, and response status.
- **Client Scoping View**: Provides transparency on which API keys are active and what permissions they hold.
- **Performance Tracking**: Captures request duration (ms) for performance monitoring of analytics endpoints.

### 2.6 Export & Reporting
- **Filtered CSV Export**: Alumni list can be exported based on active filters.
- **Chart Image Downloads**: Every visualization on the Graphs page can be downloaded as a PNG for use in university reports.
- **Filter Presets**: Ability to save current filter configuration to localStorage for quick retrieval.

## 3. Security Implementation

### 3.1 API Key Scoping & Permissions
A granular permission system ensures different clients have restricted access:
- **Analytics Dashboard**: Scoped with `["read:alumni", "read:analytics"]`.
- **Mobile AR App**: Scoped with `["read:alumni_of_day"]`.
- **Enforcement**: Middleware in `BearerTokenAuth` library validates tokens and scopes for every request.

### 3.2 Security Hardening
- **Rate Limiting**: Configured in `security_hardening.php` to prevent brute force and API abuse.
- **Security Headers**: Applied via `SecurityHeadersHook`, including:
    - `Content-Security-Policy` (CSP)
    - `X-Frame-Options: SAMEORIGIN`
    - `X-Content-Type-Options: nosniff`
    - `X-XSS-Protection`
- **Input Sanitization**: Comprehensive validation using CI Form Validation and XSS filtering.

## 4. Technical Architecture
- **Backend**: PHP CodeIgniter 3.x
- **Database**: MySQL (3NF Normalized)
- **Frontend**: Bootstrap 5, Chart.js, Vanilla JS (Fetch API)
- **Auth**: Bearer Token (for API) and PHP Sessions (for Dashboard)

## 5. API Endpoints (New)
| Endpoint | Method | Scope | Description |
|---|---|---|---|
| `/api/analytics/alumni_distribution` | GET | `read:analytics` | Distribution by degree, year, and industry. |
| `/api/analytics/skills_gap` | GET | `read:analytics` | Skills/Certs acquired post-graduation. |
| `/api/analytics/career_pathways` | GET | `read:analytics` | Mapping of degrees to job roles. |
| `/api/analytics/trends` | GET | `read:analytics` | Certification and course completion trends. |
| `/api/analytics/alumni_list` | GET | `read:alumni` | Filterable list of alumni data. |
| `/api/analytics/usage_stats` | GET | `read:analytics` | API usage logs and key metadata. |

---
*Implementation completed as per Coursework 2 requirements.*

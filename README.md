# IFT Result Tracking System

PHP and MySQL application for tracking the movement of physical examination results.

## Project structure

```text
.
├── app/                 Private application code and configuration
│   ├── config.php       Database settings and public application URL
│   ├── .htaccess        Blocks direct access to application internals on Apache
│   └── includes/        Authentication, database, mail, shared UI, helpers
├── archive/             Original project ZIP snapshot
├── database/            Database schema and seed data
├── docs/                Project notes and frontend roadmap
├── public/              Web document root
│   ├── api/             JSON endpoints
│   ├── assets/          Images and icons
│   ├── css/             Stylesheets
│   ├── js/              Browser scripts
│   └── *.php            Public pages and staff portal pages
├── composer.json        PHP dependencies
└── README.md            Setup and project guide
```

The PHP page links and static asset URLs are relative to `public/`, so they remain together when the public directory is deployed as the web root.

## Included features

- Public search by course code, course title, and lecturer name
- Level, semester, and session filtering
- Private Secretary/Admin login and view-only HOD login
- 40 IFT courses and 20 lecturers, with two lecturers assigned per course
- Result receipt tracking, handovers, stage history, delay alerts, and public timelines
- Email notification hooks and email logs when SMTP is configured
- Printable acknowledgement slips, reports, and CSV export

## Local setup with XAMPP

1. Copy the project folder to `htdocs`, for example `C:/xampp/htdocs/ift-result-tracking`.
2. Start Apache and MySQL.
3. Import [`database/schema.sql`](database/schema.sql) through phpMyAdmin.
4. Edit database credentials in [`app/config.php`](app/config.php) if they differ from the defaults.
5. Ensure `APP_URL` in `app/config.php` matches the folder name and ends in `/public`. With this folder name it is `http://localhost/IFT_Result_Tracking_System/public`.
6. Open `http://localhost/IFT_Result_Tracking_System/public/` (or replace the folder name if you renamed it).
7. To enable mail, run `composer install` from the project root and configure the SMTP environment variables below.

Default demo logins:

- Admin: `admin` / `Password123!`
- HOD: `hod` / `Password123!`

Change these credentials before a production deployment.

## Email configuration

Set these environment variables for SMTP delivery:

- `IFT_SMTP_HOST`
- `IFT_SMTP_USER`
- `IFT_SMTP_PASS`
- `IFT_SMTP_PORT` (usually `587`)
- `IFT_FROM_EMAIL`

Without PHPMailer and SMTP configuration, result tracking remains available, while email attempts are logged as failed/not configured.

## Data handling

The application tracks where physical results are, their current stage, dates and movement history, and internal administrative notes. It does not upload or store the physical result documents.

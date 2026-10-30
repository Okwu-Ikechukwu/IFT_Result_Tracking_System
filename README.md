# IFT Result Tracking System - PHP/MySQL MVP

This version converts the frontend to PHP and connects the important functions to MySQL.

## Included core functions
- Public search by course code, course title and lecturer name
- Level, semester and session filtering
- Private Secretary/Admin login
- View-only HOD login
- 40 IFT courses (5 per semester × 2 semesters × 4 levels)
- 20 lecturers
- Exactly 2 assigned lecturers per course
- 10 lecturers assigned to 100/200 levels and 10 to 300/400 levels
- Record received physical results
- Automatic tracking number and public token
- Seven-stage result movement tracking + returned/query stage
- Immutable movement/activity history
- Dashboard counts and delay alerts
- Public tracking timeline
- Lecturer email notification hooks + email log using PHPMailer when SMTP is configured
- Printable acknowledgement slip
- Reports and CSV/Excel-compatible export

## Default logins
- Admin: `admin` / `Password123!`
- HOD: `hod` / `Password123!`

Change these credentials after first login in a production deployment.

## XAMPP setup
1. Copy this folder into `htdocs`, for example `C:/xampp/htdocs/ift-result-tracking`.
2. Start Apache and MySQL.
3. Open phpMyAdmin.
4. Import `database.sql`.
5. Confirm database name is `ift_result_tracking`.
6. If your MySQL username/password differs, edit `config.php`.
7. Open `http://localhost/ift-result-tracking/index.php`.

## PHPMailer
Run `composer install` in the project folder. Then set SMTP environment variables:
- `IFT_SMTP_HOST`
- `IFT_SMTP_USER`
- `IFT_SMTP_PASS`
- `IFT_SMTP_PORT` (usually 587)
- `IFT_FROM_EMAIL`

Without PHPMailer/SMTP, the system still tracks results, but email attempts are logged as failed/not configured.

## Important design decision
The system does not upload or store physical result documents. It only stores tracking information: where the physical result is, its current stage, dates/times, movement history, and internal administrative notes.

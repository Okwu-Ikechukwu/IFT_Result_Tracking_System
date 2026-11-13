-- DESTRUCTIVE MANUAL RESET ONLY. Never mount this file into database init.
-- This drops application tables. Back up the database before running it.
SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS email_logs, activity_logs, result_movements, result_records, course_lecturers, courses, lecturers, offices, status_stages, users, settings;
SET FOREIGN_KEY_CHECKS=1;

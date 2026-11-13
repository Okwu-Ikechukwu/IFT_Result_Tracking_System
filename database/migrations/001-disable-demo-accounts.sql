-- Run manually against an existing installation before exposing it publicly.
-- Disable only the exact built-in demo account hashes from the old seed schema.
UPDATE users
SET is_active = 0
WHERE username IN ('admin', 'hod')
  AND password_hash = '$2y$12$CEwBKn5OKy6B11K540viU.sN68rWYm5B03CquA80MNPxg2QID3/D2';

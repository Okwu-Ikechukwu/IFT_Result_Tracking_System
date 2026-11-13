<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

try {
    require_once __DIR__ . '/../app/includes/db.php';
    db()->query('SELECT 1');
    http_response_code(200);
    echo '{"status":"ok"}';
} catch (Throwable $exception) {
    error_log('IFT health check failed: ' . $exception->getMessage());
    http_response_code(503);
    echo '{"status":"unavailable"}';
}

<?php

function app_env(string $name, ?string $default = null): ?string
{
    $value = getenv($name);
    return $value === false || $value === '' ? $default : $value;
}

$appEnvironment = app_env('APP_ENV', 'development');
if ($appEnvironment === 'production') {
    $missing = [];
    foreach (['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS', 'APP_URL'] as $required) {
        if (getenv($required) === false || trim((string) getenv($required)) === '') {
            $missing[] = $required;
        }
    }
    if ($missing !== []) {
        throw new RuntimeException('Missing required production configuration: ' . implode(', ', $missing));
    }
    if (getenv('DB_USER') === 'root' || str_starts_with((string) getenv('DB_PASS'), 'replace-with-')) {
        throw new RuntimeException('Production database credentials must be replaced with a dedicated application user and password.');
    }
}

define('APP_ENV', $appEnvironment);
define('DB_HOST', app_env('DB_HOST', '127.0.0.1'));
define('DB_PORT', (int) app_env('DB_PORT', '3306'));
define('DB_NAME', app_env('DB_NAME', 'ift_result_tracking'));
define('DB_USER', app_env('DB_USER', 'root'));
define('DB_PASS', app_env('DB_PASS', ''));
define('APP_NAME', app_env('APP_NAME', 'IFT Result Tracking System'));
define('APP_URL', rtrim((string) app_env('APP_URL', 'http://localhost/IFT_Result_Tracking_System/public'), '/'));

date_default_timezone_set('Africa/Lagos');

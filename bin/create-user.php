<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/includes/db.php';

function prompt(string $label): string
{
    fwrite(STDOUT, $label);
    return trim((string) fgets(STDIN));
}

function prompt_password(string $label): string
{
    fwrite(STDOUT, $label);
    $interactive = function_exists('stream_isatty') && stream_isatty(STDIN);
    if ($interactive) {
        shell_exec('stty -echo');
    }
    try {
        $password = rtrim((string) fgets(STDIN), "\r\n");
    } finally {
        if ($interactive) {
            shell_exec('stty echo');
            fwrite(STDOUT, PHP_EOL);
        }
    }
    return $password;
}

$role = 'admin';
foreach (array_slice($argv, 1) as $argument) {
    if (str_starts_with($argument, '--role=')) {
        $role = substr($argument, 7);
    } else {
        fwrite(STDERR, "Usage: php bin/create-user.php [--role=admin|hod]\n");
        exit(2);
    }
}
if (!in_array($role, ['admin', 'hod'], true)) {
    fwrite(STDERR, "Role must be admin or hod.\n");
    exit(2);
}

$pdo = db();
$adminCount = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role='admin' AND is_active=1")->fetchColumn();
if ($adminCount === 0 && $role !== 'admin') {
    fwrite(STDERR, "Create an administrator before creating an HOD account.\n");
    exit(1);
}

$name = prompt('Full name: ');
$username = prompt('Username: ');
$email = prompt('Email: ');
$password = prompt_password('Password (at least 12 characters): ');
if ($name === '' || $username === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 12) {
    fwrite(STDERR, "Name, username, valid email, and a password of at least 12 characters are required.\n");
    exit(1);
}

try {
    $statement = $pdo->prepare('INSERT INTO users (full_name, username, email, password_hash, role) VALUES (?, ?, ?, ?, ?)');
    $statement->execute([$name, $username, $email, password_hash($password, PASSWORD_DEFAULT), $role]);
    fwrite(STDOUT, ucfirst($role) . " account created.\n");
} catch (PDOException $exception) {
    error_log('IFT CLI account creation failed: ' . $exception->getMessage());
    fwrite(STDERR, "Account could not be created. Check whether the username or email is already in use.\n");
    exit(1);
}

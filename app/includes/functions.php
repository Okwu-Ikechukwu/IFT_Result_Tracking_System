<?php
require_once __DIR__ . '/db.php';

function e($value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function redirect($url): never { header('Location: ' . $url); exit; }
function flash($key, $message): void { $_SESSION['flash'][$key] = $message; }
function get_flash($key): ?string { $m = $_SESSION['flash'][$key] ?? null; unset($_SESSION['flash'][$key]); return $m; }
function csrf_token(): string { if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32)); return $_SESSION['csrf']; }
function verify_csrf(): void { if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) { http_response_code(419); exit('Invalid security token. Please go back and try again.'); } }
function app_url(string $path = ''): string { return rtrim(APP_URL, '/') . '/' . ltrim($path, '/'); }
function public_token(): string { return bin2hex(random_bytes(16)); }
function make_tracking_no(int $id): string { return 'IFT-' . date('Y') . '-' . str_pad((string)$id, 5, '0', STR_PAD_LEFT); }
function current_user(): ?array { return $_SESSION['user'] ?? null; }
function initials(string $name): string { $p = preg_split('/\s+/', trim($name)); return strtoupper(substr($p[0] ?? '',0,1) . substr($p[count($p)-1] ?? '',0,1)); }
function stage_badge_class(string $status): string { if (stripos($status,'Released')!==false) return 'green'; if (stripos($status,'Dean')!==false) return 'blue'; if (stripos($status,'Returned')!==false || stripos($status,'Query')!==false) return 'orange'; return 'blue'; }
function log_activity(int $resultId, string $action, ?string $note = null, ?string $receivedBy = null): void {
    $u = current_user();
    $stmt = db()->prepare('INSERT INTO activity_logs (result_id,user_id,action,received_by,note,ip_address,user_agent) VALUES (?,?,?,?,?,?,?)');
    $stmt->execute([$resultId, $u['id'] ?? null, $action, $receivedBy, $note, $_SERVER['REMOTE_ADDR'] ?? null, substr($_SERVER['HTTP_USER_AGENT'] ?? '',0,255)]);
}
function send_notification(int $resultId, string $event): void {
    require_once __DIR__ . '/mailer.php';
    send_result_email($resultId, $event);
}
function days_at_stage(string $since): int { return max(0, (int)floor((time() - strtotime($since)) / 86400)); }
function setting(string $key, string $default=''): string { $s=db()->prepare('SELECT setting_value FROM settings WHERE setting_key=?'); $s->execute([$key]); return $s->fetchColumn() ?: $default; }

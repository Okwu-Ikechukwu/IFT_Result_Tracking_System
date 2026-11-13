<?php
require_once __DIR__ . '/functions.php';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('ift_session');
    session_set_cookie_params([
        'httponly' => true,
        'secure' => APP_ENV === 'production' && str_starts_with(strtolower(APP_URL), 'https://'),
        'samesite' => 'Lax',
        'path' => '/',
    ]);
    session_start();
}
function require_login(array $roles=[]): void {
    if (empty($_SESSION['user'])) redirect('login.php');
    if ($roles && !in_array($_SESSION['user']['role'], $roles, true)) { http_response_code(403); exit('Access denied.'); }
}
function login_user(array $user): void { $_SESSION['user'] = ['id'=>$user['id'],'name'=>$user['full_name'],'email'=>$user['email'],'role'=>$user['role']]; session_regenerate_id(true); }
function logout_user(): void { $_SESSION=[]; if (ini_get('session.use_cookies')) { $p=session_get_cookie_params(); setcookie(session_name(),'',time()-42000,$p['path'],$p['domain'],$p['secure'],$p['httponly']); } session_destroy(); }

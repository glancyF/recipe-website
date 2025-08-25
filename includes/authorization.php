<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../db.php';

function forceLogout(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();

    setcookie('auth_token', '', time() - 3600, '/', '', !empty($_SERVER['HTTPS']), true);
}

function requireAuth(): array {
    global $conn;

    $cookieToken = $_COOKIE['auth_token'] ?? '';
    $sessionUserId = $_SESSION['user_id'] ?? null;

    if (!$cookieToken) {
        forceLogout();
        header('Location: /login/auth.php');
        exit;
    }

    if ($sessionUserId) {
        $q = $conn->prepare("SELECT id, username, email, gender, status, auth_token FROM users WHERE id=? LIMIT 1");
        $q->bind_param('i', $sessionUserId);
        $q->execute();
        $u = $q->get_result()->fetch_assoc();
        $q->close();

        if ($u && hash_equals($u['auth_token'] ?? '', $cookieToken)) {
            return $u;
        }
    }

    $q = $conn->prepare("SELECT id, username, email, gender, status, auth_token FROM users WHERE auth_token=? LIMIT 1");
    $q->bind_param('s', $cookieToken);
    $q->execute();
    $u = $q->get_result()->fetch_assoc();
    $q->close();

    if ($u) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$u['id'];
        $_SESSION['status']  = $u['status'] ?? 'user';
        return $u;
    }

    forceLogout();
    header('Location: /login/auth.php');
    exit;
}

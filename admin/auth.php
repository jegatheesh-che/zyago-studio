<?php
// ============================================================
// Zyago Studio — Admin Authentication Helper
// ============================================================
// Include this file in any protected admin page.
// Call: require_once __DIR__ . '/auth.php'; requireLogin();
// ============================================================

require_once __DIR__ . '/config.php';

function startAdminSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME_SECONDS,
            'path'     => '/',
            'secure'   => false, // set true on HTTPS
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
        session_start();
    }
}

function isLoggedIn(): bool {
    startAdminSession();
    if (empty($_SESSION['admin_logged_in']) || empty($_SESSION['admin_user'])) {
        return false;
    }
    // Expire check
    if (!empty($_SESSION['admin_expires']) && time() > $_SESSION['admin_expires']) {
        session_destroy();
        return false;
    }
    return $_SESSION['admin_logged_in'] === true;
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: ' . adminUrl('login.php'));
        exit;
    }
}

function loginAdmin(string $username, string $password): bool {
    startAdminSession();
    if ($username === ADMIN_USERNAME && password_verify($password, ADMIN_PASSWORD_HASH)) {
        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user']      = $username;
        $_SESSION['admin_expires']   = time() + SESSION_LIFETIME_SECONDS;
        return true;
    }
    return false;
}

function logoutAdmin(): void {
    startAdminSession();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}

// ── CSRF helpers ──────────────────────────────────────────────
function generateCsrfToken(): string {
    startAdminSession();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(string $token): bool {
    startAdminSession();
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(generateCsrfToken()) . '">';
}

// ── Gallery JSON helpers ───────────────────────────────────────
function readGallery(): array {
    if (!file_exists(GALLERY_JSON)) {
        return ['version' => 1, 'last_updated' => date('c'), 'items' => []];
    }
    $raw = file_get_contents(GALLERY_JSON);
    $data = json_decode($raw, true);
    return is_array($data) ? $data : ['version' => 1, 'last_updated' => date('c'), 'items' => []];
}

function writeGallery(array $data): bool {
    $data['last_updated'] = date('c');
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    return file_put_contents(GALLERY_JSON, $json, LOCK_EX) !== false;
}

// ── About JSON helpers ─────────────────────────────────────────
function readAbout(): array {
    if (!file_exists(ABOUT_JSON)) {
        return [];
    }
    $raw = file_get_contents(ABOUT_JSON);
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function writeAbout(array $data): bool {
    $data['last_updated'] = date('c');
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    return file_put_contents(ABOUT_JSON, $json, LOCK_EX) !== false;
}

// ── URL helper ────────────────────────────────────────────────
function adminUrl(string $file = ''): string {
    $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host  = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $base  = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/admin'), '/\\');
    // Normalize: if we're inside /admin/api, go up one
    $adminBase = preg_replace('#/api$#', '', $base);
    return $proto . '://' . $host . $adminBase . ($file ? '/' . ltrim($file, '/') : '');
}

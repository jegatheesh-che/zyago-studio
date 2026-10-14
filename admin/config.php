<?php
// ============================================================
// Zyago Studio — Admin Panel Configuration
// ============================================================
// SECURITY NOTE: This file is parsed by PHP and never served
// as raw text. Do NOT expose this via a non-PHP web server.
// ============================================================

// ---------- ADMIN CREDENTIALS ----------
// Change these before going live.
// Generate a new hash via: php -r "echo password_hash('YourPassword', PASSWORD_BCRYPT);"
define('ADMIN_USERNAME', 'zyago_admin');

// Default password: ZyagoAdmin@2025  (CHANGE THIS IMMEDIATELY IN PRODUCTION)
define('ADMIN_PASSWORD_HASH', '$2y$10$IESICr.o88zB8Hc3H13ex.FpQT7f5n/DL63DL5f7oArxNpdyNH.NK');

// ---------- SESSION ----------
define('SESSION_LIFETIME_SECONDS', 3600 * 8); // 8 hours
define('SESSION_NAME', 'zyago_admin_session');

// ---------- FILE UPLOAD LIMITS ----------
define('MAX_FILE_SIZE_BYTES', 25 * 1024 * 1024); // 25 MB
define('ALLOWED_MIME_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp', 'gif']);

// ---------- PATHS (relative to project root) ----------
// __DIR__ is /admin, so go up one level for root
define('ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('DATA_PATH', ROOT_PATH . 'data' . DIRECTORY_SEPARATOR);
define('GALLERY_JSON', DATA_PATH . 'gallery.json');
define('ABOUT_JSON', DATA_PATH . 'about.json');
define('UPLOADS_PATH', ROOT_PATH . 'uploads' . DIRECTORY_SEPARATOR);
define('PORTRAITS_PATH', ROOT_PATH . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'highlights' . DIRECTORY_SEPARATOR . 'portraits' . DIRECTORY_SEPARATOR);
define('EVENTS_PATH', ROOT_PATH . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'highlights' . DIRECTORY_SEPARATOR . 'events' . DIRECTORY_SEPARATOR);

// ---------- CATEGORY CONFIGURATION ----------
define('CATEGORIES', [
    'studio' => [
        'label'      => 'Studio Portrait',
        'upload_dir' => PORTRAITS_PATH,
        'web_path'   => 'assets/images/highlights/portraits/',
        'prefix'     => 'portrait-hl-',
        'pages'      => ['galleries', 'studio-portrait'],
    ],
    'events' => [
        'label'      => 'Life Events',
        'upload_dir' => EVENTS_PATH,
        'web_path'   => 'assets/images/highlights/events/',
        'prefix'     => 'event-hl-',
        'pages'      => ['galleries', 'life-events'],
    ],
]);

// ---------- TIMEZONE ----------
date_default_timezone_set('Asia/Kolkata');

<?php
// ============================================================
// Zyago Studio — Public About API
// GET /api/about.php
// Returns JSON content for the public About page.
// No authentication required (read-only public endpoint).
// ============================================================
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=60'); // 60-second cache

require_once dirname(__DIR__) . '/admin/config.php';

$aboutData = null;
if (file_exists(ABOUT_JSON)) {
    $raw = file_get_contents(ABOUT_JSON);
    $aboutData = json_decode($raw, true);
}

if (!is_array($aboutData)) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'error' => 'About data not available.']);
    exit;
}

echo json_encode([
    'ok'           => true,
    'last_updated' => $aboutData['last_updated'] ?? null,
    'data'         => $aboutData,
]);

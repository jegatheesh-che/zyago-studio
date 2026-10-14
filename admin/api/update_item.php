<?php
// ============================================================
// Zyago Studio — Update Gallery Photo Item API
// POST /admin/api/update_item.php
// Protected: requires admin session + CSRF token
// Updates photo pages placement, title, alt, and grid layout.
// ============================================================
header('Content-Type: application/json; charset=utf-8');

require_once dirname(__DIR__) . '/auth.php';

// ── Auth & CSRF ───────────────────────────────────────────────
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Unauthorized. Please log in.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    $input = $_POST;
}

$csrfToken = $input['csrf_token'] ?? '';
if (!verifyCsrfToken($csrfToken)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Invalid CSRF token. Please refresh and try again.']);
    exit;
}

$id = trim((string)($input['id'] ?? ''));
if (!$id || !preg_match('/^[a-zA-Z0-9\-_]+$/', $id)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Invalid photo ID provided.']);
    exit;
}

// ── Sanitize Inputs ───────────────────────────────────────────
$allowedPages = ['galleries', 'studio-portrait', 'life-events'];
$rawPages     = $input['pages'] ?? [];
if (!is_array($rawPages)) {
    $rawPages = array_filter(array_map('trim', explode(',', (string)$rawPages)));
}
$cleanPages = array_values(array_intersect($rawPages, $allowedPages));

$title     = trim((string)($input['title'] ?? ''));
$alt       = trim((string)($input['alt'] ?? ''));
$gridClass = trim((string)($input['grid_class'] ?? ''));

$validGridClasses = ['item-portrait', 'item-portrait-tall', 'item-landscape'];
if ($gridClass && !in_array($gridClass, $validGridClasses, true)) {
    $gridClass = 'item-portrait';
}

if (strlen($title) > 120) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Title is too long (max 120 characters).']);
    exit;
}

// ── Read & Update gallery.json ────────────────────────────────
$gallery = readGallery();
$items   = $gallery['items'] ?? [];
$foundIndex = -1;

foreach ($items as $idx => $item) {
    if (($item['id'] ?? '') === $id) {
        $foundIndex = $idx;
        break;
    }
}

if ($foundIndex === -1) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'error' => 'Photo not found in gallery catalogue.']);
    exit;
}

// Update fields
$items[$foundIndex]['pages'] = $cleanPages;
if ($title !== '') {
    $items[$foundIndex]['title'] = htmlspecialchars($title, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}
if ($alt !== '') {
    $items[$foundIndex]['alt'] = $alt;
}
if ($gridClass !== '') {
    $items[$foundIndex]['grid_class'] = $gridClass;
}

$gallery['items'] = $items;
$gallery['last_updated'] = date('c');

if (!writeGallery($gallery)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Failed to save updates to gallery database.']);
    exit;
}

http_response_code(200);
echo json_encode([
    'ok'      => true,
    'message' => 'Photo placement and details updated successfully.',
    'item'    => [
        'id'             => $items[$foundIndex]['id'],
        'category'       => $items[$foundIndex]['category'],
        'category_label' => $items[$foundIndex]['category_label'],
        'title'          => html_entity_decode($items[$foundIndex]['title']),
        'alt'            => $items[$foundIndex]['alt'] ?? '',
        'src'            => $items[$foundIndex]['src'],
        'grid_class'     => $items[$foundIndex]['grid_class'] ?? 'item-portrait',
        'is_default'     => (bool)($items[$foundIndex]['is_default'] ?? false),
        'pages'          => $items[$foundIndex]['pages'],
    ]
]);

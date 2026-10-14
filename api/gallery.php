<?php
// ============================================================
// Zyago Studio — Public Gallery API
// GET /api/gallery.php?category=studio|events|all&page=galleries
// Returns JSON for the public site to hydrate gallery grids.
// No authentication required (read-only public endpoint).
// ============================================================
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=60'); // 60-second cache

// Load config and helpers
require_once dirname(__DIR__) . '/admin/config.php';

$galleryData = [];
if (file_exists(GALLERY_JSON)) {
    $raw = file_get_contents(GALLERY_JSON);
    $galleryData = json_decode($raw, true);
}

if (empty($galleryData) || !isset($galleryData['items'])) {
    echo json_encode(['ok' => true, 'items' => [], 'total' => 0]);
    exit;
}

$items = $galleryData['items'];

// ── Optional Filters ──────────────────────────────────────────
$filterCategory = $_GET['category'] ?? 'all';
$filterPage     = $_GET['page'] ?? null;

if ($filterCategory !== 'all' && in_array($filterCategory, ['studio', 'events'], true)) {
    $items = array_values(array_filter($items, function($item) use ($filterCategory) {
        return ($item['category'] ?? '') === $filterCategory;
    }));
}

if ($filterPage !== null) {
    $items = array_values(array_filter($items, function($item) use ($filterPage) {
        return in_array($filterPage, $item['pages'] ?? [], true);
    }));
}

// ── Sanitize output ───────────────────────────────────────────
$output = array_map(function($item) {
    return [
        'id'             => $item['id'] ?? '',
        'category'       => $item['category'] ?? '',
        'category_label' => $item['category_label'] ?? '',
        'title'          => $item['title'] ?? '',
        'alt'            => $item['alt'] ?? '',
        'src'            => $item['src'] ?? '',
        'grid_class'     => $item['grid_class'] ?? 'item-portrait',
        'is_default'     => (bool)($item['is_default'] ?? false),
        'uploaded_at'    => $item['uploaded_at'] ?? null,
    ];
}, $items);

echo json_encode([
    'ok'           => true,
    'total'        => count($output),
    'last_updated' => $galleryData['last_updated'] ?? null,
    'items'        => array_values($output),
]);

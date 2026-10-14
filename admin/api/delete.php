<?php
// ============================================================
// Zyago Studio — Multi & Single Photo Delete API
// POST /admin/api/delete.php
// Protected: requires admin session + CSRF token
// Supports single ('id') and multiple ('ids[]') deletion.
// NOTE: Only non-default (user-uploaded) photos can be deleted.
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

// ── Normalize Target Photo IDs ────────────────────────────────
$targetIds = [];
if (!empty($input['ids']) && is_array($input['ids'])) {
    foreach ($input['ids'] as $id) {
        $cleanId = trim((string)$id);
        if ($cleanId && preg_match('/^[a-zA-Z0-9\-_]+$/', $cleanId)) {
            $targetIds[] = $cleanId;
        }
    }
} elseif (!empty($input['id'])) {
    $cleanId = trim((string)$input['id']);
    if ($cleanId && preg_match('/^[a-zA-Z0-9\-_]+$/', $cleanId)) {
        $targetIds[] = $cleanId;
    }
}

if (empty($targetIds)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'No valid photo ID(s) provided for deletion.']);
    exit;
}

// ── Load Gallery & Process Deletion ───────────────────────────
$gallery = readGallery();
$items   = $gallery['items'] ?? [];

$targetLookup = array_flip($targetIds);
$deletedIds   = [];
$skippedIds   = [];
$remainingItems = [];

foreach ($items as $item) {
    $itemId = $item['id'] ?? '';
    
    if (isset($targetLookup[$itemId])) {
        // Guard: Prevent deleting default seed photos
        if (!empty($item['is_default'])) {
            $skippedIds[] = $itemId;
            $remainingItems[] = $item; // keep protected item
            continue;
        }

        // Delete physical file from disk
        $filePath = ROOT_PATH . ltrim($item['src'], '/');
        if (file_exists($filePath)) {
            @unlink($filePath);
        }

        $deletedIds[] = $itemId;
    } else {
        $remainingItems[] = $item;
    }
}

// If user tried to delete only default items and none was eligible
if (empty($deletedIds)) {
    if (!empty($skippedIds)) {
        http_response_code(403);
        echo json_encode([
            'ok'    => false,
            'error' => 'Default portfolio photographs are protected and cannot be deleted.',
            'skipped' => $skippedIds
        ]);
        exit;
    }

    http_response_code(404);
    echo json_encode(['ok' => false, 'error' => 'None of the requested photos were found in the gallery catalogue.']);
    exit;
}

// ── Update gallery.json Atomically ───────────────────────────
$gallery['items'] = array_values($remainingItems);

if (!writeGallery($gallery)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Files were deleted but gallery metadata update failed. Please refresh.']);
    exit;
}

$count = count($deletedIds);
$msg = ($count === 1) 
    ? 'Photo deleted successfully.' 
    : "Successfully deleted {$count} photos.";

http_response_code(200);
echo json_encode([
    'ok'      => true,
    'deleted' => ($count === 1) ? $deletedIds[0] : $deletedIds, // backward compatibility
    'deleted_ids' => $deletedIds,
    'count'   => $count,
    'skipped' => $skippedIds,
    'message' => $msg,
]);

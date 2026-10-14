<?php
// ============================================================
// Zyago Studio — Multi & Single Photo Upload API
// POST /admin/api/upload.php
// Protected: requires admin session + CSRF token
// Supports single ('photo') and multiple ('photos[]') uploads.
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

$csrfToken = $_POST['csrf_token'] ?? '';
if (!verifyCsrfToken($csrfToken)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Invalid CSRF token. Please refresh and try again.']);
    exit;
}

// ── Input Validation ─────────────────────────────────────────
$category  = trim($_POST['category'] ?? '');
$baseTitle = trim($_POST['title'] ?? '');
$alt       = trim($_POST['alt'] ?? '');
$gridClass = trim($_POST['grid_class'] ?? 'item-portrait');

if (!array_key_exists($category, CATEGORIES)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Invalid category. Must be "studio" or "events".']);
    exit;
}

$validGridClasses = ['item-portrait', 'item-portrait-tall', 'item-landscape'];
if (!in_array($gridClass, $validGridClasses, true)) {
    $gridClass = 'item-portrait';
}

if (strlen($baseTitle) > 120) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Title is too long (max 120 characters).']);
    exit;
}

// ── Performance & Memory Configuration ───────────────────────
@ini_set('memory_limit', '512M');
@ini_set('max_execution_time', '180');

/**
 * Compresses and converts an image to high-efficiency WebP format.
 * Corrects EXIF camera rotation, scales down gracefully if > 2400px,
 * and encodes with 82% quality for sharp, ultra-lightweight delivery.
 */
function compressAndConvertToWebP(string $sourcePath, string $destinationPath, int $maxDimension = 2400, int $quality = 82): bool {
    if (!file_exists($sourcePath) || !function_exists('imagewebp')) {
        return false;
    }

    $raw = @file_get_contents($sourcePath);
    if (!$raw) {
        return false;
    }

    $image = @imagecreatefromstring($raw);
    if (!$image) {
        return false;
    }

    // EXIF orientation correction for camera JPEGs
    if (function_exists('exif_read_data')) {
        $exif = @exif_read_data($sourcePath);
        if (!empty($exif['Orientation'])) {
            switch ($exif['Orientation']) {
                case 3:
                    $rotated = imagerotate($image, 180, 0);
                    if ($rotated) { imagedestroy($image); $image = $rotated; }
                    break;
                case 6:
                    $rotated = imagerotate($image, -90, 0);
                    if ($rotated) { imagedestroy($image); $image = $rotated; }
                    break;
                case 8:
                    $rotated = imagerotate($image, 90, 0);
                    if ($rotated) { imagedestroy($image); $image = $rotated; }
                    break;
            }
        }
    }

    $origW = imagesx($image);
    $origH = imagesy($image);

    // Downscale proportionally if larger than maxDimension
    if ($origW > $maxDimension || $origH > $maxDimension) {
        if ($origW >= $origH) {
            $newW = $maxDimension;
            $newH = (int)round(($origH / $origW) * $maxDimension);
        } else {
            $newH = $maxDimension;
            $newW = (int)round(($origW / $origH) * $maxDimension);
        }

        $canvas = imagecreatetruecolor($newW, $newH);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagecopyresampled($canvas, $image, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
        imagedestroy($image);
        $image = $canvas;
    } else {
        imagealphablending($image, false);
        imagesavealpha($image, true);
    }

    $saved = @imagewebp($image, $destinationPath, $quality);
    imagedestroy($image);

    return $saved;
}

// ── Normalize Uploaded Files List ────────────────────────────
$files = [];

if (!empty($_FILES['photos']) && is_array($_FILES['photos']['name'])) {
    $count = count($_FILES['photos']['name']);
    for ($i = 0; $i < $count; $i++) {
        if ($_FILES['photos']['error'][$i] !== UPLOAD_ERR_NO_FILE) {
            $files[] = [
                'name'     => $_FILES['photos']['name'][$i],
                'type'     => $_FILES['photos']['type'][$i],
                'tmp_name' => $_FILES['photos']['tmp_name'][$i],
                'error'    => $_FILES['photos']['error'][$i],
                'size'     => $_FILES['photos']['size'][$i],
            ];
        }
    }
} elseif (!empty($_FILES['photos']) && !is_array($_FILES['photos']['name'])) {
    if ($_FILES['photos']['error'] !== UPLOAD_ERR_NO_FILE) {
        $files[] = $_FILES['photos'];
    }
} elseif (!empty($_FILES['photo']) && !is_array($_FILES['photo']['name'])) {
    if ($_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $files[] = $_FILES['photo'];
    }
}

if (empty($files)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'No files uploaded. Please select at least one photo.']);
    exit;
}

// ── Process & Validate Each File ──────────────────────────────
$catConfig = CATEGORIES[$category];
$uploadDir = $catConfig['upload_dir'];
$prefix    = $catConfig['prefix'];

if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'Upload directory could not be created.']);
        exit;
    }
}

if (!is_writable($uploadDir)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Upload directory is not writable.']);
    exit;
}

$uploadedItems = [];
$errors = [];
$nowTimestamp = date('YmdHis');
$fileCount = count($files);

foreach ($files as $index => $file) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "File '{$file['name']}': upload error code {$file['error']}.";
        continue;
    }

    if ($file['size'] > MAX_FILE_SIZE_BYTES) {
        $errors[] = "File '{$file['name']}': exceeds maximum size of 25 MB.";
        continue;
    }

    // MIME type check
    if (extension_loaded('fileinfo')) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
    } else {
        $mimeType = $file['type'];
    }

    if (!in_array($mimeType, ALLOWED_MIME_TYPES, true)) {
        $errors[] = "File '{$file['name']}': unsupported image type ({$mimeType}). Accepted: JPEG, PNG, WebP.";
        continue;
    }

    // Extension check
    $origName = basename($file['name']);
    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXTENSIONS, true)) {
        $errors[] = "File '{$file['name']}': invalid extension. Accepted: JPG, PNG, WebP.";
        continue;
    }

    // Determine title
    if (!empty($baseTitle)) {
        $itemTitle = ($fileCount > 1) ? "{$baseTitle} · " . ($index + 1) : $baseTitle;
    } else {
        // Humanize file name: "bridal_golden_hour.jpg" -> "Bridal Golden Hour"
        $rawBase = pathinfo($origName, PATHINFO_FILENAME);
        $itemTitle = ucwords(str_replace(['-', '_', '.'], ' ', $rawBase));
    }

    $itemAlt = $alt ?: $itemTitle;

    // Unique filename — compressed & saved directly as .webp
    $random = bin2hex(random_bytes(4));
    $fileName = $prefix . 'upload-' . $nowTimestamp . '-' . $index . '-' . $random . '.webp';
    $destPath = $uploadDir . $fileName;
    $webPath  = $catConfig['web_path'] . $fileName;

    // Compress & convert to WebP
    $converted = compressAndConvertToWebP($file['tmp_name'], $destPath, 2400, 82);
    if (!$converted) {
        // Fallback: if WebP conversion failed, preserve original format
        $fileName = $prefix . 'upload-' . $nowTimestamp . '-' . $index . '-' . $random . '.' . $ext;
        $destPath = $uploadDir . $fileName;
        $webPath  = $catConfig['web_path'] . $fileName;
        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            $errors[] = "File '{$file['name']}': failed to save file to destination.";
            continue;
        }
    }

    $itemId = $prefix . 'upload-' . $nowTimestamp . '-' . $index . '-' . $random;

    // Pages placement
    $allowedPages = ['galleries', 'studio-portrait', 'life-events'];
    $rawPages     = $_POST['pages'] ?? [];
    if (!is_array($rawPages)) {
        $rawPages = array_filter(array_map('trim', explode(',', (string)$rawPages)));
    }
    $cleanPages = array_values(array_intersect($rawPages, $allowedPages));
    if (empty($cleanPages)) {
        $cleanPages = $catConfig['pages'];
    }

    $uploadedItems[] = [
        'id'             => $itemId,
        'category'       => $category,
        'category_label' => $catConfig['label'],
        'title'          => htmlspecialchars($itemTitle, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
        'alt'            => $itemAlt,
        'src'            => $webPath,
        'grid_class'     => $gridClass,
        'is_default'     => false,
        'uploaded_at'    => date('c'),
        'pages'          => $cleanPages,
        '_dest_path'     => $destPath, // temporary for rollback
    ];
}

if (empty($uploadedItems)) {
    http_response_code(422);
    echo json_encode([
        'ok'     => false,
        'error'  => !empty($errors) ? implode(' ', $errors) : 'No valid files could be uploaded.',
        'errors' => $errors
    ]);
    exit;
}

// ── Atomically Update gallery.json ───────────────────────────
$gallery = readGallery();
if (!isset($gallery['items']) || !is_array($gallery['items'])) {
    $gallery['items'] = [];
}

foreach ($uploadedItems as $uItem) {
    $cleanItem = $uItem;
    unset($cleanItem['_dest_path']);
    $gallery['items'][] = $cleanItem;
}

if (!writeGallery($gallery)) {
    // Rollback saved files on disk
    foreach ($uploadedItems as $uItem) {
        if (!empty($uItem['_dest_path'])) {
            @unlink($uItem['_dest_path']);
        }
    }
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Failed to update gallery database. Uploads rolled back.']);
    exit;
}

// Format clean output
$outputItems = array_map(function($it) {
    return [
        'id'             => $it['id'],
        'category'       => $it['category'],
        'category_label' => $it['category_label'],
        'title'          => html_entity_decode($it['title']),
        'alt'            => $it['alt'],
        'src'            => $it['src'],
        'grid_class'     => $it['grid_class'],
        'is_default'     => false,
        'uploaded_at'    => $it['uploaded_at'],
        'pages'          => $it['pages'],
    ];
}, $uploadedItems);

$uploadedCount = count($outputItems);
$msg = ($uploadedCount === 1) 
    ? 'Photo uploaded successfully and added to the gallery.' 
    : "Successfully uploaded {$uploadedCount} photos to the gallery.";

http_response_code(200);
echo json_encode([
    'ok'      => true,
    'count'   => $uploadedCount,
    'item'    => $outputItems[0], // backward compatibility
    'items'   => $outputItems,
    'errors'  => $errors,
    'message' => $msg,
]);

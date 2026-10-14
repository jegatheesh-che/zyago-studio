<?php
// ============================================================
// Zyago Studio — About Page Content Admin API
// GET  /admin/api/about.php -> Retrieve current about content
// POST /admin/api/about.php -> Save updated about content
// Protected: requires admin session + CSRF token
// ============================================================
header('Content-Type: application/json; charset=utf-8');

require_once dirname(__DIR__) . '/auth.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Unauthorized. Please log in.']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $data = readAbout();
    echo json_encode(['ok' => true, 'data' => $data]);
    exit;
}

if ($method === 'POST') {
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    if (!is_array($input)) {
        $input = $_POST;
    }

    $csrfToken = $input['csrf_token'] ?? '';
    if (!verifyCsrfToken($csrfToken)) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Invalid CSRF token. Please refresh and try again.']);
        exit;
    }

    $current = readAbout();
    $aboutData = $input['data'] ?? $input;

    if (!is_array($aboutData)) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'error' => 'Invalid data payload format.']);
        exit;
    }

    // Sanitize and update fields
    $updated = [
        'last_updated' => date('c'),
        'hero' => [
            'tagline'     => trim($aboutData['hero']['tagline'] ?? $current['hero']['tagline'] ?? ''),
            'title_main'  => trim($aboutData['hero']['title_main'] ?? $current['hero']['title_main'] ?? 'About'),
            'title_italic'=> trim($aboutData['hero']['title_italic'] ?? $current['hero']['title_italic'] ?? 'ZYAGO'),
            'title_sub'   => trim($aboutData['hero']['title_sub'] ?? $current['hero']['title_sub'] ?? 'STUDIO'),
        ],
        'founder' => [
            'role'           => trim($aboutData['founder']['role'] ?? $current['founder']['role'] ?? ''),
            'name'           => trim($aboutData['founder']['name'] ?? $current['founder']['name'] ?? ''),
            'subhead'        => trim($aboutData['founder']['subhead'] ?? $current['founder']['subhead'] ?? ''),
            'bio_paragraphs' => (function() use ($aboutData, $current) {
                $rawBio = $aboutData['founder']['bio_paragraphs'] ?? $current['founder']['bio_paragraphs'] ?? [];
                if (is_string($rawBio)) {
                    $lines = preg_split('/\r\n\r\n|\n\n|\r\r/', $rawBio);
                    return array_values(array_filter(array_map('trim', $lines)));
                }
                return array_values(array_filter(array_map('trim', (array)$rawBio)));
            })(),
            'quote'          => trim($aboutData['founder']['quote'] ?? $current['founder']['quote'] ?? ''),
        ],
        'story' => [
            'title'           => trim($aboutData['story']['title'] ?? $current['story']['title'] ?? 'OUR STORY'),
            'block1_heading'  => trim($aboutData['story']['block1_heading'] ?? $current['story']['block1_heading'] ?? ''),
            'block1_text'     => trim($aboutData['story']['block1_text'] ?? $current['story']['block1_text'] ?? ''),
            'block2_heading'  => trim($aboutData['story']['block2_heading'] ?? $current['story']['block2_heading'] ?? ''),
            'block2_text'     => trim($aboutData['story']['block2_text'] ?? $current['story']['block2_text'] ?? ''),
            'block2_highlight'=> trim($aboutData['story']['block2_highlight'] ?? $current['story']['block2_highlight'] ?? ''),
        ],
        'vision' => [
            'title'     => trim($aboutData['vision']['title'] ?? $current['vision']['title'] ?? 'OUR VISION'),
            'subhead'   => trim($aboutData['vision']['subhead'] ?? $current['vision']['subhead'] ?? ''),
            'text'      => trim($aboutData['vision']['text'] ?? $current['vision']['text'] ?? ''),
            'highlight' => trim($aboutData['vision']['highlight'] ?? $current['vision']['highlight'] ?? ''),
        ],
        'mission' => [
            'title'     => trim($aboutData['mission']['title'] ?? $current['mission']['title'] ?? 'OUR MISSION'),
            'subhead'   => trim($aboutData['mission']['subhead'] ?? $current['mission']['subhead'] ?? ''),
            'intro'     => trim($aboutData['mission']['intro'] ?? $current['mission']['intro'] ?? ''),
            'points'    => (function() use ($aboutData, $current) {
                $pts = $aboutData['mission']['points'] ?? $current['mission']['points'] ?? [];
                if (!is_array($pts)) return [];
                $clean = [];
                foreach ($pts as $p) {
                    if (is_array($p)) {
                        $clean[] = [
                            'title' => trim($p['title'] ?? ''),
                            'desc'  => trim($p['desc'] ?? ''),
                        ];
                    }
                }
                return $clean;
            })(),
            'outro'     => trim($aboutData['mission']['outro'] ?? $current['mission']['outro'] ?? ''),
            'highlight' => trim($aboutData['mission']['highlight'] ?? $current['mission']['highlight'] ?? ''),
        ],
        'inspiration' => [
            'eyebrow' => trim($aboutData['inspiration']['eyebrow'] ?? $current['inspiration']['eyebrow'] ?? ''),
            'title'   => trim($aboutData['inspiration']['title'] ?? $current['inspiration']['title'] ?? ''),
            'text'    => trim($aboutData['inspiration']['text'] ?? $current['inspiration']['text'] ?? ''),
        ],
    ];

    if (!writeAbout($updated)) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'Failed to save about page changes to disk.']);
        exit;
    }

    http_response_code(200);
    echo json_encode([
        'ok'      => true,
        'message' => 'About page content updated and published successfully!',
        'data'    => $updated,
    ]);
    exit;
}

http_response_code(405);
echo json_encode(['ok' => false, 'error' => 'Method not allowed.']);

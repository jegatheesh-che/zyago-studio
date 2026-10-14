<?php
// ============================================================
// Zyago Studio — Admin Dashboard (index.php)
// ============================================================
require_once __DIR__ . '/auth.php';
requireLogin();

$gallery = readGallery();
$items   = $gallery['items'] ?? [];

$studioCount        = count(array_filter($items, fn($i) => ($i['category'] ?? '') === 'studio'));
$eventsCount        = count(array_filter($items, fn($i) => ($i['category'] ?? '') === 'events'));
$pageStudioCount    = count(array_filter($items, fn($i) => in_array('studio-portrait', $i['pages'] ?? [])));
$pageEventsCount    = count(array_filter($items, fn($i) => in_array('life-events', $i['pages'] ?? [])));
$pageGalleriesCount = count(array_filter($items, fn($i) => in_array('galleries', $i['pages'] ?? [])));
$newItems           = array_filter($items, fn($i) => !($i['is_default'] ?? false));
$newCount           = count($newItems);
$about              = readAbout();
$csrf               = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Photo Manager · Zyago Studio Admin</title>
  <meta name="robots" content="noindex, nofollow">
  <link rel="icon" type="image/png" href="../assets/images/favicon-192.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,400;1,500&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap">
  <script>
    (function() {
      try {
        var t = localStorage.getItem('zyago_admin_theme') || 'dark';
        document.documentElement.setAttribute('data-theme', t);
      } catch(e) {}
    })();
  </script>
  <link rel="stylesheet" href="css/admin.css">
</head>
<body class="admin-body">

<!-- ── SIDEBAR ─────────────────────────────────────────────── -->
<aside class="admin-sidebar" id="adminSidebar">
  <div class="sidebar-top">
    <div class="sidebar-brand">
      <img src="../assets/images/brand-logo.webp" alt="Zyago Studio" class="sidebar-logo">
      <div>
        <span class="sidebar-brand-name">ZYAGO</span>
        <span class="sidebar-brand-sub">Studio Admin</span>
      </div>
    </div>
    <button class="sidebar-close-btn" id="sidebarCloseBtn" aria-label="Close sidebar">
      <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
  </div>

  <nav class="sidebar-nav">
    <a href="#dashSection"   class="sidebar-link active" data-section="dashSection">
      <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
      Dashboard
    </a>
    <a href="#uploadSection" class="sidebar-link" data-section="uploadSection">
      <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/></svg>
      Upload Photos
    </a>
    <a href="#gallerySection" class="sidebar-link" data-section="gallerySection">
      <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
      Manage Gallery
    </a>
    <a href="#aboutSection" class="sidebar-link" data-section="aboutSection">
      <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
      Edit About Page
    </a>
  </nav>

  <div class="sidebar-bottom">
    <a href="../index.html" target="_blank" class="sidebar-link sidebar-link-ext">
      <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
      View Live Site
    </a>
    <a href="logout.php" class="sidebar-link sidebar-link-danger">
      <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      Sign Out
    </a>
  </div>
</aside>

<!-- Mobile Sidebar Backdrop Overlay -->
<div class="sidebar-backdrop" id="sidebarBackdrop" aria-hidden="true"></div>

<!-- ── MAIN AREA ───────────────────────────────────────────── -->
<div class="admin-main" id="adminMain">

  <!-- Top Bar -->
  <header class="admin-topbar">
    <button class="topbar-menu-btn" id="sidebarOpenBtn" aria-label="Open sidebar">
      <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>
    <div class="topbar-breadcrumb">
      <span class="topbar-page-name" id="topbarPageName">Dashboard</span>
    </div>
    <div class="topbar-right">
      <!-- Theme Switcher Toggle (Black & White) -->
      <button type="button" class="theme-toggle-btn" id="themeToggleBtn" aria-label="Toggle Black or White Theme" title="Switch Theme (Black / White)">
        <span class="theme-toggle-track">
          <!-- Moon icon (shows in dark) -->
          <svg class="theme-icon-moon" viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
          <!-- Sun icon (shows in light) -->
          <svg class="theme-icon-sun" viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
        </span>
        <span class="theme-toggle-label" id="themeToggleLabel">Noir</span>
      </button>

      <span class="topbar-user">
        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        <?= htmlspecialchars(ADMIN_USERNAME) ?>
      </span>
    </div>
  </header>

  <!-- ── SECTION: DASHBOARD ─────────────────────────────── -->
  <section class="admin-section active" id="dashSection">
    <div class="section-header">
      <h2 class="section-title">Photo Manager</h2>
      <p class="section-subtitle">Manage your Zyago Studio gallery catalogue</p>
    </div>

    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon stat-icon-total">
          <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
        </div>
        <div class="stat-content">
          <span class="stat-num"><?= count($items) ?></span>
          <span class="stat-label">Total Photos</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon stat-icon-studio">
          <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </div>
        <div class="stat-content">
          <span class="stat-num"><?= $studioCount ?></span>
          <span class="stat-label">Studio Portraits</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon stat-icon-events">
          <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <div class="stat-content">
          <span class="stat-num"><?= $eventsCount ?></span>
          <span class="stat-label">Life Events</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon stat-icon-new">
          <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
        </div>
        <div class="stat-content">
          <span class="stat-num"><?= $newCount ?></span>
          <span class="stat-label">Recently Added</span>
        </div>
      </div>
    </div>

    <div class="quick-actions-grid quick-actions-grid-3">
      <button class="quick-action-card" id="quickUploadBtn" data-goto="uploadSection">
        <div class="qa-icon">
          <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/></svg>
        </div>
        <div class="qa-text">
          <span class="qa-title">Upload New Photo</span>
          <span class="qa-sub">Add to Studio or Events gallery</span>
        </div>
        <svg class="qa-arrow" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
      </button>
      <button class="quick-action-card" id="quickManageBtn" data-goto="gallerySection">
        <div class="qa-icon">
          <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
        </div>
        <div class="qa-text">
          <span class="qa-title">Manage Gallery</span>
          <span class="qa-sub">View &amp; delete uploaded photos</span>
        </div>
        <svg class="qa-arrow" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
      </button>
      <button class="quick-action-card" id="quickAboutBtn" data-goto="aboutSection">
        <div class="qa-icon">
          <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
        </div>
        <div class="qa-text">
          <span class="qa-title">Edit About Page</span>
          <span class="qa-sub">Update bio, story &amp; philosophy</span>
        </div>
        <svg class="qa-arrow" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
      </button>
    </div>

    <?php if ($newCount > 0): ?>
    <div class="recent-uploads-section">
      <h3 class="subsection-title">Recently Uploaded</h3>
      <div class="admin-thumb-grid">
        <?php foreach (array_reverse(array_values($newItems)) as $item): ?>
        <div class="admin-thumb-card" data-id="<?= htmlspecialchars($item['id']) ?>">
          <div class="thumb-img-wrap">
            <img src="../<?= htmlspecialchars($item['src']) ?>" alt="<?= htmlspecialchars($item['alt'] ?? $item['title']) ?>" class="thumb-img" loading="lazy">
            <span class="thumb-badge thumb-badge-<?= htmlspecialchars($item['category']) ?>"><?= htmlspecialchars($item['category_label']) ?></span>
          </div>
          <div class="thumb-meta">
            <span class="thumb-title"><?= htmlspecialchars(html_entity_decode($item['title'])) ?></span>
            <span class="thumb-date"><?= $item['uploaded_at'] ? date('d M Y', strtotime($item['uploaded_at'])) : 'Legacy' ?></span>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </section>

  <!-- ── SECTION: UPLOAD ────────────────────────────────── -->
  <section class="admin-section" id="uploadSection">
    <div class="section-header">
      <h2 class="section-title">Upload Photos</h2>
      <p class="section-subtitle">Add new photographs to the gallery catalogue</p>
    </div>

    <div class="upload-layout">
      <!-- Upload Form Card -->
      <div class="upload-form-card">
        <form id="uploadForm" enctype="multipart/form-data" novalidate>
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>" id="uploadCsrf">

          <div class="form-group">
            <label class="form-label">Category <span class="required">*</span></label>
            <div class="category-selector">
              <label class="category-option" id="catStudio">
                <input type="radio" name="category" value="studio" checked>
                <div class="category-option-inner">
                  <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                  <span class="cat-label">Studio Portraits</span>
                  <span class="cat-sub">Portraits &amp; fine art</span>
                </div>
              </label>
              <label class="category-option" id="catEvents">
                <input type="radio" name="category" value="events">
                <div class="category-option-inner">
                  <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                  <span class="cat-label">Life Events</span>
                  <span class="cat-sub">Weddings &amp; celebrations</span>
                </div>
              </label>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Display On Pages <span class="required">*</span> <span class="form-label-hint">(select where these photos will appear)</span></label>
            <div class="page-checkboxes-group" id="uploadPageCheckboxes">
              <label class="page-check-option">
                <input type="checkbox" name="pages[]" value="galleries" checked id="pageCheckGalleries">
                <span class="page-check-custom"></span>
                <div class="page-check-info">
                  <span class="page-check-name">Main Galleries</span>
                  <span class="page-check-route">/pages/galleries.html</span>
                </div>
              </label>
              <label class="page-check-option">
                <input type="checkbox" name="pages[]" value="studio-portrait" checked id="pageCheckStudio">
                <span class="page-check-custom"></span>
                <div class="page-check-info">
                  <span class="page-check-name">Studio Portrait Page</span>
                  <span class="page-check-route">/pages/studio-portrait.html</span>
                </div>
              </label>
              <label class="page-check-option">
                <input type="checkbox" name="pages[]" value="life-events" id="pageCheckEvents">
                <span class="page-check-custom"></span>
                <div class="page-check-info">
                  <span class="page-check-name">Life Events Page</span>
                  <span class="page-check-route">/pages/life-events.html</span>
                </div>
              </label>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Grid Layout <span class="required">*</span></label>
            <div class="grid-selector">
              <label class="grid-option active" data-val="item-portrait">
                <input type="radio" name="grid_class" value="item-portrait" checked>
                <div class="grid-preview grid-preview-portrait">
                  <div class="gp-box"></div>
                </div>
                <span>Portrait</span>
              </label>
              <label class="grid-option" data-val="item-portrait-tall">
                <input type="radio" name="grid_class" value="item-portrait-tall">
                <div class="grid-preview grid-preview-portrait-tall">
                  <div class="gp-box"></div>
                </div>
                <span>Portrait Tall</span>
              </label>
              <label class="grid-option" data-val="item-landscape">
                <input type="radio" name="grid_class" value="item-landscape">
                <div class="grid-preview grid-preview-landscape">
                  <div class="gp-box"></div>
                </div>
                <span>Landscape</span>
              </label>
            </div>
          </div>

          <div class="form-group">
            <label for="photoTitle" class="form-label">Photo Title <span class="form-label-hint">(optional base title — uses filename or appends numbers for batch)</span></label>
            <input type="text" id="photoTitle" name="title" class="form-input" placeholder="e.g. Golden Hour · Bridal Portraits" maxlength="120">
          </div>

          <div class="form-group">
            <label for="photoAlt" class="form-label">Alt Text <span class="form-label-hint">(for accessibility & SEO)</span></label>
            <input type="text" id="photoAlt" name="alt" class="form-input" placeholder="e.g. Bridal portrait in golden light" maxlength="200">
          </div>

          <div class="form-group">
            <label class="form-label">Photo File(s) <span class="required">*</span> <span class="form-label-hint">(select one or multiple photos)</span></label>
            <div class="file-drop-zone" id="fileDropZone">
              <input type="file" id="photoFile" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple required class="file-input-hidden">
              <div class="drop-zone-content" id="dropZoneContent">
                <svg viewBox="0 0 24 24" width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" class="drop-icon"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/></svg>
                <p class="drop-label">Drag &amp; drop photos here</p>
                <p class="drop-sub">or <button type="button" class="drop-browse-btn" id="browseTrigger">browse files</button></p>
                <p class="drop-info">Select one or multiple photos &nbsp;|&nbsp; JPEG · PNG · WebP &nbsp;|&nbsp; Max 25 MB each</p>
              </div>
              <div class="drop-zone-preview" id="dropZonePreview" style="display:none">
                <div class="multi-preview-header">
                  <div class="mph-info">
                    <span class="mph-count" id="previewCount">0 photos selected</span>
                    <span class="mph-size" id="previewTotalSize">0 MB</span>
                  </div>
                  <div class="mph-actions">
                    <button type="button" class="btn-preview-add" id="addMoreFilesBtn">+ Add More</button>
                    <button type="button" class="btn-preview-clear" id="removeFileBtn">Clear All</button>
                  </div>
                </div>
                <div class="multi-preview-grid" id="multiPreviewGrid"></div>
              </div>
            </div>
          </div>

          <div class="upload-progress-wrap" id="uploadProgressWrap" style="display:none">
            <div class="upload-progress-bar-bg">
              <div class="upload-progress-bar" id="uploadProgressBar" style="width:0%"></div>
            </div>
            <span class="upload-progress-label" id="uploadProgressLabel">Uploading…</span>
          </div>

          <div class="admin-alert" id="uploadAlert" style="display:none" role="alert"></div>

          <button type="submit" class="btn-admin-primary" id="uploadSubmitBtn" disabled>
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/></svg>
            <span class="btn-text" id="uploadBtnText">Upload to Gallery</span>
          </button>
        </form>
      </div>

      <!-- Tips panel -->
      <div class="upload-tips-card">
        <h4 class="tips-title">Upload Guidelines</h4>
        <ul class="tips-list">
          <li>
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            Multi-selection: <strong>Upload single or multiple photos at once</strong>
          </li>
          <li>
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            Accepted formats: <strong>JPEG, PNG, WebP</strong>
          </li>
          <li>
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            Maximum file size: <strong>25 MB per photo</strong>
          </li>
          <li>
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            Photos appear in <strong>both</strong> the Master Gallery and the category page
          </li>
          <li>
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            Choose <em>Grid Layout</em> to control how photos render in the masonry grid
          </li>
        </ul>
        <div class="tips-divider"></div>
        <h4 class="tips-title">Grid Layouts</h4>
        <div class="layout-legend">
          <div class="ll-item"><div class="ll-box ll-portrait"></div><span>Portrait — 1×1 square crop</span></div>
          <div class="ll-item"><div class="ll-box ll-portrait-tall"></div><span>Portrait Tall — 1×1.6 vertical</span></div>
          <div class="ll-item"><div class="ll-box ll-landscape"></div><span>Landscape — 2×1 wide</span></div>
        </div>
      </div>
    </div>
  </section>

  <!-- ── SECTION: GALLERY MANAGEMENT ───────────────────── -->
  <section class="admin-section" id="gallerySection">
    <div class="section-header">
      <h2 class="section-title">Manage Gallery</h2>
      <p class="section-subtitle">View, filter and delete photos from the catalogue</p>
    </div>

    <div class="gallery-management-toolbar">
      <div class="gallery-filter-bar">
        <button class="gallery-filter-pill active" data-filter="all">All (<span class="badge-total-count"><?= count($items) ?></span>)</button>
        <button class="gallery-filter-pill" data-filter="page-studio">Studio Page (<span class="badge-page-studio-count"><?= $pageStudioCount ?></span>)</button>
        <button class="gallery-filter-pill" data-filter="page-events">Life Events Page (<span class="badge-page-events-count"><?= $pageEventsCount ?></span>)</button>
        <button class="gallery-filter-pill" data-filter="page-galleries">Main Galleries (<span class="badge-page-galleries-count"><?= $pageGalleriesCount ?></span>)</button>
        <button class="gallery-filter-pill" data-filter="new">New Uploads (<span class="badge-new-count"><?= $newCount ?></span>)</button>
      </div>

      <div class="batch-actions-bar" id="batchActionsBar">
        <label class="batch-select-all" id="selectAllWrap" title="Select all deletable photos">
          <input type="checkbox" id="selectAllCheckbox" class="batch-checkbox">
          <span class="batch-select-label">Select All</span>
        </label>
        <span class="batch-selected-count" id="batchSelectedCount">0 selected</span>
        <button type="button" class="btn-batch-delete" id="batchDeleteBtn" disabled>
          <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
          Delete Selected (<span id="batchDeleteNum">0</span>)
        </button>
        <button type="button" class="btn-batch-cancel" id="batchCancelBtn" style="display:none">Deselect</button>
      </div>
    </div>

    <div class="admin-alert" id="deleteAlert" style="display:none" role="alert"></div>

    <div class="admin-gallery-grid" id="adminGalleryGrid">
      <?php foreach ($items as $item): 
        $itemPages = $item['pages'] ?? [];
      ?>
      <div class="admin-gallery-card <?= $item['is_default'] ? 'is-default' : 'is-new' ?>"
           data-id="<?= htmlspecialchars($item['id']) ?>"
           data-category="<?= htmlspecialchars($item['category']) ?>"
           data-default="<?= $item['is_default'] ? '1' : '0' ?>"
           data-pages="<?= htmlspecialchars(implode(',', $itemPages)) ?>"
           data-title="<?= htmlspecialchars(html_entity_decode($item['title'])) ?>"
           data-alt="<?= htmlspecialchars($item['alt'] ?? '') ?>"
           data-grid="<?= htmlspecialchars($item['grid_class'] ?? 'item-portrait') ?>"
           data-src="../<?= htmlspecialchars($item['src']) ?>">
        <div class="agc-img-wrap">
          <?php if (!$item['is_default']): ?>
          <label class="agc-select-wrap" title="Select photo for batch deletion">
            <input type="checkbox" class="agc-card-cb" data-id="<?= htmlspecialchars($item['id']) ?>" data-title="<?= htmlspecialchars(html_entity_decode($item['title'])) ?>">
            <span class="agc-cb-custom"></span>
          </label>
          <?php endif; ?>
          <img src="../<?= htmlspecialchars($item['src']) ?>" alt="<?= htmlspecialchars($item['alt'] ?? '') ?>" class="agc-img" loading="lazy">
          <div class="agc-overlay">
            <button type="button"
                    class="agc-edit-btn"
                    data-id="<?= htmlspecialchars($item['id']) ?>"
                    data-title="<?= htmlspecialchars(html_entity_decode($item['title'])) ?>"
                    data-alt="<?= htmlspecialchars($item['alt'] ?? '') ?>"
                    data-grid="<?= htmlspecialchars($item['grid_class'] ?? 'item-portrait') ?>"
                    data-pages="<?= htmlspecialchars(implode(',', $itemPages)) ?>"
                    data-src="../<?= htmlspecialchars($item['src']) ?>"
                    aria-label="Edit photo placement and details">
              <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              Edit
            </button>
            <?php if (!$item['is_default']): ?>
            <button type="button"
                    class="agc-delete-btn"
                    data-id="<?= htmlspecialchars($item['id']) ?>"
                    data-title="<?= htmlspecialchars(html_entity_decode($item['title'])) ?>"
                    aria-label="Delete photo">
              <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
              Delete
            </button>
            <?php else: ?>
            <span class="agc-default-badge">Default</span>
            <?php endif; ?>
          </div>
        </div>
        <div class="agc-meta">
          <div class="agc-meta-top">
            <span class="agc-cat-badge agc-cat-<?= htmlspecialchars($item['category']) ?>"><?= htmlspecialchars($item['category_label']) ?></span>
            <div class="agc-page-tags">
              <?php if (in_array('studio-portrait', $itemPages)): ?>
                <span class="page-tag page-tag-studio" title="Active on Studio Portrait Page">Studio</span>
              <?php endif; ?>
              <?php if (in_array('life-events', $itemPages)): ?>
                <span class="page-tag page-tag-events" title="Active on Life Events Page">Events</span>
              <?php endif; ?>
              <?php if (in_array('galleries', $itemPages)): ?>
                <span class="page-tag page-tag-galleries" title="Active on Main Galleries">Galleries</span>
              <?php endif; ?>
            </div>
          </div>
          <span class="agc-title"><?= htmlspecialchars(html_entity_decode($item['title'])) ?></span>
          <span class="agc-date"><?= $item['uploaded_at'] ? date('d M Y', strtotime($item['uploaded_at'])) : 'Legacy entry' ?></span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- ── SECTION: ABOUT EDITOR ────────────────────────── -->
  <section class="admin-section" id="aboutSection">
    <div class="section-header section-header-split">
      <div>
        <h2 class="section-title">Edit About Page</h2>
        <p class="section-subtitle">Manage narrative copy, founder biography, brand vision, mission, and philosophy</p>
      </div>
      <div class="header-action-group">
        <a href="../pages/about.html" target="_blank" class="btn-admin-secondary">
          <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
          View Live About ↗
        </a>
        <button type="submit" form="aboutForm" class="btn-admin-primary" id="aboutHeaderSaveBtn">
          <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
          <span class="btn-save-text">Save &amp; Publish</span>
        </button>
      </div>
    </div>

    <div id="aboutAlert" class="admin-alert" style="display:none;"></div>

    <form id="aboutForm" class="about-editor-form">
      <!-- Quick Anchor Pills -->
      <div class="about-nav-pills">
        <a href="#aboutCardHero" class="about-pill active">Hero Section</a>
        <a href="#aboutCardFounder" class="about-pill">Founder &amp; Philosophy</a>
        <a href="#aboutCardStory" class="about-pill">Our Story</a>
        <a href="#aboutCardVision" class="about-pill">North Star Vision</a>
        <a href="#aboutCardMission" class="about-pill">Mission &amp; Pillars</a>
        <a href="#aboutCardInspiration" class="about-pill">Inspiration</a>
      </div>

      <!-- CARD 1: HERO -->
      <div class="about-card" id="aboutCardHero">
        <div class="about-card-header">
          <div class="about-card-icon">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
          </div>
          <div>
            <h3 class="about-card-title">1. Hero Section</h3>
            <p class="about-card-subtitle">Headline and tagline introduced at the top of the About page</p>
          </div>
        </div>
        <div class="about-card-body">
          <div class="form-group">
            <label class="form-label" for="aboutHeroTagline">Top Tagline (Uppercase)</label>
            <input type="text" id="aboutHeroTagline" name="hero_tagline" class="form-input" value="<?= htmlspecialchars($about['hero']['tagline'] ?? '') ?>" placeholder="A Studio Built Around People, Stories & Visual Craft">
          </div>
          <div class="form-grid-3">
            <div class="form-group">
              <label class="form-label" for="aboutHeroTitleMain">Title Prefix</label>
              <input type="text" id="aboutHeroTitleMain" name="hero_title_main" class="form-input" value="<?= htmlspecialchars($about['hero']['title_main'] ?? 'About') ?>" placeholder="About">
            </div>
            <div class="form-group">
              <label class="form-label" for="aboutHeroTitleItalic">Accent Word (Italic)</label>
              <input type="text" id="aboutHeroTitleItalic" name="hero_title_italic" class="form-input" value="<?= htmlspecialchars($about['hero']['title_italic'] ?? 'ZYAGO') ?>" placeholder="ZYAGO">
            </div>
            <div class="form-group">
              <label class="form-label" for="aboutHeroTitleSub">Suffix (Line 2)</label>
              <input type="text" id="aboutHeroTitleSub" name="hero_title_sub" class="form-input" value="<?= htmlspecialchars($about['hero']['title_sub'] ?? 'STUDIO') ?>" placeholder="STUDIO">
            </div>
          </div>
        </div>
      </div>

      <!-- CARD 2: FOUNDER & PHILOSOPHY -->
      <div class="about-card" id="aboutCardFounder">
        <div class="about-card-header">
          <div class="about-card-icon">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          </div>
          <div>
            <h3 class="about-card-title">2. Founder &amp; Philosophy</h3>
            <p class="about-card-subtitle">Director of Photography profile, journey and philosophy quote</p>
          </div>
        </div>
        <div class="about-card-body">
          <div class="form-grid-2">
            <div class="form-group">
              <label class="form-label" for="aboutFounderName">Founder Heading</label>
              <input type="text" id="aboutFounderName" name="founder_name" class="form-input" value="<?= htmlspecialchars($about['founder']['name'] ?? 'Meet Venkatesh.') ?>" placeholder="Meet Venkatesh.">
            </div>
            <div class="form-group">
              <label class="form-label" for="aboutFounderRole">Role Caption (Uppercase)</label>
              <input type="text" id="aboutFounderRole" name="founder_role" class="form-input" value="<?= htmlspecialchars($about['founder']['role'] ?? 'DIRECTOR OF PHOTOGRAPHY & FOUNDER') ?>" placeholder="DIRECTOR OF PHOTOGRAPHY & FOUNDER">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label" for="aboutFounderSubhead">Subhead / Italic Vision Line</label>
            <input type="text" id="aboutFounderSubhead" name="founder_subhead" class="form-input" value="<?= htmlspecialchars($about['founder']['subhead'] ?? 'The vision behind Zyago Studio.') ?>" placeholder="The vision behind Zyago Studio.">
          </div>
          <div class="form-group">
            <label class="form-label" for="aboutFounderBio">
              Biography Paragraphs
              <span class="form-label-hint">Separate each paragraph with an empty line (double enter).</span>
            </label>
            <textarea id="aboutFounderBio" name="founder_bio" class="form-input form-textarea" rows="8"><?= htmlspecialchars(implode("\n\n", (array)($about['founder']['bio_paragraphs'] ?? []))) ?></textarea>
          </div>
          <div class="form-group">
            <label class="form-label" for="aboutFounderQuote">Founder Quote (Bordered Callout)</label>
            <input type="text" id="aboutFounderQuote" name="founder_quote" class="form-input" value="<?= htmlspecialchars($about['founder']['quote'] ?? '') ?>" placeholder="“Every photograph has a story...”">
          </div>
        </div>
      </div>

      <!-- CARD 3: OUR STORY -->
      <div class="about-card" id="aboutCardStory">
        <div class="about-card-header">
          <div class="about-card-icon">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
          </div>
          <div>
            <h3 class="about-card-title">3. Our Story</h3>
            <p class="about-card-subtitle">Two narrative cards outlining the studio evolution and mission</p>
          </div>
        </div>
        <div class="about-card-body">
          <div class="form-group">
            <label class="form-label" for="aboutStoryTitle">Section Main Heading</label>
            <input type="text" id="aboutStoryTitle" name="story_title" class="form-input" value="<?= htmlspecialchars($about['story']['title'] ?? 'OUR STORY') ?>" placeholder="OUR STORY">
          </div>
          <div class="form-grid-2">
            <!-- Narrative 01 -->
            <div class="story-editor-box">
              <span class="story-box-badge">Narrative 01</span>
              <div class="form-group">
                <label class="form-label" for="aboutStoryB1Heading">Block 1 Heading</label>
                <input type="text" id="aboutStoryB1Heading" name="story_b1_heading" class="form-input" value="<?= htmlspecialchars($about['story']['block1_heading'] ?? '') ?>" placeholder="More than photographs. Stories that stay with you.">
              </div>
              <div class="form-group">
                <label class="form-label" for="aboutStoryB1Text">
                  Block 1 Text
                  <span class="form-label-hint">Separate paragraphs with double newlines.</span>
                </label>
                <textarea id="aboutStoryB1Text" name="story_b1_text" class="form-input form-textarea" rows="9"><?= htmlspecialchars($about['story']['block1_text'] ?? '') ?></textarea>
              </div>
            </div>
            <!-- Narrative 02 -->
            <div class="story-editor-box">
              <span class="story-box-badge">Narrative 02</span>
              <div class="form-group">
                <label class="form-label" for="aboutStoryB2Heading">Block 2 Heading</label>
                <input type="text" id="aboutStoryB2Heading" name="story_b2_heading" class="form-input" value="<?= htmlspecialchars($about['story']['block2_heading'] ?? '') ?>" placeholder="From a camera to a creative vision.">
              </div>
              <div class="form-group">
                <label class="form-label" for="aboutStoryB2Text">
                  Block 2 Text
                  <span class="form-label-hint">Separate paragraphs with double newlines.</span>
                </label>
                <textarea id="aboutStoryB2Text" name="story_b2_text" class="form-input form-textarea" rows="6"><?= htmlspecialchars($about['story']['block2_text'] ?? '') ?></textarea>
              </div>
              <div class="form-group">
                <label class="form-label" for="aboutStoryB2Highlight">Block 2 Highlight Box</label>
                <input type="text" id="aboutStoryB2Highlight" name="story_b2_highlight" class="form-input" value="<?= htmlspecialchars($about['story']['block2_highlight'] ?? '') ?>" placeholder="Chennai is where the journey begins...">
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- CARD 4: NORTH STAR VISION -->
      <div class="about-card" id="aboutCardVision">
        <div class="about-card-header">
          <div class="about-card-icon">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          </div>
          <div>
            <h3 class="about-card-title">4. North Star Vision</h3>
            <p class="about-card-subtitle">Long-term vision statement and gold callout</p>
          </div>
        </div>
        <div class="about-card-body">
          <div class="form-grid-2">
            <div class="form-group">
              <label class="form-label" for="aboutVisionTitle">Vision Title</label>
              <input type="text" id="aboutVisionTitle" name="vision_title" class="form-input" value="<?= htmlspecialchars($about['vision']['title'] ?? 'OUR VISION') ?>" placeholder="OUR VISION">
            </div>
            <div class="form-group">
              <label class="form-label" for="aboutVisionSubhead">Subhead / Italic Statement</label>
              <input type="text" id="aboutVisionSubhead" name="vision_subhead" class="form-input" value="<?= htmlspecialchars($about['vision']['subhead'] ?? '') ?>" placeholder="To create work that people feel, remember and return to.">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label" for="aboutVisionText">
              Vision Statement
              <span class="form-label-hint">Separate paragraphs with double newlines.</span>
            </label>
            <textarea id="aboutVisionText" name="vision_text" class="form-input form-textarea" rows="6"><?= htmlspecialchars($about['vision']['text'] ?? '') ?></textarea>
          </div>
          <div class="form-group">
            <label class="form-label" for="aboutVisionHighlight">Gold Highlight Box</label>
            <input type="text" id="aboutVisionHighlight" name="vision_highlight" class="form-input" value="<?= htmlspecialchars($about['vision']['highlight'] ?? '') ?>" placeholder="We want our work to go places, but we never want to lose sight...">
          </div>
        </div>
      </div>

      <!-- CARD 5: PURPOSE & ACTION (MISSION) -->
      <div class="about-card" id="aboutCardMission">
        <div class="about-card-header">
          <div class="about-card-icon">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/></svg>
          </div>
          <div>
            <h3 class="about-card-title">5. Purpose &amp; Action (Our Mission)</h3>
            <p class="about-card-subtitle">Mission statement and five core pillars of Zyago Studio</p>
          </div>
        </div>
        <div class="about-card-body">
          <div class="form-grid-2">
            <div class="form-group">
              <label class="form-label" for="aboutMissionTitle">Mission Title</label>
              <input type="text" id="aboutMissionTitle" name="mission_title" class="form-input" value="<?= htmlspecialchars($about['mission']['title'] ?? 'OUR MISSION') ?>" placeholder="OUR MISSION">
            </div>
            <div class="form-group">
              <label class="form-label" for="aboutMissionSubhead">Subhead / Italic Tagline</label>
              <input type="text" id="aboutMissionSubhead" name="mission_subhead" class="form-input" value="<?= htmlspecialchars($about['mission']['subhead'] ?? '') ?>" placeholder="Keep learning. Keep creating. Keep becoming better.">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label" for="aboutMissionIntro">Mission Introduction</label>
            <textarea id="aboutMissionIntro" name="mission_intro" class="form-input form-textarea" rows="2"><?= htmlspecialchars($about['mission']['intro'] ?? '') ?></textarea>
          </div>

          <!-- 5 Mission Points / Pillars -->
          <div class="form-group">
            <label class="form-label">Five Core Pillars</label>
            <div class="mission-points-list">
              <?php
              $pts = $about['mission']['points'] ?? [];
              for ($pi = 0; $pi < 5; $pi++):
                $pTitle = $pts[$pi]['title'] ?? '';
                $pDesc  = $pts[$pi]['desc'] ?? '';
              ?>
              <div class="mission-point-item">
                <div class="mpi-num">0<?= $pi + 1 ?></div>
                <div class="mpi-inputs">
                  <input type="text" name="mission_point_title_<?= $pi ?>" class="form-input" value="<?= htmlspecialchars($pTitle) ?>" placeholder="Pillar <?= $pi + 1 ?> Title (e.g. Make every story personal.)">
                  <input type="text" name="mission_point_desc_<?= $pi ?>" class="form-input mpi-desc-input" value="<?= htmlspecialchars($pDesc) ?>" placeholder="Pillar <?= $pi + 1 ?> Description...">
                </div>
              </div>
              <?php endfor; ?>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" for="aboutMissionOutro">Mission Closing Statement</label>
            <textarea id="aboutMissionOutro" name="mission_outro" class="form-input form-textarea" rows="2"><?= htmlspecialchars($about['mission']['outro'] ?? '') ?></textarea>
          </div>
          <div class="form-group">
            <label class="form-label" for="aboutMissionHighlight">Gold Highlight Box</label>
            <input type="text" id="aboutMissionHighlight" name="mission_highlight" class="form-input" value="<?= htmlspecialchars($about['mission']['highlight'] ?? '') ?>" placeholder="Our goal is simple: do meaningful work, treat people well and never stop growing.">
          </div>
        </div>
      </div>

      <!-- CARD 6: FOUNDATIONAL ELEMENTS -->
      <div class="about-card" id="aboutCardInspiration">
        <div class="about-card-header">
          <div class="about-card-icon">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          </div>
          <div>
            <h3 class="about-card-title">6. Foundational Elements</h3>
            <p class="about-card-subtitle">Inspiration strip highlighted above the CTA section</p>
          </div>
        </div>
        <div class="about-card-body">
          <div class="form-grid-2">
            <div class="form-group">
              <label class="form-label" for="aboutInspirationEyebrow">Eyebrow</label>
              <input type="text" id="aboutInspirationEyebrow" name="inspiration_eyebrow" class="form-input" value="<?= htmlspecialchars($about['inspiration']['eyebrow'] ?? 'Foundational Elements') ?>" placeholder="Foundational Elements">
            </div>
            <div class="form-group">
              <label class="form-label" for="aboutInspirationTitle">Title</label>
              <input type="text" id="aboutInspirationTitle" name="inspiration_title" class="form-input" value="<?= htmlspecialchars($about['inspiration']['title'] ?? 'INSPIRATION') ?>" placeholder="INSPIRATION">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label" for="aboutInspirationText">Inspiration Elements (Centered Italic Line)</label>
            <input type="text" id="aboutInspirationText" name="inspiration_text" class="form-input" value="<?= htmlspecialchars($about['inspiration']['text'] ?? '') ?>" placeholder="Cinema · Golden Hour Light · Classical Sculpture · Old World Architecture · Poetic Stillness · Genuine Human Connection">
          </div>
        </div>
      </div>

      <!-- Sticky Save Bar -->
      <div class="about-save-bar">
        <div class="about-save-info">
          <span class="save-status-dot"></span>
          <span class="save-status-text" id="aboutSaveStatus">Ready to edit · Last saved: <?= !empty($about['last_updated']) ? date('d M Y, h:i A', strtotime($about['last_updated'])) : 'Original' ?></span>
        </div>
        <div class="about-save-actions">
          <button type="button" class="btn-admin-secondary" id="aboutResetBtn">Reset Changes</button>
          <button type="submit" class="btn-admin-primary" id="aboutSubmitBtn">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            <span class="btn-save-text">Save &amp; Publish Changes</span>
          </button>
        </div>
      </div>

    </form>
  </section>
</div><!-- /admin-main -->

<!-- ── EDIT PHOTO PLACEMENT & METADATA MODAL ───────────── -->
<div class="admin-modal-overlay" id="editModalOverlay" style="display:none" role="dialog" aria-modal="true">
  <div class="admin-modal edit-photo-modal">
    <div class="admin-modal-header">
      <h3 class="admin-modal-title" id="editModalTitle">Edit Photo &amp; Page Placement</h3>
      <button type="button" class="modal-close-btn" id="editModalCancelX" aria-label="Cancel">✕</button>
    </div>
    <form id="editPhotoForm" class="edit-photo-modal-form" novalidate>
      <input type="hidden" id="editPhotoId" name="id">
      <div class="admin-modal-body edit-modal-body">
        
        <div class="edit-modal-top-row">
          <div class="edit-modal-thumb-box">
            <img src="" alt="" id="editModalThumb" class="edit-modal-thumb">
          </div>
          <div class="edit-modal-pages-col">
            <label class="form-label">Display On Pages <span class="required">*</span></label>
            <div class="page-checkboxes-group page-checkboxes-compact">
              <label class="page-check-option">
                <input type="checkbox" name="edit_pages[]" value="galleries" id="editPageGalleries">
                <span class="page-check-custom"></span>
                <div class="page-check-info">
                  <span class="page-check-name">Main Galleries</span>
                  <span class="page-check-route">/pages/galleries.html</span>
                </div>
              </label>
              <label class="page-check-option">
                <input type="checkbox" name="edit_pages[]" value="studio-portrait" id="editPageStudio">
                <span class="page-check-custom"></span>
                <div class="page-check-info">
                  <span class="page-check-name">Studio Portrait Page</span>
                  <span class="page-check-route">/pages/studio-portrait.html</span>
                </div>
              </label>
              <label class="page-check-option">
                <input type="checkbox" name="edit_pages[]" value="life-events" id="editPageEvents">
                <span class="page-check-custom"></span>
                <div class="page-check-info">
                  <span class="page-check-name">Life Events Page</span>
                  <span class="page-check-route">/pages/life-events.html</span>
                </div>
              </label>
            </div>
          </div>
        </div>

        <div class="form-group" style="text-align: left; margin-top: 18px;">
          <label for="editPhotoTitle" class="form-label">Photo Title</label>
          <input type="text" id="editPhotoTitle" name="title" class="form-input" maxlength="120" placeholder="e.g. Fine Art Bridal Sitting">
        </div>

        <div class="form-group" style="text-align: left;">
          <label for="editPhotoAlt" class="form-label">Alt Text (Accessibility &amp; SEO)</label>
          <input type="text" id="editPhotoAlt" name="alt" class="form-input" maxlength="200" placeholder="e.g. Bridal portrait in soft sculpted light">
        </div>

        <div class="form-group" style="text-align: left;">
          <label class="form-label">Grid Layout</label>
          <div class="grid-selector grid-selector-compact">
            <label class="grid-option" data-val="item-portrait">
              <input type="radio" name="edit_grid_class" value="item-portrait" id="editGridPortrait">
              <span>Portrait</span>
            </label>
            <label class="grid-option" data-val="item-portrait-tall">
              <input type="radio" name="edit_grid_class" value="item-portrait-tall" id="editGridTall">
              <span>Portrait Tall</span>
            </label>
            <label class="grid-option" data-val="item-landscape">
              <input type="radio" name="edit_grid_class" value="item-landscape" id="editGridLandscape">
              <span>Landscape</span>
            </label>
          </div>
        </div>

        <div class="admin-alert" id="editModalAlert" style="display:none; text-align: left;" role="alert"></div>

      </div>
      <div class="admin-modal-footer">
        <button type="button" class="btn-admin-secondary" id="editModalCancelBtn">Cancel</button>
        <button type="submit" class="btn-admin-primary" id="editModalSaveBtn">
          <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
          <span id="editModalSaveText">Save Changes</span>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ── DELETE CONFIRM MODAL ───────────────────────────── -->
<div class="admin-modal-overlay" id="deleteModalOverlay" style="display:none" role="dialog" aria-modal="true">
  <div class="admin-modal">
    <div class="admin-modal-header">
      <h3 class="admin-modal-title" id="deleteModalTitle">Delete Photo</h3>
      <button type="button" class="modal-close-btn" id="modalCancelBtn" aria-label="Cancel">✕</button>
    </div>
    <div class="admin-modal-body">
      <div class="delete-confirm-icon">
        <svg viewBox="0 0 24 24" width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
      </div>
      <p class="delete-confirm-text" id="deleteConfirmPrompt">Are you sure you want to permanently delete:</p>
      <p class="delete-confirm-title" id="deleteConfirmTitle"></p>
      <p class="delete-confirm-warn">This will remove the photo file(s) and metadata. This action cannot be undone.</p>
    </div>
    <div class="admin-modal-footer">
      <button type="button" class="btn-admin-secondary" id="modalCancelBtn2">Cancel</button>
      <button type="button" class="btn-admin-danger" id="modalDeleteConfirmBtn">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
        <span id="modalDeleteBtnText">Yes, Delete Permanently</span>
      </button>
    </div>
  </div>
</div>

<div class="admin-toast" id="adminToast" role="status" aria-live="polite"></div>

<input type="hidden" id="adminCsrfToken" value="<?= htmlspecialchars($csrf) ?>">
<script src="js/admin.js"></script>
</body>
</html>

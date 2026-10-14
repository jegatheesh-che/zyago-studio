<?php
// ============================================================
// Zyago Studio — Admin Login Page
// ============================================================
require_once __DIR__ . '/auth.php';
startAdminSession();

// Already logged in → go to dashboard
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($csrf)) {
        $error = 'Security token invalid or expired. Please refresh and try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        if (loginAdmin($username, $password)) {
            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid credentials. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login · Zyago Studio</title>
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
<body class="admin-login-body">

  <div class="login-page">
    <div class="login-visual">
      <div class="login-visual-overlay"></div>
      <div class="login-brand-mark">
        <img src="../assets/images/brand-logo.webp" alt="Zyago Studio" class="login-logo">
        <div class="login-brand-text">
          <span class="login-brand-name">ZYAGO STUDIO</span>
          <span class="login-brand-tagline">Photography & Visual Art</span>
        </div>
      </div>
      <div class="login-visual-quote">
        <em>"Every photograph is a certificate of presence."</em>
        <span>— Roland Barthes</span>
      </div>
    </div>

    <div class="login-panel">
      <div class="login-form-wrap">
        <div class="login-header-row">
          <div class="login-header">
            <h1 class="login-title">Studio Admin</h1>
            <p class="login-subtitle">Secure access to photo management</p>
          </div>
          <button type="button" class="theme-toggle-btn" id="loginThemeToggleBtn" aria-label="Toggle Black or White Theme" title="Switch Theme (Black / White)">
            <span class="theme-toggle-track">
              <svg class="theme-icon-moon" viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
              <svg class="theme-icon-sun" viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
            </span>
            <span class="theme-toggle-label" id="loginThemeToggleLabel">Noir</span>
          </button>
        </div>

        <?php if ($error): ?>
        <div class="admin-alert admin-alert-error" role="alert">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="login.php" class="login-form" id="adminLoginForm" autocomplete="off">
          <?= csrfField() ?>
          <div class="form-group">
            <label for="loginUsername" class="form-label">Username</label>
            <div class="input-wrap">
              <svg class="input-icon" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              <input type="text" id="loginUsername" name="username" class="form-input" placeholder="Admin username" required autocomplete="off" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>
          </div>

          <div class="form-group">
            <label for="loginPassword" class="form-label">Password</label>
            <div class="input-wrap">
              <svg class="input-icon" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              <input type="password" id="loginPassword" name="password" class="form-input" placeholder="••••••••" required autocomplete="current-password">
              <button type="button" class="password-toggle" id="togglePassword" aria-label="Show/hide password" tabindex="-1">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" class="eye-icon eye-on"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" class="eye-icon eye-off" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
              </button>
            </div>
          </div>

          <button type="submit" class="btn-admin-primary btn-login" id="loginSubmitBtn">
            <span class="btn-text">Sign In to Studio Admin</span>
            <svg class="btn-arrow" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
          </button>
        </form>

        <div class="login-footer-note">
          <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          Restricted access. Authorized personnel only.
        </div>
      </div>
    </div>
  </div>

  <script>
    // Toggle password visibility
    document.getElementById('togglePassword').addEventListener('click', function() {
      var inp = document.getElementById('loginPassword');
      var eyeOn = this.querySelector('.eye-on');
      var eyeOff = this.querySelector('.eye-off');
      if (inp.type === 'password') {
        inp.type = 'text';
        eyeOn.style.display = 'none';
        eyeOff.style.display = '';
      } else {
        inp.type = 'password';
        eyeOn.style.display = '';
        eyeOff.style.display = 'none';
      }
    });

    // Shake on error
    <?php if ($error): ?>
    document.querySelector('.login-form').classList.add('shake');
    <?php endif; ?>

    // Theme Switcher on Login Page
    var loginThemeBtn = document.getElementById('loginThemeToggleBtn');
    var loginThemeLabel = document.getElementById('loginThemeToggleLabel');
    function updateLoginThemeUI(t) {
      document.documentElement.setAttribute('data-theme', t);
      if (loginThemeLabel) loginThemeLabel.textContent = t === 'light' ? 'Alabaster' : 'Noir';
    }
    var currentTheme = document.documentElement.getAttribute('data-theme') || 'dark';
    updateLoginThemeUI(currentTheme);
    if (loginThemeBtn) {
      loginThemeBtn.addEventListener('click', function() {
        var active = document.documentElement.getAttribute('data-theme') === 'light' ? 'light' : 'dark';
        var next = active === 'light' ? 'dark' : 'light';
        updateLoginThemeUI(next);
        try { localStorage.setItem('zyago_admin_theme', next); } catch(e) {}
      });
    }
  </script>
</body>
</html>

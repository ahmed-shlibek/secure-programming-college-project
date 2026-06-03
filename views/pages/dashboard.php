<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<main class="page-container">

  <div class="welcome-banner">
    <div class="welcome-banner-content">
      <div class="badge">
        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        Session Active
      </div>
      <h2>Welcome back, <?= e($user['username'] ?? 'User') ?>!</h2>
      <p>You're securely logged in. Your session is protected and encrypted.</p>
    </div>
  </div>

  <div class="page-header">
    <h1 class="page-title">Security Dashboard</h1>
    <p class="page-subtitle">Active security measures protecting your account</p>
  </div>

  <div class="features-grid">

    <div class="feature-item">
      <div class="feature-item-icon card-icon-primary">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
      </div>
      <div class="feature-item-body">
        <h3>CSRF Protection</h3>
        <p>Every form submission includes a cryptographically random token validated server-side to prevent cross-site request forgery.</p>
      </div>
    </div>

    <div class="feature-item">
      <div class="feature-item-icon card-icon-success">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
      </div>
      <div class="feature-item-body">
        <h3>bcrypt Password Hashing</h3>
        <p>Passwords are hashed using bcrypt with cost factor 12. Plain-text passwords are never stored.</p>
      </div>
    </div>

    <div class="feature-item">
      <div class="feature-item-icon card-icon-secondary">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
      </div>
      <div class="feature-item-body">
        <h3>SQL Injection Prevention</h3>
        <p>All database queries use PDO prepared statements with parameterized inputs — no raw string interpolation.</p>
      </div>
    </div>

    <div class="feature-item">
      <div class="feature-item-icon card-icon-warning">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      </div>
      <div class="feature-item-body">
        <h3>XSS Prevention</h3>
        <p>All user-supplied data rendered in HTML is escaped with <code>htmlspecialchars()</code> to neutralise script injection.</p>
      </div>
    </div>

    <div class="feature-item">
      <div class="feature-item-icon card-icon-error">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      </div>
      <div class="feature-item-body">
        <h3>Brute-Force Rate Limiting</h3>
        <p>After <?= MAX_LOGIN_ATTEMPTS ?> failed attempts the account is locked for 15 minutes. Attempts are tracked per identifier in the database.</p>
      </div>
    </div>

    <div class="feature-item">
      <div class="feature-item-icon card-icon-primary">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      </div>
      <div class="feature-item-body">
        <h3>Session Fixation Defence</h3>
        <p>The session ID is regenerated on successful login via <code>session_regenerate_id(true)</code> to prevent session fixation attacks.</p>
      </div>
    </div>

    <div class="feature-item">
      <div class="feature-item-icon card-icon-success">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
      </div>
      <div class="feature-item-body">
        <h3>Secure HTTP Headers</h3>
        <p>Every response includes <code>X-Frame-Options: DENY</code>, <code>X-Content-Type-Options</code>, and a Content Security Policy.</p>
      </div>
    </div>

    <div class="feature-item">
      <div class="feature-item-icon card-icon-secondary">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>
      </div>
      <div class="feature-item-body">
        <h3>Input Validation</h3>
        <p>Server-side validation enforces username format, email syntax, password complexity, and message length before any data is trusted.</p>
      </div>
    </div>

  </div>

  <div class="card" style="margin-top:1.5rem;">
    <div class="card-header">
      <div class="card-icon card-icon-primary">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      </div>
      <span class="card-title">Your Account Details</span>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1rem;">
      <div>
        <div style="font-size:.75rem;color:var(--text-muted);font-weight:500;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.25rem;">Username</div>
        <div style="font-weight:600;"><?= e($user['username'] ?? '—') ?></div>
      </div>
      <div>
        <div style="font-size:.75rem;color:var(--text-muted);font-weight:500;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.25rem;">Email</div>
        <div style="font-weight:600;"><?= e($user['email'] ?? '—') ?></div>
      </div>
      <div>
        <div style="font-size:.75rem;color:var(--text-muted);font-weight:500;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.25rem;">Member since</div>
        <div style="font-weight:600;"><?= e(date('M j, Y', strtotime($user['created_at'] ?? 'now'))) ?></div>
      </div>
      <div>
        <div style="font-size:.75rem;color:var(--text-muted);font-weight:500;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.25rem;">Session started</div>
        <div style="font-weight:600;"><?= e(date('H:i', $_SESSION['login_time'] ?? time())) ?></div>
      </div>
    </div>
  </div>

</main>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

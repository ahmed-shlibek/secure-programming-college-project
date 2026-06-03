<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle ?? 'SecureApp') ?> — SecureApp</title>
  <link rel="stylesheet" href="<?= url('css/style.css') ?>">
</head>
<body>
<div class="main-layout">

<nav class="navbar">
  <div class="navbar-inner">
    <a class="navbar-brand" href="<?= url(isLoggedIn() ? 'dashboard' : 'about') ?>">
      <div class="brand-icon">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
      </div>
      <span class="brand-name">SecureApp</span>
    </a>

    <ul class="navbar-nav">
      <?php if (isLoggedIn()): ?>
        <li>
          <a href="<?= url('dashboard') ?>" class="<?= ($activePage ?? '') === 'dashboard' ? 'active' : '' ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            Dashboard
          </a>
        </li>
        <li>
          <a href="<?= url('about') ?>" class="<?= ($activePage ?? '') === 'about' ? 'active' : '' ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
            About
          </a>
        </li>
        <li>
          <div class="navbar-user">
            <div class="avatar"><?= e(substr(currentUser()['username'] ?? 'U', 0, 1)) ?></div>
            <?= e(currentUser()['username'] ?? 'User') ?>
          </div>
        </li>
        <li class="nav-logout">
          <form method="POST" action="<?= url('logout') ?>">
            <?= csrfField() ?>
            <button type="submit">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
              Sign Out
            </button>
          </form>
        </li>
      <?php else: ?>
        <li>
          <a href="<?= url('about') ?>" class="<?= ($activePage ?? '') === 'about' ? 'active' : '' ?>">About</a>
        </li>
        <li>
          <a href="<?= url('login') ?>">Sign In</a>
        </li>
        <li>
          <a href="<?= url('register') ?>" class="btn btn-primary" style="padding:.5rem 1rem;font-size:.875rem;">Register</a>
        </li>
      <?php endif; ?>
    </ul>
  </div>
</nav>

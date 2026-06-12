<?php

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function currentUser(): ?array
{
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'id'       => $_SESSION['user_id'],
        'username' => $_SESSION['username'] ?? 'User',
        'role'     => $_SESSION['role'] ?? 'user',
    ];
}

function isAdmin(): bool
{
    return isLoggedIn() && (($_SESSION['role'] ?? 'user') === 'admin');
}

// The real client IP. Behind Cloudflare, REMOTE_ADDR is Cloudflare's edge IP, so
// the genuine visitor IP arrives in CF-Connecting-IP. We only trust that header
// because the origin-verification check rejects any request that didn't come
// through Cloudflare; locally (no Cloudflare) we fall back to REMOTE_ADDR.
function clientIp(): string
{
    $cf = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? '';
    if ($cf !== '' && filter_var($cf, FILTER_VALIDATE_IP)) {
        return $cf;
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

// Guard for admin-only actions. Call at the very top of every admin controller
// action — never rely on a hidden nav link for authorization.
function requireAdmin(): void
{
    if (!isAdmin()) {
        flash('error', 'Access denied — administrators only.');
        redirect(isLoggedIn() ? '/about' : '/login');
    }
}

function redirect(string $path): never
{
    $base = defined('BASE_URL') ? BASE_URL : '';
    header('Location: ' . $base . $path);
    exit;
}

function url(string $path = ''): string
{
    $base = defined('BASE_URL') ? BASE_URL : '';
    return $base . '/' . ltrim($path, '/');
}

// Versioned asset URL. Appends the file's modification time as ?v=... so that
// browsers and the Cloudflare CDN fetch a fresh copy whenever the file changes
// (cache busting). Falls back to the plain URL if the file can't be found.
function asset(string $path): string
{
    $path = ltrim($path, '/');
    $full = __DIR__ . '/../../public/' . $path;
    $url  = url($path);
    if (is_file($full)) {
        $url .= '?v=' . filemtime($full);
    }
    return $url;
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Flash messages

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }
    $value = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $value;
}

// CSRF protection

function generateCsrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(string $token): bool
{
    if (empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

function csrfField(): string
{
    $token = e(generateCsrfToken());
    return "<input type=\"hidden\" name=\"csrf_token\" value=\"{$token}\">";
}

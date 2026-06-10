<?php

namespace App\Controllers;

use App\Models\User;

class AuthController
{
    // Show login form
    public function showLogin(): void
    {
        if (isLoggedIn()) {
            redirect('/dashboard');
        }
        $pageTitle = 'Sign In';
        $error     = flash('error');
        $success   = flash('success');
        require __DIR__ . '/../../views/auth/login.php';
    }

    // Process login

    public function login(): void
    {
        // CSRF check
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            flash('error', 'Invalid security token. Please try again.');
            redirect('/login');
        }

        $identifier = trim($_POST['identifier'] ?? '');
        $password   = $_POST['password'] ?? '';

        if ($identifier === '' || $password === '') {
            flash('error', 'All fields are required.');
            redirect('/login');
        }

        // Brute-force protection
        if ($this->isRateLimited($identifier)) {
            flash('error', 'Too many failed attempts. Please wait 15 minutes before trying again.');
            redirect('/login');
        }

        // Fetch user
        $user = User::findByEmailOrUsername($identifier);

        // Constant-time failure path (prevents username enumeration via timing)
        if (!$user) {
            password_verify($password, '$2y$12$invalidhashpadding000000000000000000000000000000000000u');
            $this->recordFailedAttempt($identifier);
            flash('error', 'Invalid credentials.');
            redirect('/login');
        }

        if (!password_verify($password, $user['password'])) {
            $this->recordFailedAttempt($identifier);
            flash('error', 'Invalid credentials.');
            redirect('/login');
        }

        // Successful login — clear old attempts and regenerate session
        $this->clearFailedAttempts($identifier);
        session_regenerate_id(true);

        $_SESSION['user_id']    = $user['id'];
        $_SESSION['username']   = $user['username'];
        $_SESSION['login_time'] = time();
        unset($_SESSION['csrf_token']); // force fresh CSRF token after login

        redirect('/dashboard');
    }

    // Show register form

    public function showRegister(): void
    {
        if (isLoggedIn()) {
            redirect('/dashboard');
        }
        $pageTitle = 'Create Account';
        $error     = flash('error');
        $success   = flash('success');
        require __DIR__ . '/../../views/auth/register.php';
    }

    // Process registration

    public function register(): void
    {
        // CSRF check
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            flash('error', 'Invalid security token. Please try again.');
            redirect('/register');
        }

        $username        = trim($_POST['username'] ?? '');
        $email           = trim($_POST['email'] ?? '');
        $password        = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $errors = $this->validateRegistration($username, $email, $password, $confirmPassword);

        if (!empty($errors)) {
            flash('error', implode('<br>', array_map('e', $errors)));
            redirect('/register');
        }

        if (User::findByEmail($email)) {
            flash('error', 'An account with this email already exists.');
            redirect('/register');
        }

        if (User::findByUsername($username)) {
            flash('error', 'This username is already taken.');
            redirect('/register');
        }

        // Hash with bcrypt cost 12
        $hashed = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        User::create($username, $email, $hashed);

        flash('success', 'Account created! You can now sign in.');
        redirect('/login');
    }

    // Logout

    public function logout(): void
    {
        // Accept CSRF check on logout to prevent forced-logout attacks
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            redirect('/dashboard');
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }

        session_destroy();
        redirect('/login');
    }

    // Helpers

    private function validateRegistration(
        string $username,
        string $email,
        string $password,
        string $confirm
    ): array {
        $errors = [];

        if (strlen($username) < 3 || strlen($username) > 50) {
            $errors[] = 'Username must be 3–50 characters.';
        }
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors[] = 'Username may only contain letters, numbers, and underscores.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must include at least one uppercase letter.';
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must include at least one number.';
        }
        if ($password !== $confirm) {
            $errors[] = 'Passwords do not match.';
        }

        return $errors;
    }

    private function isRateLimited(string $identifier): bool
    {
        $db   = getDB();
        $stmt = $db->prepare(
            'SELECT COUNT(*) FROM login_attempts
             WHERE identifier = ?
               AND attempted_at > DATE_SUB(NOW(), INTERVAL ? SECOND)'
        );
        $stmt->execute([$identifier, LOGIN_LOCKOUT_TIME]);
        return (int) $stmt->fetchColumn() >= MAX_LOGIN_ATTEMPTS;
    }

    private function recordFailedAttempt(string $identifier): void
    {
        $db   = getDB();
        $stmt = $db->prepare(
            'INSERT INTO login_attempts (identifier, ip_address) VALUES (?, ?)'
        );
        $stmt->execute([$identifier, $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0']);
    }

    private function clearFailedAttempts(string $identifier): void
    {
        $db   = getDB();
        $stmt = $db->prepare('DELETE FROM login_attempts WHERE identifier = ?');
        $stmt->execute([$identifier]);
    }
}

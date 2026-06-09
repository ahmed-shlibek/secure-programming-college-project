# Secure Programming College Project — Complete Guide

> **Who is this for?**  
> A new PHP student who wants to understand every part of this project: how it starts, how it handles a request, how the code is organized, and what every function does.

---

## Table of Contents

1. [What Is This Project?](#1-what-is-this-project)
2. [Project Directory Structure](#2-project-directory-structure)
3. [How a PHP Request Works (Lifecycle)](#3-how-a-php-request-works-lifecycle)
4. [Entry Point — `public/index.php`](#4-entry-point--publicindexphp)
5. [Routing — `app/routes.php`](#5-routing--approuteephp)
6. [Configuration Files](#6-configuration-files)
7. [Environment Variables — `.env`](#7-environment-variables--env)
8. [Helpers (Utility Functions)](#8-helpers-utility-functions)
9. [Models — Talking to the Database](#9-models--talking-to-the-database)
10. [Controllers — Business Logic](#10-controllers--business-logic)
11. [Views — HTML Pages](#11-views--html-pages)
12. [PHP Magic Methods Explained](#12-php-magic-methods-explained)
13. [Security Features — How and Why](#13-security-features--how-and-why)
14. [Database Tables](#14-database-tables)
15. [Full Authentication Flow (Step by Step)](#15-full-authentication-flow-step-by-step)
16. [Glossary](#16-glossary)

---

## 1. What Is This Project?

This is a **secure PHP web application** that demonstrates how to build a login/registration system while following security best practices. It includes:

- User registration and login
- A protected dashboard (only visible after login)
- A public "About" page with a contact form
- Protection against common attacks: SQL injection, XSS, CSRF, brute force, and more

The project follows an **MVC-like architecture** (Model–View–Controller), which is a popular way to organize web applications. Instead of putting all code in one file, it is split into logical layers:

| Layer | Job |
|-------|-----|
| **Model** | Talks to the database |
| **View** | Displays HTML to the user |
| **Controller** | Handles the logic in between |

---

## 2. Project Directory Structure

```
secure-programming-college-project/
│
├── public/                     ← Only this folder is exposed to the web
│   ├── index.php               ← EVERY request starts here (front controller)
│   └── js/
│       └── main.js             ← Client-side JavaScript
│
├── app/                        ← Application core (not directly accessible from web)
│   ├── config/
│   │   ├── app.php             ← App-wide constants (name, version, limits)
│   │   └── database.php        ← PDO database connection
│   ├── controllers/
│   │   ├── AuthController.php  ← Login, register, logout logic
│   │   ├── PageController.php  ← Dashboard and about page
│   │   └── ContactController.php ← Contact form submission
│   ├── models/
│   │   ├── User.php            ← User database queries
│   │   └── Contact.php         ← Contact database queries
│   ├── helpers/
│   │   ├── functions.php       ← Reusable utility functions
│   │   └── env.php             ← .env file loader
│   └── routes.php              ← Route definitions
│
├── views/                      ← HTML templates (PHP mixed with HTML)
│   ├── layouts/
│   │   ├── header.php          ← Top navbar, included by every page
│   │   └── footer.php          ← Closing tags, JS scripts
│   ├── auth/
│   │   ├── login.php           ← Login form page
│   │   └── register.php        ← Registration form page
│   ├── pages/
│   │   ├── dashboard.php       ← Protected dashboard
│   │   └── about.php           ← Public about + contact form
│   └── errors/
│       └── 404.php             ← "Page not found" error page
│
├── .env                        ← Secret config (DB credentials, never commit this)
├── .env.example                ← Template showing what .env should look like
└── composer.json               ← PHP dependency manager config
```

### Why is only `public/` exposed?

In a real server setup, you point your web server (Apache/Nginx) to the `public/` folder. This means users can only directly request files inside `public/`. All your PHP logic inside `app/` and your templates inside `views/` are **protected from direct browser access**. This is a security best practice.

---

## 3. How a PHP Request Works (Lifecycle)

Understanding the request lifecycle is the most important concept in this project. Here is exactly what happens when a user types a URL in their browser:

```
User types: http://localhost/login

Step 1: Browser sends HTTP GET request to your server
Step 2: Web server receives it, routes ALL requests to public/index.php
Step 3: index.php starts executing top-to-bottom:
        a. Configure session security settings
        b. Send security HTTP headers
        c. Load .env file (DB credentials etc.)
        d. Load config/app.php (constants)
        e. Load config/database.php (connect to DB)
        f. Load helpers (utility functions)
        g. Load routes.php (the route list)
        h. Parse the URL path → "/login"
        i. Loop through routes to find a match
        j. Check if user is logged in (auth guard)
        k. Load the correct controller file
        l. Create controller instance
        m. Call the matching method
        n. That method loads a view (HTML) and sends it to the browser

Step 4: Browser receives HTML and renders the page
```

This pattern — where every request goes through one single PHP file — is called the **Front Controller Pattern**. It gives you one central place to add security checks, logging, and routing.

---

## 4. Entry Point — `public/index.php`

This is the most important file. It runs on **every single request**.

### 4.1 Session Security (runs first, before any output)

```php
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
session_start();
```

| Setting | What it does |
|---------|-------------|
| `cookie_httponly = 1` | JavaScript cannot read the session cookie (blocks XSS stealing sessions) |
| `use_strict_mode = 1` | PHP ignores session IDs it didn't create (blocks session fixation) |
| `cookie_samesite = Lax` | Cookie is not sent with cross-site requests (blocks CSRF) |
| `gc_maxlifetime` | Session expires after 2 hours of inactivity |

### 4.2 Security HTTP Headers

```php
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; ...");
```

These are instructions sent to the browser alongside the HTML. They tell the browser to enforce extra protections (explained in detail in section 13).

### 4.3 URL Parsing

```php
$requestUri = $_SERVER['REQUEST_URI'];
$basePath = rtrim(parse_url(BASE_URL, PHP_URL_PATH) ?? '', '/');
$path = '/' . ltrim(substr($requestUri, strlen($basePath)), '/');
$path = strtok($path, '?'); // Strip query string
```

- `$_SERVER['REQUEST_URI']` gives the raw URL path, e.g. `/login?redirect=dashboard`
- `parse_url()` + `strtok()` strip the query string so we get just `/login`

### 4.4 Route Matching

```php
foreach ($routes as $route) {
    [$routeMethod, $routePath, $controllerName, $action] = $route;

    // Convert route pattern like /user/{id} to regex
    $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $routePath);

    if ($method === $routeMethod && preg_match("#^{$pattern}$#", $path, $matches)) {
        // Found a match!
    }
}
```

`preg_replace()` converts route placeholders like `{id}` into regex named groups. For example:
- Route pattern: `/user/{id}`
- Becomes regex: `/user/(?P<id>[^/]+)`
- Matches: `/user/42` and captures `id = 42`

### 4.5 Authentication Guard

```php
$publicRoutes = [
    'AuthController@showLogin',
    'AuthController@login',
    'AuthController@showRegister',
    'AuthController@register',
    'PageController@about',
    'ContactController@submit',
];

$routeKey = "{$controllerName}@{$action}";

if (!in_array($routeKey, $publicRoutes) && !isLoggedIn()) {
    redirect('/login');
}
```

Before calling the controller, the index.php checks: "Is this route public, or does the user need to be logged in?" If the route is not in the `$publicRoutes` list and the user is not logged in, they are redirected to `/login`.

### 4.6 Controller Dispatch

```php
require_once __DIR__ . "/../app/controllers/{$controllerName}.php";

// Load model files if they exist
foreach (['User', 'Contact'] as $model) {
    $modelFile = __DIR__ . "/../app/models/{$model}.php";
    if (file_exists($modelFile)) {
        require_once $modelFile;
    }
}

$controller = new $controllerName();
call_user_func_array([$controller, $action], array_values($params));
```

- `require_once` loads the PHP class file
- `new $controllerName()` creates an object of that class (e.g. `new AuthController()`)
- `call_user_func_array()` calls the method on that object and passes any URL parameters

---

## 5. Routing — `app/routes.php`

The routes file is simply an array that maps URL patterns to controller methods:

```php
return [
    ['GET',  '/login',     'AuthController',     'showLogin'],
    ['POST', '/login',     'AuthController',     'login'],
    ['GET',  '/register',  'AuthController',     'showRegister'],
    ['POST', '/register',  'AuthController',     'register'],
    ['POST', '/logout',    'AuthController',     'logout'],
    ['GET',  '/dashboard', 'PageController',     'dashboard'],
    ['GET',  '/about',     'PageController',     'about'],
    ['POST', '/contact',   'ContactController',  'submit'],
];
```

Each entry is an array with 4 values:
1. **HTTP method** — `GET` (user is visiting a page) or `POST` (user submitted a form)
2. **URL path** — the URL pattern to match
3. **Controller class** — which controller to use
4. **Method name** — which method inside that controller to call

**GET vs POST explained:**

- `GET /login` → Show the login form (no data being sent to server)
- `POST /login` → Process the login form (username/password being sent to server)

---

## 6. Configuration Files

### `app/config/app.php`

Defines global constants using `define()`:

```php
define('APP_NAME', 'secure-programming-college-project');
define('APP_VERSION', '1.0.0');
define('BASE_URL', '');          // Set this if app is in a subdirectory
define('MAX_LOGIN_ATTEMPTS', 5); // Lock out after 5 failed logins
define('LOGIN_LOCKOUT_TIME', 900);  // Lockout lasts 15 minutes (900 seconds)
define('SESSION_LIFETIME', 7200);   // Session expires after 2 hours
```

`define()` creates a constant — a value that cannot be changed after it is set. Constants are available everywhere in the code without needing to pass them as parameters.

### `app/config/database.php`

Creates a PDO (PHP Data Objects) database connection:

```php
function getDB(): PDO {
    static $pdo = null;  // Only create connection once

    if ($pdo === null) {
        $host = env('DB_HOST', '127.0.0.1');
        $port = env('DB_PORT', '3306');
        $name = env('DB_NAME', 'secure_programming_college_project');
        $user = env('DB_USER', 'root');
        $pass = env('DB_PASS', '');

        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }

    return $pdo;
}
```

**Key concepts:**

- `static $pdo = null` — The `static` keyword inside a function means this variable persists between calls. The connection is only created once (Singleton pattern).
- `PDO::ERRMODE_EXCEPTION` — PDO throws an exception if a query fails (instead of silently doing nothing).
- `PDO::FETCH_ASSOC` — Query results come back as associative arrays: `$row['email']` instead of `$row[1]`.
- `PDO::ATTR_EMULATE_PREPARES => false` — Uses the database's native prepared statements (more secure against SQL injection).

---

## 7. Environment Variables — `.env`

The `.env` file stores secrets that should not be in your source code:

```
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=secure_programming_college_project
DB_USER=root
DB_PASS=yourpassword
```

Never commit `.env` to git. That is why `.env.example` exists — it shows the structure without real secrets.

### How `.env` is loaded — `app/helpers/env.php`

```php
function loadEnv(string $path): void {
    if (!file_exists($path)) return;

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue; // Skip comments

        [$key, $value] = explode('=', $line, 2); // Split at first = only
        $key   = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'"); // Remove quotes

        putenv("{$key}={$value}");
        $_ENV[$key]    = $value;
        $_SERVER[$key] = $value;
    }
}
```

- `file()` reads the file as an array of lines
- `FILE_IGNORE_NEW_LINES` removes newline characters from each line
- `FILE_SKIP_EMPTY_LINES` skips blank lines
- `explode('=', $line, 2)` splits `DB_HOST=127.0.0.1` into `['DB_HOST', '127.0.0.1']`. The `2` limit means it only splits on the first `=`.

### `env()` function

```php
function env(string $key, $default = null) {
    $value = $_ENV[$key] ?? getenv($key);

    if ($value === false) return $default;

    return match(strtolower($value)) {
        'true'  => true,
        'false' => false,
        'empty' => '',
        'null'  => null,
        default => $value,
    };
}
```

The `match` expression converts string values like `"true"` to actual PHP booleans `true`. This means you can write `FEATURE_ENABLED=true` in `.env` and get a real boolean back.

---

## 8. Helpers (Utility Functions)

`app/helpers/functions.php` contains small reusable functions used throughout the project.

### `isLoggedIn()`

```php
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}
```

Checks if `user_id` exists in the session. When a user logs in, `$_SESSION['user_id']` is set. When they log out, the session is destroyed.

### `currentUser()`

```php
function currentUser(): ?array {
    if (!isLoggedIn()) return null;
    return [
        'id'       => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
    ];
}
```

Returns the logged-in user's data from the session. The `?array` return type means it can return either an array or `null`.

### `redirect()`

```php
function redirect(string $path): never {
    header('Location: ' . url($path));
    exit;
}
```

Sends an HTTP redirect header to the browser and immediately stops PHP execution with `exit`. The return type `never` means this function never returns — it always calls `exit`.

### `url()`

```php
function url(string $path): string {
    return BASE_URL . '/' . ltrim($path, '/');
}
```

Builds a full URL. If `BASE_URL` is empty: `url('/login')` → `/login`. If `BASE_URL` is `/myapp`: `url('/login')` → `/myapp/login`.

### `e()` — Output Escaping

```php
function e(mixed $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
```

Converts special HTML characters to safe entities:
- `<` → `&lt;`
- `>` → `&gt;`
- `"` → `&quot;`
- `'` → `&#039;`
- `&` → `&amp;`

**Always use `e()` when printing user-supplied data in HTML.** Without it, an attacker could inject `<script>` tags (XSS attack).

```php
// WRONG — attacker can inject <script>alert('hacked')</script>
echo $username;

// CORRECT — HTML is escaped, harmless text is displayed
echo e($username);
```

### `flash()` — Flash Messages

Flash messages are one-time messages shown after a redirect (like "Login failed" or "Account created!").

```php
function flash(string $key, ?string $message = null): ?string {
    if ($message !== null) {
        // SETTER: store the message
        $_SESSION['flash'][$key] = $message;
        return null;
    }
    // GETTER: retrieve and delete the message
    $value = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $value;
}
```

This one function handles both setting and getting flash messages:

```php
// In the controller (before redirecting):
flash('error', 'Invalid username or password.');
redirect('/login');

// In the view (after redirect):
$error = flash('error'); // Gets the message AND deletes it from session
if ($error): ?>
    <div class="alert"><?= e($error) ?></div>
<?php endif; ?>
```

### CSRF Token Functions

CSRF (Cross-Site Request Forgery) protection uses a secret token. See section 13 for a full explanation.

```php
function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // 64-char hex string
    }
    return $_SESSION['csrf_token'];
}
```

- `random_bytes(32)` generates 32 cryptographically random bytes
- `bin2hex()` converts those bytes to a 64-character hex string
- The token is stored in the session so we can compare it later

```php
function verifyCsrfToken(string $token): bool {
    $stored = $_SESSION['csrf_token'] ?? '';
    return hash_equals($stored, $token);
}
```

`hash_equals()` compares two strings in **constant time** — it always takes the same amount of time regardless of where strings differ. A normal `===` comparison short-circuits, which could leak timing information to an attacker.

```php
function csrfField(): string {
    $token = generateCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . e($token) . '">';
}
```

Outputs a hidden HTML form field containing the CSRF token.

---

## 9. Models — Talking to the Database

Models are classes that contain database queries. They never output HTML — they just fetch or save data.

### `app/models/User.php`

All methods are `static`, meaning you call them on the class itself, not on an object:

```php
// Static call (used in this project)
$user = User::findByEmail('alice@example.com');

// Instance call (NOT used — would require: $u = new User(); $u->findByEmail(...))
```

#### `User::findByEmailOrUsername()`

```php
public static function findByEmailOrUsername(string $identifier): ?array {
    $db   = getDB();
    $stmt = $db->prepare(
        "SELECT * FROM users WHERE email = :id OR username = :id2 LIMIT 1"
    );
    $stmt->execute([':id' => $identifier, ':id2' => $identifier]);
    return $stmt->fetch() ?: null;
}
```

- `getDB()` returns the PDO connection (singleton from config/database.php)
- `prepare()` creates a prepared statement — the SQL is sent to the database **before** the values
- `execute([':id' => $identifier])` fills in the placeholder safely
- `fetch()` returns one row as an associative array, or `false` if nothing found
- `?: null` converts `false` to `null` (cleaner return value)

**Why prepared statements prevent SQL injection:**

```php
// VULNERABLE — string concatenation lets attackers inject SQL
$stmt = $db->query("SELECT * FROM users WHERE email = '$email'");
// If $email = "' OR '1'='1" → query returns ALL users!

// SAFE — value is always treated as data, never as SQL
$stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
$stmt->execute([':email' => $email]);
```

#### `User::findById()`

```php
public static function findById(int $id): ?array {
    $db   = getDB();
    $stmt = $db->prepare(
        "SELECT id, username, email, created_at FROM users WHERE id = ? LIMIT 1"
    );
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}
```

Notice the SELECT deliberately **excludes the password column**. If you fetch a user to display on the dashboard, you should never include the password hash in the result. This is defence-in-depth — even if the result is accidentally exposed, no password hash is leaked.

#### `User::create()`

```php
public static function create(string $username, string $email, string $hashedPassword): int {
    $db   = getDB();
    $stmt = $db->prepare(
        "INSERT INTO users (username, email, password, created_at)
         VALUES (?, ?, ?, NOW())"
    );
    $stmt->execute([$username, $email, $hashedPassword]);
    return (int)$db->lastInsertId();
}
```

`lastInsertId()` returns the auto-increment ID of the row just inserted.

### `app/models/Contact.php`

```php
public static function create(string $name, string $email, string $message, string $ip): int {
    $db   = getDB();
    $stmt = $db->prepare(
        "INSERT INTO contacts (name, email, message, ip_address, created_at)
         VALUES (?, ?, ?, ?, NOW())"
    );
    $stmt->execute([$name, $email, $message, $ip]);
    return (int)$db->lastInsertId();
}
```

---

## 10. Controllers — Business Logic

Controllers receive requests from the router, use models to get/save data, and load views to show responses.

### `app/controllers/AuthController.php`

#### `showLogin()`

```php
public function showLogin(): void {
    if (isLoggedIn()) {
        redirect('/dashboard');
    }
    require __DIR__ . '/../../views/auth/login.php';
}
```

If the user is already logged in, redirect them to dashboard. Otherwise, load the login view.

#### `login()`

This is the most complex method. Here is the full flow:

```php
public function login(): void {
    // 1. Verify CSRF token
    $token = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($token)) {
        flash('error', 'Invalid request. Please try again.');
        redirect('/login');
    }

    // 2. Get and sanitize inputs
    $identifier = trim($_POST['identifier'] ?? '');
    $password   = $_POST['password'] ?? '';

    // 3. Basic validation
    if (empty($identifier) || empty($password)) {
        flash('error', 'Please fill in all fields.');
        redirect('/login');
    }

    // 4. Check rate limiting
    if ($this->isRateLimited($identifier)) {
        flash('error', 'Too many login attempts. Please try again in 15 minutes.');
        redirect('/login');
    }

    // 5. Look up user
    $user = User::findByEmailOrUsername($identifier);

    // 6. Verify password (constant-time)
    $dummyHash  = '$2y$12$invalidhashpaddinginvalidhashpadding00000000000000000000';
    $hashToTest = $user ? $user['password'] : $dummyHash;

    if (!$user || !password_verify($password, $hashToTest)) {
        $this->recordFailedAttempt($identifier);
        flash('error', 'Invalid credentials.');
        redirect('/login');
    }

    // 7. Success — set up session
    $this->clearFailedAttempts($identifier);
    session_regenerate_id(true);

    $_SESSION['user_id']    = $user['id'];
    $_SESSION['username']   = $user['username'];
    $_SESSION['login_time'] = time();

    unset($_SESSION['csrf_token']); // Force fresh CSRF token next request

    redirect('/dashboard');
}
```

**Why the dummy hash trick?**

```php
$dummyHash  = '$2y$12$invalidhashpaddinginvalidhashpadding00000000000000000000';
$hashToTest = $user ? $user['password'] : $dummyHash;
password_verify($password, $hashToTest);
```

If the user does not exist, calling `password_verify()` is skipped. An attacker measuring response times could notice that "user not found" responses are slightly faster than "wrong password" responses — revealing which usernames exist. By always calling `password_verify()` (with a dummy hash when user is not found), both paths take roughly the same time.

#### `register()`

```php
public function register(): void {
    // Verify CSRF
    $token = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($token)) { /* ... */ }

    // Collect inputs
    $username        = trim($_POST['username'] ?? '');
    $email           = trim($_POST['email'] ?? '');
    $password        = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validate inputs
    $errors = $this->validateRegistration($username, $email, $password, $confirmPassword);
    if (!empty($errors)) {
        flash('error', implode('<br>', $errors));
        redirect('/register');
    }

    // Check for duplicates
    if (User::findByEmail($email)) {
        flash('error', 'Email already registered.');
        redirect('/register');
    }
    if (User::findByUsername($username)) {
        flash('error', 'Username already taken.');
        redirect('/register');
    }

    // Hash password and create user
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    User::create($username, $email, $hashedPassword);

    flash('success', 'Account created! Please log in.');
    redirect('/login');
}
```

`password_hash($password, PASSWORD_BCRYPT, ['cost' => 12])` hashes a password using the bcrypt algorithm. The cost of `12` means the hash takes about 250ms to compute — slow enough to make brute force difficult, but fast enough for normal use.

#### `validateRegistration()` — private helper

```php
private function validateRegistration(
    string $username,
    string $email,
    string $password,
    string $confirmPassword
): array {
    $errors = [];

    if (strlen($username) < 3 || strlen($username) > 50) {
        $errors[] = 'Username must be 3–50 characters.';
    }
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = 'Username may only contain letters, numbers, and underscores.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email address.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter.';
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one number.';
    }
    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }

    return $errors;
}
```

Returns an array of error messages. Empty array = validation passed.

#### Rate Limiting Helpers

```php
private function isRateLimited(string $identifier): bool {
    $db   = getDB();
    $stmt = $db->prepare(
        "SELECT COUNT(*) FROM login_attempts
         WHERE identifier = ?
           AND attempted_at > NOW() - INTERVAL ? SECOND"
    );
    $stmt->execute([$identifier, LOGIN_LOCKOUT_TIME]);
    return (int)$stmt->fetchColumn() >= MAX_LOGIN_ATTEMPTS;
}

private function recordFailedAttempt(string $identifier): void {
    $db   = getDB();
    $stmt = $db->prepare(
        "INSERT INTO login_attempts (identifier, attempted_at, ip_address)
         VALUES (?, NOW(), ?)"
    );
    $stmt->execute([$identifier, $_SERVER['REMOTE_ADDR']]);
}

private function clearFailedAttempts(string $identifier): void {
    $db   = getDB();
    $stmt = $db->prepare("DELETE FROM login_attempts WHERE identifier = ?");
    $stmt->execute([$identifier]);
}
```

#### `logout()`

```php
public function logout(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($token)) {
        redirect('/dashboard');
    }

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], ...);
    }

    session_destroy();
    redirect('/login');
}
```

A proper logout must:
1. Clear the session data array `$_SESSION = []`
2. Delete the session cookie from the browser
3. Call `session_destroy()` to remove the server-side session file

### `app/controllers/PageController.php`

```php
public function dashboard(): void {
    $user = User::findById($_SESSION['user_id']);
    require __DIR__ . '/../../views/pages/dashboard.php';
}

public function about(): void {
    require __DIR__ . '/../../views/pages/about.php';
}
```

Simple controllers that just load views. The `dashboard()` fetches fresh user data from the DB (not just the session) to ensure the displayed info is current.

### `app/controllers/ContactController.php`

```php
public function submit(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($token)) { /* redirect */ }

    $name    = trim($_POST['name']    ?? '');
    $email   = trim($_POST['email']   ?? '');
    $message = trim($_POST['message'] ?? '');

    $errors = [];
    if (strlen($name) < 2 || strlen($name) > 100) {
        $errors[] = 'Name must be 2–100 characters.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email address.';
    }
    if (strlen($message) < 10 || strlen($message) > 2000) {
        $errors[] = 'Message must be 10–2000 characters.';
    }

    if (!empty($errors)) {
        flash('error', implode('<br>', $errors));
        redirect('/about');
    }

    $ip = $_SERVER['REMOTE_ADDR'];
    Contact::create($name, $email, $message, $ip);

    flash('success', 'Message sent successfully!');
    redirect('/about');
}
```

---

## 11. Views — HTML Pages

Views are PHP files that mix HTML with PHP output. They should never contain business logic — only display data.

### Layout system — `views/layouts/`

Every page includes the header and footer:

```php
// At the top of every view:
require __DIR__ . '/../layouts/header.php';

// ...page-specific HTML...

// At the bottom:
require __DIR__ . '/../layouts/footer.php';
```

#### `header.php` — Navigation bar

```php
<nav>
    <a href="<?= url('/dashboard') ?>">MyApp</a>

    <?php if (isLoggedIn()): ?>
        <!-- Logged-in navigation -->
        <a href="<?= url('/dashboard') ?>">Dashboard</a>
        <span><?= e(currentUser()['username']) ?></span>

        <!-- Logout (POST form for CSRF protection) -->
        <form method="POST" action="<?= url('/logout') ?>">
            <?= csrfField() ?>
            <button type="submit">Logout</button>
        </form>
    <?php else: ?>
        <!-- Guest navigation -->
        <a href="<?= url('/login') ?>">Login</a>
        <a href="<?= url('/register') ?>">Register</a>
    <?php endif; ?>
</nav>
```

Note that logout uses a **POST form, not a link**. If logout were a `<a href="/logout">` link, an attacker on another site could embed that link and trick you into logging out (CSRF). A POST request with a CSRF token prevents this.

#### `footer.php`

```php
<script src="<?= url('/js/main.js') ?>?v=<?= filemtime(
    __DIR__ . '/../../public/js/main.js'
) ?>"></script>
```

`filemtime()` returns the file modification timestamp. Appending it as `?v=1234567890` to the URL busts the browser cache whenever the file changes. Without this, users might see an old version of `main.js` after you update it.

### Display pattern in views

```php
<?php
// Always escape output
$username = currentUser()['username'];
?>
<h1>Welcome, <?= e($username) ?>!</h1>

<?php
// Flash messages
$error = flash('error');
if ($error): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>
```

---

## 12. PHP Magic Methods Explained

Magic methods are special PHP methods that start with double underscores (`__`). PHP calls them automatically in specific situations — you never call them directly.

**This project does not heavily use magic methods** (it uses static methods instead), but a PHP student should understand them. Here is a reference:

### `__construct()` — Constructor

Called automatically when you create a new object with `new`.

```php
class User {
    private string $name;
    private string $email;

    public function __construct(string $name, string $email) {
        $this->name  = $name;
        $this->email = $email;
    }
}

$user = new User('Alice', 'alice@example.com');
// PHP automatically calls __construct('Alice', 'alice@example.com')
```

**In this project:** Controllers do not define `__construct()`, so PHP uses the default empty constructor. Models use static methods only and are never instantiated.

### `__destruct()` — Destructor

Called automatically when an object is destroyed or the script ends.

```php
class DatabaseLogger {
    public function __destruct() {
        // Automatically called when script ends
        // Useful for cleanup, closing files, etc.
    }
}
```

### `__get()` and `__set()` — Property Overloading

Called when you access or set a property that does not exist.

```php
class MagicClass {
    private array $data = [];

    public function __get(string $name): mixed {
        return $this->data[$name] ?? null;
    }

    public function __set(string $name, mixed $value): void {
        $this->data[$name] = $value;
    }
}

$obj = new MagicClass();
$obj->foo = 'bar';   // PHP calls __set('foo', 'bar')
echo $obj->foo;      // PHP calls __get('foo') → 'bar'
```

### `__isset()` and `__unset()`

Called when `isset()` or `unset()` is used on non-existent properties.

```php
public function __isset(string $name): bool {
    return isset($this->data[$name]);
}

public function __unset(string $name): void {
    unset($this->data[$name]);
}
```

### `__toString()` — String Conversion

Called when you use an object where a string is expected (like in `echo`).

```php
class User {
    public function __toString(): string {
        return "User({$this->name})";
    }
}

$user = new User('Alice');
echo $user; // Calls __toString() → prints "User(Alice)"
```

### `__call()` and `__callStatic()` — Method Overloading

Called when a non-existent method is called.

```php
class Proxy {
    public function __call(string $name, array $args): mixed {
        echo "Called method: {$name} with " . count($args) . " args\n";
        return null;
    }

    public static function __callStatic(string $name, array $args): mixed {
        echo "Called static method: {$name}\n";
        return null;
    }
}

$p = new Proxy();
$p->anything(1, 2, 3); // Calls __call('anything', [1, 2, 3])
Proxy::foo();           // Calls __callStatic('foo', [])
```

### `__invoke()` — Callable Objects

Called when you use an object as a function.

```php
class Multiplier {
    public function __construct(private int $factor) {}

    public function __invoke(int $value): int {
        return $value * $this->factor;
    }
}

$double = new Multiplier(2);
echo $double(5); // Calls __invoke(5) → 10
```

### `__clone()` — Object Cloning

Called when you clone an object with `clone`.

```php
$original = new User('Alice');
$copy     = clone $original; // PHP calls __clone() on $copy
```

### `__sleep()` and `__wakeup()` — Serialization

Called when you `serialize()` or `unserialize()` an object.

```php
class Session {
    public function __sleep(): array {
        return ['user_id', 'username']; // Only serialize these properties
    }

    public function __wakeup(): void {
        // Reconnect to database after unserialization
        $this->db = getDB();
    }
}
```

### Summary Table

| Magic Method | When is it called? |
|---|---|
| `__construct()` | `new ClassName()` |
| `__destruct()` | Object destroyed or script ends |
| `__get($name)` | Read non-existent property |
| `__set($name, $value)` | Write non-existent property |
| `__isset($name)` | `isset($obj->nonExistentProp)` |
| `__unset($name)` | `unset($obj->nonExistentProp)` |
| `__toString()` | Object used as string (`echo $obj`) |
| `__call($name, $args)` | Call non-existent method |
| `__callStatic($name, $args)` | Call non-existent static method |
| `__invoke($args)` | Object used as function (`$obj(...)`) |
| `__clone()` | `clone $obj` |
| `__sleep()` | `serialize($obj)` |
| `__wakeup()` | `unserialize($data)` |

---

## 13. Security Features — How and Why

This project is specifically about **secure programming**. Here are all the security measures implemented, with explanations.

### 13.1 SQL Injection Prevention

**The threat:** An attacker enters SQL code as input to manipulate your database query.

```
Login username input: ' OR '1'='1
Generated SQL:  SELECT * FROM users WHERE username = '' OR '1'='1'
Result:         Returns ALL users → attacker logs in as first user
```

**The fix:** Prepared statements with parameter binding.

```php
// Secure — parameter is always treated as a string value, never executed as SQL
$stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]); // $username is bound safely
```

### 13.2 XSS (Cross-Site Scripting) Prevention

**The threat:** An attacker stores `<script>alert('Hacked!')</script>` as their username. When another user's browser loads the page, the script executes.

**The fix:** Escape all output with `htmlspecialchars()` via the `e()` helper.

```php
// Dangerous
echo $username; // If username is <script>..., it runs!

// Safe
echo e($username); // Outputs &lt;script&gt;... — displays as text, not code
```

Also, the **Content-Security-Policy** header tells the browser to only execute scripts from trusted sources, providing a second layer of defense.

### 13.3 CSRF (Cross-Site Request Forgery) Prevention

**The threat:** A malicious website tricks your browser into sending a request to our site (e.g., submitting the logout form or changing account settings).

**How CSRF works:**
```
1. You are logged in to bank.com
2. You visit evil.com
3. evil.com has: <img src="https://bank.com/transfer?to=attacker&amount=1000">
4. Your browser sends that request WITH your bank.com session cookie
5. Bank sees a valid authenticated request and transfers money
```

**The fix:** Every form includes a secret random token. The server checks this token on every POST request. An attacker on `evil.com` cannot read your session token (same-origin policy), so their fake request will fail the token check.

```php
// In the form (view):
<form method="POST">
    <?= csrfField() ?>  <!-- outputs <input type="hidden" name="csrf_token" value="abc123..."> -->
    ...
</form>

// In the controller:
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    // Reject the request
}
```

### 13.4 Password Hashing

**The threat:** If your database is compromised, plaintext or weakly-hashed passwords can be immediately used to log in to your site and the user's other accounts.

**The fix:** bcrypt hashing with cost 12.

```php
// Hashing (registration)
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
// Stored in DB: $2y$12$somesaltandhash...

// Verifying (login)
password_verify($plaintext, $storedHash); // true or false
```

bcrypt is a one-way function — you cannot reverse it. Even the developers cannot see users' passwords. To check a login, the password is hashed again and the hashes are compared.

### 13.5 Brute Force Rate Limiting

**The threat:** An attacker tries thousands of passwords automatically.

**The fix:** Track failed login attempts. After 5 failures in 15 minutes, block further attempts for that identifier.

```php
// Every failed login:
INSERT INTO login_attempts (identifier, attempted_at, ip_address) VALUES (?, NOW(), ?)

// On each login attempt:
SELECT COUNT(*) FROM login_attempts
WHERE identifier = ? AND attempted_at > NOW() - INTERVAL 900 SECOND
// If count >= 5 → reject the attempt
```

### 13.6 Session Fixation Prevention

**The threat:** An attacker tricks a user into using a session ID the attacker already knows, then waits for the user to log in and hijacks the session.

**The fix:** After a successful login, regenerate the session ID.

```php
session_regenerate_id(true); // Creates new session ID, deletes old one
```

This means the attacker's known session ID becomes invalid after login.

### 13.7 Session Security Settings

```php
ini_set('session.cookie_httponly', 1);   // JavaScript cannot read cookie
ini_set('session.use_strict_mode', 1);   // Reject external session IDs
ini_set('session.cookie_samesite', 'Lax'); // Not sent cross-site
```

### 13.8 HTTP Security Headers

```
X-Frame-Options: DENY
```
Prevents your pages from being embedded in `<iframe>` on other sites — blocks **clickjacking** attacks (overlaying invisible iframes to steal clicks).

```
X-Content-Type-Options: nosniff
```
Tells the browser not to guess file types — prevents MIME-sniffing attacks where a `.jpg` file containing HTML/JS is executed.

```
Content-Security-Policy: default-src 'self'; ...
```
Whitelist of allowed sources for scripts, styles, images, etc. Even if XSS is somehow injected, the browser will refuse to load scripts from unknown sources.

### 13.9 Constant-Time Comparisons

```php
// Time-safe (use for secrets)
hash_equals($storedToken, $userToken);

// NOT time-safe (do not use for secrets)
$storedToken === $userToken;
// OR
strcmp($storedToken, $userToken) === 0;
```

Timing attacks measure how long a comparison takes. Regular `===` stops at the first different character — comparing `"aXXXXX"` vs `"aaaaaa"` returns faster than `"XXXXXX"` vs `"aaaaaa"`. An attacker can exploit this to guess tokens one character at a time. `hash_equals()` always runs for the full string length.

---

## 14. Database Tables

The application requires three tables. Here is the SQL to create them:

```sql
CREATE TABLE users (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(50)  NOT NULL UNIQUE,
    email      VARCHAR(255) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,          -- bcrypt hash
    created_at DATETIME     NOT NULL DEFAULT NOW()
);

CREATE TABLE login_attempts (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    identifier   VARCHAR(255) NOT NULL,         -- email or username tried
    attempted_at DATETIME     NOT NULL,
    ip_address   VARCHAR(45)  NOT NULL,          -- supports IPv6
    INDEX idx_identifier_time (identifier, attempted_at)
);

CREATE TABLE contacts (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    email      VARCHAR(255) NOT NULL,
    message    TEXT         NOT NULL,
    ip_address VARCHAR(45)  NOT NULL,
    created_at DATETIME     NOT NULL DEFAULT NOW()
);
```

---

## 15. Full Authentication Flow (Step by Step)

### Registration

```
1. User visits /register (GET)
   → index.php matches route → AuthController::showRegister()
   → Loads views/auth/register.php → HTML form is sent to browser

2. User fills in form and submits (POST /register)
   → index.php matches route → AuthController::register()
   → CSRF token verified ✓
   → Input validated (username, email, password rules) ✓
   → Database checked: is email unique? is username unique? ✓
   → password_hash() creates bcrypt hash
   → User::create() inserts row into users table
   → flash('success', 'Account created!')
   → redirect('/login')

3. User sees success message on login page
```

### Login

```
1. User visits /login (GET)
   → AuthController::showLogin()
   → If already logged in → redirect('/dashboard')
   → Otherwise load views/auth/login.php

2. User enters credentials and submits (POST /login)
   → AuthController::login()
   → CSRF verified ✓
   → Inputs non-empty ✓
   → Rate limit check: fewer than 5 recent failed attempts ✓
   → User::findByEmailOrUsername() queries DB
   → password_verify() checks password (constant-time path used)
   
   If credentials wrong:
   → recordFailedAttempt() adds row to login_attempts
   → flash('error', 'Invalid credentials.')
   → redirect('/login')
   
   If credentials correct:
   → clearFailedAttempts() removes rows from login_attempts
   → session_regenerate_id(true) — new session ID
   → $_SESSION['user_id']  = $user['id']
   → $_SESSION['username'] = $user['username']
   → $_SESSION['login_time'] = time()
   → unset($_SESSION['csrf_token']) — force new token next request
   → redirect('/dashboard')
```

### Accessing a Protected Page

```
1. User visits /dashboard (GET)
   → index.php parses URL → matches ['GET', '/dashboard', 'PageController', 'dashboard']
   → Checks: is 'PageController@dashboard' in $publicRoutes? NO
   → Checks: isLoggedIn()? → isset($_SESSION['user_id']) → YES
   → Loads PageController.php
   → new PageController()
   → PageController::dashboard()
   → User::findById($_SESSION['user_id']) — fresh DB fetch
   → require views/pages/dashboard.php — HTML sent to browser
```

### Logout

```
1. User clicks Logout button
   → Browser sends POST /logout with csrf_token in form body
   → AuthController::logout()
   → CSRF verified ✓
   → $_SESSION = [] — clear all session data
   → setcookie(session_name(), '', time() - 42000, ...) — expire cookie in browser
   → session_destroy() — delete session file on server
   → redirect('/login')
```

---

## 16. Glossary

| Term | Definition |
|------|------------|
| **bcrypt** | A password hashing algorithm designed to be slow, making brute force attacks expensive |
| **CSRF** | Cross-Site Request Forgery — tricking a user's browser into making an unwanted authenticated request |
| **DSN** | Data Source Name — a connection string for PDO: `mysql:host=...;dbname=...` |
| **Flash message** | A one-time message stored in session, shown once after a redirect, then deleted |
| **Front controller** | A single entry point (index.php) that handles all requests |
| **Hash** | A one-way transformation of data — you cannot reverse it to get the original |
| **HTTP header** | Metadata sent with an HTTP request or response (before the HTML body) |
| **MVC** | Model-View-Controller — an architecture pattern separating data, display, and logic |
| **PDO** | PHP Data Objects — a database abstraction layer supporting multiple databases |
| **Prepared statement** | A SQL template where values are bound separately, preventing injection |
| **Rate limiting** | Blocking further attempts after too many failures in a time window |
| **Salt** | Random data added to a password before hashing, making identical passwords produce different hashes |
| **Session** | Server-side storage linked to the user via a cookie containing a session ID |
| **Session fixation** | Attack where an attacker pre-sets a victim's session ID, then uses it after they log in |
| **Singleton** | A pattern ensuring only one instance of something exists (e.g., one DB connection) |
| **Static method** | A class method callable without creating an object instance: `ClassName::method()` |
| **Timing attack** | Exploiting the difference in time taken by comparisons to infer secret values |
| **XSS** | Cross-Site Scripting — injecting malicious scripts into pages viewed by other users |

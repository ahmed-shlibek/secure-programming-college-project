<?php

//    Session security (must happen before session_start)
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime', 7200);

// Mark the session cookie Secure when the request arrived over HTTPS so it is
// never transmitted over plain HTTP. In production Cloudflare/Render set
// X-Forwarded-Proto; left off for local http dev so login still works there.
$isHttps = (($_SERVER['HTTPS'] ?? '') !== '' && ($_SERVER['HTTPS'] ?? '') !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
ini_set('session.cookie_secure', $isHttps ? '1' : '0');

session_start();

//    Bootstrap
// Composer autoloader: registers PSR-4 (App\Controllers, App\Models) and
// loads the helper files (env.php, functions.php) via the "files" autoload.
require_once __DIR__ . '/../vendor/autoload.php';

loadEnv(__DIR__ . '/../.env');

// Config files have load-order side effects (database.php reads .env at load),
// so they stay as explicit requires here rather than being autoloaded.
require_once __DIR__ . '/../app/config/app.php';       // defines BASE_URL, constants
require_once __DIR__ . '/../app/config/database.php';  // defines getDB()

//    Global error handling
//    Never leak internals (SQL, stack traces, file paths) to the user. Log the
//    real error and render our own generic 500 page. Full detail is shown only
//    when APP_DEBUG is on AND we're not in production (local dev only).
$appDebug = env('APP_DEBUG') === true && env('APP_ENV') !== 'production';

$renderServerError = static function () use ($appDebug): void {
    if (!headers_sent()) {
        http_response_code(500);
    }
    if (!$appDebug) {
        require __DIR__ . '/../views/errors/500.php';
    }
};

set_exception_handler(static function (\Throwable $e) use ($appDebug, $renderServerError): void {
    error_log('Unhandled exception: ' . $e);
    if ($appDebug) {
        http_response_code(500);
        echo '<pre>' . htmlspecialchars((string) $e, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</pre>';
        return;
    }
    $renderServerError();
});

// Catch fatal errors (which bypass the exception handler) too.
register_shutdown_function(static function () use ($renderServerError): void {
    $err = error_get_last();
    if ($err !== null && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        error_log("Fatal error: {$err['message']} in {$err['file']}:{$err['line']}");
        $renderServerError();
    }
});

//    Cloudflare origin verification
//    In production, every request must carry the X-Origin-Verify header set by
//    Cloudflare's Transform Rule. Blocks attackers who bypass Cloudflare and hit
//    the origin server directly. Fail-closed: a missing secret in production is
//    a hard 403, not a silent bypass. Skipped in local dev (APP_ENV=development).
if (env('APP_ENV') === 'production') {
    $originSecret   = env('ORIGIN_SECRET');
    $providedSecret = $_SERVER['HTTP_X_ORIGIN_VERIFY'] ?? '';

    if (empty($originSecret) || !is_string($providedSecret) || !hash_equals($originSecret, $providedSecret)) {
        http_response_code(403);
        exit('Forbidden');
    }
}

//    Enforce session lifetime
if (isLoggedIn() && isset($_SESSION['login_time'])) {
    if ((time() - $_SESSION['login_time']) > SESSION_LIFETIME) {
        $_SESSION = [];
        session_destroy();
    }
}

//    Security headers
header_remove('X-Powered-By');   // don't advertise the PHP version
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data:; object-src 'none'; base-uri 'self'; frame-ancestors 'none'");

//    Parse request   
$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (BASE_URL !== '' && str_starts_with($requestUri, BASE_URL)) {
    $requestUri = substr($requestUri, strlen(BASE_URL));
}

$requestUri = '/' . ltrim($requestUri, '/');
if ($requestUri !== '/' && str_ends_with($requestUri, '/')) {
    $requestUri = rtrim($requestUri, '/');
}

//    Root redirect   
if ($requestUri === '/') {
    redirect(isLoggedIn() ? '/about' : '/login');
}

//    Load routes     
$routes = require __DIR__ . '/../app/routes.php';

//    Route matching  
$matchedRoute = null;
$routeParams  = [];

foreach ($routes as $route) {
    [$method, $path, $controller, $action] = $route;

    if ($method !== $requestMethod) {
        continue;
    }

    $pattern = preg_replace('/\{([a-zA-Z_]+)}/', '([^/]+)', $path);
    $pattern = '#^' . $pattern . '$#';

    if (preg_match($pattern, $requestUri, $matches)) {
        $matchedRoute = $route;
        array_shift($matches);
        $routeParams = $matches;
        break;
    }
}

//    404             
if ($matchedRoute === null) {
    http_response_code(404);
    require __DIR__ . '/../views/errors/404.php';
    exit;
}

//    Auth guard      
[, , $controllerName, $actionName] = $matchedRoute;

$publicRoutes = [
    'AuthController@showLogin',
    'AuthController@login',
    'AuthController@showRegister',
    'AuthController@register',
];

if (!in_array($controllerName . '@' . $actionName, $publicRoutes) && !isLoggedIn()) {
    redirect('/login');
}

//    Dispatch
// Models and controllers are autoloaded on first use via Composer PSR-4.
$controllerClass = 'App\\Controllers\\' . $controllerName;

if (!class_exists($controllerClass)) {
    http_response_code(500);
    die('Controller not found: ' . e($controllerName));
}

$controller = new $controllerClass();

if (!method_exists($controller, $actionName)) {
    http_response_code(500);
    die('Action not found: ' . e($actionName));
}

call_user_func_array([$controller, $actionName], $routeParams);

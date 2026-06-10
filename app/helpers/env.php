<?php

use Dotenv\Dotenv;

/**
 * Load a .env file into the environment using vlucas/phpdotenv.
 *
 * Immutable mode: existing real environment variables (e.g. the DB_* vars
 * Render injects in production) are never overwritten by the file. In
 * production there is no .env in the image (.dockerignore), so this safely
 * no-ops and env() reads straight from the platform's variables.
 */
function loadEnv(string $path): void
{
    // createUnsafeImmutable (not createImmutable) so phpdotenv's PutenvAdapter
    // is registered, which reads getenv(). In production the DB_* vars are real
    // container env vars injected by Render — and the production php.ini uses
    // variables_order "GPCS" (no "E"), so $_ENV is empty and the values are only
    // visible via getenv(). createImmutable only inspects $_ENV/$_SERVER, so its
    // required() check would wrongly report them missing. "Unsafe" = uses
    // putenv(), which is safe under Apache's prefork MPM.
    $dotenv = Dotenv::createUnsafeImmutable(dirname($path), basename($path));
    $dotenv->safeLoad(); // does not throw if the .env file is absent

    // Fail-closed: refuse to boot if a critical variable is missing or empty.
    // Validates against the loaded file AND real environment variables, so it
    // protects both local dev and the Render deployment.
    $dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER'])->notEmpty();
}

/**
 * Get an environment variable with a fallback default
 */
function env(string $key, $default = null)
{
    $value = $_ENV[$key] ?? getenv($key);
    
    if ($value === false) {
        return $default;
    }

    switch (strtolower($value)) {
        case 'true':
        case '(true)':
            return true;
        case 'false':
        case '(false)':
            return false;
        case 'empty':
        case '(empty)':
            return '';
        case 'null':
        case '(null)':
            return null;
    }

    return $value;
}

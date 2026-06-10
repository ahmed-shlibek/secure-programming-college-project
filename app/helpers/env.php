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
    $dotenv = Dotenv::createImmutable(dirname($path), basename($path));
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

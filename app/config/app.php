<?php

// Application info
define('APP_NAME', 'secure-programming-college-project');
define('APP_VERSION', '1.0.0');

// Auto-detect base URL (works with Laragon virtual hosts and subdirectories)
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
define('BASE_URL', $scriptDir === '/' ? '' : rtrim($scriptDir, '/'));

// Security
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes in seconds
define('SESSION_LIFETIME', 7200);  // 2 hours in seconds

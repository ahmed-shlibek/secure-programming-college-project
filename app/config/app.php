<?php

// Application info
define('APP_NAME', 'secure-programming-college-project');
define('APP_VERSION', '1.0.0');

// Auto-detect base URL (works with Laragon virtual hosts and subdirectories)
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
define('BASE_URL', $scriptDir === '/' ? '' : rtrim($scriptDir, '/'));

// Security
define('MAX_LOGIN_ATTEMPTS', 5);          // per account (identifier) within the window
define('MAX_LOGIN_ATTEMPTS_PER_IP', 15);  // per source IP across accounts (NAT-tolerant)
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes in seconds
define('SESSION_LIFETIME', 7200);  // 2 hours in seconds

// Contact form
define('CONTACT_UPLOAD_MAX_BYTES', 5 * 1024 * 1024); // 5 MB PDF cap
define('CONTACT_RATE_WINDOW', 900);                  // 15 minutes in seconds
define('CONTACT_MAX_SUBMISSIONS', 3);                // max submissions per IP per window

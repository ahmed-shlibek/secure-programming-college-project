<?php
/**
 * Diagnostic: verify the application's database connection is using TLS.
 *
 * Runs through the real getDB() (same config the app uses) and asks MySQL
 * whether the current connection is encrypted. A non-empty Ssl_cipher means
 * the channel is over TLS.
 *
 * Usage (CLI only):
 *   php database/db-ssl-check.php
 *
 * Run it on the server to test the real Render -> Aiven channel:
 *   Render dashboard -> your service -> "Shell" -> php database/db-ssl-check.php
 * Or locally, with DB_SSL_CA + the Aiven host/credentials in your .env, to
 * confirm TLS works before deploying.
 *
 * This file lives outside public/ so it is never web-accessible; the guard
 * below also blocks any attempt to run it over HTTP.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require __DIR__ . '/../vendor/autoload.php';
loadEnv(__DIR__ . '/../.env');
require __DIR__ . '/../app/config/app.php';
require __DIR__ . '/../app/config/database.php';

echo "=== Database TLS check ===\n";

if (DB_SSL_CA === '') {
    echo "DB_SSL_CA   : (not set) -> connection will be PLAINTEXT\n";
} else {
    echo "DB_SSL_CA   : " . DB_SSL_CA . "\n";
    if (!is_readable(DB_SSL_CA)) {
        echo "WARNING     : CA file is not readable at that path!\n";
    }
}

try {
    $db = getDB();

    $cipher  = $db->query("SHOW STATUS LIKE 'Ssl_cipher'")->fetch();
    $version = $db->query("SHOW STATUS LIKE 'Ssl_version'")->fetch();
    $user    = $db->query("SELECT CURRENT_USER()")->fetchColumn();

    $cipherVal  = $cipher['Value']  ?? '';
    $versionVal = $version['Value'] ?? '';

    echo "Connected as: {$user}\n";
    echo "Ssl_version : " . ($versionVal !== '' ? $versionVal : '(none)') . "\n";
    echo "Ssl_cipher  : " . ($cipherVal  !== '' ? $cipherVal  : '(none)') . "\n\n";

    if ($cipherVal !== '') {
        echo "RESULT: [PASS] Connection is ENCRYPTED (TLS cipher: {$cipherVal}).\n";
        exit(0);
    }

    echo "RESULT: [FAIL] Connection is NOT encrypted (no TLS cipher).\n";
    echo "Check that DB_SSL_CA points to a valid CA and the server offers TLS.\n";
    exit(1);
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    if ($prev = $e->getPrevious()) {
        echo "Cause: " . $prev->getMessage() . "\n";
    }
    exit(2);
}

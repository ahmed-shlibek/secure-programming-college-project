<?php
/**
 * StockVision - Database Configuration
 * PDO singleton connection with Laragon defaults
 */

// Database credentials loaded from .env
define('DB_HOST', env('DB_HOST', '127.0.0.1'));
define('DB_PORT', env('DB_PORT', '3306'));
define('DB_NAME', env('DB_NAME', 'stockvision'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));
define('DB_CHARSET', 'utf8mb4');
// Path to the MySQL provider's CA certificate. When set, the connection is
// encrypted with TLS and the server's certificate is verified (prevents
// man-in-the-middle). Left empty for local dev (plain localhost connection).
define('DB_SSL_CA', env('DB_SSL_CA', ''));

/**
 * Get PDO database connection (singleton)
 */
function getDB(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_PORT,
            DB_NAME,
            DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
        ];

        // Enable TLS to the database when a CA certificate is configured. The
        // CA both encrypts the link and verifies the server identity, so the
        // app↔DB traffic can't be sniffed or man-in-the-middled in transit.
        if (DB_SSL_CA !== '') {
            $options[PDO::MYSQL_ATTR_SSL_CA]                 = DB_SSL_CA;
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
        }

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Don't leak connection details to the user. Re-throw a generic error;
            // the global exception handler logs it and renders our 500 page.
            throw new RuntimeException('Database connection failed.', 0, $e);
        }

        // TEMPORARY TLS diagnostic. Set DB_SSL_DEBUG=true (env) to dump the
        // connection's encryption status on the first DB hit, then load any
        // page in the browser. A non-empty Ssl_cipher means the channel is
        // encrypted. REMOVE the env var (or set it false) when finished.
        if (env('DB_SSL_DEBUG') === 'true') {
            $cipher  = $pdo->query("SHOW STATUS LIKE 'Ssl_cipher'")->fetch();
            $version = $pdo->query("SHOW STATUS LIKE 'Ssl_version'")->fetch();
            $user    = $pdo->query("SELECT CURRENT_USER()")->fetchColumn();
            $cipherVal = $cipher['Value'] ?? '';

            if (!headers_sent()) {
                header('Content-Type: text/plain; charset=utf-8');
            }
            echo "=== Database TLS check ===\n";
            echo "Connected as: {$user}\n";
            echo "DB_SSL_CA   : " . (DB_SSL_CA !== '' ? DB_SSL_CA : '(not set)') . "\n";
            echo "Ssl_version : " . (($version['Value'] ?? '') ?: '(none)') . "\n";
            echo "Ssl_cipher  : " . ($cipherVal ?: '(none)') . "\n\n";
            echo $cipherVal !== ''
                ? "RESULT: [PASS] Connection is ENCRYPTED (TLS).\n"
                : "RESULT: [FAIL] Connection is NOT encrypted.\n";
            echo "\nRemove the DB_SSL_DEBUG env var when you are done.\n";
            exit;
        }
    }

    return $pdo;
}

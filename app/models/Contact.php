<?php

namespace App\Models;

use PDO;

class Contact
{
    public static function create(
        string $name,
        string $email,
        string $message,
        string $ip,
        ?string $attachmentKey = null,
        ?string $attachmentName = null,
        ?int $attachmentSize = null,
        ?string $attachmentMime = null
    ): int {
        $db   = getDB();
        $stmt = $db->prepare(
            'INSERT INTO contacts
                (name, email, message, ip_address,
                 attachment_key, attachment_name, attachment_size, attachment_mime)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $name, $email, $message, $ip,
            $attachmentKey, $attachmentName, $attachmentSize, $attachmentMime,
        ]);
        return (int) $db->lastInsertId();
    }

    /**
     * Count submissions from an IP within the last $windowSeconds — used to
     * rate-limit the public contact form (mirrors the login_attempts pattern).
     */
    public static function recentCountForIp(string $ip, int $windowSeconds): int
    {
        $db   = getDB();
        $stmt = $db->prepare(
            'SELECT COUNT(*) FROM contacts
             WHERE ip_address = ?
               AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)'
        );
        $stmt->execute([$ip, $windowSeconds]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Most-recent contact submissions, newest first. For the admin view.
     */
    public static function all(int $limit = 100): array
    {
        $db   = getDB();
        $stmt = $db->prepare(
            'SELECT * FROM contacts ORDER BY created_at DESC, id DESC LIMIT :limit'
        );
        // LIMIT must be bound as an int — native prepares (emulation off) would
        // otherwise quote it as a string and MySQL would reject the query.
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $db   = getDB();
        $stmt = $db->prepare('SELECT * FROM contacts WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
}

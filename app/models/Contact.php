<?php

namespace App\Models;

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
}

<?php

namespace App\Models;

class Contact
{
    public static function create(string $name, string $email, string $message, string $ip): int
    {
        $db   = getDB();
        $stmt = $db->prepare(
            'INSERT INTO contacts (name, email, message, ip_address) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$name, $email, $message, $ip]);
        return (int) $db->lastInsertId();
    }
}

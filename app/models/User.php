<?php

namespace App\Models;

class User
{
    public static function findByEmailOrUsername(string $identifier): ?array
    {
        $db   = getDB();
        $stmt = $db->prepare(
            'SELECT * FROM users WHERE email = :id OR username = :id2 LIMIT 1'
        );
        $stmt->execute([':id' => $identifier, ':id2' => $identifier]);
        return $stmt->fetch() ?: null;
    }

    public static function findByEmail(string $email): ?array
    {
        $db   = getDB();
        $stmt = $db->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public static function findByUsername(string $username): ?array
    {
        $db   = getDB();
        $stmt = $db->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        return $stmt->fetch() ?: null;
    }

    public static function findById(int $id): ?array
    {
        $db   = getDB();
        $stmt = $db->prepare('SELECT id, username, email, created_at FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(string $username, string $email, string $hashedPassword): int
    {
        $db   = getDB();
        $stmt = $db->prepare(
            'INSERT INTO users (username, email, password) VALUES (?, ?, ?)'
        );
        $stmt->execute([$username, $email, $hashedPassword]);
        return (int) $db->lastInsertId();
    }
}

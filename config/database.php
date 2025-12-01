<?php

declare(strict_types=1);

function getDatabaseConnection(): PDO
{
    $databasePath = __DIR__ . '/../storage/app.sqlite';
    $shouldBootstrap = !file_exists($databasePath);

    $pdo = new PDO('sqlite:' . $databasePath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    if ($shouldBootstrap) {
        $pdo->exec('PRAGMA foreign_keys = ON');
    }

    return $pdo;
}

function initializeDatabase(PDO $pdo): void
{
    $pdo->exec('PRAGMA foreign_keys = ON');

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            name TEXT NOT NULL,
            password_hash TEXT NOT NULL,
            credit INTEGER NOT NULL DEFAULT 5000000,
            created_at TEXT NOT NULL,
            updated_at TEXT NOT NULL
        )'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS flights (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            origin_code TEXT NOT NULL,
            origin_city TEXT NOT NULL,
            destination_code TEXT NOT NULL,
            destination_city TEXT NOT NULL,
            airline TEXT NOT NULL,
            flight_code TEXT NOT NULL,
            departure_time TEXT NOT NULL,
            arrival_time TEXT NOT NULL,
            price INTEGER NOT NULL,
            created_at TEXT NOT NULL
        )'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS bookings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            flight_id INTEGER NOT NULL,
            passenger_name TEXT NOT NULL,
            status TEXT NOT NULL DEFAULT "confirmed",
            created_at TEXT NOT NULL,
            FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY(flight_id) REFERENCES flights(id) ON DELETE CASCADE
        )'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS flight_generations (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            generated_for_date TEXT NOT NULL UNIQUE,
            created_at TEXT NOT NULL
        )'
    );

    seedDefaultUser($pdo);
}

function seedDefaultUser(PDO $pdo): void
{
    $count = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    if ($count === 0) {
        $stmt = $pdo->prepare(
            'INSERT INTO users (username, name, password_hash, credit, created_at, updated_at) VALUES (:username, :name, :password_hash, :credit, :created_at, :updated_at)'
        );
        $now = (new DateTimeImmutable())->format(DateTimeInterface::ATOM);
        $stmt->execute([
            'username' => 'demo',
            'name' => 'Pengguna Demo',
            'password_hash' => password_hash('demo123', PASSWORD_DEFAULT),
            'credit' => 5000000,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}

<?php

declare(strict_types=1);

function basePath(string $path = ''): string
{
    $root = __DIR__ . '/..';
    return $path === '' ? $root : $root . '/' . ltrim($path, '/');
}

function assetPath(string $path): string
{
    return ltrim($path, '/');
}

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI'] ?? 'index.php'));
        exit();
    }
}

function currentUser(PDO $pdo): ?array
{
    if (!isLoggedIn()) {
        return null;
    }

    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id');
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $user = $stmt->fetch();

    return $user ?: null;
}

function sanitize(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function airportCatalog(): array
{
    return [
        ['code' => 'CGK', 'city' => 'Jakarta', 'name' => 'Soekarno-Hatta'],
        ['code' => 'HLP', 'city' => 'Jakarta', 'name' => 'Halim Perdanakusuma'],
        ['code' => 'SUB', 'city' => 'Surabaya', 'name' => 'Juanda'],
        ['code' => 'DPS', 'city' => 'Denpasar', 'name' => 'Ngurah Rai'],
        ['code' => 'UPG', 'city' => 'Makassar', 'name' => 'Sultan Hasanuddin'],
        ['code' => 'KNO', 'city' => 'Medan', 'name' => 'Kualanamu'],
        ['code' => 'BDO', 'city' => 'Bandung', 'name' => 'Husein Sastranegara'],
        ['code' => 'JOG', 'city' => 'Yogyakarta', 'name' => 'Yogyakarta International'],
        ['code' => 'SRG', 'city' => 'Semarang', 'name' => 'Ahmad Yani'],
        ['code' => 'BPN', 'city' => 'Balikpapan', 'name' => 'Sultan Aji Muhammad Sulaiman'],
        ['code' => 'PLM', 'city' => 'Palembang', 'name' => 'Sultan Mahmud Badaruddin II'],
        ['code' => 'LOP', 'city' => 'Lombok', 'name' => 'Zainuddin Abdul Madjid'],
        ['code' => 'PDG', 'city' => 'Padang', 'name' => 'Minangkabau'],
        ['code' => 'PKU', 'city' => 'Pekanbaru', 'name' => 'Sultan Syarif Kasim II'],
        ['code' => 'MDC', 'city' => 'Manado', 'name' => 'Sam Ratulangi'],
        ['code' => 'DJJ', 'city' => 'Jayapura', 'name' => 'Sentani'],
        ['code' => 'TIM', 'city' => 'Timika', 'name' => 'Mozes Kilangin'],
        ['code' => 'TRK', 'city' => 'Tarakan', 'name' => 'Juwata'],
        ['code' => 'SOC', 'city' => 'Solo', 'name' => 'Adi Soemarmo'],
        ['code' => 'PKY', 'city' => 'Palangkaraya', 'name' => 'Tjilik Riwut'],
    ];
}

function airlinesCatalog(): array
{
    return [
        ['name' => 'Garuda Indonesia', 'code' => 'GA', 'multiplier' => 2.8],
        ['name' => 'Citilink', 'code' => 'QG', 'multiplier' => 1.2],
        ['name' => 'Lion Air', 'code' => 'JT', 'multiplier' => 1.0],
        ['name' => 'Batik Air', 'code' => 'ID', 'multiplier' => 1.4],
        ['name' => 'Super Air Jet', 'code' => 'IU', 'multiplier' => 0.95],
        ['name' => 'Sriwijaya Air', 'code' => 'SJ', 'multiplier' => 1.1],
    ];
}

function formatRupiah(int $value): string
{
    return 'Rp ' . number_format($value, 0, ',', '.');
}

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

function stationCatalog(): array
{
    return [
        ['code' => 'GMR', 'city' => 'Jakarta', 'name' => 'Gambir'],
        ['code' => 'PSE', 'city' => 'Jakarta', 'name' => 'Pasar Senen'],
        ['code' => 'BDG', 'city' => 'Bandung', 'name' => 'Bandung'],
        ['code' => 'CBN', 'city' => 'Cirebon', 'name' => 'Cirebon'],
        ['code' => 'PWT', 'city' => 'Purwokerto', 'name' => 'Purwokerto'],
        ['code' => 'TGL', 'city' => 'Tegal', 'name' => 'Tegal'],
        ['code' => 'SMT', 'city' => 'Semarang', 'name' => 'Tawang'],
        ['code' => 'SLO', 'city' => 'Solo', 'name' => 'Balapan'],
        ['code' => 'TGU', 'city' => 'Yogyakarta', 'name' => 'Tugu'],
        ['code' => 'SBY', 'city' => 'Surabaya', 'name' => 'Gubeng'],
        ['code' => 'MLG', 'city' => 'Malang', 'name' => 'Malang'],
        ['code' => 'JBR', 'city' => 'Jember', 'name' => 'Jember'],
        ['code' => 'BWX', 'city' => 'Banyuwangi', 'name' => 'Banyuwangi Baru'],
    ];
}

function trainOperatorsCatalog(): array
{
    return [
        ['name' => 'KAI Eksekutif', 'code' => 'KX', 'multiplier' => 2.0],
        ['name' => 'KAI Bisnis', 'code' => 'KB', 'multiplier' => 1.6],
        ['name' => 'KAI Ekonomi', 'code' => 'KE', 'multiplier' => 1.1],
        ['name' => 'KAI Commuter', 'code' => 'KC', 'multiplier' => 0.7],
        ['name' => 'Railink', 'code' => 'RL', 'multiplier' => 1.3],
        ['name' => 'KAI Wisata', 'code' => 'KW', 'multiplier' => 2.4],
    ];
}

function formatRupiah(int $value): string
{
    return 'Rp ' . number_format($value, 0, ',', '.');
}

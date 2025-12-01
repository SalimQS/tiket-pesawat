<?php
/** @var PDO $pdo */
/** @var array|null $user */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? sanitize($pageTitle) . ' - ' : '' ?>Pesawatin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex-shrink-0">
                    <a href="index.php" class="text-2xl font-bold text-indigo-600">Pesawatin</a>
                </div>

                <div class="flex items-center space-x-4">
                    <?php if (isLoggedIn()): ?>
                        <span class="text-sm text-gray-600 hidden md:block">Saldo: <strong><?= formatRupiah((int) $user['credit']) ?></strong></span>
                        <a href="dashboard.php" class="text-gray-600 hover:text-indigo-600 font-medium">Dashboard</a>
                        <a href="topup.php" class="text-gray-600 hover:text-indigo-600 font-medium">Top Up</a>
                        <a href="profile.php" class="text-gray-600 hover:text-indigo-600 font-medium">Profil</a>
                        <a href="logout.php" class="text-gray-600 hover:text-red-600 font-medium">Keluar</a>
                        <span class="inline-flex items-center px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-sm font-semibold">
                            <i class="fas fa-user mr-2"></i><?= sanitize($user['username']) ?>
                        </span>
                    <?php else: ?>
                        <a href="login.php" class="flex items-center space-x-2 p-2 rounded-full bg-indigo-100 hover:bg-indigo-200 transition">
                            <i class="fas fa-user-circle text-xl text-indigo-600"></i>
                            <span class="text-sm font-semibold text-indigo-700 hidden sm:block">Masuk / Daftar</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">

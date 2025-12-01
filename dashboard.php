<?php
require_once __DIR__ . '/bootstrap.php';
requireLogin();

$user = currentUser($pdo);
$pageTitle = 'Dashboard';
$flashSuccess = $_SESSION['success'] ?? null;
$flashError = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);

$stmt = $pdo->prepare('SELECT bookings.*, flights.origin_city, flights.destination_city, flights.departure_time, flights.arrival_time, flights.airline, flights.price, flights.flight_code FROM bookings JOIN flights ON flights.id = bookings.flight_id WHERE bookings.user_id = :user_id ORDER BY bookings.created_at DESC');
$stmt->execute(['user_id' => $user['id']]);
$bookings = $stmt->fetchAll();
?>
<?php include __DIR__ . '/templates/header.php'; ?>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1">
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-bold mb-2">Halo, <?= sanitize($user['name']) ?></h2>
                <p class="text-gray-600 mb-4">Selamat datang kembali di Pesawatin.</p>
                <p class="text-sm text-gray-500">Username</p>
                <p class="font-semibold mb-4">@<?= sanitize($user['username']) ?></p>
                <p class="text-sm text-gray-500">Saldo Kredit</p>
                <p class="text-2xl font-bold text-indigo-600"><?= formatRupiah((int) $user['credit']) ?></p>
                <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-3 mt-4 space-y-2 sm:space-y-0">
                    <a href="deposit.php" class="inline-block bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 text-center">
                        Deposit Saldo
                    </a>
                    <a href="profile.php" class="inline-block bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 text-center">Kelola Profil</a>
                </div>
            </div>
        </div>
        <div class="lg:col-span-2">
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-xl font-bold mb-4">Riwayat Pemesanan</h3>
                <?php if ($flashSuccess): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
                        <p><?= sanitize($flashSuccess) ?></p>
                    </div>
                <?php endif; ?>
                <?php if ($flashError): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                        <p><?= sanitize($flashError) ?></p>
                    </div>
                <?php endif; ?>
                <?php if (count($bookings) === 0): ?>
                    <p class="text-gray-600">Belum ada pemesanan. Cari tiket dan lakukan pemesanan untuk memulai.</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($bookings as $booking): ?>
                            <div class="border border-gray-200 rounded-lg p-4 flex justify-between items-center">
                                <div>
                                    <p class="text-sm uppercase text-gray-500">Kode Penerbangan: <?= sanitize($booking['flight_code']) ?></p>
                                    <h4 class="text-lg font-semibold text-gray-800"><?= sanitize($booking['origin_city']) ?> ➝ <?= sanitize($booking['destination_city']) ?></h4>
                                    <p class="text-sm text-gray-500">Berangkat: <?= sanitize(date('d M Y H:i', strtotime($booking['departure_time']))) ?></p>
                                    <p class="text-sm text-gray-500">Maskapai: <?= sanitize($booking['airline']) ?></p>
                                    <p class="text-sm text-gray-500">Status: <?= sanitize($booking['status']) ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-gray-500">Dipesan pada</p>
                                    <p class="font-semibold"><?= sanitize(date('d M Y H:i', strtotime($booking['created_at']))) ?></p>
                                    <p class="text-red-600 font-bold mt-2"><?= formatRupiah((int) $booking['price']) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php include __DIR__ . '/templates/footer.php'; ?>

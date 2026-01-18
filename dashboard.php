<?php
require_once __DIR__ . '/bootstrap.php';
requireLogin();

$user = currentUser($pdo);
$pageTitle = 'Dashboard';
$flashSuccess = $_SESSION['success'] ?? null;
$flashError = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);

$stmt = $pdo->prepare('SELECT bookings.*, flights.origin_city, flights.destination_city, flights.origin_code, flights.destination_code, flights.departure_time, flights.arrival_time, flights.airline, flights.price, flights.flight_code FROM bookings JOIN flights ON flights.id = bookings.flight_id WHERE bookings.user_id = :user_id ORDER BY bookings.created_at DESC');
$stmt->execute(['user_id' => $user['id']]);
$bookings = $stmt->fetchAll();
$bookingCount = count($bookings);
$totalSpent = 0;
foreach ($bookings as $booking) {
    $totalSpent += (int) $booking['price'];
}
$latestBooking = $bookings[0] ?? null;
?>
<?php include __DIR__ . '/templates/header.php'; ?>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <section class="lg:col-span-1 card card-hero reveal">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="eyebrow">Dashboard Visa</p>
                    <h2 class="display text-2xl">Halo, <?= sanitize($user['name']) ?></h2>
                    <p class="muted mt-2">Selamat datang kembali di VisaNusa. Siapkan dokumen Anda lebih cepat.</p>
                </div>
                <span class="chip">@<?= sanitize($user['username']) ?></span>
            </div>

            <div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="stat">
                    <p class="stat-label">Saldo Kredit</p>
                    <p class="stat-value"><?= formatRupiah((int) $user['credit']) ?></p>
                    <p class="stat-note">Siap dipakai</p>
                </div>
                <div class="stat">
                    <p class="stat-label">Total Pengajuan</p>
                    <p class="stat-value"><?= $bookingCount ?></p>
                    <p class="stat-note">
                        <?= $latestBooking ? 'Terakhir ' . sanitize(date('d M Y', strtotime($latestBooking['created_at']))) : 'Belum ada' ?>
                    </p>
                </div>
                <div class="stat">
                    <p class="stat-label">Total Biaya</p>
                    <p class="stat-value"><?= formatRupiah($totalSpent) ?></p>
                    <p class="stat-note">Biaya layanan</p>
                </div>
            </div>

            <div class="mt-6 flex flex-col sm:flex-row gap-3">
                <a href="deposit.php" class="btn-primary">
                    <i class="fas fa-wallet mr-2"></i>Deposit Saldo
                </a>
                <a href="profile.php" class="btn-secondary">
                    <i class="fas fa-user-cog mr-2"></i>Kelola Profil
                </a>
            </div>
        </section>

        <section class="lg:col-span-2 card reveal" style="--delay: 0.08s;">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <p class="eyebrow">Riwayat</p>
                    <h3 class="text-xl font-semibold">Riwayat Pengajuan Visa</h3>
                    <p class="muted text-sm mt-1">Pantau status dan biaya layanan terbaru.</p>
                </div>
                <div class="pill">Total <?= $bookingCount ?> pengajuan</div>
            </div>

            <?php if ($flashSuccess): ?>
                <div class="alert alert-success mt-4" role="alert">
                    <i class="fas fa-check-circle"></i>
                    <p><?= sanitize($flashSuccess) ?></p>
                </div>
            <?php endif; ?>
            <?php if ($flashError): ?>
                <div class="alert alert-error mt-4" role="alert">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p><?= sanitize($flashError) ?></p>
                </div>
            <?php endif; ?>

            <?php if ($bookingCount === 0): ?>
                <div class="empty-state mt-6">
                    <i class="fas fa-passport"></i>
                    <div>
                        <h4 class="text-lg font-semibold">Belum ada pengajuan</h4>
                        <p class="muted text-sm">Mulai dengan mencari visa sesuai negara tujuan Anda.</p>
                    </div>
                    <a href="index.php" class="btn-ghost">Cari Visa</a>
                </div>
            <?php else: ?>
                <div class="mt-6 space-y-4">
                    <?php $index = 0; ?>
                    <?php foreach ($bookings as $booking): ?>
                        <?php
                        $statusValue = strtolower((string) $booking['status']);
                        $badgeClass = 'badge badge-neutral';
                        if (str_contains($statusValue, 'proses') || str_contains($statusValue, 'confirm')) {
                            $badgeClass = 'badge badge-warm';
                        } elseif (str_contains($statusValue, 'tolak') || str_contains($statusValue, 'reject')) {
                            $badgeClass = 'badge badge-danger';
                        } elseif (str_contains($statusValue, 'setuju') || str_contains($statusValue, 'approve') || str_contains($statusValue, 'selesai')) {
                            $badgeClass = 'badge badge-success';
                        }
                        $delay = 0.12 + ($index * 0.04);
                        ?>
                        <div class="visa-item reveal" style="--delay: <?= number_format($delay, 2) ?>s;">
                            <div>
                                <p class="eyebrow">Kode layanan: <?= sanitize($booking['flight_code']) ?></p>
                                <h4 class="route">
                                    <?= countryFlag($booking['origin_code']) ?> <?= sanitize($booking['origin_city']) ?>
                                    <span class="route-arrow">→</span>
                                    <?= countryFlag($booking['destination_code']) ?> <?= sanitize($booking['destination_city']) ?>
                                </h4>
                                <div class="meta">
                                    <span>Pengajuan: <?= sanitize(date('d M Y', strtotime($booking['departure_time']))) ?></span>
                                    <span>Jenis visa: <?= sanitize($booking['airline']) ?></span>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="<?= $badgeClass ?>"><?= sanitize($booking['status']) ?></span>
                                <p class="price"><?= formatRupiah((int) $booking['price']) ?></p>
                                <p class="muted text-xs">Diajukan <?= sanitize(date('d M Y H:i', strtotime($booking['created_at']))) ?></p>
                            </div>
                        </div>
                        <?php $index++; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
<?php include __DIR__ . '/templates/footer.php'; ?>

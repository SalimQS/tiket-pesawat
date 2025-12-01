<?php
require_once __DIR__ . '/bootstrap.php';

$flightId = isset($_GET['flight_id']) ? (int) $_GET['flight_id'] : 0;
if ($flightId <= 0) {
    header('Location: index.php');
    exit();
}

$flightStmt = $pdo->prepare('SELECT * FROM flights WHERE id = :id');
$flightStmt->execute(['id' => $flightId]);
$flight = $flightStmt->fetch();

if (!$flight) {
    header('Location: index.php');
    exit();
}

$user = currentUser($pdo);
$pageTitle = 'Detail Tiket ' . $flight['flight_code'];
$flashSuccess = $_SESSION['success'] ?? null;
$flashError = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);

$durationHours = ceil((strtotime($flight['arrival_time']) - strtotime($flight['departure_time'])) / 3600);
$redirect = $_SERVER['REQUEST_URI'] ?? 'ticket_detail.php?flight_id=' . $flightId;
?>
<?php include __DIR__ . '/templates/header.php'; ?>
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="bg-white p-6 rounded-xl shadow border border-gray-200">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs uppercase text-gray-500">Kode Penerbangan</p>
                    <h1 class="text-2xl font-bold text-gray-900 flex items-center space-x-2">
                        <i class="fas fa-ticket-alt text-indigo-600"></i>
                        <span><?= sanitize($flight['flight_code']) ?></span>
                    </h1>
                    <p class="text-gray-500 mt-1">Maskapai: <span class="font-semibold text-gray-800"><?= sanitize($flight['airline']) ?></span></p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500">Harga per orang</p>
                    <p class="text-3xl font-extrabold text-red-600"><?= formatRupiah((int) $flight['price']) ?></p>
                    <?php if ($user): ?>
                        <p class="text-xs text-gray-500 mt-1">Saldo Anda: <?= formatRupiah((int) $user['credit']) ?></p>
                    <?php else: ?>
                        <p class="text-xs text-gray-500 mt-1">Masuk untuk melihat kredit Anda.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
                <div class="p-4 bg-indigo-50 rounded-lg">
                    <p class="text-sm text-gray-500 mb-1">Berangkat</p>
                    <p class="text-3xl font-bold text-gray-900"><?= sanitize(date('H:i', strtotime($flight['departure_time']))) ?></p>
                    <p class="text-gray-700 font-semibold"><?= sanitize($flight['origin_city']) ?> (<?= sanitize($flight['origin_code']) ?>)</p>
                    <p class="text-xs text-gray-500 mt-1">Tanggal: <?= sanitize(date('d M Y', strtotime($flight['departure_time']))) ?></p>
                </div>
                <div class="p-4 bg-white rounded-lg border border-gray-200 flex flex-col justify-center text-center">
                    <p class="text-sm font-semibold text-gray-600">Durasi Perjalanan</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">~<?= $durationHours ?> jam</p>
                    <p class="text-xs text-gray-500 mt-1">Non-stop</p>
                </div>
                <div class="p-4 bg-indigo-50 rounded-lg text-right">
                    <p class="text-sm text-gray-500 mb-1">Tiba</p>
                    <p class="text-3xl font-bold text-gray-900"><?= sanitize(date('H:i', strtotime($flight['arrival_time']))) ?></p>
                    <p class="text-gray-700 font-semibold"><?= sanitize($flight['destination_city']) ?> (<?= sanitize($flight['destination_code']) ?>)</p>
                    <p class="text-xs text-gray-500 mt-1">Tanggal: <?= sanitize(date('d M Y', strtotime($flight['arrival_time']))) ?></p>
                </div>
            </div>
        </div>

        <?php if ($flashSuccess): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded" role="alert">
                <p><?= sanitize($flashSuccess) ?></p>
            </div>
        <?php endif; ?>
        <?php if ($flashError): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded" role="alert">
                <p><?= sanitize($flashError) ?></p>
            </div>
        <?php endif; ?>

        <div class="bg-white p-6 rounded-xl shadow border border-gray-200 space-y-4">
            <h2 class="text-xl font-bold text-gray-900">Konfirmasi Pemesanan</h2>
            <p class="text-gray-600">Periksa kembali detail tiket sebelum melanjutkan.</p>

            <?php if (!isLoggedIn()): ?>
                <div class="bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-3 rounded" role="alert">
                    <p class="font-semibold">Masuk diperlukan</p>
                    <p class="text-sm">Anda harus masuk terlebih dahulu untuk menyelesaikan pemesanan.</p>
                </div>
                <a href="login.php?redirect=<?= urlencode($redirect) ?>" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-lg text-md transition duration-300 shadow-lg">
                    Masuk untuk Melanjutkan
                </a>
            <?php else: ?>
                <?php if ((int) $user['credit'] < (int) $flight['price']): ?>
                    <div class="bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded" role="alert">
                        <p class="font-semibold flex items-center"><i class="fas fa-exclamation-triangle mr-2"></i>Saldo Anda kurang dari harga tiket.</p>
                        <p class="text-sm mt-1">Silakan lakukan deposit agar dapat memesan tiket ini.</p>
                    </div>
                    <a href="deposit.php" class="inline-block bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg text-md transition duration-300 shadow-lg">
                        Deposit sekarang
                    </a>
                <?php else: ?>
                    <form action="purchase.php" method="POST" class="space-y-3">
                        <input type="hidden" name="flight_id" value="<?= (int) $flight['id'] ?>">
                        <input type="hidden" name="redirect" value="<?= sanitize($redirect) ?>">
                        <div class="bg-indigo-50 border border-indigo-200 p-4 rounded-lg text-indigo-800">
                            <p class="font-semibold">Harga tiket: <?= formatRupiah((int) $flight['price']) ?></p>
                            <p class="text-sm">Saldo setelah pembelian diperkirakan: <?= formatRupiah((int) $user['credit'] - (int) $flight['price']) ?></p>
                        </div>
                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-lg text-md transition duration-300 shadow-lg">
                            Konfirmasi &amp; Bayar dengan Kredit
                        </button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>

            <div class="pt-4 border-t border-gray-200 text-sm text-gray-500 flex items-center space-x-2">
                <i class="fas fa-info-circle"></i>
                <span>Proses pembayaran menggunakan kredit internal (dummy). Tidak ada transaksi nyata.</span>
            </div>
        </div>

        <a href="javascript:history.back()" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 font-semibold">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>
<?php include __DIR__ . '/templates/footer.php'; ?>

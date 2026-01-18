<?php
require_once __DIR__ . '/bootstrap.php';

$visaId = isset($_GET['flight_id']) ? (int) $_GET['flight_id'] : 0;
if ($visaId <= 0) {
    header('Location: index.php');
    exit();
}

$visaStmt = $pdo->prepare('SELECT * FROM flights WHERE id = :id');
$visaStmt->execute(['id' => $visaId]);
$visa = $visaStmt->fetch();

if (!$visa) {
    header('Location: index.php');
    exit();
}

$user = currentUser($pdo);
$pageTitle = 'Detail Visa ' . $visa['flight_code'];
$flashSuccess = $_SESSION['success'] ?? null;
$flashError = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);

$processingDays = (int) ceil((strtotime($visa['arrival_time']) - strtotime($visa['departure_time'])) / 86400);
$redirect = $_SERVER['REQUEST_URI'] ?? 'ticket_detail.php?flight_id=' . $visaId;
?>
<?php include __DIR__ . '/templates/header.php'; ?>
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="bg-white p-6 rounded-xl shadow border border-gray-200">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs uppercase text-gray-500">Kode Layanan Visa</p>
                    <h1 class="text-2xl font-bold text-gray-900 flex items-center space-x-2">
                        <i class="fas fa-passport text-indigo-600"></i>
                        <span><?= sanitize($visa['flight_code']) ?></span>
                    </h1>
                    <p class="text-gray-500 mt-1">Jenis visa: <span class="font-semibold text-gray-800"><?= sanitize($visa['airline']) ?></span></p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500">Biaya layanan per pemohon</p>
                    <p class="text-3xl font-extrabold text-red-600"><?= formatRupiah((int) $visa['price']) ?></p>
                    <?php if ($user): ?>
                        <p class="text-xs text-gray-500 mt-1">Saldo Anda: <?= formatRupiah((int) $user['credit']) ?></p>
                    <?php else: ?>
                        <p class="text-xs text-gray-500 mt-1">Masuk untuk melihat saldo Anda.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
                <div class="p-4 bg-indigo-50 rounded-lg">
                    <p class="text-sm text-gray-500 mb-1">Kewarganegaraan</p>
                    <p class="text-2xl font-bold text-gray-900"><?= countryFlag($visa['origin_code']) ?> <?= sanitize($visa['origin_city']) ?></p>
                    <p class="text-gray-700 font-semibold"><?= sanitize($visa['origin_code']) ?></p>
                    <p class="text-xs text-gray-500 mt-1">Tanggal pengajuan: <?= sanitize(date('d M Y', strtotime($visa['departure_time']))) ?></p>
                </div>
                <div class="p-4 bg-white rounded-lg border border-gray-200 flex flex-col justify-center text-center">
                    <p class="text-sm font-semibold text-gray-600">Estimasi Proses</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">~<?= $processingDays ?> hari</p>
                    <p class="text-xs text-gray-500 mt-1">Termasuk review dokumen</p>
                </div>
                <div class="p-4 bg-indigo-50 rounded-lg text-right">
                    <p class="text-sm text-gray-500 mb-1">Negara Tujuan</p>
                    <p class="text-2xl font-bold text-gray-900"><?= countryFlag($visa['destination_code']) ?> <?= sanitize($visa['destination_city']) ?></p>
                    <p class="text-gray-700 font-semibold"><?= sanitize($visa['destination_code']) ?></p>
                    <p class="text-xs text-gray-500 mt-1">Estimasi selesai: <?= sanitize(date('d M Y', strtotime($visa['arrival_time']))) ?></p>
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

        <?php if ($visa['destination_code'] === 'SA'): ?>
            <div class="bg-white p-6 rounded-xl shadow border border-gray-200 space-y-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Daftar Visa Arab Saudi</h2>
                    <p class="text-gray-600 text-sm">Referensi jenis visa berdasarkan daftar yang Anda berikan.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach (saudiVisaGuide() as $category): ?>
                        <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                            <h3 class="font-semibold text-gray-800 mb-2"><?= sanitize($category['title']) ?></h3>
                            <ul class="list-disc ml-5 space-y-1 text-sm text-gray-700">
                                <?php foreach ($category['items'] as $item): ?>
                                    <li><?= sanitize($item) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="bg-white p-6 rounded-xl shadow border border-gray-200 space-y-4">
            <h2 class="text-xl font-bold text-gray-900">Konfirmasi Pengajuan Visa</h2>
            <p class="text-gray-600">Periksa kembali detail visa sebelum melanjutkan.</p>

            <?php if (!isLoggedIn()): ?>
                <div class="bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-3 rounded" role="alert">
                    <p class="font-semibold">Masuk diperlukan</p>
                    <p class="text-sm">Anda harus masuk terlebih dahulu untuk menyelesaikan pengajuan visa.</p>
                </div>
                <a href="login.php?redirect=<?= urlencode($redirect) ?>" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-lg text-md transition duration-300 shadow-lg">
                    Masuk untuk Melanjutkan
                </a>
            <?php else: ?>
                <?php if ((int) $user['credit'] < (int) $visa['price']): ?>
                    <div class="bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded" role="alert">
                        <p class="font-semibold flex items-center"><i class="fas fa-exclamation-triangle mr-2"></i>Saldo Anda kurang dari biaya layanan visa.</p>
                        <p class="text-sm mt-1">Silakan lakukan deposit agar dapat mengajukan visa ini.</p>
                    </div>
                    <a href="deposit.php" class="inline-block bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg text-md transition duration-300 shadow-lg">
                        Deposit sekarang
                    </a>
                <?php else: ?>
                    <form action="purchase.php" method="POST" class="space-y-3">
                        <input type="hidden" name="flight_id" value="<?= (int) $visa['id'] ?>">
                        <input type="hidden" name="redirect" value="<?= sanitize($redirect) ?>">
                        <div class="bg-indigo-50 border border-indigo-200 p-4 rounded-lg text-indigo-800">
                            <p class="font-semibold">Biaya layanan: <?= formatRupiah((int) $visa['price']) ?></p>
                            <p class="text-sm">Saldo setelah pengajuan diperkirakan: <?= formatRupiah((int) $user['credit'] - (int) $visa['price']) ?></p>
                        </div>
                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-lg text-md transition duration-300 shadow-lg">
                            Konfirmasi &amp; Ajukan dengan Kredit
                        </button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>

            <div class="pt-4 border-t border-gray-200 text-sm text-gray-500 flex items-center space-x-2">
                <i class="fas fa-info-circle"></i>
                <span>Pengajuan menggunakan kredit internal (dummy). Tidak ada transaksi nyata.</span>
            </div>
        </div>

        <a href="javascript:history.back()" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 font-semibold">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>
<?php include __DIR__ . '/templates/footer.php'; ?>

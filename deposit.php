<?php
require_once __DIR__ . '/bootstrap.php';
requireLogin();

$user = currentUser($pdo);
$pageTitle = 'Deposit Saldo';

$flashSuccess = $_SESSION['success'] ?? null;
$flashError = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawAmount = trim((string) ($_POST['amount'] ?? ''));
    $amount = (int) preg_replace('/[^0-9]/', '', $rawAmount);
    $method = trim($_POST['method'] ?? '');
    $note = trim($_POST['note'] ?? '');

    if ($amount <= 0) {
        $flashError = 'Nominal deposit harus lebih besar dari 0.';
    } elseif ($method === '') {
        $flashError = 'Pilih metode pembayaran untuk melanjutkan deposit.';
    } else {
        $pdo->beginTransaction();
        try {
            $now = (new DateTimeImmutable())->format(DateTimeInterface::ATOM);
            $stmt = $pdo->prepare('UPDATE users SET credit = credit + :amount, updated_at = :updated_at WHERE id = :id');
            $stmt->execute([
                'amount' => $amount,
                'updated_at' => $now,
                'id' => $user['id'],
            ]);

            $pdo->commit();
            $_SESSION['success'] = 'Deposit sebesar ' . formatRupiah($amount) . ' via ' . $method . ' berhasil. Saldo langsung bertambah.';
            header('Location: deposit.php');
            exit();
        } catch (Throwable $exception) {
            $pdo->rollBack();
            $flashError = 'Gagal memproses deposit. Silakan coba lagi.';
        }
    }

    $user = currentUser($pdo);
}
?>
<?php include __DIR__ . '/templates/header.php'; ?>
    <div class="max-w-3xl mx-auto">
        <div class="bg-white p-6 rounded-lg shadow mb-6">
            <h2 class="text-2xl font-bold mb-2">Deposit Saldo</h2>
            <p class="text-gray-600 mb-4">Isi ulang kredit Anda secara instan. Proses ini dummy: saldo langsung bertambah tanpa pembayaran nyata.</p>
            <div class="flex items-center space-x-4 p-4 bg-indigo-50 rounded-lg">
                <div class="flex-1">
                    <p class="text-sm text-gray-500">Saldo saat ini</p>
                    <p class="text-3xl font-extrabold text-indigo-700"><?= formatRupiah((int) $user['credit']) ?></p>
                </div>
                <div class="text-indigo-600 text-3xl">
                    <i class="fas fa-wallet"></i>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow">
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

            <form action="deposit.php" method="POST" class="space-y-4">
                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">Nominal Deposit</label>
                    <div class="relative rounded-md shadow-sm">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <span class="text-gray-500 sm:text-sm">Rp</span>
                        </div>
                        <input type="number" min="10000" step="10000" id="amount" name="amount" required class="block w-full rounded-md border-gray-300 pl-10 focus:border-indigo-500 focus:ring-indigo-500 p-3" placeholder="500000">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Minimal deposit Rp10.000. Angka tanpa titik/koma.</p>
                </div>
                <div>
                    <label for="method" class="block text-sm font-medium text-gray-700 mb-1">Metode Pembayaran</label>
                    <select id="method" name="method" required class="w-full border border-gray-300 rounded-lg p-3 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Pilih metode</option>
                        <option value="Transfer Bank">Transfer Bank (BCA/BNI/Mandiri/BRI)</option>
                        <option value="Virtual Account">Virtual Account</option>
                        <option value="Kartu Kredit">Kartu Kredit/Debit</option>
                        <option value="E-Wallet">E-Wallet (OVO/DANA/Gopay/ShopeePay)</option>
                    </select>
                </div>
                <div>
                    <label for="note" class="block text-sm font-medium text-gray-700 mb-1">Catatan/Referensi (opsional)</label>
                    <input type="text" id="note" name="note" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Contoh: 1234567890 atau nama pemilik rekening">
                    <p class="text-xs text-gray-500 mt-1">Informasi ini hanya simulasi dan tidak divalidasi.</p>
                </div>
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-lg text-md transition duration-300 shadow-lg">
                    <i class="fas fa-plus-circle mr-2"></i>Deposit Sekarang
                </button>
            </form>
        </div>
    </div>
<?php include __DIR__ . '/templates/footer.php'; ?>

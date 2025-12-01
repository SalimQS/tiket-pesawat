<?php
require_once __DIR__ . '/bootstrap.php';

$asal = $_GET['asal'] ?? null;
$tujuan = $_GET['tujuan'] ?? null;
$tanggal = $_GET['tanggal'] ?? null;
$maskapai = $_GET['maskapai'] ?? null;
$hargaMax = isset($_GET['harga_max']) ? (int) $_GET['harga_max'] : null;

if (!$asal || !$tujuan || !$tanggal) {
    header('Location: index.php');
    exit();
}

$user = currentUser($pdo);
$pageTitle = 'Hasil Pencarian';

$params = [
    'asal' => $asal,
    'tujuan' => $tujuan,
    'tanggal' => $tanggal,
];

$query = 'SELECT * FROM flights WHERE origin_code = :asal AND destination_code = :tujuan AND date(departure_time) = :tanggal';

if ($maskapai) {
    $query .= ' AND airline = :maskapai';
    $params['maskapai'] = $maskapai;
}

if ($hargaMax && $hargaMax > 0) {
    $query .= ' AND price <= :harga_max';
    $params['harga_max'] = $hargaMax;
}

$query .= ' ORDER BY departure_time ASC';

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$flights = $stmt->fetchAll();
$maskapaiUnik = $pdo->query('SELECT DISTINCT airline FROM flights ORDER BY airline ASC')->fetchAll(PDO::FETCH_COLUMN);
?>
<?php include __DIR__ . '/templates/header.php'; ?>
    <h1 class="text-2xl font-bold mb-4 text-gray-800">
        Penerbangan dari <span class="text-indigo-600"><?= sanitize($asal) ?></span>
        ke <span class="text-indigo-600"><?= sanitize($tujuan) ?></span>
        <span class="text-lg font-normal text-gray-500"> (<?= sanitize($tanggal) ?>)</span>
    </h1>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <div class="lg:col-span-1">
            <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-200 sticky top-4">
                <h3 class="text-xl font-bold mb-4 text-indigo-600"><i class="fas fa-sliders-h mr-2"></i> Filter</h3>

                <form action="list_tiket.php" method="GET" class="space-y-4">
                    <input type="hidden" name="asal" value="<?= sanitize($asal) ?>">
                    <input type="hidden" name="tujuan" value="<?= sanitize($tujuan) ?>">
                    <input type="hidden" name="tanggal" value="<?= sanitize($tanggal) ?>">

                    <div>
                        <label for="maskapai" class="block text-sm font-medium text-gray-700 mb-1">Maskapai</label>
                        <select id="maskapai" name="maskapai" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Semua Maskapai</option>
                            <?php foreach ($maskapaiUnik as $maskapaiItem): ?>
                                <option value="<?= sanitize($maskapaiItem) ?>" <?= $maskapai === $maskapaiItem ? 'selected' : '' ?>><?= sanitize($maskapaiItem) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="harga_max" class="block text-sm font-medium text-gray-700 mb-1">Harga Maksimal (Rp)</label>
                        <input type="number" id="harga_max" name="harga_max" min="0" placeholder="Contoh: 1500000" value="<?= $hargaMax && $hargaMax > 0 ? sanitize((string) $hargaMax) : '' ?>" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 rounded-lg transition duration-300">
                        Terapkan Filter
                    </button>
                </form>
            </div>
        </div>

        <div class="lg:col-span-3 space-y-4">
            <?php if (count($flights) > 0): ?>
                <?php foreach ($flights as $flight): ?>
                    <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition duration-300 border border-gray-200">
                        <div class="flex justify-between items-center mb-4 border-b pb-3">
                            <div>
                                <p class="text-xs uppercase text-gray-500">Kode: <?= sanitize($flight['flight_code']) ?></p>
                                <h3 class="text-xl font-bold text-gray-900 flex items-center">
                                    <i class="fas fa-plane mr-2 text-indigo-600"></i>
                                    <?= sanitize($flight['airline']) ?>
                                </h3>
                                <p class="text-sm text-gray-500"><?= sanitize($flight['origin_city']) ?> ➝ <?= sanitize($flight['destination_city']) ?></p>
                            </div>
                            <div class="text-right">
                                <p class="text-2xl font-extrabold text-red-600"><?= formatRupiah((int) $flight['price']) ?></p>
                                <p class="text-xs text-gray-500">Harga per orang</p>
                            </div>
                        </div>

                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div class="text-left">
                                <p class="text-3xl font-extrabold text-gray-800"><?= sanitize(date('H:i', strtotime($flight['departure_time']))) ?></p>
                                <p class="text-lg font-semibold text-gray-600"><?= sanitize($flight['origin_code']) ?></p>
                            </div>

                            <div class="text-center text-gray-500 flex flex-col items-center">
                                <p class="text-sm font-semibold mb-1">Durasi ~<?= ceil((strtotime($flight['arrival_time']) - strtotime($flight['departure_time'])) / 3600) ?> jam</p>
                                <div class="h-1 w-20 bg-gray-300 rounded-full my-1"></div>
                                <p class="text-sm">Non-stop</p>
                            </div>

                            <div class="text-right">
                                <p class="text-3xl font-extrabold text-gray-800"><?= sanitize(date('H:i', strtotime($flight['arrival_time']))) ?></p>
                                <p class="text-lg font-semibold text-gray-600"><?= sanitize($flight['destination_code']) ?></p>
                            </div>

                            <div class="ml-0 md:ml-8">
                                <?php if (!isLoggedIn()): ?>
                                    <a href="login.php?redirect=<?= urlencode($_SERVER['REQUEST_URI'] ?? 'list_tiket.php') ?>" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-lg text-md transition duration-300 shadow-lg">
                                        Masuk untuk Pesan
                                    </a>
                                <?php else: ?>
                                    <form action="purchase.php" method="POST">
                                        <input type="hidden" name="flight_id" value="<?= (int) $flight['id'] ?>">
                                        <input type="hidden" name="redirect" value="<?= sanitize($_SERVER['REQUEST_URI'] ?? 'list_tiket.php') ?>">
                                        <button type="submit" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-lg text-md transition duration-300 shadow-lg">
                                            Pesan Sekarang
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded-md shadow-md" role="alert">
                    <p class="font-bold">Tiket Tidak Ditemukan</p>
                    <p>Mohon maaf, tidak ada penerbangan yang sesuai dengan kriteria filter saat ini.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <p class="mt-8 text-center"><a href="index.php" class="text-indigo-600 hover:text-indigo-800 font-medium">← Ubah Pencarian</a></p>
<?php include __DIR__ . '/templates/footer.php'; ?>

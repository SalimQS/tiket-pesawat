<?php
require_once __DIR__ . '/bootstrap.php';

$asal = $_GET['asal'] ?? null;
$tujuan = $_GET['tujuan'] ?? null;
$tanggal = $_GET['tanggal'] ?? null;
$operator = $_GET['operator'] ?? null;
$hargaInput = $_GET['harga_max'] ?? null;
$hargaMax = null;

if ($hargaInput !== null && $hargaInput !== '') {
    $hargaMax = (int) preg_replace('/\D+/', '', (string) $hargaInput);
    if ($hargaMax <= 0) {
        $hargaMax = null;
    }
}

if (!$asal || !$tujuan || !$tanggal) {
    header('Location: index.php');
    exit();
}

$user = currentUser($pdo);
$stations = stationCatalog();
$pageTitle = 'Hasil Pencarian';
$today = (new DateTimeImmutable('today'))->format('Y-m-d');

$params = [
    'asal' => $asal,
    'tujuan' => $tujuan,
    'tanggal' => $tanggal,
];

$query = 'SELECT * FROM trains WHERE origin_code = :asal AND destination_code = :tujuan AND date(departure_time) = :tanggal';

if ($operator) {
    $query .= ' AND operator = :operator';
    $params['operator'] = $operator;
}

if ($hargaMax && $hargaMax > 0) {
    $query .= ' AND price <= :harga_max';
    $params['harga_max'] = $hargaMax;
}

$query .= ' ORDER BY departure_time ASC';

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$trains = $stmt->fetchAll();
$operatorUnik = $pdo->query('SELECT DISTINCT operator FROM trains ORDER BY operator ASC')->fetchAll(PDO::FETCH_COLUMN);
?>
<?php include __DIR__ . '/templates/header.php'; ?>
    <h1 class="text-2xl font-bold mb-4 text-gray-800">
        Perjalanan kereta dari <span class="text-indigo-600"><?= sanitize($asal) ?></span>
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
                        <label for="operator" class="block text-sm font-medium text-gray-700 mb-1">Operator Kereta</label>
                        <select id="operator" name="operator" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Semua Operator</option>
                            <?php foreach ($operatorUnik as $operatorItem): ?>
                                <option value="<?= sanitize($operatorItem) ?>" <?= $operator === $operatorItem ? 'selected' : '' ?>><?= sanitize($operatorItem) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="harga_max" class="block text-sm font-medium text-gray-700 mb-1">Harga Maksimal (Rp)</label>
                        <input type="number" id="harga_max" name="harga_max" min="0" placeholder="Contoh: 1500000" value="<?= $hargaMax !== null ? sanitize((string) $hargaMax) : '' ?>" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 rounded-lg transition duration-300">
                        Terapkan Filter
                    </button>
                </form>
            </div>
        </div>

        <div class="lg:col-span-3 space-y-4">
            <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-200">
                <h2 class="text-xl font-bold mb-4 text-gray-800 flex items-center">
                    <i class="fas fa-search mr-2 text-indigo-600"></i> Cari Tiket Lain
                </h2>
                <form action="list_tiket.php" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="asal_re" class="block text-sm font-semibold text-gray-700 mb-1">Dari</label>
                        <select id="asal_re" name="asal" required class="w-full border border-gray-300 rounded-lg p-3 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Pilih stasiun asal</option>
                            <?php foreach ($stations as $station): ?>
                                <option value="<?= sanitize($station['code']) ?>" <?= $station['code'] === $asal ? 'selected' : '' ?>><?= sanitize($station['city']) ?> (<?= sanitize($station['code']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="tujuan_re" class="block text-sm font-semibold text-gray-700 mb-1">Ke</label>
                        <select id="tujuan_re" name="tujuan" required class="w-full border border-gray-300 rounded-lg p-3 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Pilih stasiun tujuan</option>
                            <?php foreach ($stations as $station): ?>
                                <option value="<?= sanitize($station['code']) ?>" <?= $station['code'] === $tujuan ? 'selected' : '' ?>><?= sanitize($station['city']) ?> (<?= sanitize($station['code']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="tanggal_re" class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Berangkat</label>
                        <input type="date" id="tanggal_re" name="tanggal" required value="<?= sanitize($tanggal) ?>" min="<?= $today ?>" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 rounded-lg text-lg transition duration-300 shadow-md">
                            <i class="fas fa-search mr-2"></i> Cari
                        </button>
                    </div>
                </form>
            </div>

            <?php if (count($trains) > 0): ?>
                <?php foreach ($trains as $train): ?>
                    <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition duration-300 border border-gray-200">
                        <div class="flex justify-between items-center mb-4 border-b pb-3">
                            <div>
                                <p class="text-xs uppercase text-gray-500">Kode: <?= sanitize($train['train_code']) ?></p>
                                <h3 class="text-xl font-bold text-gray-900 flex items-center">
                                    <i class="fas fa-train mr-2 text-indigo-600"></i>
                                    <?= sanitize($train['operator']) ?>
                                </h3>
                                <p class="text-sm text-gray-500"><?= sanitize($train['origin_city']) ?> ➝ <?= sanitize($train['destination_city']) ?></p>
                            </div>
                            <div class="text-right">
                                <p class="text-2xl font-extrabold text-red-600"><?= formatRupiah((int) $train['price']) ?></p>
                                <p class="text-xs text-gray-500">Harga per penumpang</p>
                            </div>
                        </div>

                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div class="text-left">
                                <p class="text-3xl font-extrabold text-gray-800"><?= sanitize(date('H:i', strtotime($train['departure_time']))) ?></p>
                                <p class="text-lg font-semibold text-gray-600"><?= sanitize($train['origin_code']) ?></p>
                            </div>

                            <div class="text-center text-gray-500 flex flex-col items-center">
                                <p class="text-sm font-semibold mb-1">Durasi ~<?= ceil((strtotime($train['arrival_time']) - strtotime($train['departure_time'])) / 3600) ?> jam</p>
                                <div class="h-1 w-20 bg-gray-300 rounded-full my-1"></div>
                                <p class="text-sm">Langsung</p>
                            </div>

                            <div class="text-right">
                                <p class="text-3xl font-extrabold text-gray-800"><?= sanitize(date('H:i', strtotime($train['arrival_time']))) ?></p>
                                <p class="text-lg font-semibold text-gray-600"><?= sanitize($train['destination_code']) ?></p>
                            </div>

                            <div class="ml-0 md:ml-8 space-y-2 text-sm">
                                <?php if (!isLoggedIn()): ?>
                                    <a href="login.php?redirect=<?= urlencode($_SERVER['REQUEST_URI'] ?? 'list_tiket.php') ?>" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-lg text-md transition duration-300 shadow-lg">
                                        Masuk untuk Pesan
                                    </a>
                                <?php else: ?>
                                    <a href="ticket_detail.php?train_id=<?= (int) $train['id'] ?>" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-lg text-md transition duration-300 shadow-lg text-center w-full">
                                        Lihat Detail &amp; Pesan
                                    </a>
                                    <?php if ((int) $user['credit'] < (int) $train['price']): ?>
                                        <p class="text-red-600 flex items-center space-x-2"><i class="fas fa-exclamation-triangle"></i><span>Kredit kurang dari harga tiket ini. Silakan deposit.</span></p>
                                        <a class="inline-block text-indigo-600 hover:text-indigo-800 font-semibold" href="deposit.php"><i class="fas fa-wallet mr-2"></i>Deposit sekarang</a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded-md shadow-md" role="alert">
                    <p class="font-bold">Tiket Tidak Ditemukan</p>
                    <p>Mohon maaf, tidak ada perjalanan kereta yang sesuai dengan kriteria filter saat ini.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <p class="mt-8 text-center"><a href="index.php" class="text-indigo-600 hover:text-indigo-800 font-medium">← Ubah Pencarian</a></p>
<?php include __DIR__ . '/templates/footer.php'; ?>

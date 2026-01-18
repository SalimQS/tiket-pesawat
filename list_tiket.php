<?php
require_once __DIR__ . '/bootstrap.php';

$asal = $_GET['asal'] ?? null;
$tujuan = $_GET['tujuan'] ?? null;
$tanggal = $_GET['tanggal'] ?? null;
$jenisVisa = $_GET['jenis_visa'] ?? null;
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
$countries = countryCatalog();
$pageTitle = 'Hasil Pencarian Visa';
$today = (new DateTimeImmutable('today'))->format('Y-m-d');

$params = [
    'asal' => $asal,
    'tujuan' => $tujuan,
    'tanggal' => $tanggal,
];

$query = 'SELECT * FROM flights WHERE origin_code = :asal AND destination_code = :tujuan AND date(departure_time) = :tanggal';

if ($jenisVisa) {
    $query .= ' AND airline = :jenis_visa';
    $params['jenis_visa'] = $jenisVisa;
}

if ($hargaMax && $hargaMax > 0) {
    $query .= ' AND price <= :harga_max';
    $params['harga_max'] = $hargaMax;
}

$query .= ' ORDER BY departure_time ASC';

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$flights = $stmt->fetchAll();
$jenisVisaList = $pdo->query('SELECT DISTINCT airline FROM flights ORDER BY airline ASC')->fetchAll(PDO::FETCH_COLUMN);
?>
<?php include __DIR__ . '/templates/header.php'; ?>
    <h1 class="text-2xl font-bold mb-4 text-gray-800">
        Visa untuk <span class="text-indigo-600"><?= countryFlag($asal) ?> <?= sanitize(countryName($asal)) ?></span>
        ke <span class="text-indigo-600"><?= countryFlag($tujuan) ?> <?= sanitize(countryName($tujuan)) ?></span>
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
                        <label for="jenis_visa" class="block text-sm font-medium text-gray-700 mb-1">Jenis Visa</label>
                        <select id="jenis_visa" name="jenis_visa" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Semua Jenis</option>
                            <?php foreach ($jenisVisaList as $visaItem): ?>
                                <option value="<?= sanitize($visaItem) ?>" <?= $jenisVisa === $visaItem ? 'selected' : '' ?>><?= sanitize($visaItem) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="harga_max" class="block text-sm font-medium text-gray-700 mb-1">Biaya Maksimal (Rp)</label>
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
                    <i class="fas fa-search mr-2 text-indigo-600"></i> Cari Visa Lain
                </h2>
                <form action="list_tiket.php" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="asal_re" class="block text-sm font-semibold text-gray-700 mb-1">Kewarganegaraan</label>
                        <select id="asal_re" name="asal" required class="w-full border border-gray-300 rounded-lg p-3 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Pilih kewarganegaraan</option>
                            <?php foreach ($countries as $country): ?>
                                <option value="<?= sanitize($country['code']) ?>" <?= $country['code'] === $asal ? 'selected' : '' ?>><?= sanitize($country['name']) ?> (<?= sanitize($country['code']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="tujuan_re" class="block text-sm font-semibold text-gray-700 mb-1">Negara tujuan</label>
                        <select id="tujuan_re" name="tujuan" required class="w-full border border-gray-300 rounded-lg p-3 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Pilih negara tujuan</option>
                            <?php foreach ($countries as $country): ?>
                                <option value="<?= sanitize($country['code']) ?>" <?= $country['code'] === $tujuan ? 'selected' : '' ?>><?= sanitize($country['name']) ?> (<?= sanitize($country['code']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="tanggal_re" class="block text-sm font-semibold text-gray-700 mb-1">Tanggal rencana berangkat</label>
                        <input type="date" id="tanggal_re" name="tanggal" required value="<?= sanitize($tanggal) ?>" min="<?= $today ?>" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 rounded-lg text-lg transition duration-300 shadow-md">
                            <i class="fas fa-search mr-2"></i> Cari Visa
                        </button>
                    </div>
                </form>
            </div>

            <?php if (count($flights) > 0): ?>
                <?php foreach ($flights as $flight): ?>
                    <?php $processingDays = (int) ceil((strtotime($flight['arrival_time']) - strtotime($flight['departure_time'])) / 86400); ?>
                    <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition duration-300 border border-gray-200">
                        <div class="flex justify-between items-center mb-4 border-b pb-3">
                            <div>
                                <p class="text-xs uppercase text-gray-500">Kode layanan: <?= sanitize($flight['flight_code']) ?></p>
                                <h3 class="text-xl font-bold text-gray-900 flex items-center">
                                    <i class="fas fa-stamp mr-2 text-indigo-600"></i>
                                    <?= sanitize($flight['airline']) ?>
                                </h3>
                                <p class="text-sm text-gray-500">
                                    <?= countryFlag($flight['origin_code']) ?> <?= sanitize($flight['origin_city']) ?> ➝
                                    <?= countryFlag($flight['destination_code']) ?> <?= sanitize($flight['destination_city']) ?>
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-2xl font-extrabold text-red-600"><?= formatRupiah((int) $flight['price']) ?></p>
                                <p class="text-xs text-gray-500">Biaya layanan per pemohon</p>
                            </div>
                        </div>

                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div class="text-left">
                                <p class="text-xs uppercase text-gray-500">Kewarganegaraan</p>
                                <p class="text-3xl font-extrabold text-gray-800"><?= countryFlag($flight['origin_code']) ?> <?= sanitize($flight['origin_code']) ?></p>
                                <p class="text-lg font-semibold text-gray-600"><?= sanitize($flight['origin_city']) ?></p>
                                <p class="text-xs text-gray-500 mt-1">Pengajuan: <?= sanitize(date('d M Y', strtotime($flight['departure_time']))) ?></p>
                            </div>

                            <div class="text-center text-gray-500 flex flex-col items-center">
                                <p class="text-sm font-semibold mb-1">Proses ~<?= $processingDays ?> hari</p>
                                <div class="h-1 w-20 bg-gray-300 rounded-full my-1"></div>
                                <p class="text-sm">Termasuk review dokumen</p>
                            </div>

                            <div class="text-right">
                                <p class="text-xs uppercase text-gray-500">Tujuan</p>
                                <p class="text-3xl font-extrabold text-gray-800"><?= countryFlag($flight['destination_code']) ?> <?= sanitize($flight['destination_code']) ?></p>
                                <p class="text-lg font-semibold text-gray-600"><?= sanitize($flight['destination_city']) ?></p>
                                <p class="text-xs text-gray-500 mt-1">Estimasi selesai: <?= sanitize(date('d M Y', strtotime($flight['arrival_time']))) ?></p>
                            </div>

                            <div class="ml-0 md:ml-8 space-y-2 text-sm">
                                <?php if (!isLoggedIn()): ?>
                                    <a href="login.php?redirect=<?= urlencode($_SERVER['REQUEST_URI'] ?? 'list_tiket.php') ?>" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-lg text-md transition duration-300 shadow-lg">
                                        Masuk untuk Ajukan
                                    </a>
                                <?php else: ?>
                                    <a href="ticket_detail.php?flight_id=<?= (int) $flight['id'] ?>" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-lg text-md transition duration-300 shadow-lg text-center w-full">
                                        Lihat Detail &amp; Ajukan
                                    </a>
                                    <?php if ((int) $user['credit'] < (int) $flight['price']): ?>
                                        <p class="text-red-600 flex items-center space-x-2"><i class="fas fa-exclamation-triangle"></i><span>Saldo kurang dari biaya layanan ini. Silakan deposit.</span></p>
                                        <a class="inline-block text-indigo-600 hover:text-indigo-800 font-semibold" href="deposit.php"><i class="fas fa-wallet mr-2"></i>Deposit sekarang</a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded-md shadow-md" role="alert">
                    <p class="font-bold">Visa Tidak Ditemukan</p>
                    <p>Mohon maaf, belum ada paket visa yang sesuai dengan kriteria filter saat ini.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <p class="mt-8 text-center"><a href="index.php" class="text-indigo-600 hover:text-indigo-800 font-medium">← Ubah Pencarian Visa</a></p>
<?php include __DIR__ . '/templates/footer.php'; ?>

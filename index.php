<?php
require_once __DIR__ . '/bootstrap.php';

$user = currentUser($pdo);
$pageTitle = 'Cari Tiket';
$airports = airportCatalog();
$today = (new DateTimeImmutable('today'))->format('Y-m-d');

$recommendedStmt = $pdo->prepare('SELECT * FROM flights WHERE date(departure_time) >= :today ORDER BY price ASC LIMIT 6');
$recommendedStmt->execute(['today' => $today]);
$recommendedFlights = $recommendedStmt->fetchAll();
?>
<?php include __DIR__ . '/templates/header.php'; ?>
    <div class="bg-white p-6 md:p-8 rounded-xl shadow-2xl border border-indigo-100">
        <h1 class="text-3xl font-extrabold mb-6 text-center text-gray-800">Tiket Pesawat</h1>

        <form action="list_tiket.php" method="GET" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="asal" class="block text-sm font-semibold text-gray-700 mb-1">Dari</label>
                    <select id="asal" name="asal" required class="w-full border border-gray-300 rounded-lg p-3 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Pilih kota asal</option>
                        <?php foreach ($airports as $airport): ?>
                            <option value="<?= sanitize($airport['code']) ?>" data-city="<?= sanitize($airport['city']) ?>"><?= sanitize($airport['city']) ?> (<?= sanitize($airport['code']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="tujuan" class="block text-sm font-semibold text-gray-700 mb-1">Ke</label>
                    <select id="tujuan" name="tujuan" required class="w-full border border-gray-300 rounded-lg p-3 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Pilih kota tujuan</option>
                        <?php foreach ($airports as $airport): ?>
                            <option value="<?= sanitize($airport['code']) ?>"><?= sanitize($airport['city']) ?> (<?= sanitize($airport['code']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="tanggal" class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Berangkat</label>
                    <input type="date" id="tanggal" name="tanggal" required value="<?= $today ?>" min="<?= $today ?>" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 rounded-lg text-lg transition duration-300 shadow-md">
                        <i class="fas fa-search mr-2"></i> Cari
                    </button>
                </div>
            </div>
        </form>
    </div>

    <?php if (count($recommendedFlights) > 0): ?>
        <div class="mt-10">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-bold text-gray-800">Rekomendasi untuk Anda</h2>
                <p class="text-sm text-gray-500">Harga terbaik untuk 7 hari ke depan</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($recommendedFlights as $flight): ?>
                    <div class="bg-white p-5 rounded-xl shadow-lg border border-gray-200 hover:shadow-xl transition duration-300">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <p class="text-xs uppercase text-gray-500">Kode <?= sanitize($flight['flight_code']) ?></p>
                                <h3 class="text-lg font-bold text-gray-900 flex items-center">
                                    <i class="fas fa-plane-departure mr-2 text-indigo-600"></i>
                                    <?= sanitize($flight['airline']) ?>
                                </h3>
                                <p class="text-sm text-gray-500"><?= sanitize($flight['origin_city']) ?> ➝ <?= sanitize($flight['destination_city']) ?></p>
                            </div>
                            <div class="text-right">
                                <p class="text-xl font-extrabold text-red-600"><?= formatRupiah((int) $flight['price']) ?></p>
                                <p class="text-xs text-gray-500">per orang</p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between text-gray-700 mb-3">
                            <div>
                                <p class="text-sm font-semibold">Berangkat</p>
                                <p class="text-lg font-bold"><?= sanitize(date('d M, H:i', strtotime($flight['departure_time']))) ?></p>
                            </div>
                            <div class="text-center">
                                <p class="text-xs uppercase text-gray-500">Durasi</p>
                                <p class="font-semibold">~<?= ceil((strtotime($flight['arrival_time']) - strtotime($flight['departure_time'])) / 3600) ?> jam</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold">Tiba</p>
                                <p class="text-lg font-bold"><?= sanitize(date('d M, H:i', strtotime($flight['arrival_time']))) ?></p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">Rute: <?= sanitize($flight['origin_code']) ?> → <?= sanitize($flight['destination_code']) ?></span>
                            <a href="list_tiket.php?asal=<?= urlencode($flight['origin_code']) ?>&tujuan=<?= urlencode($flight['destination_code']) ?>&tanggal=<?= urlencode(date('Y-m-d', strtotime($flight['departure_time']))) ?>" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold py-2 px-4 rounded-lg transition duration-300">
                                Lihat Tiket
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
<?php include __DIR__ . '/templates/footer.php'; ?>

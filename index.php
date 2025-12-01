<?php
require_once __DIR__ . '/bootstrap.php';

$user = currentUser($pdo);
$pageTitle = 'Cari Tiket';
$airports = airportCatalog();
$today = (new DateTimeImmutable('today'))->format('Y-m-d');
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
<?php include __DIR__ . '/templates/footer.php'; ?>

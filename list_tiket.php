<?php
session_start();

// --- 1. DUMMY DATA TIKET PESAWAT (Menggantikan Database) ---
$asal_pencarian = $_GET['asal'] ?? 'JKT';
$tujuan_pencarian = $_GET['tujuan'] ?? 'DPS';
$tanggal_pencarian = $_GET['tanggal'] ?? date('Y-m-d');

$dummy_penerbangan = [
    // Data Tiket (Disesuaikan dengan rute pencarian JKT ke DPS)
    [
        'kode_penerbangan' => 'GA-400', 
        'maskapai' => 'Garuda Indonesia', 
        'logo' => 'fas fa-plane-departure text-blue-600',
        'asal' => 'JKT', 'tujuan' => 'DPS', 
        'tanggal' => $tanggal_pencarian,
        'waktu_berangkat' => '08:00', 'waktu_tiba' => '10:45', 
        'harga' => 1850000, 'transit' => 'Langsung'
    ],
    [
        'kode_penerbangan' => 'ID-6501', 
        'maskapai' => 'Batik Air', 
        'logo' => 'fas fa-plane text-indigo-600',
        'asal' => 'JKT', 'tujuan' => 'DPS', 
        'tanggal' => $tanggal_pencarian,
        'waktu_berangkat' => '10:30', 'waktu_tiba' => '13:20', 
        'harga' => 1250000, 'transit' => 'Langsung'
    ],
    [
        'kode_penerbangan' => 'QG-880', 
        'maskapai' => 'Citilink', 
        'logo' => 'fas fa-plane-up text-green-600',
        'asal' => 'JKT', 'tujuan' => 'DPS', 
        'tanggal' => $tanggal_pencarian,
        'waktu_berangkat' => '13:00', 'waktu_tiba' => '15:45', 
        'harga' => 950000, 'transit' => 'Langsung'
    ],
    [
        'kode_penerbangan' => 'JT-018', 
        'maskapai' => 'Lion Air', 
        'logo' => 'fas fa-feather-alt text-yellow-600',
        'asal' => 'JKT', 'tujuan' => 'DPS', 
        'tanggal' => $tanggal_pencarian,
        'waktu_berangkat' => '16:40', 'waktu_tiba' => '19:35', 
        'harga' => 780000, 'transit' => 'Langsung'
    ],
    [
        'kode_penerbangan' => 'SJ-105', 
        'maskapai' => 'Sriwijaya Air', 
        'logo' => 'fas fa-plane-circle-exclamation text-red-600',
        'asal' => 'JKT', 'tujuan' => 'DPS', 
        'tanggal' => $tanggal_pencarian,
        'waktu_berangkat' => '06:00', 'waktu_tiba' => '12:00', 
        'harga' => 820000, 'transit' => '1x Transit (SUB)'
    ],
];

// --- 2. LOGIKA FILTER DATA (Sama seperti sebelumnya, tapi diterapkan ke array) ---

$maskapai_filter = $_GET['maskapai'] ?? '';
$harga_max_filter = $_GET['harga_max'] ?? 0;

$hasil_penerbangan = array_filter($dummy_penerbangan, function($penerbangan) use ($maskapai_filter, $harga_max_filter, $asal_pencarian, $tujuan_pencarian, $tanggal_pencarian) {
    $match = true;

    // Filter Maskapai
    if (!empty($maskapai_filter) && $penerbangan['maskapai'] != $maskapai_filter) {
        $match = false;
    }

    // Filter Harga Maksimal
    if ($harga_max_filter > 0 && $penerbangan['harga'] > $harga_max_filter) {
        $match = false;
    }
    
    // Filter Rute (tetap memastikan data dummy sesuai dengan rute pencarian)
    if ($penerbangan['asal'] != $asal_pencarian || $penerbangan['tujuan'] != $tujuan_pencarian) {
        $match = false;
    }

    return $match;
});

// --- 3. LOGIKA UNTUK TAMPILAN ---
$is_logged_in = isset($_SESSION['user_id']);
$username = $is_logged_in ? $_SESSION['username'] : '';

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Hasil Pencarian Tiket - <?php echo htmlspecialchars($asal_pencarian); ?> ke <?php echo htmlspecialchars($tujuan_pencarian); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen">

    <nav class="bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex-shrink-0">
                    <a href="index.php" class="text-2xl font-bold text-indigo-600">Pesawatin</a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="<?php echo $is_logged_in ? 'dashboard.php' : 'login.php'; ?>" 
                       class="flex items-center space-x-2 p-2 rounded-full bg-indigo-100 hover:bg-indigo-200 transition">
                        <i class="fas fa-user-circle text-xl text-indigo-600"></i>
                        <span class="text-sm font-semibold text-indigo-700 hidden sm:block">
                            <?php echo $is_logged_in ? htmlspecialchars($username) : 'Masuk / Daftar'; ?>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </nav>
    
    <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">
        
        <h1 class="text-2xl font-bold mb-4 text-gray-800">
            Penerbangan dari <span class="text-indigo-600"><?php echo htmlspecialchars($asal_pencarian); ?></span> 
            ke <span class="text-indigo-600"><?php echo htmlspecialchars($tujuan_pencarian); ?></span>
            <span class="text-lg font-normal text-gray-500"> (<?php echo htmlspecialchars($tanggal_pencarian); ?>)</span>
        </h1>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

            <div class="lg:col-span-1">
                <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-200 sticky top-4">
                    <h3 class="text-xl font-bold mb-4 text-indigo-600"><i class="fas fa-sliders-h mr-2"></i> Filter</h3>
                    
                    <form action="list_tiket.php" method="GET" class="space-y-4">
                        <input type="hidden" name="asal" value="<?php echo htmlspecialchars($asal_pencarian); ?>">
                        <input type="hidden" name="tujuan" value="<?php echo htmlspecialchars($tujuan_pencarian); ?>">
                        <input type="hidden" name="tanggal" value="<?php echo htmlspecialchars($tanggal_pencarian); ?>">

                        <div>
                            <label for="maskapai" class="block text-sm font-medium text-gray-700 mb-1">Maskapai</label>
                            <select id="maskapai" name="maskapai" 
                                    class="w-full border border-gray-300 rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Semua Maskapai</option>
                                <?php
                                $maskapai_unik = array_unique(array_column($dummy_penerbangan, 'maskapai'));
                                foreach ($maskapai_unik as $m) {
                                    $selected = $maskapai_filter == $m ? 'selected' : '';
                                    echo "<option value=\"$m\" $selected>$m</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div>
                            <label for="harga_max" class="block text-sm font-medium text-gray-700 mb-1">Harga Maksimal (Rp)</label>
                            <input type="number" id="harga_max" name="harga_max" min="0" placeholder="Contoh: 1000000" 
                                   value="<?php echo $harga_max_filter > 0 ? htmlspecialchars($harga_max_filter) : ''; ?>"
                                   class="w-full border border-gray-300 rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 rounded-lg transition duration-300">
                            Terapkan Filter
                        </button>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-3 space-y-4">
                <?php
                if (count($hasil_penerbangan) > 0) {
                    foreach ($hasil_penerbangan as $row) {
                        ?>
                        <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition duration-300 border border-gray-200">
                            <div class="flex justify-between items-center mb-4 border-b pb-3">
                                <h3 class="text-xl font-bold text-gray-900 flex items-center">
                                    <i class="<?php echo $row['logo']; ?> mr-2"></i> 
                                    <?php echo htmlspecialchars($row['maskapai']); ?>
                                </h3>
                                <div class="text-right">
                                    <p class="text-2xl font-extrabold text-red-600">Rp <?php echo number_format($row['harga']); ?></p>
                                    <p class="text-xs text-gray-500">Harga per orang</p>
                                </div>
                            </div>
                            
                            <div class="flex justify-between items-center">
                                <div class="text-left">
                                    <p class="text-3xl font-extrabold text-gray-800"><?php echo htmlspecialchars($row['waktu_berangkat']); ?></p>
                                    <p class="text-lg font-semibold text-gray-600"><?php echo htmlspecialchars($row['asal']); ?></p>
                                </div>
                                
                                <div class="text-center text-gray-500 flex flex-col items-center">
                                    <p class="text-sm font-semibold mb-1"><?php echo htmlspecialchars($row['transit']); ?></p>
                                    <div class="h-1 w-20 bg-gray-300 rounded-full my-1"></div>
                                    <p class="text-sm">Kode: <?php echo htmlspecialchars($row['kode_penerbangan']); ?></p>
                                </div>
                                
                                <div class="text-right">
                                    <p class="text-3xl font-extrabold text-gray-800"><?php echo htmlspecialchars($row['waktu_tiba']); ?></p>
                                    <p class="text-lg font-semibold text-gray-600"><?php echo htmlspecialchars($row['tujuan']); ?></p>
                                </div>

                                <div class="ml-8">
                                    <a href="#" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-lg text-md transition duration-300 shadow-lg">
                                        Pilih
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    ?>
                    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded-md shadow-md" role="alert">
                        <p class="font-bold">Tiket Tidak Ditemukan</p>
                        <p>Mohon maaf, tidak ada penerbangan yang sesuai dengan kriteria filter saat ini.</p>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>

        <p class="mt-8 text-center"><a href="index.php" class="text-indigo-600 hover:text-indigo-800 font-medium">← Ubah Pencarian</a></p>
    </div>
</body>
</html>
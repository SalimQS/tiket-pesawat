<?php
session_start();
// Cek status login (untuk menampilkan di navbar)
$is_logged_in = isset($_SESSION['user_id']);
$username = $is_logged_in ? $_SESSION['username'] : '';

// Jika Anda menggunakan database, pastikan koneksi.php disertakan di sini.
// include 'koneksi.php'; 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Pesawatin - Cari Tiket Pesawat</title>
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
                    <!-- <a href="#" class="text-gray-600 hover:text-indigo-600 font-medium hidden md:block">Pesan Hotel</a>
                    <a href="#" class="text-gray-600 hover:text-indigo-600 font-medium hidden md:block">Aktivitas</a> -->
                    
                    <a href="<?php echo $is_logged_in ? 'dashboard.php' : 'login.php'; ?>" 
                       class="flex items-center space-x-2 p-2 rounded-full bg-indigo-100 hover:bg-indigo-200 transition">
                        <i class="fas fa-user-circle text-xl text-indigo-600"></i>
                        <?php if ($is_logged_in): ?>
                            <span class="text-sm font-semibold text-indigo-700 hidden sm:block"><?php echo htmlspecialchars($username); ?></span>
                        <?php else: ?>
                            <span class="text-sm font-semibold text-indigo-700 hidden sm:block">Masuk / Daftar</span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </div>
    </nav>
    
    <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">
        <div class="bg-white p-6 md:p-8 rounded-xl shadow-2xl border border-indigo-100">
            <h1 class="text-3xl font-extrabold mb-6 text-center text-gray-800">Tiket Pesawat</h1>

            <form action="list_tiket.php" method="GET" class="space-y-4">
                
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="asal" class="block text-sm font-semibold text-gray-700 mb-1">Dari</label>
                        <input type="text" id="asal" name="asal" required placeholder="Kota Asal / Bandara"
                               class="w-full border border-gray-300 rounded-lg p-3 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label for="tujuan" class="block text-sm font-semibold text-gray-700 mb-1">Ke</label>
                        <input type="text" id="tujuan" name="tujuan" required placeholder="Kota Tujuan / Bandara"
                               class="w-full border border-gray-300 rounded-lg p-3 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label for="tanggal" class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Berangkat</label>
                        <input type="date" id="tanggal" name="tanggal" required 
                               class="w-full border border-gray-300 rounded-lg p-3 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div class="flex items-end">
                         <button type="submit" 
                                class="w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 rounded-lg text-lg transition duration-300 shadow-md">
                            <i class="fas fa-search mr-2"></i> Cari
                        </button>
                    </div>
                </div>

                <div id="filter-options" class="hidden border-t pt-4 mt-4">
                    <h2 class="text-xl font-semibold mb-3 text-gray-800">Filter Tambahan</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="maskapai" class="block text-sm font-medium text-gray-700 mb-1">Maskapai</label>
                            <select id="maskapai" name="maskapai" 
                                    class="w-full border border-gray-300 rounded-lg p-3 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Semua Maskapai</option>
                                <option value="Garuda">Garuda Indonesia</option>
                                <option value="Lion">Lion Air</option>
                                </select>
                        </div>
                        <div>
                            <label for="harga_max" class="block text-sm font-medium text-gray-700 mb-1">Harga Maksimal (Rp)</label>
                            <input type="number" id="harga_max" name="harga_max" min="0" placeholder="Contoh: 1500000"
                                   class="w-full border border-gray-300 rounded-lg p-3 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                </div>
                
                 <div class="text-center pt-2">
                     <button type="button" onclick="toggleFilter()" 
                            class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                        <i class="fas fa-sliders-h mr-1"></i> Tampilkan Filter
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function toggleFilter() {
            const filterDiv = document.getElementById('filter-options');
            const button = document.querySelector('[onclick="toggleFilter()"]');
            
            filterDiv.classList.toggle('hidden');
            
            // Mengubah teks tombol berdasarkan status filter
            if (filterDiv.classList.contains('hidden')) {
                button.innerHTML = '<i class="fas fa-sliders-h mr-1"></i> Tampilkan Filter';
            } else {
                button.innerHTML = '<i class="fas fa-times-circle mr-1"></i> Sembunyikan Filter';
            }
        }
    </script>
</body>
</html>
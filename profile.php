<?php
require_once __DIR__ . '/bootstrap.php';
requireLogin();

$user = currentUser($pdo);
$pageTitle = 'Profil';
$success = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name === '' || $username === '') {
        $error = 'Nama dan username wajib diisi.';
    } else {
        $pdo->beginTransaction();
        try {
            $exists = $pdo->prepare('SELECT id FROM users WHERE username = :username AND id != :id');
            $exists->execute(['username' => $username, 'id' => $user['id']]);
            if ($exists->fetch()) {
                $error = 'Username sudah digunakan.';
            } else {
                $updateQuery = 'UPDATE users SET name = :name, username = :username, updated_at = :updated_at';
                $params = [
                    'name' => $name,
                    'username' => $username,
                    'updated_at' => (new DateTimeImmutable())->format(DateTimeInterface::ATOM),
                    'id' => $user['id'],
                ];

                if ($password !== '') {
                    $updateQuery .= ', password_hash = :password_hash';
                    $params['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
                }

                $updateQuery .= ' WHERE id = :id';
                $stmt = $pdo->prepare($updateQuery);
                $stmt->execute($params);

                $_SESSION['username'] = $username;
                $success = 'Profil berhasil diperbarui.';
                $pdo->commit();
                $user = currentUser($pdo);
            }
        } catch (Throwable $e) {
            $pdo->rollBack();
            $error = 'Terjadi kesalahan saat menyimpan data.';
        }
    }
}
?>
<?php include __DIR__ . '/templates/header.php'; ?>
    <div class="max-w-3xl mx-auto">
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-2xl font-bold mb-4">Profil</h2>
            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
                    <p><?= sanitize($success) ?></p>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                    <p><?= sanitize($error) ?></p>
                </div>
            <?php endif; ?>

            <form action="profile.php" method="POST" class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                    <input type="text" id="name" name="name" value="<?= sanitize($user['name']) ?>" required class="w-full border border-gray-300 rounded-lg p-3 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <input type="text" id="username" name="username" value="<?= sanitize($user['username']) ?>" required class="w-full border border-gray-300 rounded-lg p-3 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password Baru (opsional)</label>
                    <input type="password" id="password" name="password" placeholder="Biarkan kosong jika tidak ingin mengubah" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <p class="text-sm text-gray-500">Saldo kredit saat ini: <strong><?= formatRupiah((int) $user['credit']) ?></strong></p>
                    <p class="text-xs text-gray-400">Setiap pengguna baru otomatis mendapatkan kredit awal Rp 5.000.000.</p>
                </div>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-lg text-md transition duration-300 shadow-lg">Simpan Perubahan</button>
            </form>
        </div>
    </div>
<?php include __DIR__ . '/templates/footer.php'; ?>

<?php
require_once __DIR__ . '/bootstrap.php';

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $name === '' || $password === '') {
        $error = 'Semua field wajib diisi.';
    } else {
        $exists = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = :username');
        $exists->execute(['username' => $username]);

        if ((int) $exists->fetchColumn() > 0) {
            $error = 'Username sudah digunakan.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO users (username, name, password_hash, credit, created_at, updated_at) VALUES (:username, :name, :password_hash, :credit, :created_at, :updated_at)');
            $now = (new DateTimeImmutable())->format(DateTimeInterface::ATOM);
            $stmt->execute([
                'username' => $username,
                'name' => $name,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'credit' => 5000000,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $success = 'Akun berhasil dibuat. Silakan masuk.';
        }
    }
}

$pageTitle = 'Daftar';
$user = currentUser($pdo);
?>
<?php include __DIR__ . '/templates/header.php'; ?>
    <div class="flex items-center justify-center">
        <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-md">
            <h2 class="text-3xl font-bold mb-6 text-center text-indigo-600">Daftar</h2>

            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                    <p><?= sanitize($error) ?></p>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
                    <p><?= sanitize($success) ?></p>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST" class="space-y-4">
                <div>
                    <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Nama Lengkap</label>
                    <input type="text" id="name" name="name" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                    <input type="text" id="username" name="username" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                    <input type="password" id="password" name="password" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full transition duration-300">Buat Akun</button>
            </form>

            <p class="mt-4 text-sm text-center text-gray-600">Sudah punya akun? <a href="login.php" class="text-indigo-600 hover:text-indigo-800 font-semibold">Masuk di sini</a></p>
        </div>
    </div>
<?php include __DIR__ . '/templates/footer.php'; ?>

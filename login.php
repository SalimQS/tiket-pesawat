<?php
require_once __DIR__ . '/bootstrap.php';

$error = null;
$redirect = $_GET['redirect'] ?? 'dashboard.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $redirect = $_POST['redirect'] ?? $redirect;

    $stmt = $pdo->prepare('SELECT id, username, password_hash FROM users WHERE username = :username');
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header('Location: ' . $redirect);
        exit();
    }

    $error = 'Username atau password salah.';
}

$pageTitle = 'Masuk';
$user = currentUser($pdo);
?>
<?php include __DIR__ . '/templates/header.php'; ?>
    <div class="flex items-center justify-center">
        <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-md">
            <h2 class="text-3xl font-bold mb-6 text-center text-indigo-600">Masuk</h2>

            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                    <p><?= sanitize($error) ?></p>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="space-y-4">
                <input type="hidden" name="redirect" value="<?= sanitize($redirect) ?>">
                <div>
                    <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                    <input type="text" id="username" name="username" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                    <input type="password" id="password" name="password" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <button type="submit" name="submit_login" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full transition duration-300">Login</button>
            </form>

            <p class="mt-4 text-sm text-center text-gray-600">Belum punya akun? <a href="register.php" class="text-indigo-600 hover:text-indigo-800 font-semibold">Daftar sekarang</a></p>
        </div>
    </div>
<?php include __DIR__ . '/templates/footer.php'; ?>

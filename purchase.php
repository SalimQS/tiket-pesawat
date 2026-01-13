<?php
require_once __DIR__ . '/bootstrap.php';
requireLogin();

$user = currentUser($pdo);
$redirect = $_POST['redirect'] ?? 'dashboard.php';
$trainId = isset($_POST['train_id']) ? (int) $_POST['train_id'] : 0;

if ($trainId <= 0) {
    header('Location: ' . $redirect);
    exit();
}

$trainStmt = $pdo->prepare('SELECT * FROM trains WHERE id = :id');
$trainStmt->execute(['id' => $trainId]);
$train = $trainStmt->fetch();

if (!$train) {
    header('Location: ' . $redirect);
    exit();
}

if ((int) $user['credit'] < (int) $train['price']) {
    $_SESSION['error'] = 'Saldo kredit tidak mencukupi untuk melakukan pemesanan ini. Silakan lakukan deposit.';
    header('Location: ' . $redirect);
    exit();
}

$pdo->beginTransaction();
try {
    $insertBooking = $pdo->prepare('INSERT INTO bookings (user_id, train_id, passenger_name, status, created_at) VALUES (:user_id, :train_id, :passenger_name, :status, :created_at)');
    $now = (new DateTimeImmutable())->format(DateTimeInterface::ATOM);
    $insertBooking->execute([
        'user_id' => $user['id'],
        'train_id' => $train['id'],
        'passenger_name' => $user['name'],
        'status' => 'confirmed',
        'created_at' => $now,
    ]);

    $updateCredit = $pdo->prepare('UPDATE users SET credit = credit - :price, updated_at = :updated_at WHERE id = :id');
    $updateCredit->execute([
        'price' => $train['price'],
        'updated_at' => $now,
        'id' => $user['id'],
    ]);

    $pdo->commit();
    $_SESSION['success'] = 'Pemesanan berhasil. Kredit terpotong ' . formatRupiah((int) $train['price']) . '.';
} catch (Throwable $exception) {
    $pdo->rollBack();
    $_SESSION['error'] = 'Gagal memproses pemesanan.';
}

header('Location: dashboard.php');
exit();

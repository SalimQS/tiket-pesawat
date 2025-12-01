<?php
require_once __DIR__ . '/bootstrap.php';
requireLogin();

$user = currentUser($pdo);
$redirect = $_POST['redirect'] ?? 'dashboard.php';
$flightId = isset($_POST['flight_id']) ? (int) $_POST['flight_id'] : 0;

if ($flightId <= 0) {
    header('Location: ' . $redirect);
    exit();
}

$flightStmt = $pdo->prepare('SELECT * FROM flights WHERE id = :id');
$flightStmt->execute(['id' => $flightId]);
$flight = $flightStmt->fetch();

if (!$flight) {
    header('Location: ' . $redirect);
    exit();
}

if ((int) $user['credit'] < (int) $flight['price']) {
    $_SESSION['error'] = 'Saldo kredit tidak mencukupi untuk melakukan pemesanan ini. Silakan lakukan deposit.';
    header('Location: ' . $redirect);
    exit();
}

$pdo->beginTransaction();
try {
    $insertBooking = $pdo->prepare('INSERT INTO bookings (user_id, flight_id, passenger_name, status, created_at) VALUES (:user_id, :flight_id, :passenger_name, :status, :created_at)');
    $now = (new DateTimeImmutable())->format(DateTimeInterface::ATOM);
    $insertBooking->execute([
        'user_id' => $user['id'],
        'flight_id' => $flight['id'],
        'passenger_name' => $user['name'],
        'status' => 'confirmed',
        'created_at' => $now,
    ]);

    $updateCredit = $pdo->prepare('UPDATE users SET credit = credit - :price, updated_at = :updated_at WHERE id = :id');
    $updateCredit->execute([
        'price' => $flight['price'],
        'updated_at' => $now,
        'id' => $user['id'],
    ]);

    $pdo->commit();
    $_SESSION['success'] = 'Pemesanan berhasil. Kredit terpotong ' . formatRupiah((int) $flight['price']) . '.';
} catch (Throwable $exception) {
    $pdo->rollBack();
    $_SESSION['error'] = 'Gagal memproses pemesanan.';
}

header('Location: dashboard.php');
exit();

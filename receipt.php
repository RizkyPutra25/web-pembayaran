<?php
require __DIR__ . '/functions.php';
require_login();
$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM payments WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);
$pay = $stmt->fetch();

if (!$pay) {
  die("Struk tidak ditemukan!");
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Struk Pembayaran</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="card">
    <h2>Struk Pembayaran</h2>
    <p>ID Transaksi: <?= esc($pay['id']) ?></p>
    <p>Paket: <?= esc($pay['package']) ?></p>
    <p>Jumlah: Rp <?= number_format($pay['amount'],0,',','.') ?></p>
    <p>Status: <?= esc($pay['status']) ?></p>
    <p>Tanggal: <?= esc($pay['created_at']) ?></p>
    <a href="dashboard.php">Kembali</a>
  </div>
</body>
</html>

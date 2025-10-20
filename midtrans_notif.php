<?php
require 'vendor/autoload.php';
require 'koneksi.php';

\Midtrans\Config::$serverKey = "SB-Mid-server-hdII43b38NgauPkjMtqEcgLX";
\Midtrans\Config::$isProduction = false;

$json = file_get_contents('php://input');
$notif = json_decode($json);

$order_id = $notif->order_id ?? '';
$transaction_status = $notif->transaction_status ?? 'failed';

// Ubah status sesuai notifikasi dari Midtrans
$status = 'pending';
if ($transaction_status == 'capture' || $transaction_status == 'settlement') {
    $status = 'success';
} elseif ($transaction_status == 'deny' || $transaction_status == 'expire' || $transaction_status == 'cancel') {
    $status = 'failed';
}

$stmt = $conn->prepare("UPDATE transaksi SET status=? WHERE order_id=?");
$stmt->bind_param("ss", $status, $order_id);
$stmt->execute();

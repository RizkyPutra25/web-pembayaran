<?php
require_once 'vendor/autoload.php';

\Midtrans\Config::$serverKey = 'SB-Mid-server-hdII43b38NgauPkjMtqEcgLX';
\Midtrans\Config::$isProduction = false; // pakai sandbox dulu
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;

$order_id = uniqid();
$amount = 150000; // contoh harga Rp150.000

// Simpan ke database
$conn = new mysqli("localhost", "root", "", "db_wifi");
$conn->query("INSERT INTO transaksi (order_id, amount) VALUES ('$order_id', '$amount')");

// Buat transaksi Midtrans
$params = [
    'transaction_details' => [
        'order_id' => $order_id,
        'gross_amount' => $amount,
    ]
];

try {
    $snapToken = \Midtrans\Snap::getSnapToken($params);
    echo json_encode(["token" => $snapToken]);
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>

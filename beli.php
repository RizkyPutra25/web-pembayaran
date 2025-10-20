<?php
session_start();
require 'db.php'; // koneksi ke database
require_once 'vendor/autoload.php'; // composer autoload midtrans

header('Content-Type: application/json'); // pastikan selalu JSON
error_reporting(E_ALL);
ini_set('display_errors', 0); // jangan tampilkan error HTML

// ===== Cek request POST =====
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["error" => "Akses tidak valid"]);
    exit;
}

// ===== Cek login =====
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo json_encode(["error" => "User belum login"]);
    exit;
}

// ===== Ambil paket =====
$paket_id = intval($_POST['id']);
if ($paket_id <= 0) {
    echo json_encode(["error" => "Paket tidak valid"]);
    exit;
}

// Paket internet (harus sama dengan dasbord.php)
$paketInternet = [
    1 => ["nama" => "Paket 7 Mbps", "harga" => 125000],
    2 => ["nama" => "Paket 10 Mbps", "harga" => 150000],
    3 => ["nama" => "Paket 15 Mbps", "harga" => 200000],
];

if (!isset($paketInternet[$paket_id])) {
    echo json_encode(["error" => "Paket tidak ditemukan"]);
    exit;
}

$harga = $paketInternet[$paket_id]['harga'];

// ===== Konfigurasi Midtrans =====
\Midtrans\Config::$serverKey = "SB-Mid-server-hdII43b38NgauPkjMtqEcgLX"; // ganti dengan server key sandbox
\Midtrans\Config::$isProduction = false;
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;

// ===== Generate order ID unik =====
$order_id = "ORDER-" . $user_id . "-" . time();

// ===== Data transaksi untuk Midtrans =====
$params = [
    'transaction_details' => [
        'order_id' => $order_id,
        'gross_amount' => $harga,
    ],
    'item_details' => [[
        'id' => $paket_id,
        'price' => $harga,
        'quantity' => 1,
        'name' => $paketInternet[$paket_id]['nama']
    ]],
    'customer_details' => [
        'first_name' => $_SESSION['username'],
        'email' => $_SESSION['email'] ?? "user@mail.com",
    ]
];

try {
    // ===== Generate snap token =====
    $snapToken = \Midtrans\Snap::getSnapToken($params);

    // ===== Simpan transaksi ke database =====
    $stmt = $conn->prepare("INSERT INTO transaksi (user_id, paket_id, order_id, status, created_at, updated_at) VALUES (?, ?, ?, 'pending', NOW(), NOW())");
    $stmt->bind_param("iis", $user_id, $paket_id, $order_id);
    $stmt->execute();

    // ===== Kembalikan snap token =====
    echo json_encode(["snapToken" => $snapToken, "order_id" => $order_id]);

} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}

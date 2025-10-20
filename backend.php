<?php
session_start();

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User belum login']);
    exit;
}

// 1. Panggil autoload dari composer
require_once __DIR__ . '/vendor/autoload.php'; 

// 2. Konfigurasi Midtrans
\Midtrans\Config::$serverKey = "SB-Mid-server-hdII43b38NgauPkjMtqEcgLX"; // Ganti sesuai server key
\Midtrans\Config::$isProduction = false; // false = Sandbox, true = Production
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;

// 3. Ambil data dari frontend (JavaScript)
$input = json_decode(file_get_contents("php://input"), true);
$amount = $input['amount'] ?? 0; 
$customer_name = $input['customer_name'] ?? $_SESSION['username'];
$customer_email = $input['customer_email'] ?? 'user@example.com';
$customer_phone = $input['customer_phone'] ?? '08123456789';

if ($amount > 0) {
    // 4. Buat order ID unik
    $order_id = 'ORDER-' . time();

    // 5. Simpan transaksi ke database
    include 'db.php';
    $stmt = $conn->prepare("INSERT INTO transaksi (order_id, user_id, amount, status, created_at) VALUES (?, ?, ?, 'pending', NOW())");
    $stmt->bind_param("sii", $order_id, $_SESSION['user_id'], $amount);
    $stmt->execute();

    // 6. Buat data transaksi untuk Midtrans
    $params = [
        'transaction_details' => [
            'order_id' => $order_id,
            'gross_amount' => $amount,
        ],
        'customer_details' => [
            'first_name' => $customer_name,
            'email' => $customer_email,
            'phone' => $customer_phone
        ]
    ];

    try {
        // 7. Dapatkan Snap Token dari Midtrans
        $snapToken = \Midtrans\Snap::getSnapToken($params);

        // 8. Kirim token ke frontend
        echo json_encode(['token' => $snapToken]);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Amount tidak valid']);
}
?>

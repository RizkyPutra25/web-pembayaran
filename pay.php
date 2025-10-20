<?php
session_start();
include 'db.php';

// cek login
if (!isset($_SESSION['user_id'])) {
    die("Harus login dulu!");
}

if (isset($_GET['id'])) {
    $paket_id = (int) $_GET['id'];
    $user_id = $_SESSION['user_id'];

    // Simpan transaksi ke tabel
    $stmt = $pdo->prepare("INSERT INTO transaksi (user_id, paket_id, status) VALUES (?, ?, 'pending')");
    $stmt->execute([$user_id, $paket_id]);

    echo "Transaksi berhasil dibuat! <br>";
    echo "<a href='dashboard.php'>Kembali ke Dashboard</a>";
} else {
    echo "Paket tidak ditemukan.";
}

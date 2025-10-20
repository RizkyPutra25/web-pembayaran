<?php
$servername = "localhost";
$username   = "wifi_user";      // user baru
$password   = "password123";     // password yang sudah dibuat
$dbname     = "web_pembayaran";  // database yang baru dibuat

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>

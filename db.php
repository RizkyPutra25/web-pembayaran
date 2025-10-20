<?php
$servername = "localhost";
$username   = "root"; // default XAMPP
$password   = "";     // default XAMPP kosong
$dbname     = "wifi_payment"; // ganti sesuai nama database kamu

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>

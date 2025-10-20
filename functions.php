<?php
session_start();

// ======= KONFIGURASI DATABASE =======
$dsn = 'mysql:host=localhost;dbname=wifi_payment;charset=utf8mb4';
$username = 'root';  // ganti jika berbeda
$password = '';
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// ======= CEK LOGIN =======
if (!isset($_SESSION['user_id'])) {
    // Untuk testing, bisa set manual
    // $_SESSION['user_id'] = 5;
    die("Silakan login terlebih dahulu!");
}

// ======= AMBIL DATA TRANSAKSI =======
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    die("ID transaksi tidak valid!");
}

// Query ambil transaksi + nama user
$stmt = $pdo->prepare("
    SELECT t.*, u.nama AS user_name
    FROM transaksi t
    JOIN users u ON t.user_id = u.id
    WHERE t.id = ?
");
$stmt->execute([$id]);
$transaksi = $stmt->fetch();

if (!$transaksi) {
    die("Struk tidak ditemukan!");
}

// Tentukan warna status
$statusClass = match(strtolower($transaksi['status'])) {
    'success' => 'status-success',
    'pending' => 'status-pending',
    'failed'  => 'status-failed',
    default => 'status-pending'
};

// Fungsi escape HTML
function esc($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Struk Pembayaran</title>
<style>
body { font-family: 'Segoe UI', sans-serif; background:#f4f7fa; display:flex; justify-content:center; align-items:center; min-height:100vh; margin:0; padding:0; }
.receipt-card { background:#fff; padding:30px; border-radius:14px; box-shadow:0 6px 20px rgba(0,0,0,0.15); width:400px; animation:fadeIn 0.5s ease-in-out; }
.receipt-card h2 { text-align:center; margin-bottom:20px; color:#2c3e50; }
.receipt-item { margin:10px 0; font-size:15px; }
.receipt-item strong { display:inline-block; width:120px; color:#555; }
.status-success { color: #27ae60; font-weight:bold; }
.status-pending { color: #f39c12; font-weight:bold; }
.status-failed { color: #e74c3c; font-weight:bold; }
.btn-container { text-align:center; margin-top:20px; }
.btn { display:inline-block; background:linear-gradient(135deg,#3498db,#2ecc71); color:#fff; padding:12px 18px; border-radius:10px; text-decoration:none; font-weight:bold; margin:5px; transition:all 0.3s ease; }
.btn:hover { background:linear-gradient(135deg,#2ecc71,#3498db); transform:translateY(-3px) scale(1.05); box-shadow:0 4px 12px rgba(0,0,0,0.3); }
@keyframes fadeIn { from { opacity:0; transform:translateY(-20px); } to { opacity:1; transform:translateY(0); } }
</style>
</head>
<body>
<div class="receipt-card" id="receipt-content">
  <h2>Struk Pembayaran</h2>
  <div class="receipt-item"><strong>ID Transaksi:</strong> <?= esc($transaksi['id']) ?></div>
  <div class="receipt-item"><strong>User:</strong> <?= esc($transaksi['user_name']) ?></div>
  <div class="receipt-item"><strong>Paket ID:</strong> <?= esc($transaksi['paket_id']) ?></div>
  <div class="receipt-item"><strong>Status:</strong> <span class="<?= $statusClass ?>"><?= ucfirst($transaksi['status']) ?></span></div>
  <div class="receipt-item"><strong>Tanggal:</strong> <?= esc($transaksi['created_at']) ?></div>

  <div class="btn-container">
    <a href="dashboard.php" class="btn">🔙 Kembali</a>
    <a href="#" onclick="downloadPDF()" class="btn">⬇️ Download PDF</a>
  </div>
</div>

<!-- jsPDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
function downloadPDF() {
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF();
  doc.html(document.getElementById('receipt-content'), {
    callback: function(doc){ doc.save("struk-pembayaran.pdf"); },
    x:10, y:10
  });
}
</script>
</body>
</html>

<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$order_id = $_GET['order_id'] ?? '';
if (empty($order_id)) die("Order ID tidak ditemukan!");

$stmt = $conn->prepare("SELECT t.*, u.username, u.email 
                        FROM transaksi t 
                        JOIN users u ON t.user_id = u.id 
                        WHERE t.order_id = ?");
$stmt->bind_param("s", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$transaksi = $result->fetch_assoc();

if (!$transaksi) die("Transaksi tidak ditemukan!");

$paketInternet = [
    1 => "Paket 7 Mbps",
    2 => "Paket 10 Mbps",
    3 => "Paket 15 Mbps",
];

$nama_paket = $paketInternet[$transaksi['paket_id']] ?? "Unknown";
$paid_status = ['settlement', 'capture'];
$isPaid = in_array(strtolower($transaksi['status']), $paid_status);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Struk Pembayaran</title>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #e0e7ff; /* biru pastel kalem */
    color: #333;
    text-align: center;
    padding: 20px;
}

.struk {
    background: #fff;
    border-radius: 12px;
    padding: 25px;
    max-width: 600px;
    margin: auto;
    box-shadow: 0 6px 15px rgba(0,0,0,0.1);
}

h2 {
    color: #2e7d32; /* hijau lembut untuk sukses */
    margin-bottom: 5px;
}

h4 {
    color: #555;
    margin-top: 0;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

td {
    padding: 10px;
    border-bottom: 1px solid #ddd;
    text-align: left;
}

.btn-container {
    margin-top: 25px;
}

.btn {
    display: inline-block;
    margin: 5px;
    padding: 10px 22px;
    background: #60a5fa; /* biru kalem */
    color: #fff;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 500;
    transition: 0.3s;
}

.btn:hover {
    background: #3b82f6; /* biru hover */
}

.alert {
    color: #d97706; /* oranye kalem */
    font-weight: bold;
    margin: 20px 0;
}

@media print {
    body { background: #fff; }
    .struk { box-shadow: none; }
    .btn-container { display: none; }
}
</style>
</head>
<body>

<?php if($isPaid): ?>
<div class="struk" id="strukContent">
    <h2>✅ Pembayaran Berhasil</h2>
    <h4>Struk Resmi Pembelian Paket Internet</h4>
    <hr>
    <table>
        <tr><td><b>Order ID</b></td><td><?= htmlspecialchars($transaksi['order_id']) ?></td></tr>
        <tr><td><b>Paket</b></td><td><?= htmlspecialchars($nama_paket) ?></td></tr>
        <tr><td><b>Status</b></td><td><?= ucfirst(htmlspecialchars($transaksi['status'])) ?></td></tr>
        <tr><td><b>Tanggal</b></td><td><?= htmlspecialchars($transaksi['created_at']) ?></td></tr>
        <tr><td><b>Nama</b></td><td><?= htmlspecialchars($transaksi['username']) ?></td></tr>
        <tr><td><b>Email</b></td><td><?= htmlspecialchars($transaksi['email'] ?: '-') ?></td></tr>
    </table>
</div>

<div class="btn-container">
    <a href="dasbord.php" class="btn">🔙 Kembali ke Dashboard</a>
    <a href="#" onclick="downloadPDF()" class="btn">⬇️ Download Struk PDF</a>
</div>

<script>
function downloadPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    doc.setFont("helvetica","bold");
    doc.setFontSize(16);
    doc.text("Struk Pembayaran", 20, 20);
    doc.setFont("helvetica","normal");
    doc.setFontSize(12);
    const data = [
        ["Order ID", "<?= $transaksi['order_id'] ?>"],
        ["Paket", "<?= $nama_paket ?>"],
        ["Status", "<?= ucfirst($transaksi['status']) ?>"],
        ["Tanggal", "<?= $transaksi['created_at'] ?>"],
        ["Nama", "<?= $transaksi['username'] ?>"],
        ["Email", "<?= $transaksi['email'] ?: '-' ?>"]
    ];
    let y = 40;
    data.forEach(row => {
        doc.text(`${row[0]}: ${row[1]}`, 20, y);
        y += 10;
    });
    doc.save("Struk_<?= $transaksi['order_id'] ?>.pdf");
}
</script>

<?php else: ?>
<div class="alert">
    ⚠️ Belum bayar! Harap lanjutkan pembayaran terlebih dahulu untuk mencetak struk.
</div>
<div class="btn-container">
    <a href="dasbord.php" class="btn">🔙 Kembali ke Dashboard</a>
</div>
<?php endif; ?>

</body>
</html>

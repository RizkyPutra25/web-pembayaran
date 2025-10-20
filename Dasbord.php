<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];

// Data paket
$paketInternet = [
    1 => ["nama" => "Paket 7 Mbps", "harga" => 125000],
    2 => ["nama" => "Paket 10 Mbps", "harga" => 150000],
    3 => ["nama" => "Paket 15 Mbps", "harga" => 200000],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Dashboard NRF NETT</title>
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="SB-Mid-client-aOghJwWxznF6Z6Xd"></script>
<style>
@import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&display=swap');

body {
    margin: 0;
    padding: 0;
    font-family: 'Orbitron', sans-serif;
    overflow: hidden;
    background: #0a0a0a;
    color: #fff;
    text-align: center;
    min-height: 100vh;
}

canvas#network {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: -1;
}

h2 {
    margin-top: 30px;
    font-size: 2.5rem;
    text-shadow: 0 0 10px #00f0ff, 0 0 20px #ff00ff;
}
h3 { text-shadow: 0 0 5px #00ffff; }

.paket-container {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    margin-top: 30px;
}

.paket-card {
    background: rgba(0,0,0,0.85);
    border: 2px solid #00f0ff;
    box-shadow: 0 0 20px #ff00ff, 0 0 40px #00ffff;
    border-radius: 15px;
    padding: 20px;
    margin: 15px;
    width: 250px;
    transition: 0.3s;
}
.paket-card:hover {
    transform: scale(1.05);
    box-shadow: 0 0 30px #ff00ff, 0 0 60px #00ffff;
}

.paket-card h3 {
    margin-bottom: 10px;
    color: #ff69b4;
    text-shadow: 0 0 5px #000, 0 0 10px #ff69b4;
}
.paket-card p {
    font-size: 1.1rem;
    margin-bottom: 15px;
    color: #00ffff;
    text-shadow: 0 0 5px #000, 0 0 10px #00ffff;
}

.paket-card a {
    display: inline-block;
    padding: 10px 20px;
    border-radius: 8px;
    text-decoration: none;
    background: linear-gradient(45deg, #ff00ff, #00ffff);
    color: #0a0a0a;
    font-weight: bold;
    transition: 0.3s;
}
.paket-card a:hover {
    background: #0a0a0a;
    color: #ff00ff;
    box-shadow: 0 0 15px #ff00ff, 0 0 30px #00ffff;
}

.logout-btn {
    display: inline-block;
    margin-top: 30px;
    padding: 10px 25px;
    border-radius: 8px;
    background: #ff0044;
    color: #fff;
    text-decoration: none;
    font-weight: bold;
    transition: 0.3s;
}
.logout-btn:hover {
    background: #cc0033;
    box-shadow: 0 0 15px #ff0044, 0 0 30px #ff77aa;
}

/* Tombol CS */
.cs-button {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: linear-gradient(45deg, #00ffff, #ff00ff);
    color: #0a0a0a;
    font-weight: bold;
    padding: 14px 24px;
    border-radius: 40px;
    cursor: pointer;
    box-shadow: 0 0 15px #ff00ff, 0 0 25px #00ffff;
    transition: 0.3s;
    z-index: 1000;
    animation: pulse 2s infinite;
}
.cs-button:hover {
    background: #0a0a0a;
    color: #ff00ff;
    box-shadow: 0 0 20px #ff00ff, 0 0 40px #00ffff;
}

/* Animasi pulse */
@keyframes pulse {
    0% {
        transform: scale(1);
        box-shadow: 0 0 10px #ff00ff, 0 0 20px #00ffff;
    }
    50% {
        transform: scale(1.1);
        box-shadow: 0 0 25px #ff00ff, 0 0 50px #00ffff;
    }
    100% {
        transform: scale(1);
        box-shadow: 0 0 10px #ff00ff, 0 0 20px #00ffff;
    }
}
</style>
</head>
<body>
<canvas id="network"></canvas>

<h2>Selamat Datang, <?= htmlspecialchars($username) ?> 👋</h2>
<h3>Pilih Paket Internet</h3>

<div class="paket-container">
<?php foreach ($paketInternet as $id => $paket): ?>
    <div class="paket-card">
        <h3><?= $paket['nama'] ?></h3>
        <p>Harga: Rp <?= number_format($paket['harga'],0,',','.') ?></p>
        <a href="#" onclick="bayar(<?= $id ?>)">Beli Sekarang</a>
    </div>
<?php endforeach; ?>
</div>

<a href="logout.php" class="logout-btn">Logout</a>

<div class="cs-button" onclick="window.open('https://wa.me/6283834257279','_blank')">
    💬 Chat CS
</div>

<script>
// === Fungsi bayar Midtrans ===
let sedangBayar = false; // 🔒 Global flag

function bayar(id) {
    if (sedangBayar) {
        alert("Pembayaran sedang diproses, tunggu sebentar...");
        return;
    }
    sedangBayar = true; // kunci agar tidak double klik

    fetch('beli.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id=' + id
    })
    .then(res => res.json())
    .then(data => {
        if (data.snapToken) {
            snap.pay(data.snapToken, {
                onSuccess: function(result){
                    alert("Pembayaran berhasil!");
                    console.log(result);
                    sedangBayar = false; // buka kunci lagi
                },
                onPending: function(result){
                    alert("Pembayaran tertunda.");
                    console.log(result);
                    sedangBayar = false;
                },
                onError: function(result){
                    alert("Pembayaran gagal.");
                    console.log(result);
                    sedangBayar = false;
                },
                onClose: function(){
                    alert("Popup ditutup.");
                    sedangBayar = false;
                }
            });
        } else {
            alert(data.error || "Terjadi kesalahan.");
            sedangBayar = false;
        }
    })
    .catch(err => {
        console.error(err);
        alert("Gagal memproses pembayaran.");
        sedangBayar = false;
    });
}

// === Animasi Cyberpunk Background ===
const canvas = document.getElementById('network');
const ctx = canvas.getContext('2d');
let width = canvas.width = window.innerWidth;
let height = canvas.height = window.innerHeight;
const nodes = [];
const nodeCount = 70;
const colors = ['#ff00ff','#00ffff','#ff007f','#7f00ff'];

class Node {
    constructor() {
        this.x = Math.random() * width;
        this.y = Math.random() * height;
        this.vx = (Math.random() - 0.5) * 1;
        this.vy = (Math.random() - 0.5) * 1;
        this.radius = Math.random() * 3 + 1;
        this.color = colors[Math.floor(Math.random() * colors.length)];
    }
    move() {
        this.x += this.vx;
        this.y += this.vy;
        if (this.x < 0 || this.x > width) this.vx *= -1;
        if (this.y < 0 || this.y > height) this.vy *= -1;
    }
    draw() { // ✅ tambahkan kurung kurawal di sini
        ctx.beginPath();
        ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
        ctx.fillStyle = this.color;
        ctx.shadowBlur = 10;
        ctx.shadowColor = this.color;
        ctx.fill();
    }
}

function connectNodes() {
    for (let i = 0; i < nodeCount; i++) {
        for (let j = i + 1; j < nodeCount; j++) {
            const dx = nodes[i].x - nodes[j].x;
            const dy = nodes[i].y - nodes[j].y;
            const dist = Math.sqrt(dx * dx + dy * dy);
            if (dist < 150) {
                ctx.beginPath();
                ctx.strokeStyle = 'rgba(255,0,255,' + (1 - dist / 150) + ')';
                ctx.lineWidth = 1;
                ctx.moveTo(nodes[i].x, nodes[i].y);
                ctx.lineTo(nodes[j].x, nodes[j].y);
                ctx.stroke();
            }
        }
    }
}

function animate() {
    ctx.clearRect(0, 0, width, height);
    nodes.forEach(node => { node.move(); node.draw(); });
    connectNodes();
    requestAnimationFrame(animate);
}

for (let i = 0; i < nodeCount; i++) nodes.push(new Node());
window.addEventListener('resize', () => {
    width = canvas.width = window.innerWidth;
    height = canvas.height = window.innerHeight;
});
animate();
</script>
</body>
</html>

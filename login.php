<?php
session_start();
include 'db.php';

$error = '';
$success = '';

if (isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $user, $hashed_password);
        $stmt->fetch();
        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $user;
            header("Location: Dasbord.php");
            exit;
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
    $stmt->close();
}

if (isset($_POST['register'])) {
    $new_username = $_POST['new_username'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($new_password !== $confirm_password) {
        $error = "Konfirmasi password tidak sama!";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $new_username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = "Username sudah terdaftar!";
        } else {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $new_username, $hashed);
            if ($stmt->execute()) {
                $success = "Registrasi berhasil! Silakan login.";
            } else {
                $error = "Terjadi kesalahan saat registrasi!";
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login & Register NRF NETT</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&display=swap');

body {
    margin: 0;
    padding: 0;
    font-family: 'Orbitron', sans-serif;
    overflow: hidden;
    background: #0a0a0a;
    color: #fff;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

canvas#network {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: -1;
}

.container {
    background: rgba(0,0,0,0.85);
    padding: 30px 25px;
    border-radius: 15px;
    box-shadow: 0 0 20px rgba(0,255,255,0.4);
    text-align: center;
    width: 350px;
    animation: fadeIn 1s ease;
}

h2 { margin-bottom: 20px; color: #00f0ff; text-shadow: 0 0 10px #00f0ff; }
.input-field {
    width: 100%;
    padding: 12px;
    margin: 10px 0;
    border: 1px solid #00f0ff;
    border-radius: 8px;
    outline: none;
    background: rgba(0,0,0,0.6);
    color: #fff;
    transition: 0.3s;
}
.input-field:focus {
    border-color: #ff00ff;
    box-shadow: 0 0 8px rgba(255,0,255,0.6);
}
.btn {
    width: 100%;
    padding: 12px;
    background: linear-gradient(45deg, #ff00ff, #00ffff);
    border: none;
    color: #0a0a0a;
    font-size: 16px;
    border-radius: 8px;
    cursor: pointer;
    transition: 0.3s;
}
.btn:hover {
    background: #0a0a0a;
    color: #ff00ff;
    box-shadow: 0 0 15px #ff00ff, 0 0 25px #00ffff;
}
.error { color: #ff4d4d; margin-bottom: 10px; text-shadow: 0 0 5px #ff4d4d; }
.success { color: #4dff4d; margin-bottom: 10px; text-shadow: 0 0 5px #4dff4d; }
.tab { display: flex; justify-content: space-around; margin-bottom: 20px; }
.tab button { padding: 10px 20px; cursor: pointer; border: none; background: #222; border-radius: 8px; color: #fff; transition:0.3s;}
.tab button.active { background: #00f0ff; color: #0a0a0a; }
form { display: none; }
form.active { display: block; }
@keyframes fadeIn { from {opacity:0; transform:scale(0.9);} to {opacity:1; transform:scale(1);} }
</style>
</head>
<body>
<canvas id="network"></canvas>

<div class="container">
    <div class="tab">
        <button id="loginTab" class="active">Login</button>
        <button id="registerTab">Register</button>
    </div>

    <?php if ($error) echo "<div class='error'>$error</div>"; ?>
    <?php if ($success) echo "<div class='success'>$success</div>"; ?>

    <!-- Form Login -->
    <form method="POST" id="loginForm" class="active">
        <input type="text" name="username" class="input-field" placeholder="Username" required>
        <input type="password" name="password" class="input-field" placeholder="Password" required>
        <button type="submit" name="login" class="btn">Masuk</button>
    </form>

    <!-- Form Register -->
    <form method="POST" id="registerForm">
        <input type="text" name="new_username" class="input-field" placeholder="Username" required>
        <input type="password" name="new_password" class="input-field" placeholder="Password" required>
        <input type="password" name="confirm_password" class="input-field" placeholder="Konfirmasi Password" required>
        <button type="submit" name="register" class="btn">Daftar</button>
    </form>
</div>

<script>
const loginTab = document.getElementById('loginTab');
const registerTab = document.getElementById('registerTab');
const loginForm = document.getElementById('loginForm');
const registerForm = document.getElementById('registerForm');

loginTab.addEventListener('click', () => {
    loginTab.classList.add('active');
    registerTab.classList.remove('active');
    loginForm.classList.add('active');
    registerForm.classList.remove('active');
});

registerTab.addEventListener('click', () => {
    registerTab.classList.add('active');
    loginTab.classList.remove('active');
    registerForm.classList.add('active');
    loginForm.classList.remove('active');
});

// Cyberpunk network background
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
        this.color = colors[Math.floor(Math.random()*colors.length)];
    }
    move() {
        this.x += this.vx;
        this.y += this.vy;
        if(this.x<0||this.x>width) this.vx*=-1;
        if(this.y<0||this.y>height) this.vy*=-1;
    }
    draw() {
        ctx.beginPath();
        ctx.arc(this.x,this.y,this.radius,0,Math.PI*2);
        ctx.fillStyle = this.color;
        ctx.shadowBlur = 10;
        ctx.shadowColor = this.color;
        ctx.fill();
    }
}

function connectNodes(){
    for(let i=0;i<nodeCount;i++){
        for(let j=i+1;j<nodeCount;j++){
            const dx = nodes[i].x - nodes[j].x;
            const dy = nodes[i].y - nodes[j].y;
            const dist = Math.sqrt(dx*dx+dy*dy);
            if(dist<150){
                ctx.beginPath();
                ctx.strokeStyle = 'rgba(255,0,255,'+(1-dist/150)+')';
                ctx.lineWidth=1;
                ctx.moveTo(nodes[i].x,nodes[i].y);
                ctx.lineTo(nodes[j].x,nodes[j].y);
                ctx.stroke();
            }
        }
    }
}

function animate(){
    ctx.clearRect(0,0,width,height);
    nodes.forEach(node=>{node.move(); node.draw();});
    connectNodes();
    requestAnimationFrame(animate);
}

for(let i=0;i<nodeCount;i++) nodes.push(new Node());
window.addEventListener('resize',()=>{
    width=canvas.width=window.innerWidth;
    height=canvas.height=window.innerHeight;
});
animate();
</script>
</body>
</html>

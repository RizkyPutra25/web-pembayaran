<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>NRF NETT</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&display=swap');

    body {
      margin: 0;
      font-family: 'Orbitron', sans-serif;
      background: #0a0a0a;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      height: 100vh;
      text-align: center;
      color: #fff;
    }

    h1 {
      font-size: 3rem;
      margin-bottom: 10px;
      text-shadow: 0 0 10px #ff00ff, 0 0 20px #00ffff, 0 0 30px #ff00ff;
    }

    p {
      font-size: 1.2rem;
      margin-bottom: 20px;
      text-shadow: 0 0 5px #ff00ff, 0 0 10px #00ffff;
    }

    a {
      text-decoration: none;
      padding: 12px 30px;
      background: linear-gradient(45deg, #ff00ff, #00ffff, #ff00ff);
      color: #0a0a0a;
      font-weight: bold;
      border-radius: 30px;
      transition: 0.3s;
      box-shadow: 0 0 10px #ff00ff, 0 0 20px #00ffff;
    }

    a:hover {
      background: #0a0a0a;
      color: #ff00ff;
      transform: scale(1.1);
      box-shadow: 0 0 20px #ff00ff, 0 0 40px #00ffff, 0 0 60px #ff00ff;
    }

    footer {
      position: absolute;
      bottom: 15px;
      font-size: 0.9rem;
      opacity: 0.7;
      color: #ff00ff;
    }

    canvas#network {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: -1;
    }
  </style>
</head>
<body>
  <canvas id="network"></canvas>

  <h1>Selamat Datang di NRF NETT</h1>
  <p>Masuk untuk menjelajahi jaringan teknologi kami</p>
  <a href="login.php">Login Sekarang</a>

  <footer>
    © <?php echo date("Y"); ?> NRF NETT
  </footer>

  <script>
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
        if(this.x < 0 || this.x > width) this.vx *= -1;
        if(this.y < 0 || this.y > height) this.vy *= -1;
      }
      draw() {
        ctx.beginPath();
        ctx.arc(this.x, this.y, this.radius, 0, Math.PI*2);
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
          const dist = Math.sqrt(dx*dx + dy*dy);
          if(dist < 150) {
            ctx.beginPath();
            ctx.strokeStyle = 'rgba(255,0,255,' + (1 - dist/150) + ')';
            ctx.lineWidth = 1;
            ctx.moveTo(nodes[i].x, nodes[i].y);
            ctx.lineTo(nodes[j].x, nodes[j].y);
            ctx.stroke();
          }
        }
      }
    }

    function animate() {
      ctx.clearRect(0,0,width,height);
      nodes.forEach(node => {
        node.move();
        node.draw();
      });
      connectNodes();
      requestAnimationFrame(animate);
    }

    for(let i=0;i<nodeCount;i++){
      nodes.push(new Node());
    }

    window.addEventListener('resize', () => {
      width = canvas.width = window.innerWidth;
      height = canvas.height = window.innerHeight;
    });

    animate();
  </script>
</body>
</html>

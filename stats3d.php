<?php
session_start();
include 'db.php';
if(!isset($_SESSION['username'])){ header('Location: login.php'); exit; }

// Query totals by fish
$q = "SELECT f.nom_fish, COALESCE(SUM(ci.total),0) AS total, COALESCE(SUM(ci.quantite),0) AS quantite
      FROM fish f
      LEFT JOIN commande_items ci ON ci.id_fish = f.id
      GROUP BY f.id ORDER BY total DESC";
$res = mysqli_query($conn, $q);
$data = [];
while($r = mysqli_fetch_assoc($res)){
    $data[] = $r;
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Statistiques 3D</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
<style>
body{font-family:'Poppins',sans-serif;background:#071123;color:#fff;padding:2rem}
.container{width:900px;margin:0 auto;background:linear-gradient(180deg,#071827,#04223a);padding:1rem;border-radius:10px;box-shadow:0 20px 50px rgba(0,0,0,0.6)}
.chart-wrap{perspective:1000px;padding:2rem}
.chart-card{transform:rotateX(15deg);background:linear-gradient(180deg,#081726,#0b2a3f);padding:1rem;border-radius:8px}
button{margin-top:1rem;padding:0.6rem 1rem;border-radius:6px;border:none;background:#1e88e5;color:#fff}
</style>
</head>
<body>
<div class="container">
<h2>📊 Statistiques des ventes (3D)</h2>
<div class="chart-wrap">
  <div class="chart-card">
    <canvas id="chart" width="800" height="400"></canvas>
  </div>
</div>
<button id="downloadPNG">Télécharger PNG</button>
<a href="stats_pdf.php" style="margin-left:1rem"><button>Exporter PDF</button></a>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const data = <?= json_encode($data) ?>;
const labels = data.map(d=>d.nom_fish);
const totals = data.map(d=>parseFloat(d.total));

const ctx = document.getElementById('chart').getContext('2d');
const gradient = ctx.createLinearGradient(0,0,0,400);
gradient.addColorStop(0,'#63b3ed');
gradient.addColorStop(1,'#1e3a8a');

const chart = new Chart(ctx, {
  type: 'bar',
  data: { labels, datasets: [{ label: 'Total DT', data: totals, backgroundColor: gradient, borderRadius: 8, barThickness: 40 }] },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: { y: { beginAtZero: true } }
  }
});

// Download PNG
document.getElementById('downloadPNG').addEventListener('click', ()=>{
    const a = document.createElement('a');
    a.href = document.getElementById('chart').toDataURL('image/png');
    a.download = 'stats.png';
    a.click();
});
</script>
</body>
</html>
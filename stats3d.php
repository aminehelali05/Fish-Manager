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
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css?v=20251223">
</head>
<body>
  <div class="app-container">
    <div class="panel">
      <h2>📊 Statistiques des ventes</h2>

      <div class="card">
        <div class="chart-wrap">
          <canvas id="chart" width="800" height="400"></canvas>
        </div>

        <div class="center mt-4">
          <button id="downloadPNG">Télécharger PNG</button>
          <a class="btn ml-3" href="stats_pdf.php">Exporter PDF</a>
        </div>
      </div>
    </div>
  </div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const data = <?= json_encode($data) ?>;
const labels = data.map(d=>d.nom_fish);
const totals = data.map(d=>parseFloat(d.total));

const ctx = document.getElementById('chart').getContext('2d');
const gradient = ctx.createLinearGradient(0,0,0,400);
gradient.addColorStop(0,'#93c5fd');
gradient.addColorStop(1,'#3b82f6');

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
<script src="js_main.js"></script>
</body>
</html>
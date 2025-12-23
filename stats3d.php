<?php
session_start();
include 'db.php';
if(!isset($_SESSION['username'])){ header('Location: login.php'); exit; }

// Filters (GET)
$client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0; // 0 = all
$period = isset($_GET['period']) ? $_GET['period'] : 'month'; // day|month|year
$start = isset($_GET['start']) ? $_GET['start'] : date('Y-m-d', strtotime('-6 months'));
$end = isset($_GET['end']) ? $_GET['end'] : date('Y-m-d');

// Determine SQL date format
switch($period){
  case 'day': $fmt = "DATE(created_at)"; break;
  case 'year': $fmt = "DATE_FORMAT(created_at,'%Y')"; break;
  default: $fmt = "DATE_FORMAT(created_at,'%Y-%m')"; $period='month';
}

// Aggregate totals by period
$where = "created_at BETWEEN '".mysqli_real_escape_string($conn,$start)." 00:00:00' AND '".mysqli_real_escape_string($conn,$end)." 23:59:59'";
if($client_id>0) $where .= " AND id_client=$client_id";

$q = "SELECT $fmt AS period, COALESCE(SUM(total),0) AS total, COALESCE(SUM(montant_paye),0) AS paid
       FROM commandes
       WHERE $where
       GROUP BY period
       ORDER BY period";
$res = mysqli_query($conn,$q);
$labels = [];
$totals = [];
while($r = mysqli_fetch_assoc($res)){
  $labels[] = $r['period'];
  $totals[] = floatval($r['total']);
}

// Per-client summary in range
$summary = [];
$sq = "SELECT c.id, c.nom, c.prenom, COALESCE(SUM(cmd.total),0) AS total, COALESCE(SUM(cmd.montant_paye),0) AS paid
       FROM clients c
       LEFT JOIN commandes cmd ON cmd.id_client=c.id AND cmd.created_at BETWEEN '".mysqli_real_escape_string($conn,$start)." 00:00:00' AND '".mysqli_real_escape_string($conn,$end)." 23:59:59'
       GROUP BY c.id ORDER BY total DESC";
$sr = mysqli_query($conn,$sq);
while($s = mysqli_fetch_assoc($sr)) $summary[] = $s;

// Orders list for selected client or all orders in range
$orders = [];
$oq = "SELECT o.*, c.nom, c.prenom FROM commandes o JOIN clients c ON o.id_client=c.id WHERE o.created_at BETWEEN '".mysqli_real_escape_string($conn,$start)." 00:00:00' AND '".mysqli_real_escape_string($conn,$end)." 23:59:59'";
if($client_id>0) $oq .= " AND o.id_client=$client_id";
$oq .= " ORDER BY o.created_at DESC";
$or = mysqli_query($conn,$oq);
while($o = mysqli_fetch_assoc($or)) $orders[] = $o;
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
        <form id="statsForm" method="get" class="mb-4">
          <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:center">
            <label for="client_id">Client</label>
            <select id="client_id" name="client_id">
              <option value="0">Tous les clients</option>
              <?php $cs = mysqli_query($conn,"SELECT id,nom,prenom FROM clients ORDER BY nom"); while($c=mysqli_fetch_assoc($cs)){ $sel = ($client_id==$c['id'])? 'selected':''; echo "<option value='{$c['id']}' $sel>".htmlspecialchars($c['nom'].' '.$c['prenom'])."</option>"; } ?>
            </select>

            <label for="period">Période</label>
            <select id="period" name="period">
              <option value="day" <?php if($period=='day') echo 'selected'; ?>>Jour</option>
              <option value="month" <?php if($period=='month') echo 'selected'; ?>>Mois</option>
              <option value="year" <?php if($period=='year') echo 'selected'; ?>>Année</option>
            </select>

            <label>Du</label>
            <input type="date" name="start" value="<?= htmlspecialchars($start) ?>">
            <label>Au</label>
            <input type="date" name="end" value="<?= htmlspecialchars($end) ?>">

            <button type="submit" class="btn">Appliquer</button>
            <a class="btn" href="stats3d.php">Réinitialiser</a>
          </div>
        </form>

        <div class="chart-wrap">
          <canvas id="chart" width="900" height="380"></canvas>
        </div>

        <div class="center mt-4">
          <button id="downloadPNG">Télécharger PNG</button>
          <a class="btn ml-3" href="stats_pdf.php">Exporter PDF</a>
        </div>
      </div>
    </div>
    <div class="panel mt-4">
      <h3>Détail par client</h3>
      <div id="summary"></div>
    </div>

    <div class="panel mt-4">
      <h3>Commandes</h3>
      <div id="orders"></div>
    </div>
  </div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const labels = <?= json_encode($labels) ?>;
const totals = <?= json_encode($totals) ?>;

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
// Populate orders table
const orders = <?= json_encode($orders) ?>;
const summary = <?= json_encode($summary) ?>;

function renderSummary(){
  const container = document.getElementById('summary');
  if(!container) return;
  let html = '<table><tr><th>Client</th><th>Total</th><th>Payé</th><th>Reste</th></tr>';
  summary.forEach(s=>{
    html += `<tr><td>${s.nom} ${s.prenom}</td><td>${parseFloat(s.total).toFixed(2)}</td><td>${parseFloat(s.paid).toFixed(2)}</td><td>${(parseFloat(s.total)-parseFloat(s.paid)).toFixed(2)}</td></tr>`;
  });
  html += '</table>';
  container.innerHTML = html;
}

function renderOrders(){
  const container = document.getElementById('orders');
  if(!container) return;
  let html = '<table><tr><th>ID</th><th>Client</th><th>Date</th><th>Total</th><th>Payé</th><th>Reste</th></tr>';
  orders.forEach(o=>{
    const reste = (parseFloat(o.total)||0) - (parseFloat(o.montant_paye)||0);
    html += `<tr><td>${o.id}</td><td>${o.nom} ${o.prenom}</td><td>${o.created_at}</td><td>${parseFloat(o.total).toFixed(2)}</td><td>${parseFloat(o.montant_paye).toFixed(2)}</td><td>${reste.toFixed(2)}</td></tr>`;
  });
  html += '</table>';
  container.innerHTML = html;
}

renderSummary();
renderOrders();
</script>
<script src="js_main.js"></script>
</body>
</html>
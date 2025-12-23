<?php
session_start();
include "db.php";

/* Page access: require login; show friendly message if not admin */
if (!isset($_SESSION['username'])){
  header("Location: login.php");
  exit;
}
$not_admin = (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Comptes Clients</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="style.css?v=20251223">
</head>
<body>

<header class="glow">
📊 Comptes des clients
</header>

<a class="btn" href="index.php">⬅ Retour</a>

<!-- ================= TABLE COMPTES ================= -->
<?php if($not_admin): ?>
  <section class="panel">
    <h2>Accès refusé</h2>
    <p>Vous devez être administrateur pour voir la page des comptes.</p>
    <p><a class="btn" href="index.php">Retour</a></p>
  </section>
<?php else: ?>
  <section class="table-section">
  <table>
  <tr>
    <th>Client</th>
    <th>Total Vente (DT)</th>
    <th>Total Payé (DT)</th>
    <th>Reste (DT)</th>
  </tr>

  <?php
  $sql = "
  SELECT 
      c.id,
      c.nom,
      c.prenom,
      COALESCE(SUM(cmd.total),0) AS total_vente,
      COALESCE(SUM(cmd.montant_paye),0) AS total_paye,
      COALESCE(SUM(cmd.total - cmd.montant_paye),0) AS reste
  FROM clients c
  LEFT JOIN commandes cmd ON cmd.id_client = c.id
  GROUP BY c.id
  ORDER BY c.nom
  ";

  $res = mysqli_query($conn, $sql);

  if (!$res) {
      echo "<tr><td colspan='4'>Erreur SQL</td></tr>";
  } else {
      while ($row = mysqli_fetch_assoc($res)) {
          $cid = (int)$row['id'];
          echo "
          <tr>
            <td>".htmlspecialchars($row['nom'])." ".htmlspecialchars($row['prenom'])."</td>
            <td>".number_format((float)$row['total_vente'],2)."</td>
            <td>".number_format((float)$row['total_paye'],2)."</td>
            <td>".number_format((float)$row['reste'],2)."</td>
          </tr>";
      }
  }
  ?>
  </table>
  </section>
<?php endif; ?>

<!-- ================= STATS ================= -->
<section class="table-section">
<h2>📈 Statistiques</h2>

<table>
<tr>
  <th>Période</th>
  <th>Total Vente (DT)</th>
  <th>Total Payé (DT)</th>
  <th>Reste (DT)</th>
</tr>

<?php
$stats = [
  "Jour" => "DATE(date_vente) = CURDATE()",
  "Semaine" => "YEARWEEK(date_vente,1) = YEARWEEK(CURDATE(),1)",
  "Mois" => "MONTH(date_vente) = MONTH(CURDATE()) AND YEAR(date_vente)=YEAR(CURDATE())"
];

foreach ($stats as $label => $cond) {
    $q = "
    SELECT 
      COALESCE(SUM(prix_total),0) v,
      COALESCE(SUM(montant_paye),0) p,
      COALESCE(SUM(prix_total - montant_paye),0) r
    FROM orders
    WHERE $cond
    ";
    $r = mysqli_query($conn, $q);
    $d = mysqli_fetch_assoc($r);

    echo "
    <tr>
      <td>$label</td>
      <td>{$d['v']}</td>
      <td>{$d['p']}</td>
      <td>{$d['r']}</td>
    </tr>";
}
?>
</table>
</section>

</body>
  <!-- Inline minimal fallback to trigger page-load animation quickly -->
  <script>setTimeout(function(){ try{ document.body.classList.add('is-loaded'); }catch(e){} },40);</script>
  <script src="js_main.js"></script>
</html>

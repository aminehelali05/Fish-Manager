<?php
session_start();
include "db.php";

/* حماية الصفحة */
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit;
}
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
    c.nom,
    c.prenom,
    COALESCE(SUM(o.prix_total),0) AS total_vente,
    COALESCE(SUM(o.montant_paye),0) AS total_paye,
    COALESCE(SUM(o.prix_total - o.montant_paye),0) AS reste
FROM clients c
LEFT JOIN orders o ON o.id_client = c.id
GROUP BY c.id
ORDER BY c.nom
";

$res = mysqli_query($conn, $sql);

if (!$res) {
    echo "<tr><td colspan='4'>Erreur SQL</td></tr>";
} else {
    while ($row = mysqli_fetch_assoc($res)) {
        echo "
        <tr>
          <td>{$row['nom']} {$row['prenom']}</td>
          <td>{$row['total_vente']}</td>
          <td>{$row['total_paye']}</td>
          <td>{$row['reste']}</td>
        </tr>";
    }
}
?>
</table>
</section>

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
</html>

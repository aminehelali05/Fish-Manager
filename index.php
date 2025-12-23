<?php
session_start();
include "db.php";

if(!isset($_SESSION['username'])){
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Fish Manager</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css?v=20251223">
</head>
<body>

<div class="topbar">
  <div class="topbar-inner">
    <div class="brand">
      <div class="brand-title">🐟 Fish Manager</div>
    </div>
    <nav class="nav">
      <a href="stats3d.php">📊 Statistiques 3D</a>
      <a href="comptes.php">💳 Comptes</a>
      <a href="logout.php" class="is-danger">🚪 Déconnexion</a>
    </nav>
  </div>
</div>

<div class="app-container">

<!-- DASHBOARD -->
<section class="cards">
  <div class="card" id="card-clients">
    <span class="metric-label">👥 Clients</span>
    <span class="metric-value" id="clientsCount"><?php $r=mysqli_query($conn,"SELECT COUNT(*) nb FROM clients"); echo mysqli_fetch_assoc($r)['nb']; ?></span>
  </div>
  <div class="card" id="card-fish">
    <span class="metric-label">🐠 Types de poissons</span>
    <span class="metric-value" id="fishCount"><?php $r=mysqli_query($conn,"SELECT COUNT(*) nb FROM fish"); echo mysqli_fetch_assoc($r)['nb']; ?></span>
  </div>
  <div class="card" id="card-sales">
    <span class="metric-label">🧾 Ventes</span>
    <span class="metric-value" id="salesCount"><?php $r=mysqli_query($conn,"SELECT COUNT(*) nb FROM commandes"); echo mysqli_fetch_assoc($r)['nb']; ?></span>
  </div>
</section>

<!-- AJOUT CLIENT -->
<section class="panel">
<h2>➕ Ajouter un client</h2>
<form id="clientForm" action="add_client.php" method="POST">
  <input name="nom" placeholder="Nom" required>
  <input name="prenom" placeholder="Prénom" required>
  <input name="telephone" placeholder="Téléphone" required>
  <button type="submit">Ajouter</button>
</form>
</section>

<!-- LISTE CLIENTS -->
<section class="panel">
<h2>👥 Clients</h2>
<table>
<tr><th>Nom</th><th>Téléphone</th><th>Total Achat</th><th>Total Payé</th><th>Actions</th></tr>
<?php
$cl = mysqli_query($conn,"SELECT * FROM clients ORDER BY nom");
while($client = mysqli_fetch_assoc($cl)){
    $id = (int)$client['id'];
    echo "<tr>";
    echo "<td>".htmlspecialchars($client['nom'])." ".htmlspecialchars($client['prenom'])."</td>";
    echo "<td>".htmlspecialchars($client['telephone'])."</td>";
    echo "<td>".htmlspecialchars($client['total_achat'])."</td>";
    echo "<td>".htmlspecialchars($client['total_paye'])."</td>";
    echo "<td>
            <a href='edit_client.php?id={$id}'>✏️</a>
            <a href='delete_client.php?id={$id}'>🗑️</a>
          </td>";
    echo "</tr>";
}
?>
</table>
</section>

<!-- AJOUT / MISE À JOUR POISSON -->
<section class="panel">
<h2>➕ Achat / Mise à jour poisson</h2>
<form id="fishForm" action="add_fish.php" method="POST">
  <input type="text" name="nom_fish" placeholder="Nom du poisson" required>
  <input type="number" step="0.01" name="quantite_kg" placeholder="Quantité achetée (kg)" required>
  <input type="number" step="0.01" name="prix_achat" placeholder="Prix d'achat / kg" required>
  <input type="number" step="0.01" name="prix_vente" placeholder="Prix de vente / kg" required>
  <button type="submit">Valider l'achat</button>
</form>
<p>ℹ️ Si le poisson existe déjà, la quantité sera ajoutée et le capital recalculé.</p>
</section>

<!-- NOUVELLE COMMANDE MULTI-POISSONS -->
<section class="panel">
<h2>🧾 Nouvelle commande</h2>
<form id="orderForm" action="add_order.php" method="POST">
<select name="id_client" required>
<option value="">-- Choisir client --</option>
<?php
$clients = mysqli_query($conn,"SELECT * FROM clients ORDER BY nom");
while($c=mysqli_fetch_assoc($clients)){
    echo "<option value='{$c['id']}'>{$c['nom']} {$c['prenom']} ({$c['telephone']})</option>";
}
?>
</select>

<h3>Choisir les poissons et quantités :</h3>
<?php
$fishes = mysqli_query($conn,"SELECT * FROM fish ORDER BY nom_fish");
if ($fishes) {
  while($f = mysqli_fetch_assoc($fishes)){
    echo '<div class="checkbox-qty fade-in">';
    echo "<label><input type='checkbox' name='id_fish[]' value='".htmlspecialchars($f['id'])."'> ".htmlspecialchars($f['nom_fish'])." (".htmlspecialchars($f['quantite_kg'])." kg dispo)</label>";
    echo "<input type='number' name='quantite[]' min='0' step='0.01' placeholder='Qté'>";
    echo '</div>';
  }
} else {
  echo '<p>Aucun poisson disponible.</p>';
}
?>

<select name="type_vente" required>
<option value="Market">Market</option>
<option value="Restaurant">Restaurant</option>
</select>

<select name="type_paiement" required>
<option value="Complet">Paiement complet</option>
<option value="Acompte">Acompte</option>
</select>

<input type="number" step="0.01" name="montant_acompte" placeholder="Montant acompte (si applicable)">

<button type="submit">Valider la commande</button>
</form>
</section>

<!-- HISTORIQUE VENTES -->
<section class="panel">
<h2>📋 Historique des ventes</h2>
<table>
<tr>
<th>Client</th>
<th>Poisson</th>
<th>Qté</th>
<th>Total</th>
<th>Payé</th>
<th>Reste</th>
<th>Date</th>
<th>Actions</th>
</tr>
<?php
$sql="SELECT o.*, c.nom, c.prenom, f.nom_fish 
      FROM commandes o
      JOIN clients c ON o.id_client=c.id
      JOIN commande_items ci ON ci.id_commande=o.id
      JOIN fish f ON f.id=ci.id_fish
      ORDER BY o.created_at DESC";
$res=mysqli_query($conn,$sql);
while($row=mysqli_fetch_assoc($res)){
    $reste = $row['total'] - $row['montant_paye'];
    echo "<tr>
    <td>{$row['nom']} {$row['prenom']}</td>
    <td>{$row['nom_fish']}</td>
    <td>{$row['quantite']}</td>
    <td>{$row['total']}</td>
    <td>{$row['montant_paye']}</td>
    <td>$reste</td>
    <td>{$row['created_at']}</td>
    <td><a href='edit_order.php?id={$row['id']}'>✏️</a> <a href='delete_order.php?id={$row['id']}'>🗑️</a></td>
    </tr>";
}
?>
</table>
</section>

<div class="center mt-4">
  <a class="btn btn-danger" href="logout.php">🚪 Déconnexion</a>
</div>

  </div> <!-- .app-container -->

  <!-- Inline minimal fallback to trigger page-load animation quickly -->
  <script>setTimeout(function(){ try{ document.body.classList.add('is-loaded'); }catch(e){} },40);</script>
  <script src="js_main.js"></script>
  </body>
  </html>

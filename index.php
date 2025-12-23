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
<link rel="stylesheet" href="style.css">
<style>
/* ======== STYLE GLOBAL ======== */
body { font-family: Arial, sans-serif; background-color: #121212; color: #eee; margin: 0; padding: 0; }
header { text-align: center; font-size: 2rem; padding: 1rem; background: #1e88e5; color: #fff; text-shadow: 0 0 10px #1e88e5; }
.cards { display: flex; justify-content: space-around; margin: 1rem; }
.card { background: #1e1e1e; padding: 1rem; border-radius: 10px; width: 25%; text-align: center; box-shadow: 0 0 10px #1e88e5; }
.form-section { background: #1e1e1e; margin: 1rem; padding: 1rem; border-radius: 10px; box-shadow: 0 0 10px #1e88e5; }
.form-section h2 { margin-top: 0; }
.form-section input, .form-section select, .form-section button { display: block; width: 90%; margin: 0.5rem 0; padding: 0.5rem; border-radius: 5px; border: none; }
button { background: #1e88e5; color: #fff; cursor: pointer; transition: 0.3s; }
button:hover { background: #1565c0; }
.table-section { margin: 1rem; }
table { width: 100%; border-collapse: collapse; }
th, td { padding: 0.5rem; border: 1px solid #1e88e5; text-align: center; }
th { background: #1e88e5; color: #fff; }
.checkbox-qty { display: flex; align-items: center; justify-content: space-between; background: #2c2c2c; padding: 0.5rem; margin: 0.3rem 0; border-radius: 5px; }
.checkbox-qty input[type="number"] { width: 80px; }
</style>
</head>
<body>

<header>🐟 Fish Manager – Tableau de bord</header>

<!-- DASHBOARD -->
<section class="cards">
  <div class="card">👥 Clients<br><?php $r=mysqli_query($conn,"SELECT COUNT(*) nb FROM clients"); echo mysqli_fetch_assoc($r)['nb']; ?></div>
  <div class="card">🐠 Types de poissons<br><?php $r=mysqli_query($conn,"SELECT COUNT(*) nb FROM fish"); echo mysqli_fetch_assoc($r)['nb']; ?></div>
  <div class="card">🧾 Ventes<br><?php $r=mysqli_query($conn,"SELECT COUNT(*) nb FROM commandes"); echo mysqli_fetch_assoc($r)['nb']; ?></div>
</section>

<!-- AJOUT CLIENT -->
<section class="form-section">
<h2>➕ Ajouter un client</h2>
<form action="add_client.php" method="POST">
  <input name="nom" placeholder="Nom" required>
  <input name="prenom" placeholder="Prénom" required>
  <input name="telephone" placeholder="Téléphone" required>
  <button>Ajouter</button>
</form>
</section>

<!-- LISTE CLIENTS -->
<section class="table-section">
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
<section class="form-section">
<h2>➕ Achat / Mise à jour poisson</h2>
<form action="add_fish.php" method="POST">
  <input type="text" name="nom_fish" placeholder="Nom du poisson" required>
  <input type="number" step="0.01" name="quantite_kg" placeholder="Quantité achetée (kg)" required>
  <input type="number" step="0.01" name="prix_achat" placeholder="Prix d'achat / kg" required>
  <input type="number" step="0.01" name="prix_vente" placeholder="Prix de vente / kg" required>
  <button>Valider l'achat</button>
</form>
<p>ℹ️ Si le poisson existe déjà, la quantité sera ajoutée et le capital recalculé.</p>
</section>

<!-- NOUVELLE COMMANDE MULTI-POISSONS -->
<section class="form-section">
<h2>🧾 Nouvelle commande</h2>
<form action="add_order.php" method="POST">
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
    echo '<div class="checkbox-qty">';
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

<button>Valider la commande</button>
</form>
</section>

<!-- HISTORIQUE VENTES -->
<section class="table-section">
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

<a class="btn logout" href="logout.php">🚪 Déconnexion</a>

</body>
</html>

<?php
session_start();
include "db.php";

if(!isset($_SESSION['username'])){ header('Location: login.php'); exit; }

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $id = intval($_POST['id']);
    $nom = mysqli_real_escape_string($conn, $_POST['nom_fish']);
    $quantite = floatval($_POST['quantite_kg']);
    $pa = floatval($_POST['prix_achat']);
    $pv = floatval($_POST['prix_vente']);

    mysqli_query($conn, "UPDATE fish SET nom_fish='$nom', quantite_kg=$quantite, prix_achat=$pa, prix_vente=$pv WHERE id=$id");
    header('Location: index.php');
    exit;
}

if(!isset($_GET['id'])){ header('Location: index.php'); exit; }
$id = intval($_GET['id']);
$r = mysqli_query($conn, "SELECT * FROM fish WHERE id=$id");
if(!$r || mysqli_num_rows($r) == 0){ header('Location:index.php'); exit; }
$f = mysqli_fetch_assoc($r);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Modifier poisson</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css?v=20251223">
</head>
<body>
  <div class="app-container">
    <div class="panel">
      <h2>Modifier poisson</h2>
      <form method="post">
        <input type="hidden" name="id" value="<?= htmlspecialchars($f['id']) ?>">
        <label>Nom</label>
        <input name="nom_fish" value="<?= htmlspecialchars($f['nom_fish']) ?>" required>
        <label>Quantité (kg)</label>
        <input type="number" step="0.01" name="quantite_kg" value="<?= htmlspecialchars($f['quantite_kg']) ?>" required>
        <label>Prix achat / kg</label>
        <input type="number" step="0.01" name="prix_achat" value="<?= htmlspecialchars($f['prix_achat']) ?>" required>
        <label>Prix vente / kg</label>
        <input type="number" step="0.01" name="prix_vente" value="<?= htmlspecialchars($f['prix_vente']) ?>" required>
        <button type="submit">Enregistrer</button>
      </form>
      <p><a href="index.php">⬅ Retour</a></p>
    </div>
  </div>
  <script>setTimeout(function(){ try{ document.body.classList.add('is-loaded'); }catch(e){} },40);</script>
  <script src="js_main.js"></script>
</body>
</html>

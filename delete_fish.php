<?php
session_start();
include "db.php";

if(!isset($_SESSION['username'])){
    header("Location: login.php");
    exit;
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $id = intval($_POST['id']);
    // Check if fish is referenced in any order items
    $r = mysqli_query($conn, "SELECT COUNT(*) n FROM commande_items WHERE id_fish=$id");
    $c = mysqli_fetch_assoc($r);
    if($c && intval($c['n']) > 0){
      $_SESSION['flash_error'] = 'Impossible de supprimer: ce poisson est lié à des commandes.';
      header('Location: index.php');
      exit;
    }

    mysqli_query($conn, "DELETE FROM fish WHERE id=$id");
    $_SESSION['flash_success'] = 'Poisson supprimé.';
    header("Location: index.php");
    exit;
}

if(!isset($_GET['id'])){ header("Location: index.php"); exit; }
$id = intval($_GET['id']);
$r = mysqli_query($conn, "SELECT * FROM fish WHERE id=$id");
if(!$r || mysqli_num_rows($r) == 0){ header("Location: index.php"); exit; }
$f = mysqli_fetch_assoc($r);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Supprimer poisson</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css?v=20251223">
</head>
<body>
  <div class="app-container">
    <div class="panel">
      <h2>Supprimer poisson</h2>
      <p>Supprimer le poisson <strong><?= htmlspecialchars($f['nom_fish']) ?></strong> ?</p>
      <form method="post">
        <input type="hidden" name="id" value="<?= htmlspecialchars($f['id']) ?>">
        <button type="submit">Oui, supprimer</button>
      </form>
      <p><a href="index.php">Annuler</a></p>
    </div>
  </div>
  <script>setTimeout(function(){ try{ document.body.classList.add('is-loaded'); }catch(e){} },40);</script>
  <script src="js_main.js"></script>
</body>
</html>

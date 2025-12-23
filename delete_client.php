<?php
session_start();
include "db.php";

if(!isset($_SESSION['username'])){
    header("Location: login.php");
    exit;
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $id = intval($_POST['id']);
    mysqli_query($conn, "DELETE FROM clients WHERE id=$id");
    $_SESSION['flash_success'] = 'Client supprimé.';
    header("Location: index.php");
    exit;
}

if(!isset($_GET['id'])){
    header("Location: index.php");
    exit;
}
$id = intval($_GET['id']);
$r = mysqli_query($conn, "SELECT * FROM clients WHERE id=$id");
if(!$r || mysqli_num_rows($r) == 0){
    header("Location: index.php");
    exit;
}
$c = mysqli_fetch_assoc($r);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>Supprimer client</title>
</head>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css?v=20251223">
</head>
</head>
<body>
<h2>Supprimer client</h2>
<p>Voulez-vous vraiment supprimer <?=htmlspecialchars($c['nom'])." ".htmlspecialchars($c['prenom'])?> ?</p>
<form method="post">
    <input type="hidden" name="id" value="<?=htmlspecialchars($c['id'])?>">
    <button>Oui, supprimer</button>
</form>
<a href="index.php">Annuler</a>
    <!-- Inline minimal fallback to trigger page-load animation quickly -->
    <script>setTimeout(function(){ try{ document.body.classList.add('is-loaded'); }catch(e){} },40);</script>
    <script src="js_main.js"></script>
</body>
</html>
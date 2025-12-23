<?php
session_start();
include "db.php";

if(!isset($_SESSION['username'])){
    header("Location: login.php");
    exit;
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $id = intval($_POST['id']);
    $nom = mysqli_real_escape_string($conn, $_POST['nom']);
    $prenom = mysqli_real_escape_string($conn, $_POST['prenom']);
    $tel = mysqli_real_escape_string($conn, $_POST['telephone']);

    mysqli_query($conn, "UPDATE clients SET nom='$nom', prenom='$prenom', telephone='$tel' WHERE id=$id");
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
<title>Modifier client</title>
<link rel="stylesheet" href="style.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css?v=20251223">
</head>
<body>
<h2>Modifier client</h2>
<form method="post">
    <input type="hidden" name="id" value="<?=htmlspecialchars($c['id'])?>">
    <input name="nom" value="<?=htmlspecialchars($c['nom'])?>" required>
    <input name="prenom" value="<?=htmlspecialchars($c['prenom'])?>" required>
    <input name="telephone" value="<?=htmlspecialchars($c['telephone'])?>" required>
    <button>Enregistrer</button>
</form>
<a href="index.php">⬅ Retour</a>
</body>
</html>
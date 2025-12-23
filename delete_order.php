<?php
session_start();
include "db.php";

if(!isset($_SESSION['username'])){
    header("Location: login.php");
    exit;
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $id = intval($_POST['id']);

    // get commande totals
    $r = mysqli_query($conn, "SELECT id_client, total, montant_paye FROM commandes WHERE id=$id");
    if($r && mysqli_num_rows($r) == 1){
        $c = mysqli_fetch_assoc($r);
        $client = $c['id_client'];
        $total = floatval($c['total']);
        $paye = floatval($c['montant_paye']);

        // delete commande_items (cascade should handle but keep safe)
        mysqli_query($conn, "DELETE FROM commande_items WHERE id_commande=$id");
        mysqli_query($conn, "DELETE FROM commandes WHERE id=$id");

        // update client totals
        mysqli_query($conn, "UPDATE clients SET total_achat = total_achat - $total, total_paye = total_paye - $paye WHERE id=$client");
    }

    header("Location: index.php");
    exit;
}

if(!isset($_GET['id'])){ header("Location: index.php"); exit; }
$id = intval($_GET['id']);
$r = mysqli_query($conn, "SELECT o.id, c.nom, c.prenom FROM commandes o JOIN clients c ON o.id_client=c.id WHERE o.id=$id");
if(!$r || mysqli_num_rows($r)==0){ header("Location:index.php"); exit; }
$o = mysqli_fetch_assoc($r);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Supprimer commande</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=20251223">
</head>
<body>
<h2>Supprimer commande</h2>
<p>Supprimer la commande de <?=htmlspecialchars($o['nom'])." ".htmlspecialchars($o['prenom'])?> ?</p>
<form method="post"><input type="hidden" name="id" value="<?=htmlspecialchars($id)?>"><button>Oui, supprimer</button></form>
<a href="index.php">Annuler</a>
    <!-- Inline minimal fallback to trigger page-load animation quickly -->
    <script>setTimeout(function(){ try{ document.body.classList.add('is-loaded'); }catch(e){} },40);</script>
    <script src="js_main.js"></script>
</body>
</html>
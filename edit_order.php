<?php
session_start();
include "db.php";

if(!isset($_SESSION['username'])){
    header("Location: login.php");
    exit;
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $id = intval($_POST['id']);
    $montant_paye = floatval($_POST['montant_paye']);
    $type_paiement = mysqli_real_escape_string($conn, $_POST['type_paiement']);

    // get old values
    $r = mysqli_query($conn, "SELECT id_client, montant_paye, total FROM commandes WHERE id=$id");
    if($r && mysqli_num_rows($r)==1){
        $c = mysqli_fetch_assoc($r);
        $client = $c['id_client'];
        $old_paye = floatval($c['montant_paye']);
        $total = floatval($c['total']);

        $reste = $total - $montant_paye;

        mysqli_query($conn, "UPDATE commandes SET montant_paye=$montant_paye, reste=$reste, type_paiement='$type_paiement' WHERE id=$id");

        // update client totals
        $delta = $montant_paye - $old_paye;
        if($delta != 0){
            mysqli_query($conn, "UPDATE clients SET total_paye = total_paye + $delta WHERE id=$client");
        }
    }

    header("Location: index.php");
    exit;
}

if(!isset($_GET['id'])){ header("Location: index.php"); exit; }
$id = intval($_GET['id']);
$r = mysqli_query($conn, "SELECT * FROM commandes WHERE id=$id");
if(!$r || mysqli_num_rows($r)==0){ header("Location:index.php"); exit; }
$o = mysqli_fetch_assoc($r);
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="utf-8"><title>Modifier commande</title></head>
<body>
<h2>Modifier commande #<?=htmlspecialchars($o['id'])?></h2>
<form method="post">
    <input type="hidden" name="id" value="<?=htmlspecialchars($o['id'])?>">
    <label>Montant payé: <input type="number" step="0.01" name="montant_paye" value="<?=htmlspecialchars($o['montant_paye'])?>"></label>
    <label>Type paiement:
        <select name="type_paiement">
            <option value="Complet" <?= $o['type_paiement']=='Complet'?'selected':''?>>Complet</option>
            <option value="Acompte" <?= $o['type_paiement']=='Acompte'?'selected':''?>>Acompte</option>
        </select>
    </label>
    <button>Enregistrer</button>
</form>
<a href="index.php">⬅ Retour</a>
</body>
</html>
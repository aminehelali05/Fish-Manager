<?php
session_start();
include "db.php";

if(
    empty($_POST['nom_fish']) ||
    empty($_POST['quantite_kg']) ||
    empty($_POST['prix_achat']) ||
    empty($_POST['prix_vente'])
){
    die("Données manquantes");
}

$nom = mysqli_real_escape_string($conn, trim($_POST['nom_fish']));
$q = floatval($_POST['quantite_kg']);
$pa = floatval($_POST['prix_achat']);
$pv = floatval($_POST['prix_vente']);

if($q <= 0 || $pa <= 0 || $pv <= 0){
    die("Valeurs invalides");
}

// capital de cet achat
$capital_achat = $q * $pa;

// vérifier si le poisson existe déjà
$check = mysqli_query($conn,"SELECT * FROM fish WHERE nom_fish='$nom'");

if(mysqli_num_rows($check) == 1){

    // 🐟 POISSON EXISTANT
    $f = mysqli_fetch_assoc($check);

    $new_q = $f['quantite_kg'] + $q;
    $new_capital = $f['capital'] + $capital_achat;

    mysqli_query($conn,"
        UPDATE fish SET
        quantite_kg = $new_q,
        prix_achat = $pa,
        prix_vente = $pv,
        capital = $new_capital
        WHERE id = {$f['id']}
    ");

} else {

    // 🐠 NOUVEAU POISSON
    mysqli_query($conn,"
        INSERT INTO fish
        (nom_fish, quantite_kg, prix_achat, prix_vente, capital)
        VALUES
        ('$nom', $q, $pa, $pv, $capital_achat)
    ");
}

// flash success and redirect
$_SESSION['flash_success'] = "Poisson enregistré / mis à jour avec succès.";
header("Location:index.php");
exit;


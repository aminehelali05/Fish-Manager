<?php
session_start();
include "db.php";

// Récupérer les données
$nom = mysqli_real_escape_string($conn, $_POST['nom']);
$prenom = mysqli_real_escape_string($conn, $_POST['prenom']);
$tel = mysqli_real_escape_string($conn, $_POST['telephone']);

// Vérification si le client existe déjà
$check = mysqli_query($conn, "SELECT * FROM clients WHERE nom='$nom' AND prenom='$prenom' AND telephone='$tel'");
if(mysqli_num_rows($check) > 0){
    $_SESSION['flash_error'] = 'Client existe déjà !';
    header('Location: index.php');
    exit;
}

// Ajouter le client
mysqli_query($conn, "INSERT INTO clients(nom, prenom, telephone) VALUES('$nom', '$prenom', '$tel')");
$_SESSION['flash_success'] = 'Client ajouté avec succès.';
header("Location: index.php");
?>

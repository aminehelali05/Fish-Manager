<?php
include "db.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $id_fish = $_POST['id_fish'];
    $quantite = $_POST['quantite'];

    if($quantite > 0){
        // تحديث المخزون
        mysqli_query($conn, "UPDATE fish SET quantite_kg = quantite_kg + $quantite WHERE id=$id_fish");
        echo "<script>alert('Stock mis à jour avec succès'); window.location='index.php';</script>";
    } else {
        echo "<script>alert('Quantité invalide'); window.location='index.php';</script>";
    }
}
?>

<?php
session_start();
include "db.php";

if(!isset($_SESSION['username'])){
    header("Location: login.php");
    exit;
}

/* ========= 1. Récupérer les données du formulaire ========= */
$id_client       = $_POST['id_client'];
$fish_ids        = $_POST['id_fish'];      // array de poissons sélectionnés
$quantites_input = $_POST['quantite'];     // array de quantités en texte (ex: "2,1.5")
$type_vente      = $_POST['type_vente'];
$type_paiement   = $_POST['type_paiement'];
$montant_acompte = $_POST['montant_acompte'] ?? 0;

// transformer les quantités en array float
$quantites = array_map('floatval', explode(',', $quantites_input[0]));

/* ========= 2. Créer la commande principale ========= */
mysqli_query($conn, "INSERT INTO commandes(id_client,type_vente,type_paiement,created_at)
                     VALUES($id_client,'$type_vente','$type_paiement',NOW())");
$id_commande = mysqli_insert_id($conn);

$total_commande = 0;

/* ========= 3. Insérer chaque poisson dans commande_items ========= */
for($i=0; $i<count($fish_ids); $i++){
    $id_fish = $fish_ids[$i];
    $qte     = $quantites[$i];

    if($qte <= 0) continue;

    $fish = mysqli_fetch_assoc(mysqli_query($conn,"SELECT prix_vente, quantite_kg FROM fish WHERE id=$id_fish"));

    $prix = $fish['prix_vente'];
    $total_ligne = $qte * $prix;
    $total_commande += $total_ligne;

    // Insérer dans commande_items
    mysqli_query($conn, "INSERT INTO commande_items(id_commande,id_fish,quantite,prix_vente,total)
                         VALUES($id_commande,$id_fish,$qte,$prix,$total_ligne)");

    // Mettre à jour le stock
    mysqli_query($conn, "UPDATE fish 
                         SET quantite_kg = quantite_kg - $qte
                         WHERE id=$id_fish");
}

/* ========= 4. Calculer le montant payé et reste ========= */
if($type_paiement == "Complet"){
    $montant_paye = $total_commande;
} else {
    $montant_paye = floatval($montant_acompte);
}

$reste = $total_commande - $montant_paye;

/* ========= 5. Mettre à jour la commande ========= */
mysqli_query($conn, "UPDATE commandes 
                     SET total=$total_commande,
                         montant_paye=$montant_paye,
                         reste=$reste
                     WHERE id=$id_commande");

/* ========= 6. Mettre à jour le compte client ========= */
mysqli_query($conn, "UPDATE clients
                     SET total_achat = total_achat + $total_commande,
                         total_paye  = total_paye  + $montant_paye
                     WHERE id=$id_client");

/* ========= 7. Redirection vers index ========= */
header("Location:index.php");
exit;
?>

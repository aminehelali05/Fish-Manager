<?php
session_start();
include "db.php";

if(!isset($_SESSION['username'])){
    header("Location: login.php");
    exit;
}

/* ========= 1. Récupérer et valider les données du formulaire ========= */
$id_client       = isset($_POST['id_client']) ? intval($_POST['id_client']) : 0;
$fish_ids        = isset($_POST['id_fish']) && is_array($_POST['id_fish']) ? $_POST['id_fish'] : [];
$quantites_map   = isset($_POST['quantite']) && is_array($_POST['quantite']) ? $_POST['quantite'] : [];
$unit_map        = isset($_POST['unit']) && is_array($_POST['unit']) ? $_POST['unit'] : [];
$type_vente      = isset($_POST['type_vente']) ? mysqli_real_escape_string($conn, $_POST['type_vente']) : '';
$type_paiement   = isset($_POST['type_paiement']) ? mysqli_real_escape_string($conn, $_POST['type_paiement']) : '';
$montant_acompte = isset($_POST['montant_acompte']) ? floatval($_POST['montant_acompte']) : 0.0;

// Basic checks
if($id_client <= 0){ die("Client invalide"); }
if(empty($fish_ids) || !is_array($fish_ids)){
    echo "<script>alert('Aucun poisson sélectionné.'); window.location='index.php';</script>";
    exit;
}

/* ========= 2. Créer la commande principale ========= */
mysqli_query($conn, "INSERT INTO commandes(id_client,type_vente,type_paiement,created_at)
                     VALUES($id_client,'$type_vente','$type_paiement',NOW())");
$id_commande = mysqli_insert_id($conn);

$total_commande = 0;
$items_inserted = 0;

/* ========= 3. Insérer chaque poisson dans commande_items ========= */
foreach($fish_ids as $id_fish){
    $id_fish = intval($id_fish);
    $qte = isset($quantites_map[$id_fish]) ? floatval($quantites_map[$id_fish]) : 0.0;
    // support user-entered unit: kg (default) or dr (1 dr = 10 kg)
    $unit = isset($unit_map[$id_fish]) ? $unit_map[$id_fish] : 'kg';
    if($unit === 'dr'){
        $qte = $qte * 10.0; // convert dr to kg
    }
    if($qte <= 0) continue;

    $r = mysqli_query($conn, "SELECT prix_vente, quantite_kg FROM fish WHERE id=$id_fish");
    if(!$r || mysqli_num_rows($r) == 0) continue;
    $fish = mysqli_fetch_assoc($r);

    // Prevent over-selling: cap by available stock
    $available = floatval($fish['quantite_kg']);
    if($qte > $available){
        // skip items if not enough stock (or optionally you can set $qte = $available)
        continue;
    }

    $prix = floatval($fish['prix_vente']);
    $total_ligne = $qte * $prix;
    $total_commande += $total_ligne;

    // Insérer dans commande_items
    mysqli_query($conn, "INSERT INTO commande_items(id_commande,id_fish,quantite,prix_vente,total)
                         VALUES($id_commande,$id_fish,$qte,$prix,$total_ligne)");

    // Mettre à jour le stock
    mysqli_query($conn, "UPDATE fish 
                         SET quantite_kg = quantite_kg - $qte
                         WHERE id=$id_fish");

    $items_inserted++;
}

// If no items were inserted, cleanup the empty commande and abort
if($items_inserted == 0){
    mysqli_query($conn, "DELETE FROM commandes WHERE id=$id_commande");
    $_SESSION['flash_error'] = 'Aucun article valide dans la commande (quantités insuffisantes ou non sélectionnées).';
    header('Location: index.php');
    exit;
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
// set success flash message and redirect
$_SESSION['flash_success'] = "Commande #$id_commande créée avec succès.";
header("Location:index.php");
exit;
?>

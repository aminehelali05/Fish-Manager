<?php
session_start();
include 'db.php';
if(!isset($_SESSION['username'])){ header('Location: login.php'); exit; }

if(!isset($_GET['client_id'])){ header('Location: index.php'); exit; }
$client_id = intval($_GET['client_id']);

$r = mysqli_query($conn, "SELECT * FROM clients WHERE id=$client_id");
if(!$r || mysqli_num_rows($r) == 0){ header('Location: index.php'); exit; }
$client = mysqli_fetch_assoc($r);

require __DIR__ . '/fpdf182/fpdf.php';

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'Fish Manager - Facture client',0,1,'C');
$pdf->Ln(4);

$pdf->SetFont('Arial','',12);
$pdf->Cell(0,6,'Client: '.$client['nom'].' '.$client['prenom'],0,1);
$pdf->Cell(0,6,'Telephone: '.$client['telephone'],0,1);
$pdf->Ln(6);

$overallTotal = 0;

$orders = mysqli_query($conn, "SELECT * FROM commandes WHERE id_client={$client_id} ORDER BY created_at");
if($orders && mysqli_num_rows($orders) > 0){
    while($o = mysqli_fetch_assoc($orders)){
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(0,6,'Commande #'.$o['id'].' - '.$o['created_at'],0,1);

        // table header
        $pdf->SetFont('Arial','B',11);
        $pdf->Cell(80,7,'Poisson',1,0,'L');
        $pdf->Cell(30,7,'Qté (kg)',1,0,'R');
        $pdf->Cell(40,7,'Prix uni',1,0,'R');
        $pdf->Cell(40,7,'Total',1,1,'R');

        $pdf->SetFont('Arial','',11);
        $items = mysqli_query($conn, "SELECT ci.*, f.nom_fish FROM commande_items ci JOIN fish f ON f.id=ci.id_fish WHERE ci.id_commande={$o['id']}");
        $orderTotal = 0;
        while($it = mysqli_fetch_assoc($items)){
            $pdf->Cell(80,6,$it['nom_fish'],1,0,'L');
            $pdf->Cell(30,6,number_format($it['quantite'],2),1,0,'R');
            $pdf->Cell(40,6,number_format($it['prix_vente'],2).' €',1,0,'R');
            $pdf->Cell(40,6,number_format($it['total'],2).' €',1,1,'R');
            $orderTotal += floatval($it['total']);
        }
        $pdf->SetFont('Arial','B',11);
        $pdf->Cell(150,7,'Sous-total commande',1,0,'R');
        $pdf->Cell(40,7,number_format($orderTotal,2).' €',1,1,'R');
        $pdf->Ln(6);
        $overallTotal += $orderTotal;
    }

    $pdf->SetFont('Arial','B',13);
    $pdf->Cell(150,9,'TOTAL GENERAL',1,0,'R');
    $pdf->Cell(40,9,number_format($overallTotal,2).' €',1,1,'R');

} else {
    $pdf->Cell(0,6,'Aucune commande trouvée pour ce client.',0,1);
}

$pdf->Output('D','facture_client_'.$client_id.'.pdf');
exit;

<?php
session_start();
include "db.php";
require('fpdf/fpdf.php'); // لازم يكون عندك مكتبة FPDF

if(!isset($_GET['id_client'])) die("Client non spécifié");

$id_client = intval($_GET['id_client']);
$client = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM clients WHERE id=$id_client"));

if(!$client) die("Client non trouvé");

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);

$pdf->Cell(0,10,"Facture de: {$client['nom']} {$client['prenom']}",0,1);
$pdf->SetFont('Arial','',12);
$pdf->Cell(0,10,"Telephone: {$client['telephone']}",0,1);
$pdf->Cell(0,10,"Date: ".date('d-m-Y'),0,1);
$pdf->Ln(5);

// ================= HISTORIQUE ACHATS =================
$pdf->SetFont('Arial','B',12);
$pdf->Cell(50,10,"Poisson",1);
$pdf->Cell(30,10,"Qté (kg)",1);
$pdf->Cell(30,10,"Total DT",1);
$pdf->Cell(30,10,"Payé DT",1);
$pdf->Cell(30,10,"Reste DT",1);
$pdf->Cell(20,10,"Date",1);
$pdf->Ln();

$pdf->SetFont('Arial','',12);
$res = mysqli_query($conn,"
SELECT o.*, f.nom_fish 
FROM orders o 
JOIN fish f ON o.id_fish=f.id
WHERE o.id_client=$id_client
ORDER BY o.created_at DESC
");

$total_general = 0;
$payé_total = 0;
$reste_total = 0;

while($row = mysqli_fetch_assoc($res)){
    $reste = $row['prix_total'] - $row['montant_paye'];
    $total_general += $row['prix_total'];
    $payé_total += $row['montant_paye'];
    $reste_total += $reste;

    $pdf->Cell(50,10,$row['nom_fish'],1);
    $pdf->Cell(30,10,$row['quantite_kg'],1);
    $pdf->Cell(30,10,$row['prix_total'],1);
    $pdf->Cell(30,10,$row['montant_paye'],1);
    $pdf->Cell(30,10,$reste,1);
    $pdf->Cell(20,10,date('d-m', strtotime($row['created_at'])),1);
    $pdf->Ln();
}

// ================= TOTAL =================
$pdf->SetFont('Arial','B',12);
$pdf->Cell(80,10,"TOTAL",1);
$pdf->Cell(30,10,$total_general,1);
$pdf->Cell(30,10,$payé_total,1);
$pdf->Cell(30,10,$reste_total,1);
$pdf->Cell(20,10,"",1);

$pdf->Output();

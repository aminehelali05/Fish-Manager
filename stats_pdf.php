<?php
session_start();
include 'db.php';
require('fpdf182/fpdf.php');
if(!isset($_SESSION['username'])){ header('Location: login.php'); exit; }

$q = "SELECT f.nom_fish, COALESCE(SUM(ci.total),0) AS total, COALESCE(SUM(ci.quantite),0) AS quantite
      FROM fish f
      LEFT JOIN commande_items ci ON ci.id_fish = f.id
      GROUP BY f.id ORDER BY total DESC";
$res = mysqli_query($conn, $q);

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,'Statistiques des ventes',0,1);
$pdf->Ln(4);
$pdf->SetFont('Arial','B',12);
$pdf->Cell(90,8,'Poisson',1);
$pdf->Cell(40,8,'Quantite (kg)',1);
$pdf->Cell(40,8,'Total DT',1);
$pdf->Ln();
$pdf->SetFont('Arial','',12);

while($r = mysqli_fetch_assoc($res)){
    $pdf->Cell(90,8,$r['nom_fish'],1);
    $pdf->Cell(40,8,$r['quantite'],1);
    $pdf->Cell(40,8,$r['total'],1);
    $pdf->Ln();
}

$pdf->Output('D','stats.pdf');

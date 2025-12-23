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

// helper to ensure Poppins fonts and safe text encoding
function ensurePoppinsFontsClient(){
    $fontDir = __DIR__ . '/fpdf182/font';
    $ttfDir = __DIR__ . '/assets/fonts';
    $needed = ['Poppins-Regular.php','Poppins-Bold.php','Poppins-SemiBold.php'];
    $missing = false;
    foreach($needed as $f){ if(!file_exists($fontDir.'/'.$f)) { $missing = true; break; } }
    if(!$missing) return;
    $cur = getcwd();
    chdir($fontDir);
    try{
        require_once __DIR__.'/fpdf182/makefont/makefont.php';
        if(file_exists($ttfDir.'/Poppins-Regular.ttf')) try{ MakeFont($ttfDir.'/Poppins-Regular.ttf','cp1252',true,false); }catch(Exception $e){}
        if(file_exists($ttfDir.'/Poppins-Bold.ttf')) try{ MakeFont($ttfDir.'/Poppins-Bold.ttf','cp1252',true,false); }catch(Exception $e){}
        if(file_exists($ttfDir.'/Poppins-SemiBold.ttf')) try{ MakeFont($ttfDir.'/Poppins-SemiBold.ttf','cp1252',true,false); }catch(Exception $e){}
    } catch(Exception $e){}
    chdir($cur);
}

function safeTextClient($s){
    $t = @iconv('UTF-8','CP1252//TRANSLIT',$s);
    if($t === false) $t = preg_replace('/[^\x20-\x7E]/','',$s);
    return $t;
}

ensurePoppinsFontsClient();

$font = 'Helvetica';
if(file_exists(__DIR__.'/fpdf182/font/Poppins-Regular.php')){
    if(file_exists(__DIR__.'/fpdf182/font/Poppins-Regular.php')) $pdf->AddFont('Poppins','','Poppins-Regular.php');
    if(file_exists(__DIR__.'/fpdf182/font/Poppins-Bold.php')) $pdf->AddFont('Poppins','B','Poppins-Bold.php');
    $font = 'Poppins';
}

$pdf->AddPage();
$pdf->SetFont($font,'B',16);
$pdf->Cell(0,10, safeTextClient('Fish Manager - Facture client'),0,1,'C');
$pdf->Ln(4);

$pdf->SetFont($font,'',12);
$pdf->Cell(0,6, safeTextClient('Client: '.$client['nom'].' '.$client['prenom']),0,1);
$pdf->Cell(0,6, safeTextClient('Telephone: '.$client['telephone']),0,1);
$pdf->Ln(6);

$overallTotal = 0;

$orders = mysqli_query($conn, "SELECT * FROM commandes WHERE id_client={$client_id} ORDER BY created_at");
if($orders && mysqli_num_rows($orders) > 0){
    while($o = mysqli_fetch_assoc($orders)){
        $pdf->SetFont($font,'B',12);
        $pdf->Cell(0,6, safeTextClient('Commande #'.$o['id'].' - '.$o['created_at']),0,1);

        // table header
        $pdf->SetFont($font,'B',11);
        $pdf->Cell(70,7,'Poisson',1,0,'L');
        $pdf->Cell(30,7,'Qté (kg)',1,0,'C');
        $pdf->Cell(30,7,'Qté (dr)',1,0,'C');
        $pdf->Cell(30,7,'Prix uni',1,0,'R');
        $pdf->Cell(40,7,'Total',1,1,'R');

        $pdf->SetFont($font,'',11);
        $items = mysqli_query($conn, "SELECT ci.*, f.nom_fish FROM commande_items ci JOIN fish f ON f.id=ci.id_fish WHERE ci.id_commande={$o['id']}");
        $orderTotal = 0;
        while($it = mysqli_fetch_assoc($items)){
            $q = floatval($it['quantite']);
            $dr = $q / 10.0;
            $pdf->Cell(70,6, safeTextClient($it['nom_fish']),1,0,'L');
            $pdf->Cell(30,6, number_format($q,2),1,0,'C');
            $pdf->Cell(30,6, number_format($dr,2),1,0,'C');
            $pdf->Cell(30,6, number_format($it['prix_vente'],2).' €',1,0,'R');
            $pdf->Cell(40,6, number_format($it['total'],2).' €',1,1,'R');
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

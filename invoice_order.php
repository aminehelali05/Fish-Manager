<?php
session_start();
include 'db.php';

if(!isset($_SESSION['username'])){
    header('Location: login.php'); exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if($id <= 0){ die('Commande invalide'); }

require __DIR__ . '/fpdf182/fpdf.php';

// Ensure Poppins font is available to FPDF (generate if missing)
function ensurePoppinsFonts(){
    $fontDir = __DIR__ . '/fpdf182/font';
    $ttfDir = __DIR__ . '/assets/fonts';
    $needed = ['Poppins-Regular.php','Poppins-Bold.php','Poppins-SemiBold.php'];
    $missing = false;
    foreach($needed as $f){ if(!file_exists($fontDir.'/'.$f)) { $missing = true; break; } }
    if(!$missing) return;

    // try to generate using makefont utility but avoid subsetting (some TTFs break subset)
    $cur = getcwd();
    chdir($fontDir);
    try{
        require_once __DIR__.'/fpdf182/makefont/makefont.php';
        // MakeFont will create basename.php and basename.z in current dir
        // Use subset=false to avoid calling TTFParser->Subset which can fail for some fonts
        if(file_exists($ttfDir.'/Poppins-Regular.ttf')) try{ MakeFont($ttfDir.'/Poppins-Regular.ttf','cp1252',true,false); }catch(Exception $e){}
        if(file_exists($ttfDir.'/Poppins-Bold.ttf')) try{ MakeFont($ttfDir.'/Poppins-Bold.ttf','cp1252',true,false); }catch(Exception $e){}
        if(file_exists($ttfDir.'/Poppins-SemiBold.ttf')) try{ MakeFont($ttfDir.'/Poppins-SemiBold.ttf','cp1252',true,false); }catch(Exception $e){}
    } catch(Exception $e){
        // ignore, fallback to core fonts
    }
    chdir($cur);
}

function asciiText($s){
    // Replace common currency symbols with ASCII label
    $s = str_replace(['€','€ '], [' DT',' DT '], $s);
    // transliterate to ASCII and remove any non-printable/non-ascii characters
    $t = @iconv('UTF-8','ASCII//TRANSLIT',$s);
    if($t === false) $t = preg_replace('/[^\x20-\x7E]/','',$s);
    // remove any remaining non-ascii
    $t = preg_replace('/[^\x20-\x7E]/','',$t);
    return $t;
}

// fetch order
$r = mysqli_query($conn, "SELECT o.*, c.nom, c.prenom, c.telephone FROM commandes o JOIN clients c ON o.id_client=c.id WHERE o.id=$id");
if(!$r || mysqli_num_rows($r)==0) die('Commande introuvable');
$o = mysqli_fetch_assoc($r);

$items = mysqli_query($conn, "SELECT ci.*, f.nom_fish FROM commande_items ci JOIN fish f ON f.id=ci.id_fish WHERE ci.id_commande={$o['id']}");

// prepare PDF
ensurePoppinsFonts();
$pdf = new FPDF();
// Try to use Poppins if available
if(file_exists(__DIR__.'/fpdf182/font/Poppins-Regular.php')){
    $pdf->AddFont('Poppins','','Poppins-Regular.php');
    if(file_exists(__DIR__.'/fpdf182/font/Poppins-Bold.php')) $pdf->AddFont('Poppins','B','Poppins-Bold.php');
    $font = 'Poppins';
} else {
    $font = 'Helvetica';
}

$pdf->AddPage();
$pdf->SetFont($font,'B',14);
$pdf->Cell(0,10, safeText('Fish Manager - Facture commande #' . $o['id']), 0, 1, 'C');
$pdf->Ln(4);

$pdf->SetFont($font,'B',14);
$pdf->Cell(0,10, asciiText('Fish Manager - Facture commande #' . $o['id']), 0, 1, 'C');
$pdf->Cell(0,6, asciiText('Telephone: '.$o['telephone']),0,1);
$pdf->Cell(0,6, safeText('Date: '.$o['created_at']),0,1);
$pdf->Ln(6);
$pdf->Cell(0,6, asciiText('Client: '.$o['nom'].' '.$o['prenom']), 0,1);
$pdf->Cell(0,6, asciiText('Telephone: '.$o['telephone']),0,1);
$pdf->Cell(0,6, asciiText('Date: '.$o['created_at']),0,1);
$pdf->Cell(30,7,'Qte (kg)',1,0,'C');
$pdf->Cell(30,7,'Qte (dr)',1,0,'C');
$pdf->Cell(30,7,'Prix uni',1,0,'C');
$pdf->Cell(30,7,'Total',1,1,'C');

$pdf->SetFont($font,'',11);
$subtotal = 0.0;
while($it = mysqli_fetch_assoc($items)){
    $q = floatval($it['quantite']);
    $dr = $q / 10.0;
    $prix = floatval($it['prix_vente']);
    $tline = floatval($it['total']);
    $pdf->Cell(80,7, asciiText($it['nom_fish']),1,0);
    $pdf->Cell(30,7, number_format($q,2),1,0,'C');
    $pdf->Cell(30,7, number_format($dr,2),1,0,'C');
    $pdf->Cell(30,7, number_format($prix,2).' DT',1,0,'C');
    $pdf->Cell(30,7, number_format($tline,2).' DT',1,1,'C');
    $subtotal += $tline;
}

$pdf->SetFont($font,'B',11);
$pdf->Cell(170,7,'Sous-total commande',1,0,'R');
$pdf->Cell(30,7, number_format($subtotal,2).' DT',1,1,'C');

$pdf->Ln(6);
$pdf->Cell(0,6, asciiText('Merci pour votre achat.'),0,1);

$pdf->Output('D', 'facture_commande_'.$o['id'].'.pdf');
exit;

?>

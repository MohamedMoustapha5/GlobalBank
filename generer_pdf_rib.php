<?php
require_once 'config.php';
require_once 'vendor/autoload.php';


// ========== AJOUTER CES FONCTIONS SI ELLES MANQUENT ==========
if (!function_exists('formatRoutingNumber')) {
    function formatRoutingNumber($routing) {
        return substr($routing, 0, 4) . '-' . substr($routing, 4, 4) . '-' . substr($routing, 8, 1);
    }
}

if (!function_exists('formatAccountNumber')) {
    function formatAccountNumber($account) {
        return trim(chunk_split($account, 4, ' '));
    }
}
// =============================================================

// Vérifier si l'utilisateur est connecté (client)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// ... reste du code
// Vérifier si l'utilisateur est connecté (client)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Récupérer le RIB du client et ses informations
try {
    $stmt = $pdo->prepare("SELECT r.*, u.nom, u.prenom, u.email, u.pays 
                           FROM rib_bancaires r 
                           JOIN utilisateurs u ON r.user_id = u.id 
                           WHERE r.user_id = ?");
    $stmt->execute([$user_id]);
    $rib = $stmt->fetch();
    
    if (!$rib) {
        die("RIB non trouvé");
    }
} catch (Exception $e) {
    die("Erreur: " . $e->getMessage());
}

// Création du PDF
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'A4', true, 'UTF-8', false);

// Configuration
$pdf->SetCreator('UBS Bank USA');
$pdf->SetAuthor('UBS');
$pdf->SetTitle('Bank Statement - ' . strtoupper($rib['prenom'] . ' ' . $rib['nom']));
$pdf->SetSubject('Bank Account Information');

// Supprimer l'en-tête et le pied de page par défaut
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Marges
$pdf->SetMargins(20, 20, 20);
$pdf->SetAutoPageBreak(TRUE, 20);

// Ajouter une page
$pdf->AddPage();

// ==============================================
// EN-TÊTE AVEC LOGO (DYNAMIQUE)
// ==============================================

// Logo - UTILISE getActiveLogo()
$logo_file = getActiveLogo();
if (file_exists($logo_file)) {
    // Déterminer le type d'image
    $ext = strtolower(pathinfo($logo_file, PATHINFO_EXTENSION));
    $img_type = '';
    if ($ext == 'png') $img_type = 'PNG';
    elseif ($ext == 'jpg' || $ext == 'jpeg') $img_type = 'JPG';
    elseif ($ext == 'gif') $img_type = 'GIF';
    else $img_type = 'PNG';
    
    $pdf->Image($logo_file, 20, 15, 40, 0, $img_type, '', 'T', false, 300, '', false, false, 0, false, false, false);
}

// Titre
$pdf->SetFont('helvetica', 'B', 22);
$pdf->SetY(20);
$pdf->SetX(70);
$pdf->Cell(0, 10, 'Bank Account Information', 0, 1, 'L');
$pdf->SetFont('helvetica', 'I', 10);
$pdf->SetX(70);
$pdf->Cell(0, 5, 'United States - New York', 0, 1, 'L');
$pdf->Ln(15);

// Ligne de séparation
$pdf->Line(20, 50, 190, 50);
$pdf->Ln(10);

// ==============================================
// ACCOUNT HOLDER INFORMATION
// ==============================================
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 8, 'Account Holder / Titulaire du compte', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(50, 7, 'Name:', 0, 0, 'L');
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 7, strtoupper($rib['prenom'] . ' ' . $rib['nom']), 0, 1, 'L');
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(50, 7, 'Email:', 0, 0, 'L');
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 7, $rib['email'], 0, 1, 'L');
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(50, 7, 'Country / Pays:', 0, 0, 'L');
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 7, $rib['pays'], 0, 1, 'L');
$pdf->Ln(5);

// ==============================================
// BANK ACCOUNT DETAILS
// ==============================================
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 8, 'Bank Account Details', 0, 1, 'L');

// Routing Number
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(60, 10, 'Routing Number (ABA):', 0, 0, 'L');
$pdf->SetFont('courier', 'B', 14);
$pdf->SetFillColor(248, 249, 250);
$pdf->Cell(0, 10, formatRoutingNumber($rib['routing_number']), 0, 1, 'L', 1);

// Account Number
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(60, 10, 'Account Number:', 0, 0, 'L');
$pdf->SetFont('courier', 'B', 14);
$pdf->Cell(0, 10, formatAccountNumber($rib['account_number']), 0, 1, 'L', 1);

// Clé RIB
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(60, 10, 'Clé RIB:', 0, 0, 'L');
$pdf->SetFont('courier', 'B', 14);
$pdf->Cell(0, 10, isset($rib['cle_rib']) ? $rib['cle_rib'] : 'Non disponible', 0, 1, 'L', 1);

// IBAN
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(60, 10, 'IBAN:', 0, 0, 'L');
$pdf->SetFont('courier', 'B', 14);
$pdf->Cell(0, 10, isset($rib['iban']) ? $rib['iban'] : 'US02 0210 0012 3456 7890 1234', 0, 1, 'L', 1);

// SWIFT Code
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(60, 10, 'SWIFT Code / BIC:', 0, 0, 'L');
$pdf->SetFont('courier', 'B', 14);
$pdf->Cell(0, 10, $rib['swift_code'], 0, 1, 'L', 1);

$pdf->Ln(5);

// ==============================================
// BANK INFORMATION
// ==============================================
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 8, 'Bank Information', 0, 1, 'L');

// Bank Name
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(50, 8, 'Bank Name:', 0, 0, 'L');
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 8, $rib['bank_name'], 0, 1, 'L');

// Bank Address
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(50, 8, 'Address:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 12);
$pdf->MultiCell(0, 8, $rib['bank_address'], 0, 'L', 0, 1);

// Beneficiary Name
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(50, 8, 'Beneficiary:', 0, 0, 'L');
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 8, $rib['beneficiary_name'], 0, 1, 'L');

// Beneficiary Country
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(50, 8, 'Beneficiary Country:', 0, 0, 'L');
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 8, $rib['pays'], 0, 1, 'L');

$pdf->Ln(10);

// ==============================================
// LEGAL MENTIONS
// ==============================================
$pdf->SetFont('helvetica', 'I', 9);
$pdf->SetTextColor(100, 100, 100);
$pdf->MultiCell(0, 5, 'This document provides the bank account information of the holder. It is valid for all banking operations requiring these details. ', 0, 'L', 0, 1);

$pdf->Ln(5);
$pdf->SetFont('helvetica', 'I', 8);
$pdf->Cell(0, 5, 'Document issued on ' . date('d/m/Y') . ' - Valid until further notice', 0, 1, 'C');

// Générer le PDF
$pdf->Output('Bank_Statement_' . strtoupper($rib['prenom'] . '_' . $rib['nom']) . '.pdf', 'D');
exit;
?>
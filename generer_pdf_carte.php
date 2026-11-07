<?php
require_once 'config.php';
require_once 'vendor/autoload.php';

// Vérifier si l'utilisateur est connecté (client)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Récupérer les informations de la carte (vérifier qu'elle appartient bien au client)
try {
    $stmt = $pdo->prepare("SELECT c.*, u.nom, u.prenom, u.email 
                           FROM cartes_virtuelles c 
                           JOIN utilisateurs u ON c.user_id = u.id 
                           WHERE c.id = ? AND c.user_id = ?");
    $stmt->execute([$id, $user_id]);
    $carte = $stmt->fetch();
    
    if (!$carte) {
        die("Carte non trouvée ou vous n'êtes pas autorisé à la consulter");
    }
} catch (Exception $e) {
    die("Erreur: " . $e->getMessage());
}

// Création du PDF
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'A4', true, 'UTF-8', false);

// Configuration
$pdf->SetCreator('UBS Banque');
$pdf->SetAuthor('UBS');
$pdf->SetTitle('Carte Virtuelle #'.$carte['id']);
$pdf->SetSubject('Carte bancaire virtuelle');

// Supprimer l'en-tête et le pied de page par défaut
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Marges
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(TRUE, 15);

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
    
    $pdf->Image($logo_file, 15, 10, 50, 0, $img_type, '', 'T', false, 300, '', false, false, 0, false, false, false);
}

// Titre
$pdf->SetFont('helvetica', 'B', 20);
$pdf->SetY(25);
$pdf->Cell(0, 10, 'CARTE VIRTUELLE', 0, 1, 'C');
$pdf->Ln(10);

// ==============================================
// INFORMATIONS DU CLIENT
// ==============================================
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 8, 'Informations du titulaire', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 11);
$pdf->Cell(40, 7, 'Nom:', 0, 0, 'L');
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(0, 7, strtoupper($carte['prenom'] . ' ' . $carte['nom']), 0, 1, 'L');
$pdf->SetFont('helvetica', '', 11);
$pdf->Cell(40, 7, 'Email:', 0, 0, 'L');
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(0, 7, $carte['email'], 0, 1, 'L');
$pdf->Ln(5);

// ==============================================
// REPRÉSENTATION DE LA CARTE
// ==============================================

// Fond de la carte
$pdf->SetFillColor(198, 40, 40); // Rouge UBS
$pdf->RoundedRect(15, $pdf->GetY(), 180, 100, 5, '1111', 'F');

// Position de départ pour le contenu de la carte
$startY = $pdf->GetY() + 10;

// Logo UBS sur la carte - UTILISE getActiveLogo() aussi
$logo_file = getActiveLogo();
if (file_exists($logo_file)) {
    $ext = strtolower(pathinfo($logo_file, PATHINFO_EXTENSION));
    $img_type = '';
    if ($ext == 'png') $img_type = 'PNG';
    elseif ($ext == 'jpg' || $ext == 'jpeg') $img_type = 'JPG';
    elseif ($ext == 'gif') $img_type = 'GIF';
    else $img_type = 'PNG';
    
    $pdf->Image($logo_file, 25, $startY, 30, 0, $img_type, '', '', false, 300, '', false, false, 0, false, false, false);
}

// Type de carte
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetXY(150, $startY);
$pdf->Cell(30, 8, $carte['type_carte'], 0, 1, 'R');

// Puce avec grillage
$pdf->SetFillColor(255, 215, 0); // Or
$pdf->RoundedRect(25, $startY + 20, 45, 30, 3, '1111', 'F');

// Grillage
$pdf->SetDrawColor(0, 0, 0);
$pdf->SetLineWidth(0.2);
// Lignes horizontales
$pdf->Line(25, $startY + 25, 70, $startY + 25);
$pdf->Line(25, $startY + 30, 70, $startY + 30);
$pdf->Line(25, $startY + 35, 70, $startY + 35);
$pdf->Line(25, $startY + 40, 70, $startY + 40);
$pdf->Line(25, $startY + 45, 70, $startY + 45);
// Lignes verticales
$pdf->Line(30, $startY + 20, 30, $startY + 50);
$pdf->Line(35, $startY + 20, 35, $startY + 50);
$pdf->Line(40, $startY + 20, 40, $startY + 50);
$pdf->Line(45, $startY + 20, 45, $startY + 50);
$pdf->Line(50, $startY + 20, 50, $startY + 50);
$pdf->Line(55, $startY + 20, 55, $startY + 50);
$pdf->Line(60, $startY + 20, 60, $startY + 50);
$pdf->Line(65, $startY + 20, 65, $startY + 50);

// Sans contact
$pdf->SetFont('helvetica', 'B', 16);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetXY(140, $startY + 25);
$pdf->Cell(30, 8, chr(128).' WiFi', 0, 1, 'R');

// Numéro de carte
$pdf->SetFont('courier', 'B', 16);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetXY(25, $startY + 60);
$pdf->Cell(0, 8, $carte['numero_carte'], 0, 1, 'L');

// Date d'expiration
$pdf->SetFont('helvetica', '', 12);
$pdf->SetXY(25, $startY + 70);
$pdf->Cell(30, 8, 'Expire fin:', 0, 0, 'L');
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(30, 8, $carte['date_expiration'], 0, 1, 'L');

// Nom du titulaire
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetXY(25, $startY + 80);
$pdf->Cell(0, 8, strtoupper($carte['nom_titulaire']), 0, 1, 'L');

$pdf->SetY($startY + 100);

// ==============================================
// INFORMATIONS COMPLÉMENTAIRES
// ==============================================
$pdf->Ln(10);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 8, 'Détails de la carte', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 11);
$pdf->Cell(50, 7, 'Type de carte:', 0, 0, 'L');
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(0, 7, $carte['type_carte'], 0, 1, 'L');
$pdf->SetFont('helvetica', '', 11);
$pdf->Cell(50, 7, 'Plafond mensuel:', 0, 0, 'L');
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(0, 7, number_format($carte['plafond'], 2) . ' €', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 11);
$pdf->Cell(50, 7, 'Statut:', 0, 0, 'L');
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(0, 7, ucfirst($carte['statut']), 0, 1, 'L');
$pdf->SetFont('helvetica', '', 11);
$pdf->Cell(50, 7, 'Date de création:', 0, 0, 'L');
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(0, 7, date('d/m/Y', strtotime($carte['date_creation'])), 0, 1, 'L');

// ==============================================
// MENTIONS LÉGALES
// ==============================================
$pdf->Ln(10);
$pdf->SetFont('helvetica', 'I', 8);
$pdf->SetTextColor(100, 100, 100);
$pdf->MultiCell(0, 4, 'Cette carte doit être signée par le titulaire. Toute utilisation non autorisée est interdite. En cas de perte ou de vol, veuillez contacter immédiatement votre agence.', 0, 'L', 0, 1);

// Générer le PDF
$pdf->Output('carte_virtuelle_'.$carte['id'].'_'.date('Ymd').'.pdf', 'D');
exit;
?>
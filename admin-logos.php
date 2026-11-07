<?php
require_once 'config.php';

// Vérifier si l'utilisateur est admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Créer la table de configuration si elle n'existe pas
createConfigurationTable();

$message = '';
$message_type = '';

// Traiter les actions POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['change_logo'])) {
        $new_logo = $_POST['selected_logo'] ?? '';

        if (!empty($new_logo) && file_exists(__DIR__ . '/' . $new_logo)) {
            if (setActiveLogo($new_logo)) {
                $message = "✅ Logo changé avec succès !";
                $message_type = "success";
            } else {
                $message = "❌ Erreur lors du changement du logo.";
                $message_type = "error";
            }
        } else {
            $message = "❌ Veuillez sélectionner un logo valide.";
            $message_type = "error";
        }
    } elseif (isset($_POST['upload_logo'])) {
        if (isset($_FILES['new_logo']) && $_FILES['new_logo']['error'] === UPLOAD_ERR_OK) {
            $uploaded = uploadLogoFile($_FILES['new_logo']);
            if ($uploaded !== false) {
                $message = "✅ Logo ajouté avec succès !";
                $message_type = "success";
            } else {
                $message = "❌ Impossible d'ajouter ce logo. Vérifiez le format et réessayez.";
                $message_type = "error";
            }
        } else {
            $message = "❌ Aucun fichier de logo valide n'a été téléchargé.";
            $message_type = "error";
        }
    } elseif (isset($_POST['delete_logo']) && !empty($_POST['logo_to_delete'])) {
        $logo_to_delete = $_POST['logo_to_delete'];
        $active_logo = getActiveLogo();

        if ($logo_to_delete === $active_logo) {
            $message = "❌ Vous ne pouvez pas supprimer le logo actif. Changez d'abord de logo.";
            $message_type = "error";
        } elseif (deleteLogoFile($logo_to_delete)) {
            $message = "✅ Logo supprimé avec succès !";
            $message_type = "success";
        } else {
            $message = "❌ Impossible de supprimer ce logo.";
            $message_type = "error";
        }
    }
}

$active_logo = getActiveLogo();
$available_logos = getAvailableLogos();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Logos - Admin</title>
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
        
        .sidebar.active { left: 0; }
        
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
        
        .sidebar-overlay.active { display: block; }
        
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
        
        .card {
            background: white;
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .card-title {
            color: #333;
            font-weight: 600;
            margin-bottom: 20px;
            font-size: 18px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .alert {
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .current-logo {
            display: flex;
            align-items: center;
            gap: 20px;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .current-logo img {
            max-width: 150px;
            max-height: 80px;
            background: white;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
        
        .logos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .logo-card {
            background: #f8f9fa;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
        }
        
        .logo-card:hover {
            border-color: #667eea;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .logo-card.active {
            border-color: #28a745;
            background-color: #e8f5e9;
        }
        
        .logo-card.active::after {
            content: '✓';
            position: absolute;
            top: 10px;
            right: 10px;
            background: #28a745;
            color: white;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .logo-preview {
            width: 100%;
            height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .logo-preview img {
            max-width: 90%;
            max-height: 80px;
            object-fit: contain;
        }
        
        .logo-name {
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .logo-input {
            display: none;
        }
        
        .btn {
            padding: 10px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #28a745;
            color: white;
        }
        
        .btn-primary:hover {
            background: #218838;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .button-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
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
            
            .logos-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
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
            <a href="admin-cartes.php"><i class="fas fa-credit-card"></i> Cartes virtuelles</a>
            <a href="admin-nouveau-virement.php"><i class="fas fa-plus-circle"></i> Nouveau virement</a>
            <a href="admin-clients.php"><i class="fas fa-users"></i> Clients</a>
            <a href="admin-logos.php" class="active"><i class="fas fa-image"></i> Logos</a>
            <a href="logout.php" style="border-top: 1px solid rgba(255,255,255,0.1); margin-top: 50px;"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </nav>
    </div>
    
    <div class="main-content">
        <div class="admin-header">
            <h2><i class="fas fa-image"></i> Gestion des Logos</h2>
            <div class="user-info">
                <span><i class="fas fa-user"></i> <?= htmlspecialchars(($_SESSION['user_prenom'] ?? '') . ' ' . ($_SESSION['user_nom'] ?? '')) ?></span>
                <a href="logout.php" class="btn btn-danger btn-sm"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
            </div>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-<?= $message_type ?>">
                <i class="fas fa-<?= $message_type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                <?= $message ?>
            </div>
        <?php endif; ?>
        
        <!-- Logo actuel -->
        <div class="card">
            <div class="card-title">
                <i class="fas fa-eye"></i> Logo actuellement utilisé
            </div>
            <div class="current-logo">
                <img src="<?= htmlspecialchars($active_logo) ?>" alt="Logo actif">
                <div>
                    <p><strong>Chemin:</strong> <?= htmlspecialchars($active_logo) ?></p>
                    <p class="text-muted mb-0">Ce logo apparaît sur l'ensemble du site</p>
                </div>
            </div>
        </div>
        
        <!-- Ajouter un logo -->
        <div class="card">
            <div class="card-title">
                <i class="fas fa-upload"></i> Ajouter un nouveau logo
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div style="display:flex; flex-wrap:wrap; gap:15px; align-items:center;">
                    <div style="flex:1; min-width:220px;">
                        <input type="file" name="new_logo" accept="image/png,image/jpeg,image/jpg,image/gif,image/webp" class="form-control" required>
                    </div>
                    <button type="submit" name="upload_logo" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i> Ajouter le logo
                    </button>
                </div>
                <p style="margin-top:10px; color:#666; font-size:0.95rem;">Formats autorisés : PNG, JPG, JPEG, GIF, WEBP. Taille maximale recommandée : 2 Mo.</p>
            </form>
        </div>

        <!-- Sélection du logo -->
        <div class="card">
            <div class="card-title">
                <i class="fas fa-palette"></i> Choisir un nouveau logo
            </div>
            
            <?php if (empty($available_logos)): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> Aucun logo trouvé dans le dossier images/.
                    Veuillez ajouter des images (PNG, JPG, JPEG, GIF, WEBP).
                </div>
            <?php else: ?>
                <form method="POST" id="logoForm">
                    <input type="hidden" name="delete_logo" value="0">
                    <input type="hidden" name="logo_to_delete" value="">
                    <div class="logos-grid" id="logosContainer">
                        <?php foreach ($available_logos as $logo): ?>
                            <div class="logo-card <?= $logo['path'] === $active_logo ? 'active' : '' ?>" 
                                 data-logo="<?= htmlspecialchars($logo['path']) ?>">
                                <input type="radio" name="selected_logo" value="<?= htmlspecialchars($logo['path']) ?>" 
                                       class="logo-input" <?= $logo['path'] === $active_logo ? 'checked' : '' ?>>
                                <div class="logo-preview">
                                    <img src="<?= htmlspecialchars($logo['path']) ?>" alt="<?= htmlspecialchars($logo['display_name']) ?>">
                                </div>
                                <div class="logo-name"><?= htmlspecialchars($logo['display_name']) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($logo['name']) ?></small>
                                <?php if ($logo['path'] !== $active_logo): ?>
                                    <button type="button" class="btn btn-sm btn-danger delete-logo" data-logo="<?= htmlspecialchars($logo['path']) ?>" style="margin-top:10px;">Supprimer</button>
                                <?php else: ?>
                                    <span class="badge badge-success" style="margin-top:10px; display:inline-block;">Actif</span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="button-group">
                        <button type="submit" name="change_logo" value="1" class="btn btn-primary">
                            <i class="fas fa-check"></i> Appliquer ce logo
                        </button>
                        <a href="admin-dashboard.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Retour
                        </a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    // Menu responsive
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
    
    window.addEventListener('resize', () => {
        if (window.innerWidth > 768) {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        }
    });
    
    // Gestion du clic sur les cartes de logo
    document.querySelectorAll('.logo-card').forEach(card => {
        card.addEventListener('click', function(e) {
            if (e.target.classList.contains('delete-logo')) {
                return;
            }
            document.querySelectorAll('.logo-card').forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            this.querySelector('input[type="radio"]').checked = true;
        });
    });

    document.querySelectorAll('.delete-logo').forEach(button => {
        button.addEventListener('click', function() {
            const logoPath = this.getAttribute('data-logo');
            if (!confirm('Confirmez-vous la suppression de ce logo ?')) {
                return;
            }
            document.querySelector('input[name="delete_logo"]').value = '1';
            document.querySelector('input[name="logo_to_delete"]').value = logoPath;
            document.getElementById('logoForm').submit();
        });
    });
    
    // Validation du formulaire
    document.getElementById('logoForm').addEventListener('submit', function(e) {
        const selected = document.querySelector('input[name="selected_logo"]:checked');
        if (!selected) {
            e.preventDefault();
            alert('Veuillez sélectionner un logo');
        }
    });
    </script>
</body>
</html>
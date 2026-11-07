<?php
require_once 'config.php';

// Vérifier si l'utilisateur est connecté ET est admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Récupérer toutes les cartes avec les infos clients
try {
    $stmt = $pdo->query("
        SELECT c.*, u.nom, u.prenom, u.email 
        FROM cartes_virtuelles c 
        LEFT JOIN utilisateurs u ON c.user_id = u.id 
        ORDER BY c.date_creation DESC
    ");
    $cartes = $stmt->fetchAll();
} catch (Exception $e) {
    $cartes = [];
    $erreur = "Erreur: " . $e->getMessage();
}

// Traitement de la suppression
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM cartes_virtuelles WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: admin-cartes.php?msg=deleted");
        exit;
    } catch (Exception $e) {
        $erreur = "Erreur lors de la suppression";
    }
}

$message = '';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'deleted') $message = "Carte supprimée avec succès";
    if ($_GET['msg'] == 'created') $message = "Carte créée avec succès";
    if ($_GET['msg'] == 'updated') $message = "Carte modifiée avec succès";
}

// Fonction pour masquer le numéro de carte
function masquerNumero($numero) {
    return substr($numero, 0, 4) . ' **** **** ' . substr($numero, -4);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des cartes virtuelles - Admin</title>
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
        
        .filter-bar {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .filter-bar input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            min-width: 200px;
        }
        
        .filter-bar select {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: auto;
            min-width: 150px;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            white-space: nowrap;
        }
        
        .btn-success:hover {
            background: #218838;
            color: white;
            text-decoration: none;
        }
        
        .table-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow-x: auto;
        }
        
        .table-card h4 {
            margin-bottom: 20px;
            color: #333;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1000px;
        }
        
        th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            color: #555;
            font-weight: 600;
            white-space: nowrap;
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
            vertical-align: middle;
        }
        
        .badge-statut {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }
        
        .badge-active {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-bloquee {
            background: #f8d7da;
            color: #721c24;
        }
        
        .badge-expiree {
            background: #fff3cd;
            color: #856404;
        }
        
        .action-buttons {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        
        .btn-icon {
            width: 32px;
            height: 32px;
            border-radius: 5px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .btn-icon:hover {
            transform: scale(1.1);
            color: white;
        }
        
        .btn-edit { background: #28a745; }
        .btn-view { background: #17a2b8; }
        .btn-delete { background: #dc3545; }
        
        .client-name {
            font-weight: bold;
            color: #333;
            white-space: nowrap;
        }
        
        .client-email {
            font-size: 0.75rem;
            color: #666;
        }
        
        .carte-numero {
            font-family: monospace;
            font-weight: bold;
            color: #667eea;
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
            
            .filter-bar {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-bar input,
            .filter-bar select,
            .filter-bar .btn-success {
                width: 100%;
            }
            
            .user-info {
                width: 100%;
                justify-content: space-between;
            }
            
            .btn-danger {
                width: 100%;
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
            <a href="logout.php" style="border-top: 1px solid rgba(255,255,255,0.1); margin-top: 50px;"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </nav>
    </div>
    
    <div class="main-content">
        <div class="admin-header">
            <h2><i class="fas fa-credit-card"></i> Gestion des cartes virtuelles</h2>
            <div class="user-info">
                <span><i class="fas fa-user"></i> <?= htmlspecialchars(($_SESSION['user_prenom'] ?? '') . ' ' . ($_SESSION['user_nom'] ?? '')) ?></span>
                <a href="logout.php" class="btn btn-danger btn-sm"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
            </div>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= $message ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($erreur)): ?>
            <div class="alert alert-danger"><?= $erreur ?></div>
        <?php endif; ?>
        
        <div class="filter-bar">
            <input type="text" id="searchInput" placeholder="Rechercher par client, numéro de carte...">
            <select id="statutFilter">
                <option value="">Tous les statuts</option>
                <option value="active">Active</option>
                <option value="bloquee">Bloquée</option>
                <option value="expiree">Expirée</option>
            </select>
            <a href="admin-nouvelle-carte.php" class="btn-success"><i class="fas fa-plus"></i> Nouvelle carte</a>
        </div>
        
        <div class="table-card">
            <h4>Liste des cartes virtuelles</h4>
            <div class="table-responsive">
                <table id="cartesTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Client</th>
                            <th>Numéro de carte</th>
                            <th>Expiration</th>
                            <th>Type</th>
                            <th>Plafond</th>
                            <th>Statut</th>
                            <th>Date création</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($cartes)): ?>
                            <tr><td colspan="9" class="text-center">Aucune carte trouvée</td></tr>
                        <?php else: ?>
                            <?php foreach ($cartes as $carte): ?>
                            <tr>
                                <td>#<?= $carte['id'] ?></td>
                                <td>
                                    <div class="client-name"><?= htmlspecialchars($carte['prenom'] ?? '') ?> <?= htmlspecialchars($carte['nom'] ?? '') ?></div>
                                    <div class="client-email"><?= htmlspecialchars($carte['email'] ?? '') ?></div>
                                </td>
                                <td><span class="carte-numero"><?= masquerNumero($carte['numero_carte']) ?></span></td>
                                <td><?= $carte['date_expiration'] ?></td>
                                <td><?= $carte['type_carte'] ?></td>
                                <td><?= number_format($carte['plafond'], 2) ?> €</td>
                                <td>
                                    <?php
                                    $badge_class = '';
                                    if ($carte['statut'] == 'active') $badge_class = 'badge-active';
                                    elseif ($carte['statut'] == 'bloquee') $badge_class = 'badge-bloquee';
                                    elseif ($carte['statut'] == 'expiree') $badge_class = 'badge-expiree';
                                    ?>
                                    <span class="badge-statut <?= $badge_class ?>"><?= ucfirst($carte['statut']) ?></span>
                                </td>
                                <td><?= date('d/m/Y', strtotime($carte['date_creation'])) ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="admin-modifier-carte.php?id=<?= $carte['id'] ?>" class="btn-icon btn-edit" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="#" class="btn-icon btn-view" title="Voir détails" onclick="alert('Numéro complet: <?= $carte['numero_carte'] ?>\nCVV: <?= $carte['cvv'] ?>');">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="?delete=<?= $carte['id'] ?>" class="btn-icon btn-delete" title="Supprimer" 
                                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette carte ?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
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
    
    $('#searchInput').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('#cartesTable tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
    
    $('#statutFilter').on('change', function() {
        var statut = $(this).val();
        $('#cartesTable tbody tr').each(function() {
            if (!statut) $(this).show();
            else {
                var statutCell = $(this).find('td:eq(6)').text().trim().toLowerCase();
                $(this).toggle(statutCell === statut);
            }
        });
    });
    
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        }
    });
    </script>
</body>
</html>
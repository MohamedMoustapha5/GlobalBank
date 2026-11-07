<?php
require_once 'config.php';

// Vérifier si l'utilisateur est connecté ET est admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$message = '';
$message_type = '';

// Traitement de la suppression d'un client
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    try {
        $check = $pdo->prepare("SELECT COUNT(*) as total FROM virements WHERE user_id = ?");
        $check->execute([$id]);
        $total_virements = $check->fetch()['total'];
        
        if ($total_virements > 0) {
            $message = "Impossible de supprimer ce client car il a $total_virements virement(s) associé(s).";
            $message_type = "warning";
        } else {
            $stmt = $pdo->prepare("DELETE FROM utilisateurs WHERE id = ? AND (role != 'admin' OR role IS NULL)");
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() > 0) {
                $message = "Client supprimé avec succès.";
                $message_type = "success";
            } else {
                $message = "Client non trouvé ou impossible à supprimer.";
                $message_type = "danger";
            }
        }
    } catch (Exception $e) {
        $message = "Erreur lors de la suppression: " . $e->getMessage();
        $message_type = "danger";
    }
}

// Récupérer tous les clients
try {
    $stmt = $pdo->query("
        SELECT 
            u.id, u.nom, u.prenom, u.email, u.telephone, u.date_inscription,
            (SELECT COUNT(*) FROM virements WHERE user_id = u.id) as total_virements,
            (SELECT COUNT(*) FROM virements WHERE user_id = u.id AND statut = 'En attente') as virements_attente,
            (SELECT MAX(date_creation) FROM virements WHERE user_id = u.id) as dernier_virement
        FROM utilisateurs u 
        WHERE u.role != 'admin' OR u.role IS NULL
        ORDER BY u.id DESC
    ");
    $clients = $stmt->fetchAll();
} catch (Exception $e) {
    $clients = [];
    $message = "Erreur: " . $e->getMessage();
    $message_type = "danger";
}

$stats = [
    'total_clients' => count($clients),
    'total_avec_virements' => count(array_filter($clients, fn($c) => $c['total_virements'] > 0)),
    'total_virements' => array_sum(array_column($clients, 'total_virements')),
    'total_attente' => array_sum(array_column($clients, 'virements_attente'))
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des clients - Admin</title>
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
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #667eea;
        }
        
        .stat-card .number {
            font-size: 2rem;
            font-weight: bold;
            color: #333;
        }
        
        .stat-card .label {
            color: #666;
            font-size: 0.9rem;
        }
        
        .search-bar {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .search-bar input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            min-width: 200px;
        }
        
        .table-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow-x: auto;
        }
        
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
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
        
        .badge-virement {
            background: #e3f2fd;
            color: #0d47a1;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            display: inline-block;
            white-space: nowrap;
        }
        
        .badge-attente {
            background: #fff3cd;
            color: #856404;
            padding: 3px 8px;
            border-radius: 20px;
            font-size: 0.7rem;
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
        
        .btn-view { background: #17a2b8; }
        .btn-edit { background: #28a745; }
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
        
        .date-inscription {
            font-size: 0.7rem;
            color: #999;
        }
        
        @media (max-width: 992px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
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
            
            .stats-grid {
                gap: 15px;
            }
            
            .stat-card .number {
                font-size: 1.5rem;
            }
            
            .search-bar {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-bar input {
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
        
        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .admin-header h2 {
                font-size: 1.2rem;
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
    <a href="admin-nouveau-virement.php"><i class="fas fa-plus-circle"></i> Nouveau virement</a>
    <a href="admin-clients.php" class="active"><i class="fas fa-users"></i> Clients</a>
    <a href="admin-logos.php"><i class="fas fa-image"></i> Logos</a>
    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
</nav>
    </div>
    
    <div class="main-content">
        <div class="admin-header">
            <h2><i class="fas fa-users"></i> Gestion des clients</h2>
            <div class="user-info">
                <span><i class="fas fa-user"></i> <?= htmlspecialchars(($_SESSION['user_prenom'] ?? '') . ' ' . ($_SESSION['user_nom'] ?? '')) ?></span>
                <a href="logout.php" class="btn btn-danger btn-sm"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
            </div>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-<?= $message_type ?>"><?= $message ?></div>
        <?php endif; ?>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="number"><?= $stats['total_clients'] ?></div>
                <div class="label">Total clients</div>
            </div>
            <div class="stat-card">
                <div class="number"><?= $stats['total_avec_virements'] ?></div>
                <div class="label">Clients actifs</div>
            </div>
            <div class="stat-card">
                <div class="number"><?= $stats['total_virements'] ?></div>
                <div class="label">Virements total</div>
            </div>
            <div class="stat-card">
                <div class="number"><?= $stats['total_attente'] ?></div>
                <div class="label">En attente</div>
            </div>
        </div>
        
        <div class="search-bar">
            <i class="fas fa-search" style="color: #999;"></i>
            <input type="text" id="searchInput" placeholder="Rechercher par nom, prénom ou email...">
        </div>
        
        <div class="table-card">
            <h4>Liste des clients</h4>
            <div class="table-responsive">
                <table id="clientsTable">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Contact</th>
                            <th>Inscription</th>
                            <th>Statistiques</th>
                            <th>Dernier virement</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($clients)): ?>
                            <tr><td colspan="6" class="text-center">Aucun client</td></tr>
                        <?php else: ?>
                            <?php foreach ($clients as $client): ?>
                            <tr>
                                <td>
                                    <div class="client-name">
                                        <i class="fas fa-user-circle" style="color: #667eea;"></i>
                                        <?= htmlspecialchars($client['prenom'] . ' ' . $client['nom']) ?>
                                    </div>
                                    <div class="client-email">ID: #<?= $client['id'] ?></div>
                                </td>
                                <td>
                                    <div><i class="fas fa-envelope"></i> <?= htmlspecialchars($client['email']) ?></div>
                                    <?php if ($client['telephone']): ?>
                                        <div><i class="fas fa-phone"></i> <?= htmlspecialchars($client['telephone']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div><?= date('d/m/Y', strtotime($client['date_inscription'] ?? 'now')) ?></div>
                                    <div class="date-inscription"><?= date('H:i', strtotime($client['date_inscription'] ?? 'now')) ?></div>
                                </td>
                                <td>
                                    <span class="badge-virement">
                                        <i class="fas fa-exchange-alt"></i> <?= $client['total_virements'] ?>
                                    </span>
                                    <?php if ($client['virements_attente'] > 0): ?>
                                        <br><span class="badge-attente"><i class="fas fa-clock"></i> <?= $client['virements_attente'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= $client['dernier_virement'] ? date('d/m/Y', strtotime($client['dernier_virement'])) : 'Aucun' ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="admin-virements.php?client=<?= $client['id'] ?>" class="btn-icon btn-view" title="Voir virements">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="admin-modifier-client.php?id=<?= $client['id'] ?>" class="btn-icon btn-edit" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="?delete=<?= $client['id'] ?>" class="btn-icon btn-delete" title="Supprimer" 
                                           onclick="return confirm('Supprimer ce client ?')">
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
        $('#clientsTable tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
    </script>
</body>
</html>
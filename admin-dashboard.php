<?php
require_once 'config.php';

// Vérifier si l'utilisateur est connecté ET est admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Récupérer les statistiques
try {
    // Total des virements
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM virements");
    $total_virements = $stmt->fetch()['total'] ?? 0;
    
    // Total des clients (non admins)
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM utilisateurs WHERE role != 'admin' OR role IS NULL");
    $total_clients = $stmt->fetch()['total'] ?? 0;
    
    // Virements en attente
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM virements WHERE statut = 'En attente'");
    $virements_attente = $stmt->fetch()['total'] ?? 0;
    
    // Virements aujourd'hui
    $aujourdhui = date('Y-m-d');
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM virements WHERE DATE(date_creation) = ?");
    $stmt->execute([$aujourdhui]);
    $virements_aujourdhui = $stmt->fetch()['total'] ?? 0;
    
} catch (Exception $e) {
    $total_virements = 0;
    $total_clients = 0;
    $virements_attente = 0;
    $virements_aujourdhui = 0;
}

// Récupérer les 5 virements les plus récents
try {
    $stmt = $pdo->query("
        SELECT 
            v.*, 
            u.nom as client_nom, 
            u.prenom as client_prenom,
            u.email as client_email
        FROM virements v 
        LEFT JOIN utilisateurs u ON v.user_id = u.id 
        ORDER BY v.date_creation DESC 
        LIMIT 5
    ");
    $virements_recents = $stmt->fetchAll();
} catch (Exception $e) {
    $virements_recents = [];
}

// Récupérer les 10 derniers accès
try {
    $stmt_acces = $pdo->query("
        SELECT h.*, u.nom, u.prenom, u.email, v.code_swift as virement_code
        FROM historique_acces h
        LEFT JOIN utilisateurs u ON h.user_id = u.id
        LEFT JOIN virements v ON h.virement_id = v.id
        ORDER BY h.date_acces DESC
        LIMIT 10
    ");
    $acces_recents = $stmt_acces->fetchAll();
} catch (Exception $e) {
    $acces_recents = [];
}

// Traitement de la suppression
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM virements WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: admin-dashboard.php?msg=deleted");
        exit;
    } catch (Exception $e) {
        $error = "Erreur lors de la suppression";
    }
}

$message = '';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'deleted') $message = "Virement supprimé avec succès";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord Admin</title>
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
            line-height: 1.2;
        }
        
        .stat-card .label {
            color: #666;
            font-size: 0.9rem;
            margin-top: 5px;
        }
        
        .actions-bar {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            text-decoration: none;
            display: inline-block;
            transition: background 0.3s;
        }
        
        .btn-primary:hover {
            background: #5a67d8;
            color: white;
            text-decoration: none;
        }
        
        .btn-primary i {
            margin-right: 8px;
        }
        
        .table-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow-x: auto;
            margin-bottom: 25px;
        }
        
        .table-card h3 {
            margin-bottom: 20px;
            color: #333;
            font-size: 1.3rem;
        }
        
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 700px;
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
            white-space: nowrap;
        }
        
        .badge-valide { background: #d4edda; color: #155724; }
        .badge-attente { background: #fff3cd; color: #856404; }
        .badge-encours { background: #cce5ff; color: #004085; }
        .badge-refuse { background: #f8d7da; color: #721c24; }
        
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
            white-space: nowrap;
        }
        
        .code-swift {
            font-family: monospace;
            font-weight: bold;
            color: #667eea;
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
            
            .admin-header h2 {
                font-size: 1.3rem;
            }
            
            .user-info {
                width: 100%;
                justify-content: space-between;
            }
            
            .stats-grid {
                gap: 15px;
            }
            
            .stat-card {
                padding: 15px;
            }
            
            .stat-card .number {
                font-size: 1.5rem;
            }
            
            .btn-primary {
                width: 100%;
                text-align: center;
            }
        }
        
        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .admin-header {
                padding: 15px;
            }
            
            .user-info {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
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
    <a href="admin-dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a>
    <a href="admin-virements.php"><i class="fas fa-exchange-alt"></i> Virements</a>
    <a href="admin-cartes.php"><i class="fas fa-credit-card"></i> Cartes virtuelles</a>  <!-- NOUVEAU -->
    <a href="admin-nouveau-virement.php"><i class="fas fa-plus-circle"></i> Nouveau virement</a>
    <a href="admin-clients.php"><i class="fas fa-users"></i> Clients</a>
    <a href="admin-logos.php"><i class="fas fa-image"></i> Logos</a>
    <a href="logout.php" style="border-top: 1px solid rgba(255,255,255,0.1); margin-top: 50px;"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
</nav>
    </div>
    
    <div class="main-content">
        <div class="admin-header">
            <h2><i class="fas fa-tachometer-alt"></i> Tableau de bord</h2>
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
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="number"><?= $total_virements ?></div>
                <div class="label">Total virements</div>
            </div>
            <div class="stat-card">
                <div class="number"><?= $total_clients ?></div>
                <div class="label">Clients inscrits</div>
            </div>
            <div class="stat-card">
                <div class="number"><?= $virements_attente ?></div>
                <div class="label">En attente</div>
            </div>
            <div class="stat-card">
                <div class="number"><?= $virements_aujourdhui ?></div>
                <div class="label">Aujourd'hui</div>
            </div>
        </div>
        
        <!-- Actions -->
        <div class="actions-bar">
            <a href="admin-nouveau-virement.php" class="btn-primary">
                <i class="fas fa-plus-circle"></i> Créer un nouveau virement
            </a>
        </div>
        
        <!-- Virements récents -->
        <div class="table-card">
            <h3>Virements récents</h3>
            
            <?php if (empty($virements_recents)): ?>
                <p class="text-center text-muted">Aucun virement trouvé</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Code SWIFT</th>
                                <th>Client</th>
                                <th>Montant</th>
                                <th>Date</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($virements_recents as $v): ?>
                            <tr>
                                <td>#<?= $v['id'] ?></td>
                                <td><span class="code-swift"><?= htmlspecialchars($v['code_swift'] ?? 'N/A') ?></span></td>
                                <td>
                                    <div class="client-name">
                                        <?= htmlspecialchars($v['client_prenom'] ?? '') ?> <?= htmlspecialchars($v['client_nom'] ?? 'Client inconnu') ?>
                                    </div>
                                    <div class="client-email"><?= htmlspecialchars($v['client_email'] ?? '') ?></div>
                                </td>
                                <td><strong><?= number_format($v['montant'], 2) ?> <?= $v['devise'] ?></strong></td>
                                <td><?= date('d/m/Y', strtotime($v['date_creation'])) ?></td>
                                <td>
                                    <?php
                                    $badge_class = '';
                                    if ($v['statut'] == 'Validé') $badge_class = 'badge-valide';
                                    elseif ($v['statut'] == 'En attente') $badge_class = 'badge-attente';
                                    elseif ($v['statut'] == 'En cours') $badge_class = 'badge-encours';
                                    elseif ($v['statut'] == 'Refusé') $badge_class = 'badge-refuse';
                                    else $badge_class = 'badge-attente';
                                    ?>
                                    <span class="badge-statut <?= $badge_class ?>"><?= $v['statut'] ?></span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="admin-modifier-virement.php?id=<?= $v['id'] ?>" class="btn-icon btn-edit" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="admin-details-virement.php?id=<?= $v['id'] ?>" class="btn-icon btn-view" title="Voir">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="?delete=<?= $v['id'] ?>" class="btn-icon btn-delete" title="Supprimer" 
                                           onclick="return confirm('Supprimer ce virement ?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- SECTION ACCÈS RÉCENTS -->
        <div class="table-card">
            <h3><i class="fas fa-history"></i> Accès récents aux statuts</h3>
            
            <?php if (empty($acces_recents)): ?>
                <p class="text-center text-muted">Aucun accès enregistré</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Code SWIFT</th>
                                <th>Date d'accès</th>
                                <th>IP</th>
                                <th>Appareil</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($acces_recents as $acces): ?>
                            <tr>
                                <td>
                                    <div class="client-name">
                                        <?= htmlspecialchars($acces['prenom'] ?? '') ?> <?= htmlspecialchars($acces['nom'] ?? 'Client inconnu') ?>
                                    </div>
                                    <div class="client-email"><?= htmlspecialchars($acces['email'] ?? '') ?></div>
                                </td>
                                <td><span class="code-swift"><?= htmlspecialchars($acces['code_swift'] ?? 'N/A') ?></span></td>
                                <td><?= date('d/m/Y H:i:s', strtotime($acces['date_acces'])) ?></td>
                                <td><?= htmlspecialchars($acces['ip_adresse'] ?? '0.0.0.0') ?></td>
                                <td>
                                    <?php
                                    $user_agent = $acces['user_agent'] ?? '';
                                    if (strpos($user_agent, 'Mobile') !== false) {
                                        echo '<i class="fas fa-mobile-alt" title="Mobile"></i> Mobile';
                                    } elseif (strpos($user_agent, 'Tablet') !== false) {
                                        echo '<i class="fas fa-tablet-alt" title="Tablette"></i> Tablette';
                                    } else {
                                        echo '<i class="fas fa-desktop" title="Ordinateur"></i> Ordinateur';
                                    }
                                    ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const closeBtn = document.getElementById('closeSidebar');
    
    function openSidebar() {
        sidebar.classList.add('active');
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    function closeSidebar() {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    }
    
    menuToggle.addEventListener('click', openSidebar);
    overlay.addEventListener('click', closeSidebar);
    
    if (closeBtn) {
        closeBtn.addEventListener('click', closeSidebar);
    }
    
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            closeSidebar();
        }
    });
    </script>
</body>
</html>
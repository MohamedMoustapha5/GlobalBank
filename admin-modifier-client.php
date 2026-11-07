<?php
require_once 'config.php';

// Vérifier si l'utilisateur est connecté ET est admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? 0;
$message = '';
$message_type = '';

// Récupérer les infos du client
try {
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ? AND (role != 'admin' OR role IS NULL)");
    $stmt->execute([$id]);
    $client = $stmt->fetch();
    
    if (!$client) {
        header("Location: admin-clients.php?error=notfound");
        exit;
    }
} catch (Exception $e) {
    header("Location: admin-clients.php?error=db");
    exit;
}

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $sql = "UPDATE utilisateurs SET 
                nom = ?, prenom = ?, email = ?, telephone = ?
                WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST['nom'],
            $_POST['prenom'],
            $_POST['email'],
            $_POST['telephone'],
            $id
        ]);
        
        header("Location: admin-clients.php?msg=updated");
        exit;
        
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
    <title>Modifier client - Admin</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #f4f6f9;
        }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            position: fixed;
            width: 16.666667%;
        }
        .sidebar a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            padding: 15px 20px;
            display: block;
            transition: all 0.3s;
        }
        .main-content {
            margin-left: 16.666667%;
            padding: 30px;
        }
        .admin-header {
            background: white;
            padding: 15px 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .form-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 600px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="col-md-2 p-0 sidebar">
        <div style="padding: 30px 20px; font-size: 24px; font-weight: bold;">Admin</div>
        <nav>
            <a href="admin-dashboard.php"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a>
            <a href="admin-virements.php"><i class="fas fa-exchange-alt"></i> Virements</a>
            <a href="admin-nouveau-virement.php"><i class="fas fa-plus-circle"></i> Nouveau virement</a>
            <a href="admin-clients.php" class="active"><i class="fas fa-users"></i> Clients</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </nav>
    </div>
    
    <div class="main-content">
        <div class="admin-header">
            <h2><i class="fas fa-edit"></i> Modifier client #<?= $id ?></h2>
            <a href="admin-clients.php" class="btn btn-secondary">← Retour</a>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-<?= $message_type ?>"><?= $message ?></div>
        <?php endif; ?>
        
        <div class="form-card">
            <form method="POST">
                <div class="form-group">
                    <label>Nom</label>
                    <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($client['nom']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Prénom</label>
                    <input type="text" name="prenom" class="form-control" value="<?= htmlspecialchars($client['prenom']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($client['email']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Téléphone</label>
                    <input type="text" name="telephone" class="form-control" value="<?= htmlspecialchars($client['telephone']) ?>">
                </div>
                <button type="submit" class="btn btn-success">Enregistrer</button>
            </form>
        </div>
    </div>
</body>
</html>
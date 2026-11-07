<?php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? 0;

// Récupérer le virement
$stmt = $pdo->prepare("SELECT v.*, u.nom, u.prenom, u.email FROM virements v LEFT JOIN utilisateurs u ON v.user_id = u.id WHERE v.id = ?");
$stmt->execute([$id]);
$v = $stmt->fetch();

if (!$v) {
    header("Location: admin-virements.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Détails du virement #<?= $id ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Détails du virement #<?= $id ?></h2>
        <pre><?php print_r($v); ?></pre>
        <a href="admin-virements.php" class="btn btn-secondary">Retour</a>
    </div>
</body>
</html>
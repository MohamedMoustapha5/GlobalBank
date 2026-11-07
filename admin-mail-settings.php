<?php
require_once 'config.php';

// Vérifier si admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sauvegarder les paramètres
    $keys = [
        'mail_username', 'mail_password', 'mail_from', 'mail_from_name',
        'mail_host', 'mail_port', 'mail_secure', 'use_smtp', 'mail_smtp_debug'
    ];

    foreach ($keys as $k) {
        $v = isset($_POST[$k]) ? trim($_POST[$k]) : '';
        setConfig($k, $v);
    }

    $message = 'Paramètres SMTP enregistrés.';
    $message_type = 'success';

    // Option test email
    if (!empty($_POST['test_email'])) {
        $to = trim($_POST['test_email']);
        $subject = 'Test email depuis l\'admin';
        $body = '<p>Ceci est un test d\'envoi SMTP configuré via l\'interface d\'admin.</p>';
        $res = sendMailSMTP($to, $subject, $body);
        if ($res === true) {
            $message .= ' Test envoyé à ' . htmlspecialchars($to) . '.';
        } else {
            $message .= ' Envoi de test échoué: ' . htmlspecialchars($res);
            $message_type = 'danger';
        }
    }
}

// Charger valeurs existantes
$mail_username = getConfig('mail_username', $mail_username ?? '');
$mail_password = getConfig('mail_password', $mail_password ?? '');
$mail_from = getConfig('mail_from', $mail_from ?? '');
$mail_from_name = getConfig('mail_from_name', $mail_from_name ?? 'BANK OF AMERICA');
$mail_host = getConfig('mail_host', $mail_host ?? 'smtp.hostinger.com');
$mail_port = getConfig('mail_port', $mail_port ?? 587);
$mail_secure = getConfig('mail_secure', $mail_secure ?? 'tls');
$use_smtp = getConfig('use_smtp', $use_smtp ? '1' : '0');
$mail_smtp_debug = getConfig('mail_smtp_debug', $mail_smtp_debug ?? 2);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètres Mail - Admin</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>body{padding:20px}</style>
 </head>
<body>
<div class="container">
    <h3>Paramètres SMTP</h3>
    <?php if ($message): ?>
        <div class="alert alert-<?= $message_type ?>"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Utiliser SMTP</label>
            <select name="use_smtp" class="form-control">
                <option value="1" <?= $use_smtp == '1' ? 'selected' : '' ?>>Oui</option>
                <option value="0" <?= $use_smtp == '0' ? 'selected' : '' ?>>Non</option>
            </select>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Serveur SMTP (host)</label>
                <input type="text" name="mail_host" class="form-control" value="<?= htmlspecialchars($mail_host) ?>">
            </div>
            <div class="form-group col-md-2">
                <label>Port</label>
                <input type="text" name="mail_port" class="form-control" value="<?= htmlspecialchars($mail_port) ?>">
            </div>
            <div class="form-group col-md-4">
                <label>Sécurité</label>
                <select name="mail_secure" class="form-control">
                    <option value="tls" <?= $mail_secure == 'tls' ? 'selected' : '' ?>>TLS</option>
                    <option value="ssl" <?= $mail_secure == 'ssl' ? 'selected' : '' ?>>SSL</option>
                    <option value="" <?= $mail_secure == '' ? 'selected' : '' ?>>Aucun</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Nom d'utilisateur SMTP (email)</label>
                <input type="email" name="mail_username" class="form-control" value="<?= htmlspecialchars($mail_username) ?>" required>
            </div>
            <div class="form-group col-md-6">
                <label>Mot de passe SMTP</label>
                <input type="text" name="mail_password" class="form-control" value="<?= htmlspecialchars($mail_password) ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Adresse From</label>
                <input type="email" name="mail_from" class="form-control" value="<?= htmlspecialchars($mail_from) ?>">
            </div>
            <div class="form-group col-md-6">
                <label>Nom From</label>
                <input type="text" name="mail_from_name" class="form-control" value="<?= htmlspecialchars($mail_from_name) ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Niveau debug (0=off,1,2)</label>
            <input type="number" name="mail_smtp_debug" class="form-control" value="<?= htmlspecialchars($mail_smtp_debug) ?>">
        </div>

        <div class="form-group">
            <label>Envoyer un email de test (optionnel)</label>
            <input type="email" name="test_email" class="form-control" placeholder="destinataire@exemple.com">
        </div>

        <button class="btn btn-primary">Enregistrer et (optionnel) envoyer test</button>
        <a href="admin-virements.php" class="btn btn-secondary">Retour</a>
    </form>
</div>
</body>
</html>

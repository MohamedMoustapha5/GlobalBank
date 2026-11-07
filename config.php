<?php
// config.php
$host = 'localhost';
$dbname = 'globalbank';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Démarrer la session UNIQUEMENT si elle n'est pas déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Charger l'autoload Composer pour PHPMailer
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// Paramètres d'envoi email SMTP Hostinger (à personnaliser)
$use_smtp = true;
$mail_host = 'smtp.hostinger.com';
$mail_port = 587; // 587 ou 465 selon configuration
$mail_username = getenv('GLOBALBANK_MAIL_USERNAME') ?: '';
$mail_password = getenv('GLOBALBANK_MAIL_PASSWORD') ?: '';
$mail_secure = 'tls'; // 'ssl' ou 'tls'
$mail_from = getenv('GLOBALBANK_MAIL_FROM') ?: '';
$mail_from_name = 'BANK';

// Debug et log pour diagnostic SMTP
$mail_smtp_debug = 2; // 0=off, 1=client, 2=client+server
$mail_log_file = __DIR__ . '/logs/mail_debug.log';

// Inclure les fonctions (MAIS PAS init_lang.php ici)
require_once 'fonctions.php';

// Assurer les colonnes de date et motif du virement existent dans la table virements
if (function_exists('ensureVirementColumns')) {
    ensureVirementColumns();
}

// Créer la table de configuration si elle n'existe pas (pour les logos)
if (function_exists('createConfigurationTable')) {
    createConfigurationTable();
}

// Créer la table de configuration si elle n'existe pas
if (function_exists('createConfigurationTable')) {
    createConfigurationTable();
}

// Charger les paramètres SMTP depuis la table configuration si disponibles
if (function_exists('getConfig')) {
    $tmp = getConfig('use_smtp');
    if ($tmp !== null && $tmp !== '') $use_smtp = ($tmp === '1' || $tmp === 1 || strtolower($tmp) === 'true');
    $mail_host = getConfig('mail_host', $mail_host);
    $mail_port = (int)getConfig('mail_port', $mail_port);
    $mail_username = getConfig('mail_username', $mail_username);
    $mail_password = getConfig('mail_password', $mail_password);
    $mail_secure = getConfig('mail_secure', $mail_secure);
    $mail_from = getConfig('mail_from', $mail_from);
    $mail_from_name = getConfig('mail_from_name', $mail_from_name);
    $mail_smtp_debug = (int)getConfig('mail_smtp_debug', $mail_smtp_debug);
    $mail_log_file = getConfig('mail_log_file', $mail_log_file);
}
?>
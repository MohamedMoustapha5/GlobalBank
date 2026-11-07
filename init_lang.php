<?php
// init_lang.php - Initialisation de la langue (À inclure au début de chaque page)

// Ne pas redémarrer la session si elle existe déjà
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialiser la langue par défaut si pas définie
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'fr';
}

// Permet de changer de langue via ?lang=en ou ?lang=fr
if (isset($_GET['lang']) && in_array($_GET['lang'], ['fr', 'en'])) {
    $_SESSION['lang'] = $_GET['lang'];
    $current_url = strtok($_SERVER['REQUEST_URI'], '?');
    header("Location: " . $current_url);
    exit;
}

// Charger les traductions (NE charge PAS config.php)
$lang = [];
require_once 'lang.php';
$current_lang = $_SESSION['lang'];
$t = $lang[$current_lang] ?? $lang['fr'];

function trans($key) {
    global $t;
    return $t[$key] ?? $key;
}
?>
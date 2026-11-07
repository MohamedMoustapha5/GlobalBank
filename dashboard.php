<?php
require_once 'config.php';
require_once 'init_lang.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (($_SESSION['user_role'] ?? 'client') === 'admin') {
    header("Location: admin-dashboard.php");
    exit;
}

$prenom = trim($_SESSION['user_prenom'] ?? '');
$nom = trim($_SESSION['user_nom'] ?? '');
$nom_complet = trim($prenom . ' ' . $nom);
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($_SESSION['lang'] ?? 'fr', ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#172433">
    <title><?= htmlspecialchars(trans('espace_client'), ENT_QUOTES, 'UTF-8') ?> - UBS</title>
    <link rel="stylesheet" href="css/dashboard.css?v=5">
</head>
<body>
    <header class="topbar">
        <div class="topbar-inner">
            <div class="topbar-actions">
                <span class="welcome-user"><?= htmlspecialchars(trans('dashboard_bienvenue'), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars($nom_complet, ENT_QUOTES, 'UTF-8') ?></span>
                <a class="logout-link" href="logout.php"><?= htmlspecialchars(trans('deconnexion'), ENT_QUOTES, 'UTF-8') ?></a>
            </div>
        </div>
    </header>

    <main class="dashboard">
        <section class="welcome-panel" aria-labelledby="welcome-title">
            <div class="welcome-copy">
                <span class="eyebrow"><?= htmlspecialchars(trans('dashboard_espace_prive'), ENT_QUOTES, 'UTF-8') ?></span>
                <h1 id="welcome-title"><?= htmlspecialchars(trans('dashboard_bonjour'), ENT_QUOTES, 'UTF-8') ?><?= $prenom !== '' ? ' ' . htmlspecialchars($prenom, ENT_QUOTES, 'UTF-8') : '' ?></h1>
                <p><?= htmlspecialchars(trans('dashboard_intro'), ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <div class="welcome-mark" aria-hidden="true">
                <svg viewBox="0 0 64 64" fill="none">
                    <path d="M32 6 54 15v16c0 14-9 23-22 28C19 54 10 45 10 31V15L32 6Z" stroke="currentColor" stroke-width="2.5"/>
                    <path d="m22 32 7 7 14-15" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
        </section>

        <section class="services-section" aria-labelledby="services-title">
            <div class="section-heading">
                <div>
                    <span class="eyebrow"><?= htmlspecialchars(trans('dashboard_raccourcis'), ENT_QUOTES, 'UTF-8') ?></span>
                    <h2 id="services-title"><?= htmlspecialchars(trans('dashboard_services_titre'), ENT_QUOTES, 'UTF-8') ?></h2>
                </div>
                <p><?= htmlspecialchars(trans('dashboard_services_intro'), ENT_QUOTES, 'UTF-8') ?></p>
            </div>

            <div class="service-grid">
                <a class="service-card" href="verification.php">
                    <span class="service-icon icon-transfer" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none"><path d="M4 7h15m0 0-4-4m4 4-4 4M20 17H5m0 0 4 4m-4-4 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    <span class="service-title"><?= htmlspecialchars(trans('dashboard_suivi_titre'), ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="service-description"><?= htmlspecialchars(trans('dashboard_suivi_desc'), ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="service-arrow" aria-hidden="true">↗</span>
                </a>

                <a class="service-card" href="client-cartes.php">
                    <span class="service-icon icon-card" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none"><rect x="3" y="5" width="18" height="14" rx="2.5" stroke="currentColor" stroke-width="1.8"/><path d="M3 10h18M7 15h3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    </span>
                    <span class="service-title"><?= htmlspecialchars(trans('mes_cartes'), ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="service-description"><?= htmlspecialchars(trans('dashboard_cartes_desc'), ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="service-arrow" aria-hidden="true">↗</span>
                </a>

                <a class="service-card" href="client-rib.php">
                    <span class="service-icon icon-account" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none"><path d="M4 9.5h16M6.5 9.5V18m5.5-8.5V18m5.5-8.5V18M3 21h18M12 3 3 7v2h18V7l-9-4Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    <span class="service-title"><?= htmlspecialchars(trans('mon_rib'), ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="service-description"><?= htmlspecialchars(trans('dashboard_rib_desc'), ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="service-arrow" aria-hidden="true">↗</span>
                </a>
            </div>
        </section>

        <footer class="dashboard-footer">
            <span><?= htmlspecialchars(trans('dashboard_securite'), ENT_QUOTES, 'UTF-8') ?></span>
        </footer>
    </main>
</body>
</html>
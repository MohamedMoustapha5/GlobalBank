<?php
// 🔐 Démarrer la session
session_start();
require_once 'config.php';
require_once 'init_lang.php';

$message = trans('saisissez_code');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $code = trim($_POST["code"] ?? '');

    try {
        $stmt = $pdo->prepare("SELECT * FROM virements WHERE code_swift = ?");
        $stmt->execute([$code]);
        $virement = $stmt->fetch();

        if ($virement) {
            $_SESSION['authentifie'] = true;
            $_SESSION['code_utilise'] = $code;
            header("Location: statut-virement.php");
            exit;
        } else {
            $message = trans('code_incorrect');
        }
    } catch (Exception $e) {
        $message = trans('erreur_verification');
    }
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($_SESSION['lang'] ?? 'fr', ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(trans('verification_titre_page'), ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="verification.css?v=2">
</head>
<body>
    <div class="page-wrapper">
        <section class="verification-panel">
            <div class="header-row">
                <div class="logo-frame logo-left">
                    <img src="<?= htmlspecialchars(getActiveLogo()) ?>" alt="Logo UBS">
                </div>
                <div class="meta-group meta-right">
                    <a class="home-button" href="index.php">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1V10Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>Accueil</span>
                    </a>
                    <div class="meta-subtitle"><?= strtoupper(htmlspecialchars($_SESSION['lang'] ?? 'fr', ENT_QUOTES, 'UTF-8')) ?></div>
                </div>
            </div>

            <div class="content-block">
                <p class="intro-text"><?= htmlspecialchars(trans('verification_intro'), ENT_QUOTES, 'UTF-8') ?></p>

                <h1 class="page-title"><?= htmlspecialchars(trans('verification_titre'), ENT_QUOTES, 'UTF-8') ?></h1>
                <p class="page-subtitle"><?= htmlspecialchars(trans('verification_sous_titre'), ENT_QUOTES, 'UTF-8') ?></p>

                <?php if (!empty($message)) : ?>
                    <div class="message-box"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>

                <form method="POST" class="verify-form">
                    <div class="form-row">
                        <input type="text" name="code" class="verify-input" placeholder="<?= htmlspecialchars(trans('verification_placeholder'), ENT_QUOTES, 'UTF-8') ?>" required>
                        <button type="submit" class="verify-button"><?= trans('verifier') ?></button>
                    </div>
                </form>
            </div>
        </section>

        <p class="hero-footer"><?= htmlspecialchars(trans('verification_securite'), ENT_QUOTES, 'UTF-8') ?></p>
    </div>
</body>
</html>

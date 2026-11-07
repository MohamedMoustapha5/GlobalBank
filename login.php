<?php
require_once 'config.php';
require_once 'init_lang.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $mot_de_passe = $_POST['mot_de_passe'];

    if (empty($email) || empty($mot_de_passe)) {
        $message = "Veuillez remplir tous les champs.";
    } else {
        // Chercher l'utilisateur
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($mot_de_passe, $user['mot_de_passe'])) {
            // Connexion réussie
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nom'] = $user['nom'];
            $_SESSION['user_prenom'] = $user['prenom'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'] ?? 'client'; // Ajout du rôle
            
            // ✅ REDIRECTION SELON LE RÔLE
            if (isset($user['role']) && $user['role'] === 'admin') {
                header("Location: admin-dashboard.php"); // Admin va vers son dashboard
            } else {
                header("Location: dashboard.php");
            }
            exit;
        } else {
            $message = "Email ou mot de passe incorrect.";
        }
    }
}
?>

<!-- Le reste de votre HTML reste IDENTIQUE -->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - UBS</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .login-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .home-button {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 7px 12px;
            border: 1px solid rgba(255, 255, 255, 0.75);
            border-radius: 6px;
            background: #fff;
            color: #b21f2d;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            white-space: nowrap;
            transition: background-color 0.2s ease, box-shadow 0.2s ease;
        }

        .home-button:hover,
        .home-button:focus {
            background: #f8f9fa;
            color: #8f1924;
            text-decoration: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        .home-button:focus-visible {
            outline: 3px solid rgba(255, 255, 255, 0.65);
            outline-offset: 2px;
        }

        .home-button svg {
            width: 16px;
            height: 16px;
            flex: 0 0 auto;
        }

        @media (max-width: 360px) {
            .home-button {
                padding: 6px 8px;
                font-size: 0.82rem;
            }
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-danger text-white login-header">
                        <h4 class="mb-0">Connexion</h4>
                        <a class="home-button" href="index.php">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1V10Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>Accueil</span>
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-danger"><?= $message ?></div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Mot de passe</label>
                                <input type="password" name="mot_de_passe" class="form-control" required>
                            </div>
                            
                            <button type="submit" class="btn btn-danger btn-block">Se connecter</button>
                        </form>
                        
                        <p class="text-center mt-3">
                            Pas encore de compte ? <a href="register.php">Inscrivez-vous</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
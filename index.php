<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Initialisation - NE PAS inclure config.php si init_lang.php le fait déjà
// Mais init_lang.php n'inclut PAS config.php, donc on inclut les deux
require_once 'config.php';
require_once 'init_lang.php';

function homeText(string $french, string $english): string
{
    return ($_SESSION['lang'] ?? 'fr') === 'en' ? $english : $french;
}

// Vérifier si l'utilisateur est connecté
$est_connecte = isset($_SESSION['user_id']);
$user_prenom = $_SESSION['user_prenom'] ?? '';

// Le reste de votre code...
?>

<!doctype html>
<html lang="<?= htmlspecialchars($_SESSION['lang'] ?? 'fr', ENT_QUOTES, 'UTF-8') ?>">
  <head>
    <title><?= homeText('Accueil', 'Home') ?></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Assurez-vous d'inclure Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">        
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,700" rel="stylesheet">
    <link rel="shortcut icon" href="../images/favicon.png">
    <link rel="stylesheet" href="fonts/icomoon/style.css">

    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/jquery-ui.css">
    <link rel="stylesheet" href="css/owl.carousel.min.css">
    <link rel="stylesheet" href="css/owl.theme.default.min.css">
    <link rel="stylesheet" href="css/owl.theme.default.min.css">
     <link rel="shortcut icon" href="../images/favicon.png">

    <link rel="stylesheet" href="css/jquery.fancybox.min.css">
    <link rel="stylesheet" href="css/lang-switcher.css">

    <link rel="stylesheet" href="css/bootstrap-datepicker.css">

    <link rel="stylesheet" href="fonts/flaticon/font/flaticon.css">

    <link rel="stylesheet" href="css/aos.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/index.css">

    <style>
/* Style pour les dropdowns au survol */
.has-children:hover .dropdown {
    display: block !important;
}

.dropdown li a:hover {
    background-color: #f8f8f8;
    padding-left: 25px !important;
    transition: all 0.3s;
}

.site-menu > li > a:hover {
    color: #FF3131 !important;
}

/* Menu mobile actif */
.site-mobile-menu.active {
    right: 0 !important;
}

.site-mobile-menu-overlay.active {
    display: block !important;
}

/* Responsive */
@media (max-width: 1199px) {
    .d-none.d-xl-block {
        display: none !important;
    }
    
    .d-inline-block.d-xl-none {
        display: inline-block !important;
    }
}

@media (min-width: 1200px) {
    .site-mobile-menu,
    .site-mobile-menu-overlay {
        display: none !important;
    }
}
</style>
 
  </head>
  <body data-spy="scroll" data-target=".site-navbar-target" data-offset="300">
  

  <div id="overlayer"></div>
  <div class="loader">
    <div class="spinner-border text-primary" role="status">
      <span class="sr-only">Loading...</span>
    </div>
  </div>


  <div class="site-wrap">

    <div class="site-mobile-menu site-navbar-target" id="mobile-navigation">
      <div class="site-mobile-menu-header">
        <span class="site-mobile-menu-title">Menu</span>
        <button type="button" class="site-mobile-menu-close js-menu-toggle" aria-label="Fermer le menu">
          <span class="icon-close2" aria-hidden="true"></span>
        </button>
      </div>
      <div class="site-mobile-menu-body"></div>
    </div>
   
    
   <header class="site-navbar js-sticky-header site-navbar-target" role="banner">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-6 col-xl-2">
               <img src="<?php echo getActiveLogo(); ?>" alt="logo" class="responsive-logo" style="max-height: 50px;">
            </div>
            <div class="col-6 col-xl-10">
                <!-- Menu desktop horizontal -->
                <nav class="site-navigation position-relative text-right d-none d-xl-block" role="navigation">
                    <ul class="site-menu main-menu d-flex justify-content-end" style="list-style: none; margin: 0; padding: 0;">
                        <li style="margin-left: 30px;"><a href="#home-section" class="nav-link" style="color: #000; text-decoration: none; font-weight: 500; padding: 20px 0; display: block;"><?= trans('accueil') ?></a></li>
                        
                        <?php if ($est_connecte): ?>
    <li style="margin-left: 30px; position: relative;" class="has-children">
        <a href="#" class="nav-link"><?= trans('bonjour') ?> <?= htmlspecialchars($user_prenom) ?></a>
        <ul class="dropdown" style="position: absolute; top: 100%; left: 0; background: white; box-shadow: 0 5px 15px rgba(0,0,0,0.1); min-width: 200px; padding: 10px 0; display: none; list-style: none; z-index: 1000;">
            <li><a href="dashboard.php" style="color: #333; text-decoration: none; padding: 10px 20px; display: block;"><?= trans('mon_compte') ?></a></li>
            <li><a href="client-cartes.php" style="color: #333; text-decoration: none; padding: 10px 20px; display: block;"><?= trans('mes_cartes') ?></a></li>
            <li><a href="verification.php" style="color: #333; text-decoration: none; padding: 10px 20px; display: block;"><?= trans('verifier_statut') ?></a></li>
            <li><a href="client-rib.php" style="color: #333; text-decoration: none; padding: 10px 20px; display: block;"><?= trans('mon_rib') ?></a></li>
            <li><a href="logout.php" style="color: #FF3131; text-decoration: none; padding: 10px 20px; display: block;"><?= trans('deconnexion') ?></a></li>
        </ul>
    </li>
<?php else: ?>
                            <li style="margin-left: 30px; position: relative;" class="has-children">
                                 <a href="#about-section" class="nav-link"><?= trans('espace_client') ?> <span style="margin-left: 8px;"></span></a>
                                <ul class="dropdown" style="position: absolute; top: 100%; left: 0; background: white; box-shadow: 0 5px 15px rgba(0,0,0,0.1); min-width: 200px; padding: 10px 0; display: none; list-style: none; z-index: 1000;">
                                    <li><a href="login.php" style="color: #333; text-decoration: none; padding: 10px 20px; display: block;"><?= trans('connexion') ?></a></li>
                                    <li><a href="register.php" style="color: #333; text-decoration: none; padding: 10px 20px; display: block;"><?= trans('inscription') ?></a></li>
                                </ul>
                            </li>
                        <?php endif; ?>

                        <li style="margin-left: 30px; position: relative;" class="has-children">
                            <a href="#about-section" class="nav-link"><?= trans('a_propos') ?> <span style="margin-left: 8px;"></span></a>
                            <ul class="dropdown" style="position: absolute; top: 100%; left: 0; background: white; box-shadow: 0 5px 15px rgba(0,0,0,0.1); min-width: 200px; padding: 10px 0; display: none; list-style: none; z-index: 1000;">
                                <li><a href="#faq-section" style="color: #333; text-decoration: none; padding: 10px 20px; display: block;"><?= trans('faq') ?></a></li>
                                <li><a href="#services-section" style="color: #333; text-decoration: none; padding: 10px 20px; display: block;"><?= trans('services') ?></a></li>
                                <li><a href="#testimonials-section" style="color: #333; text-decoration: none; padding: 10px 20px; display: block;"><?= trans('temoignages') ?></a></li>
                            </ul>
                        </li>
                        
                        <li style="margin-left: 30px;"><a href="#contact-section" class="nav-link" style="color: #000; text-decoration: none; font-weight: 500; padding: 20px 0; display: block;"><?= trans('contact') ?></a></li>
                        <li style="margin-left: 30px;"><a href="verification.php" class="nav-link" style="color: #000; text-decoration: none; font-weight: 500; padding: 20px 0; display: block;"><?= trans('statut') ?></a></li>
                        
                        <!-- Sélecteur de langue -->
                        <li style="margin-left: 30px; padding: 15px 0;">
                            <div class="lang-switcher">
                                <a href="index.php?lang=fr" class="<?= ($_SESSION['lang'] ?? 'fr') === 'fr' ? 'active' : 'inactive' ?>" title="Français">🇫🇷 FR</a>
                                <a href="index.php?lang=en" class="<?= ($_SESSION['lang'] ?? 'fr') === 'en' ? 'active' : 'inactive' ?>" title="English">🇬🇧 EN</a>
                            </div>
                        </li>
                    </ul>
                </nav>
                
                <!-- Bouton hamburger pour mobile -->
                <div class="d-inline-block d-xl-none float-right" style="padding: 15px 0;">
                    <a href="#" class="site-menu-toggle js-menu-toggle" aria-controls="mobile-navigation" aria-expanded="false" aria-label="Ouvrir le menu de navigation">
                        <span class="icon-menu">☰</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="site-mobile-menu-overlay"></div>
    
    <div class="site-blocks-cover overlay" style="background-image: url(images/m4.jpg); background-repeat: no-repeat; background-size: cover;" data-aos="fade" id="home-section">
    <div class="container">
        <div class="row align-items-center justify-content-center">
            <div class="col-md-10 mt-lg-5">
                <div class="single-text owl-carousel">

                    <!-- Premier slide -->
                    <div class="slide" id="slide1">
                        <h1 class="text-uppercase text-center" data-aos="fade-up" style="margin-top: 20px;"><?= homeText('Une banque qui vous simplifie la vie.', 'A bank that makes life easier.') ?></h1>
                        <p class="mb-4 desc text-center" data-aos="fade-up" data-aos-delay="100"><?= homeText('Des solutions financières simples et transparentes, conçues pour vous accompagner à chaque étape de votre parcours.', 'Simple, transparent financial solutions designed to support you at every step of your journey.') ?></p>
                    </div>

                    <!-- Deuxième slide -->
                    <div class="slide">
                        <h1 class="text-uppercase text-center" data-aos="fade-up" style="margin-top: 20px;"><?= homeText('Des services adaptés', 'Services tailored to you') ?></h1>
                        <p class="mb-4 desc text-center" data-aos="fade-up" data-aos-delay="100"><?= homeText('Découvrez nos offres pour vous accompagner dans vos projets, que ce soit pour vos comptes ou vos prêts.', 'Discover our services to support your plans, whether you need an account or a loan.') ?></p>
                    </div>

                    <!-- Troisième slide -->
                    <div class="slide">
                        <h1 class="text-uppercase text-center" data-aos="fade-up" style="margin-top: 20px;"><?= homeText('Financement rapide', 'Fast financing') ?></h1>
                        <p class="mb-4 desc text-center" data-aos="fade-up" data-aos-delay="100"><?= homeText('Obtenez un financement sur mesure pour vos projets personnels ou professionnels.', 'Get tailored financing for your personal or professional projects.') ?></p>
                     </div>

                    <!-- Quatrième slide -->
                    <div class="slide">
                        <h1 class="text-uppercase text-center" data-aos="fade-up" style="margin-top: 20px;"><?= homeText('Épargnez avec nous', 'Save with us') ?></h1>
                        <p class="mb-4 desc text-center" data-aos="fade-up" data-aos-delay="100"><?= homeText('Profitez de taux compétitifs pour faire fructifier votre épargne.', 'Benefit from competitive rates to grow your savings.') ?></p>
                    </div>
                    
                </div>

                <div class="fixed-form mt-4">
                  <div>
                      <h2 class="mb-2"><?= homeText('Avez-vous un code SWIFT ?', 'Do you have a SWIFT code?') ?></h2>
                      <p><?= homeText('Veuillez vérifier si votre transaction a été initiée.', 'Check whether your transaction has been initiated.') ?></p>
                  </div>
                  <a class="btn" href="verification.php"><?= homeText('Vérifier votre statut', 'Check your status') ?></a>
               </div>                
            </div>
        </div>
    </div>

    </div>

<script>  
    function statut(){
       window.location.href ="html/verification.html";
    }

</script>

<div class="site-section cta-big-image" id="about-section">
  <div class="container">
    <div class="row mb-5 justify-content-center">
      <div class="col-md-8 text-center">
        <h2 class="section-title mb-3" data-aos="fade-up"><?= homeText('Une Expérience Inoubliable', 'An Unforgettable Experience') ?></h2>
        <p class="lead" data-aos="fade-up" data-aos-delay="100">
          <?= homeText('Chez Banks, nous réinventons votre quotidien avec un accompagnement personnalisé, une technologie intuitive et une attention de chaque instant. Votre tranquillité d’esprit est au cœur de nos priorités.', 'At Banks, we transform your everyday banking with personalized support, intuitive technology and attentive service. Your peace of mind is our priority.') ?>
        </p>
      </div>
    </div>
    <div class="row">
      <div class="col-lg-6 mb-5" data-aos="fade-up">
        <figure class="circle-bg">
          <img src="images/assistant.png" alt="UBSb" class="img-fluid">
        </figure>
      </div>
      <div class="col-lg-5 ml-auto" data-aos="fade-up" data-aos-delay="100">
        <h3 class="text-black mb-4" style="color: #FF3131;"><?= homeText('Des Solutions Qui Vous Ressemblent', 'Solutions That Fit You') ?></h3>
        <p>
          <?= homeText('Parce que chaque personne est unique, nous prenons le temps de vous écouter pour vous proposer des réponses simples, claires et parfaitement adaptées à vos projets, petits ou grands.', 'Because everyone is unique, we take the time to listen and offer clear, straightforward solutions tailored to your plans, big or small.') ?>
        </p>
        <p>
          <?= homeText('Banks, c’est la promesse d’un service humain, d’une relation de confiance, et d’une équipe à vos côtés à chaque étape de votre parcours.', 'Banks stands for personal service, a relationship built on trust and a team by your side at every step.') ?>
        </p>
      </div>
    </div>
  </div>
</div>

<div class="site-section" id="next">
  <div class="row mb-5 justify-content-center">
    <div class="col-md-8 text-center">
      <h2 class="section-title mb-3" data-aos="fade-up" style="color: #FF3131;"><?= homeText('Un Engagement Sincère pour un Monde Meilleur', 'A Genuine Commitment to a Better World') ?></h2>
      <p class="lead" data-aos="fade-up" data-aos-delay="100">
        <?= homeText('Nous croyons au pouvoir de l’action collective. C’est pourquoi nous soutenons activement des projets qui font la différence, et qui participent à construire un avenir plus solidaire et plus juste.', 'We believe in the power of collective action. That is why we actively support projects that make a difference and help build a fairer, more inclusive future.') ?>
      </p>
    </div>
  </div>
  <div class="container">
    <div class="row">
      <div class="col-lg-6 mb-5" data-aos="fade-up">
        <figure class="circle-bg">
          <img src="images/imageb5.jpg" alt="UBSb Community Engagement" class="img-fluid">
        </figure>
      </div>
      <div class="col-lg-5 ml-auto" data-aos="fade-up" data-aos-delay="100">
        <div class="mb-4">
          <h3 class="h3 mb-4 text-black"><?= homeText('Nos Valeurs : Proximité et Impact Positif', 'Our Values: Connection and Positive Impact') ?></h3>
          <p>
            <?= homeText('Nous mettons tout en œuvre pour accompagner des initiatives porteuses d’espoir et de changement. Chaque geste compte, chaque action est pensée pour durer.', 'We do everything we can to support initiatives that bring hope and change. Every contribution matters, and every action is designed to last.') ?>
          </p>
        </div>
        <div class="mb-4">
          <ul class="list-unstyled ul-check success">
            <li><?= homeText('Appui aux projets citoyens et locaux', 'Support for local community projects') ?></li>
            <li><?= homeText("Encouragement à l'apprentissage pour tous", 'Promoting learning opportunities for everyone') ?></li>
            <li><?= homeText('Collaboration avec des acteurs engagés', 'Working with committed partners') ?></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>

  <!-- Section d'inscription avec image de fond -->
  <section class="signup-section" style="background-image: url(images/m4.jpg); background-repeat: no-repeat; background-size: cover;">
    <div class="container">
      <div class="row align-items-center justify-content-center" style="height: 60vh;"> <!-- Ajustement de la hauteur -->
        <div class="col-md-8 text-center">
          <h1 class="text-uppercase text-white" data-aos="fade-up"><?= homeText('Avez-vous un code SWIFT ?', 'Do you have a SWIFT code?') ?></h1>
          <p class="mb-4 desc text-white" data-aos="fade-up" data-aos-delay="100" style="color:#000;"><?= homeText('Vérifiez votre statut pour savoir si la transaction a été initiée.', 'Check your status to see whether the transaction has been initiated.') ?></p>
          
          <!-- Formulaire d'inscription avec champ email et bouton -->
          <div class="d-flex justify-content-center" data-aos="fade-up" data-aos-delay="100">
            <form action="verification.php" class="sign-up-form d-flex align-items-center" data-aos="fade-up" data-aos-delay="200" style="background-color: rgb(255, 255, 255,0.6); padding: 10px; border-radius: 10px;">
              <input type="submit" value="<?= homeText('Vérifier votre statut', 'Check your status') ?>" style="background-color: #FF3131; color: white; padding: 10px 20px; border-radius: 5px; border: none;">
            </form>                
          </div>
        </div>
      </div>
    </div>
  </section>


    
    <section class="site-section border-bottom bg-light" id="services-section">
      <div class="container">
        <div class="row mb-5 justify-content-center">
          <div class="col-md-7 text-center">
            <h2 class="section-title mb-3"><?= homeText('Nos Services', 'Our Services') ?></h2>
            <p class="lead"><?= homeText('Explorez nos solutions financières sur mesure, conçues pour répondre à vos attentes et simplifier la gestion de vos finances.', 'Explore our tailored financial solutions, designed to meet your needs and make managing your finances easier.') ?></p>
          </div>
        </div>
        <div class="row align-items-stretch">
          <div class="col-md-6 col-lg-4 mb-4 mb-lg-4" data-aos="fade-up">
            <div class="unit-4">
              <div class="unit-4-icon">
                <img src="images/flaticon-svg/svg/001-wallet.svg" alt="<?= homeText('Compte courant', 'Current account') ?>" class="img-fluid w-25 mb-4">
              </div>
              <div>
                <h3><?= homeText('Compte courant', 'Current account') ?></h3>
                <p><?= homeText('Gérez facilement vos finances quotidiennes grâce à notre compte courant, offrant un accès rapide et sécurisé à votre argent à tout moment.', 'Manage your day-to-day finances with a current account that gives you fast, secure access to your money whenever you need it.') ?></p>
                <p><a href="#" style="color: #FF3131;"><?= homeText('En savoir plus', 'Learn more') ?></a></p>
              </div>
            </div>
          </div>
          
          <div class="col-md-6 col-lg-4 mb-4 mb-lg-4" data-aos="fade-up" data-aos-delay="100">
            <div class="unit-4">
              <div class="unit-4-icon">
                <img src="images/flaticon-svg/svg/003-notes.svg" alt="<?= homeText("Compte d'épargne", 'Savings account') ?>" class="img-fluid w-25 mb-4">
              </div>
              <div>
                <h3><?= homeText("Compte d'épargne", 'Savings account') ?></h3>
                <p><?= homeText('Faites fructifier vos économies avec un taux d’intérêt attractif tout en assurant la sécurité de vos fonds pour vos projets futurs.', 'Grow your savings with an attractive interest rate while keeping your funds secure for future plans.') ?></p>
                <p><a href="#" style="color: #FF3131;"><?= homeText('En savoir plus', 'Learn more') ?></a></p>
              </div>
            </div>
          </div>
          
          <div class="col-md-6 col-lg-4 mb-4 mb-lg-4" data-aos="fade-up" data-aos-delay="200">
            <div class="unit-4">
              <div class="unit-4-icon">
                <img src="images/flaticon-svg/svg/002-rich.svg" alt="<?= homeText('Prêts personnalisés', 'Personal loans') ?>" class="img-fluid w-25 mb-4">
              </div>
              <div>
                <h3><?= homeText('Prêts Personnalisés', 'Personalized loans') ?></h3>
                <p><?= homeText('Financer vos projets n’a jamais été aussi simple. Obtenez un prêt sur mesure, adapté à vos besoins et à vos ambitions.', 'Financing your plans has never been easier. Get a loan tailored to your needs and ambitions.') ?></p>
                <p><a href="#" style="color: #FF3131;"><?= homeText('En savoir plus', 'Learn more') ?></a></p>
              </div>
            </div>
          </div>
    
          <div class="col-md-6 col-lg-4 mb-4 mb-lg-4" data-aos="fade-up">
            <div class="unit-4">
              <div class="unit-4-icon">
                <img src="images/flaticon-svg/svg/003-notes.svg" alt="<?= homeText('Cartes de crédit', 'Credit cards') ?>" class="img-fluid w-25 mb-4">
              </div>
              <div>
                <h3><?= homeText('Services de cartes de crédit', 'Credit card services') ?></h3>
                <p><?= homeText('Accédez à des cartes de crédit flexibles et profitez d’avantages exclusifs pour simplifier la gestion de vos paiements.', 'Enjoy flexible credit cards and exclusive benefits that make managing your payments easier.') ?></p>
                <p><a href="#" style="color: #FF3131;"><?= homeText('En savoir plus', 'Learn more') ?></a></p>
              </div>
            </div>
          </div>
          
          <div class="col-md-6 col-lg-4 mb-4 mb-lg-4" data-aos="fade-up" data-aos-delay="100">
            <div class="unit-4">
              <div class="unit-4-icon">
                <img src="images/flaticon-svg/svg/004-cart.svg" alt="<?= homeText('Paiement en ligne', 'Online payments') ?>" class="img-fluid w-25 mb-4">
              </div>
              <div>
                <h3><?= homeText('Paiement de factures en ligne', 'Online bill payments') ?></h3>
                <p><?= homeText('Réglez vos factures en ligne de manière rapide et sécurisée, où que vous soyez, grâce à notre plateforme intuitive.', 'Pay your bills quickly and securely from anywhere with our intuitive platform.') ?></p>
                <p><a href="#" style="color: #FF3131;"><?= homeText('En savoir plus', 'Learn more') ?></a></p>
              </div>
            </div>
          </div>
          
          <div class="col-md-6 col-lg-4 mb-4 mb-lg-4" data-aos="fade-up" data-aos-delay="200">
            <div class="unit-4">
              <div class="unit-4-icon">
                <img src="images/flaticon-svg/svg/005-megaphone.svg" alt="<?= homeText("Outils d'épargne et planification", 'Savings and planning tools') ?>" class="img-fluid w-25 mb-4">
              </div>
              <div>
                <h3><?= homeText("Outils d'épargne et de planification financière", 'Savings and financial planning tools') ?></h3>
                <p><?= homeText('Planifiez sereinement votre avenir financier avec nos outils spécialisés qui vous aident à épargner et investir de manière efficace.', 'Plan your financial future with confidence using tools that help you save and invest effectively.') ?></p>
                <p><a href="#" style="color: #FF3131;"><?= homeText('En savoir plus', 'Learn more') ?></a></p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
            
  


    <section class="site-section">
      <div class="container">
    
        <div class="row mb-5 justify-content-center">
          <div class="col-md-7 text-center">
            <h2 class="section-title mb-3" data-aos="fade-up" data-aos-delay=""><?= homeText('Comment ça marche', 'How It Works') ?></h2>
            <p class="lead" data-aos="fade-up" data-aos-delay="100"><?= homeText('Chez nous, nous avons conçu un parcours client intuitif et sécurisé pour simplifier la gestion de vos finances. Faites-nous confiance pour un accompagnement sur-mesure à chaque étape.', 'We have designed an intuitive, secure customer journey to make managing your finances easier. Count on us for tailored support at every step.') ?></p>
          </div>
        </div>
        
        <div class="row align-items-lg-center">
          <div class="col-lg-6 mb-5" data-aos="fade-up" data-aos-delay="">
            <div class="owl-carousel slide-one-item-alt">
              <img src="images/fille2.jpg" alt="Image" class="img-fluid">
              <img src="images/imagesecu.jpg" alt="Image" class="img-fluid">
              <img src="images/assistant.png" alt="Image" class="img-fluid">
            </div>
            <div class="custom-direction">
              <a href="#" class="custom-prev" ><span><span class="icon-keyboard_backspace"></span></span></a><a href="#" class="custom-next"><span><span class="icon-keyboard_backspace"></span></span></a>
            </div>
          </div>
    
          <div class="col-lg-5 ml-auto" data-aos="fade-up" data-aos-delay="100">
            <div class="owl-carousel slide-one-item-alt-text">
              <div>
                <h2 class="section-title mb-3" style="color: #000;"><?= homeText('01. Démarche 100% en ligne', '01. 100% online process') ?></h2>
                <p><?= homeText('Ouvrez un compte ou faites une demande de prêt directement en ligne en quelques clics. Notre interface intuitive garantit un processus simple et rapide, sans tracas.', 'Open an account or apply for a loan online in just a few clicks. Our intuitive interface makes the process simple and quick.') ?></p>
                <p><a href="#" class="btn btn-primary mr-2 mb-2" style="color: #fff;"><?= homeText('En savoir plus', 'Learn more') ?></a></p>
              </div>
              
              <div>
                <h2 class="section-title mb-3" style="color: #000;"><?= homeText('02. Sécurité et validation rapide', '02. Security and fast approval') ?></h2>
                <p><?= homeText('Votre demande est traitée avec les plus hauts standards de sécurité. Nos équipes valident rapidement vos informations pour vous offrir un service réactif et fiable.', 'Your application is handled using the highest security standards. Our teams promptly verify your information to provide a responsive, reliable service.') ?></p>
                <p><a href="#" class="btn btn-primary mr-2 mb-2" style="color: #fff;"><?= homeText('En savoir plus', 'Learn more') ?></a></p>
              </div>
              
              <div>
                <h2 class="section-title mb-3" style="color: #000;"><?= homeText('03. Offres personnalisées', '03. Personalized offers') ?></h2>
                <p><?= homeText('Bénéficiez d’offres sur mesure, adaptées à vos objectifs. Que vous souhaitiez épargner ou emprunter, nous vous accompagnons avec des solutions flexibles et avantageuses.', 'Benefit from offers tailored to your goals. Whether you want to save or borrow, we provide flexible solutions to support you.') ?></p>
                <p><a href="#" class="btn btn-primary mr-2 mb-2" style="color: #fff;"><?= homeText('En savoir plus', 'Learn more') ?></a></p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
        

    <section class="site-section testimonial-wrap" id="testimonials-section" data-aos="fade">
      <div class="container">
        <div class="row mb-5">
          <div class="col-12 text-center">
            <h2 class="section-title mb-3" style="color: #000;"><?= homeText('Témoignages', 'Testimonials') ?></h2>
          </div>
        </div>
      </div>
      <div class="slide-one-item home-slider owl-carousel">
        
        <div>
          <div class="testimonial">
            <blockquote class="mb-5">
              <p>&ldquo;<?= homeText("Grâce à UBSb, j'ai pu gérer mes finances avec une tranquillité d'esprit totale. Leurs solutions sont fiables et adaptées à mes besoins spécifiques.", 'Thanks to UBSb, I have been able to manage my finances with complete peace of mind. Their solutions are reliable and tailored to my specific needs.') ?>&rdquo;</p>
            </blockquote>
            <figure class="mb-4 d-flex align-items-center justify-content-center">
              <div><img src="images/fille1.jpg" alt="Image" class="w-50 img-fluid mb-3"></div>
              <div>
                <h3>Caroline Dupuis</h3>
                <p><?= homeText('Entrepreneuse', 'Entrepreneur') ?></p>
              </div>
            </figure>
          </div>
        </div>
    
        <div>
          <div class="testimonial">
            <blockquote class="mb-5">
              <p>&ldquo;<?= homeText("En tant qu'ingénieur, je recherche l'efficacité et la rapidité. UBSb a parfaitement compris mes attentes avec un service rapide et des conseils précis.", 'As an engineer, I value efficiency and speed. UBSb understood exactly what I needed, with fast service and clear advice.') ?>&rdquo;</p>
            </blockquote>
            <figure class="mb-4 d-flex align-items-center justify-content-center">
              <div><img src="images/person_1.jpg" alt="Image" class="w-50 img-fluid mb-3"></div>
              <div>
                <h3>Thomas Bernard</h3>
                <p><?= homeText('Ingénieur', 'Engineer') ?></p>
              </div>
            </figure>
          </div>
        </div>
    
        <div>
          <div class="testimonial">
            <blockquote class="mb-5">
              <p>&ldquo;<?= homeText("Leurs solutions financières sur mesure m'ont permis de réaliser mes projets de manière fluide. UBSb est plus qu'un simple partenaire financier, c'est un véritable soutien.", 'Their tailored financial solutions helped me bring my plans to life. UBSb is more than a financial partner; it is a true source of support.') ?>&rdquo;</p>
            </blockquote>
            <figure class="mb-4 d-flex align-items-center justify-content-center">
              <div><img src="images/person_2.jpg" alt="Image" class="w-50 img-fluid mb-3"></div>
              <div>
                <h3>Anne Dubois</h3>
                <p><?= homeText('Architecte', 'Architect') ?></p>
              </div>
            </figure>
          </div>
        </div>
    
        <div>
          <div class="testimonial">
            <blockquote class="mb-5">
              <p>&ldquo;<?= homeText("Le service client est exceptionnel. Je me sens écouté, et mes besoins sont toujours pris en compte. C'est exactement ce que je recherchais dans une banque.", 'The customer service is exceptional. I feel heard, and my needs are always considered. This is exactly what I was looking for in a bank.') ?>&rdquo;</p>
            </blockquote>
            <figure class="mb-4 d-flex align-items-center justify-content-center">
              <div><img src="images/home1.jpg" alt="Image" class="w-50 img-fluid mb-3"></div>
              <div>
                <h3>Paul Martin</h3>
                <p><?= homeText('Médecin', 'Doctor') ?></p>
              </div>
            </figure>
          </div>
        </div>
    
      </div>
    </section>
       
    <section class="site-section" id="about-section">
      <div class="container">
        <div class="row">
          <div class="col-lg-6 mb-5" data-aos="fade-up" data-aos-delay="">
            <figure class="circle-bg">
              <img src="images/imagesecu.jpg" alt="UBSb" class="img-fluid">
            </figure>
          </div>
          <div class="col-lg-5 ml-auto" data-aos="fade-up" data-aos-delay="100">
            <div class="row">
              <div class="col-12 mb-4" data-aos="fade-up" data-aos-delay="">
                <div class="unit-4 d-flex">
                  <div class="unit-4-icon mr-4 mb-3"><span class="text-primary flaticon-head"></span></div>
                  <div>
                    <h3><?= homeText('Prêts bancaires flexibles', 'Flexible bank loans') ?></h3>
                    <p><?= homeText('Chez nous, nous comprenons que chaque projet est unique. Nos prêts personnalisés vous offrent la flexibilité nécessaire pour réaliser vos ambitions, que ce soit pour une nouvelle maison, un investissement ou une entreprise.', 'We understand that every plan is unique. Our personalized loans give you the flexibility to achieve your goals, whether you are buying a home, investing or starting a business.') ?></p>
                    <p class="mb-0"><a href="#" style="color: #FF3131;"><?= homeText('En savoir plus', 'Learn more') ?></a></p>
                  </div>
                </div>
              </div>
              <div class="col-12 mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="unit-4 d-flex">
                  <div class="unit-4-icon mr-4 mb-3"><span class="text-primary flaticon-smartphone"></span></div>
                  <div>
                    <h3><?= homeText('Conseils bancaires sur mesure', 'Tailored banking advice') ?></h3>
                    <p><?= homeText('Nos experts sont là pour vous accompagner dans toutes vos décisions financières. Nous proposons des consultations bancaires adaptées à vos besoins, avec des solutions innovantes et sécurisées pour garantir votre succès financier.', 'Our experts are here to support your financial decisions. We offer banking consultations tailored to your needs, with innovative and secure solutions to help you succeed.') ?></p>
                    <p class="mb-0"><a href="#" style="color: #FF3131;"><?= homeText('En savoir plus', 'Learn more') ?></a></p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
      

    <section class="site-section" id="faq-section">
      <div class="container">
        <div class="row mb-5">
          <div class="col-12 text-center" data-aos="fade">
            <h2 class="section-title mb-3" style="color: #000;"><?= homeText('Questions Fréquentes', 'Frequently Asked Questions') ?></h2>
          </div>
        </div>
    
        <div class="row">
          <div class="col-md-6 col-lg-4 mb-4 mb-lg-4" data-aos="fade-up" data-aos-delay="">
            <div class="h-entry">
              <h2 class="font-size-regular"><a href="#" style="color: #FF3131;"><?= homeText('Pourquoi devrais-je choisir UBSb ?', 'Why should I choose UBSb?') ?></a></h2>
              <p><?= homeText('UBSb combine une expertise financière de premier plan avec des solutions flexibles adaptées à vos besoins. Que vous soyez un particulier ou une entreprise, nous vous offrons la sécurité, la rapidité et la personnalisation pour gérer vos finances avec confiance.', 'UBSb combines financial expertise with flexible solutions tailored to your needs. Whether you are an individual or a business, we offer the security, speed and personal service to help you manage your finances with confidence.') ?></p>
            </div>
          </div>
          <div class="col-md-6 col-lg-4 mb-4 mb-lg-4" data-aos="fade-up" data-aos-delay="100">
            <div class="h-entry">
              <h2 class="font-size-regular"><a href="#" style="color: #FF3131;"><?= homeText('Comment puis-je obtenir un prêt facilement ?', 'How can I get a loan easily?') ?></a></h2>
              <p><?= homeText('Avec UBSb, il vous suffit de soumettre votre demande en ligne. Nos experts traiteront votre dossier rapidement pour vous offrir des solutions de prêt personnalisées, avec des conditions flexibles et adaptées à votre situation.', 'With UBSb, simply submit your application online. Our experts will review it promptly and offer personalized loan solutions with flexible terms suited to your situation.') ?></p>
            </div>
          </div>
          <div class="col-md-6 col-lg-4 mb-4 mb-lg-4" data-aos="fade-up" data-aos-delay="200">
            <div class="h-entry">
              <h2 class="font-size-regular"><a href="#" style="color: #FF3131;"><?= homeText('La banque propose-t-elle des services numériques ?', 'Does the bank offer digital services?') ?></a></h2>
              <p><?= homeText('Absolument ! Notre plateforme numérique vous permet de gérer vos comptes, suivre vos transactions, et effectuer des paiements en toute sécurité depuis votre smartphone ou ordinateur, où que vous soyez.', 'Absolutely! Our digital platform lets you manage your accounts, track transactions and make secure payments from your phone or computer, wherever you are.') ?></p>
            </div>
          </div>
        </div>
      </div>
    </section>
    <section class="signup-section" style="background-image: url(images/m4.jpg); background-repeat: no-repeat; background-size: cover;">
      <div class="container">
        <div class="row align-items-center justify-content-center" style="height: 60vh;"> <!-- Ajustement de la hauteur -->
          <div class="col-md-8 text-center">
            <h1 class="text-uppercase text-white" data-aos="fade-up"><?= homeText('Avez-vous un code SWIFT ?', 'Do you have a SWIFT code?') ?></h1>
            <p class="mb-4 desc text-white" data-aos="fade-up" data-aos-delay="100" style="color:#000;"><?= homeText('Vérifiez votre statut pour savoir si la transaction a été initiée.', 'Check your status to see whether the transaction has been initiated.') ?></p>
            
            <!-- Formulaire d'inscription avec champ email et bouton -->
            <div class="d-flex justify-content-center" data-aos="fade-up" data-aos-delay="100">
              <form action="verification.php" class="sign-up-form d-flex align-items-center" data-aos="fade-up" data-aos-delay="200" style="background-color: rgb(255, 255, 255,0.6); padding: 10px; border-radius: 10px;">
                <input type="submit" class="btn" value="<?= homeText('Vérifier votre statut', 'Check your status') ?>" style="background-color: #FF3131; color: white; padding: 10px 20px; border-radius: 5px; border: none;">
              </form>                
            </div>
          </div>
        </div>
      </div>
    </section>
  
  
      <footer class="site-footer" id="contact-section">
      <div class="container">
        <div class="row">
    
          <!-- À Propos Section -->
          <div class="col-md-5 mb-4">
            <h2 class="footer-heading"><?= homeText('À propos de nous', 'About us') ?></h2>
            <p><?= homeText('UBSb vous accompagne dans toutes vos démarches bancaires avec des solutions adaptées à vos besoins, que vous soyez particulier ou professionnel.', 'UBSb supports you with all your banking needs through solutions tailored to you, whether you are an individual or a business.') ?></p>
          </div>
    
          <!-- Navigation Section -->
          <div class="col-md-3 ml-auto mb-4">
            <h2 class="footer-heading"><?= homeText('Navigation', 'Navigation') ?></h2>
            <ul class="list-unstyled">
              <li><a href="#about-section" class="smoothscroll"><?= homeText('À propos', 'About') ?></a></li>
              <li><a href="#services-section" class="smoothscroll"><?= homeText('Nos Services', 'Our Services') ?></a></li>
              <li><a href="#testimonials-section" class="smoothscroll"><?= homeText('Témoignages', 'Testimonials') ?></a></li>
              <li><a href="#contact-section" class="smoothscroll"><?= homeText('Contactez-nous', 'Contact us') ?></a></li>
            </ul>
          </div>
    
          <!-- Contact Section -->
          <div class="col-md-4 mb-4">
            <h2 class="footer-heading"><?= homeText('Contactez-nous', 'Contact us') ?></h2>
            <div class="social-icons">
              <h3><?= homeText('Suivez-nous :', 'Follow us:') ?></h3>
              <a href="#home-section" class="social-icon"><i class="fab fa-facebook-f"></i></a>
              <a href="#home-section" class="social-icon"><i class="fab fa-twitter"></i></a>
              <a href="#home-section" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
              <a href="#home-section" class="social-icon"><i class="fab fa-instagram"></i></a>
            </div>
          </div>    
        </div>
    
        <!-- Footer Bottom Section -->
        <div class="row pt-5 mt-5 text-center">
          <div class="col-md-12">
            <div class="border-top pt-4">
              <p>&copy; 2019 UBSb. <?= homeText('Tous droits réservés.', 'All rights reserved.') ?></p>
            </div>
          </div>
        </div>
      </div>
    </footer>
    
  </div> <!-- .site-wrap -->
  <script>
    // JavaScript pour gérer le défilement
    const forms = document.querySelectorAll('.sign-up-form input[type="text"]');
    forms.forEach(form => {
      form.addEventListener('focus', () => {
        document.querySelector('.single-text').classList.add('no-scroll');
      });
      form.addEventListener('blur', () => {
        document.querySelector('.single-text').classList.remove('no-scroll');
      });
    });
  
  </script>

  <script src="js/jquery-3.3.1.min.js"></script>
  <script src="js/jquery-ui.js"></script>
  <script src="js/popper.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="js/owl.carousel.min.js"></script>
  <script src="js/jquery.countdown.min.js"></script>
  <script src="js/jquery.easing.1.3.js"></script>
  <script src="js/aos.js"></script>
  <script src="js/jquery.fancybox.min.js"></script>
  <script src="js/jquery.sticky.js"></script>
  <script src="js/isotope.pkgd.min.js"></script>  
  <script src="js/main.js"></script>

  
  <script>
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.querySelector('.site-menu-toggle');
    const mobileMenu = document.querySelector('.site-mobile-menu');
    const closeBtn = document.querySelector('.site-mobile-menu-close');
    const overlay = document.querySelector('.site-mobile-menu-overlay');
    
    // Ouvrir le menu
    if (menuToggle && mobileMenu && overlay) {
        menuToggle.addEventListener('click', function(e) {
            e.preventDefault();
            mobileMenu.classList.add('active');
            overlay.classList.add('active');
            menuToggle.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden';
        });
    }
    
    // Fermer le menu
    function closeMobileMenu() {
        mobileMenu.classList.remove('active');
        overlay.classList.remove('active');
        if (menuToggle) {
            menuToggle.setAttribute('aria-expanded', 'false');
        }
        document.body.style.overflow = '';
    }
    
    if (closeBtn) {
        closeBtn.addEventListener('click', closeMobileMenu);
    }
    
    if (overlay) {
        overlay.addEventListener('click', closeMobileMenu);
    }
    
    // Générer le contenu du menu mobile
    const mobileBody = document.querySelector('.site-mobile-menu-body');
    if (mobileBody) {
        mobileBody.innerHTML = `
            <ul style="list-style: none; padding: 0;">
                <li style="border-bottom: 1px solid #f0f0f0;"><a href="#home-section" style="display: block; padding: 15px; color: #333; text-decoration: none;"><?= trans('accueil') ?></a></li>
                <li style="border-bottom: 1px solid #f0f0f0;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <a href="#" style="display: block; padding: 15px; color: #333; text-decoration: none;"><?= $est_connecte ? trans('bonjour') . ' ' . htmlspecialchars($user_prenom) : trans('espace_client') ?></a>
                        <span style="padding: 15px; cursor: pointer;" class="mobile-arrow">▼</span>
                    </div>
                    <ul style="list-style: none; padding: 0; background: #f9f9f9; display: none;">
                        <?php if ($est_connecte): ?>
                            <li><a href="dashboard.php" style="display: block; padding: 10px 30px; color: #333; text-decoration: none;"><?= trans('mon_compte') ?></a></li>
                            <li><a href="client-cartes.php" style="color: #333; text-decoration: none; padding: 10px 30px; display: block;"><?= trans('mes_cartes') ?></a></li>
                            <li><a href="verification.php" style="display: block; padding: 10px 30px; color: #333; text-decoration: none;"><?= trans('verifier_statut') ?></a></li>
                            <li><a href="client-rib.php" style="color: #333; text-decoration: none; padding: 10px 30px; display: block;"><?= trans('mon_rib') ?></a></li>
                            <li><a href="logout.php" style="display: block; padding: 10px 30px; color: #FF3131; text-decoration: none;"><?= trans('deconnexion') ?></a></li>
                        <?php else: ?>
                            <li><a href="login.php" style="display: block; padding: 10px 30px; color: #333; text-decoration: none;"><?= trans('connexion') ?></a></li>
                            <li><a href="register.php" style="display: block; padding: 10px 30px; color: #333; text-decoration: none;"><?= trans('inscription') ?></a></li>
                        <?php endif; ?>
                    </ul>
                </li>
                <li style="border-bottom: 1px solid #f0f0f0;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <a href="#" style="display: block; padding: 15px; color: #333; text-decoration: none;"><?= trans('a_propos') ?></a>
                        <span style="padding: 15px; cursor: pointer;" class="mobile-arrow">▼</span>
                    </div>
                    <ul style="list-style: none; padding: 0; background: #f9f9f9; display: none;">
                        <li><a href="#faq-section" style="display: block; padding: 10px 30px; color: #333; text-decoration: none;"><?= trans('faq') ?></a></li>
                        <li><a href="#services-section" style="display: block; padding: 10px 30px; color: #333; text-decoration: none;"><?= trans('services') ?></a></li>
                        <li><a href="#testimonials-section" style="display: block; padding: 10px 30px; color: #333; text-decoration: none;"><?= trans('temoignages') ?></a></li>
                    </ul>
                </li>
                <li style="border-bottom: 1px solid #f0f0f0;"><a href="#contact-section" style="display: block; padding: 15px; color: #333; text-decoration: none;"><?= trans('contact') ?></a></li>
                <li style="border-bottom: 1px solid #f0f0f0;"><a href="verification.php" style="display: block; padding: 15px; color: #333; text-decoration: none;"><?= trans('statut') ?></a></li>
                <li style="border-bottom: 1px solid #f0f0f0; padding: 15px;">
                    <div class="lang-switcher" style="justify-content: flex-start;">
                        <a href="index.php?lang=fr" class="<?= ($_SESSION['lang'] ?? 'fr') === 'fr' ? 'active' : 'inactive' ?>" title="Français">🇫🇷 FR</a>
                        <a href="index.php?lang=en" class="<?= ($_SESSION['lang'] ?? 'fr') === 'en' ? 'active' : 'inactive' ?>" title="English">🇬🇧 EN</a>
                    </div>
                </li>
            </ul>
        `;
        
        // Gestion des sous-menus dans mobile
        document.querySelectorAll('.mobile-arrow').forEach(arrow => {
            arrow.addEventListener('click', function() {
                const submenu = this.parentElement.nextElementSibling;
                if (submenu.style.display === 'none' || !submenu.style.display) {
                    submenu.style.display = 'block';
                    this.style.transform = 'rotate(180deg)';
                } else {
                    submenu.style.display = 'none';
                    this.style.transform = 'rotate(0deg)';
                }
            });
        });
    }
    
    // Fermer sur redimensionnement
    window.addEventListener('resize', function() {
        if (window.innerWidth > 1199) {
            closeMobileMenu();
        }
    });
});
</script>
  
  </body>
</html>
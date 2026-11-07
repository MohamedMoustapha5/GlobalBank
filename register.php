<?php
require_once 'config.php';
require_once 'init_lang.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $telephone = trim($_POST['telephone']);
    $pays = trim($_POST['pays']);
    $mot_de_passe = $_POST['mot_de_passe'];
    $confirm_mdp = $_POST['confirm_mdp'];

    if (empty($nom) || empty($prenom) || empty($email) || empty($mot_de_passe) || empty($pays)) {
        $message = "Tous les champs obligatoires doivent être remplis.";
    } elseif ($mot_de_passe !== $confirm_mdp) {
        $message = "Les mots de passe ne correspondent pas.";
    } elseif (strlen($mot_de_passe) < 6) {
        $message = "Le mot de passe doit contenir au moins 6 caractères.";
    } else {
        $check = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
        $check->execute([$email]);
        
        if ($check->fetch()) {
            $message = "Cet email est déjà utilisé.";
        } else {
            $hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO utilisateurs (nom, prenom, email, telephone, pays, mot_de_passe) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            
            if ($stmt->execute([$nom, $prenom, $email, $telephone, $pays, $hash])) {
                $message = "Inscription réussie ! <a href='login.php'>Connectez-vous</a>";
            } else {
                $message = "Erreur lors de l'inscription.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - UBS</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #c62828 0%, #b71c1c 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
            width: 100%;
            max-width: 600px;
        }
        
        .card-header {
            background: white;
            color: #c62828;
            text-align: center;
            padding: 25px;
            border: none;
        }
        
        .card-header h4 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }
        
        .card-body {
            padding: 40px;
            background: white;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            font-weight: 600;
            color: #555;
            margin-bottom: 8px;
            display: block;
        }
        
        .form-control {
            height: 50px;
            border-radius: 8px;
            border: 2px solid #e0e0e0;
            padding: 10px 15px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: #c62828;
            box-shadow: 0 0 0 3px rgba(198, 40, 40, 0.1);
            outline: none;
        }
        
        select.form-control {
            height: 50px;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1em;
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #c62828, #b71c1c);
            border: none;
            height: 50px;
            font-size: 18px;
            font-weight: bold;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(198, 40, 40, 0.4);
        }
        
        .alert {
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 25px;
        }
        
        .text-center a {
            color: #c62828;
            text-decoration: none;
            font-weight: 600;
        }
        
        .text-center a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4>Inscription</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-info"><?= $message ?></div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nom *</label>
                                        <input type="text" name="nom" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Prénom *</label>
                                        <input type="text" name="prenom" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Email *</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Téléphone</label>
                                <input type="tel" name="telephone" class="form-control">
                            </div>
                            
                            <!-- ✅ LISTE COMPLÈTE DES PAYS -->
                            <div class="form-group">
                                <label>Pays *</label>
                                <select name="pays" class="form-control" required>
                                    <option value="">-- Sélectionnez votre pays --</option>
                                    
                                    <optgroup label="🇪🇺 Europe">
                                        <option value="Albanie">Albanie</option>
                                        <option value="Allemagne">Allemagne</option>
                                        <option value="Andorre">Andorre</option>
                                        <option value="Autriche">Autriche</option>
                                        <option value="Belgique">Belgique</option>
                                        <option value="Biélorussie">Biélorussie</option>
                                        <option value="Bosnie-Herzégovine">Bosnie-Herzégovine</option>
                                        <option value="Bulgarie">Bulgarie</option>
                                        <option value="Chypre">Chypre</option>
                                        <option value="Croatie">Croatie</option>
                                        <option value="Danemark">Danemark</option>
                                        <option value="Espagne">Espagne</option>
                                        <option value="Estonie">Estonie</option>
                                        <option value="Finlande">Finlande</option>
                                        <option value="France">France</option>
                                        <option value="Grèce">Grèce</option>
                                        <option value="Hongrie">Hongrie</option>
                                        <option value="Irlande">Irlande</option>
                                        <option value="Islande">Islande</option>
                                        <option value="Italie">Italie</option>
                                        <option value="Kosovo">Kosovo</option>
                                        <option value="Lettonie">Lettonie</option>
                                        <option value="Liechtenstein">Liechtenstein</option>
                                        <option value="Lituanie">Lituanie</option>
                                        <option value="Luxembourg">Luxembourg</option>
                                        <option value="Macédoine du Nord">Macédoine du Nord</option>
                                        <option value="Malte">Malte</option>
                                        <option value="Moldavie">Moldavie</option>
                                        <option value="Monaco">Monaco</option>
                                        <option value="Monténégro">Monténégro</option>
                                        <option value="Norvège">Norvège</option>
                                        <option value="Pays-Bas">Pays-Bas</option>
                                        <option value="Pologne">Pologne</option>
                                        <option value="Portugal">Portugal</option>
                                        <option value="République tchèque">République tchèque</option>
                                        <option value="Roumanie">Roumanie</option>
                                        <option value="Royaume-Uni">Royaume-Uni</option>
                                        <option value="Russie">Russie</option>
                                        <option value="Saint-Marin">Saint-Marin</option>
                                        <option value="Serbie">Serbie</option>
                                        <option value="Slovaquie">Slovaquie</option>
                                        <option value="Slovénie">Slovénie</option>
                                        <option value="Suède">Suède</option>
                                        <option value="Suisse">Suisse</option>
                                        <option value="Ukraine">Ukraine</option>
                                        <option value="Vatican">Vatican</option>
                                    </optgroup>
                                    
                                    <optgroup label="🌍 Afrique">
                                        <option value="Afrique du Sud">Afrique du Sud</option>
                                        <option value="Algérie">Algérie</option>
                                        <option value="Angola">Angola</option>
                                        <option value="Bénin">Bénin</option>
                                        <option value="Botswana">Botswana</option>
                                        <option value="Burkina Faso">Burkina Faso</option>
                                        <option value="Burundi">Burundi</option>
                                        <option value="Cameroun">Cameroun</option>
                                        <option value="Cap-Vert">Cap-Vert</option>
                                        <option value="Comores">Comores</option>
                                        <option value="Congo">Congo</option>
                                        <option value="Côte d'Ivoire">Côte d'Ivoire</option>
                                        <option value="Djibouti">Djibouti</option>
                                        <option value="Égypte">Égypte</option>
                                        <option value="Érythrée">Érythrée</option>
                                        <option value="Eswatini">Eswatini</option>
                                        <option value="Éthiopie">Éthiopie</option>
                                        <option value="Gabon">Gabon</option>
                                        <option value="Gambie">Gambie</option>
                                        <option value="Ghana">Ghana</option>
                                        <option value="Guinée">Guinée</option>
                                        <option value="Guinée-Bissau">Guinée-Bissau</option>
                                        <option value="Guinée équatoriale">Guinée équatoriale</option>
                                        <option value="Kenya">Kenya</option>
                                        <option value="Lesotho">Lesotho</option>
                                        <option value="Liberia">Liberia</option>
                                        <option value="Libye">Libye</option>
                                        <option value="Madagascar">Madagascar</option>
                                        <option value="Malawi">Malawi</option>
                                        <option value="Mali">Mali</option>
                                        <option value="Maroc">Maroc</option>
                                        <option value="Maurice">Maurice</option>
                                        <option value="Mauritanie">Mauritanie</option>
                                        <option value="Mayotte">Mayotte</option>
                                        <option value="Mozambique">Mozambique</option>
                                        <option value="Namibie">Namibie</option>
                                        <option value="Niger">Niger</option>
                                        <option value="Nigeria">Nigeria</option>
                                        <option value="Ouganda">Ouganda</option>
                                        <option value="République centrafricaine">République centrafricaine</option>
                                        <option value="République démocratique du Congo">République démocratique du Congo</option>
                                        <option value="Rwanda">Rwanda</option>
                                        <option value="Sahara occidental">Sahara occidental</option>
                                        <option value="Sao Tomé-et-Principe">Sao Tomé-et-Principe</option>
                                        <option value="Sénégal">Sénégal</option>
                                        <option value="Seychelles">Seychelles</option>
                                        <option value="Sierra Leone">Sierra Leone</option>
                                        <option value="Somalie">Somalie</option>
                                        <option value="Soudan">Soudan</option>
                                        <option value="Soudan du Sud">Soudan du Sud</option>
                                        <option value="Tanzanie">Tanzanie</option>
                                        <option value="Tchad">Tchad</option>
                                        <option value="Togo">Togo</option>
                                        <option value="Tunisie">Tunisie</option>
                                        <option value="Zambie">Zambie</option>
                                        <option value="Zimbabwe">Zimbabwe</option>
                                    </optgroup>
                                    
                                    <optgroup label="🌎 Amérique">
                                        <option value="Antigua-et-Barbuda">Antigua-et-Barbuda</option>
                                        <option value="Argentine">Argentine</option>
                                        <option value="Bahamas">Bahamas</option>
                                        <option value="Barbade">Barbade</option>
                                        <option value="Belize">Belize</option>
                                        <option value="Bolivie">Bolivie</option>
                                        <option value="Brésil">Brésil</option>
                                        <option value="Canada">Canada</option>
                                        <option value="Chili">Chili</option>
                                        <option value="Colombie">Colombie</option>
                                        <option value="Costa Rica">Costa Rica</option>
                                        <option value="Cuba">Cuba</option>
                                        <option value="Dominique">Dominique</option>
                                        <option value="Équateur">Équateur</option>
                                        <option value="États-Unis">États-Unis (USA)</option>
                                        <option value="Grenade">Grenade</option>
                                        <option value="Guatemala">Guatemala</option>
                                        <option value="Guyana">Guyana</option>
                                        <option value="Haïti">Haïti</option>
                                        <option value="Honduras">Honduras</option>
                                        <option value="Jamaïque">Jamaïque</option>
                                        <option value="Mexique">Mexique</option>
                                        <option value="Nicaragua">Nicaragua</option>
                                        <option value="Panama">Panama</option>
                                        <option value="Paraguay">Paraguay</option>
                                        <option value="Pérou">Pérou</option>
                                        <option value="République dominicaine">République dominicaine</option>
                                        <option value="Saint-Christophe-et-Niévès">Saint-Christophe-et-Niévès</option>
                                        <option value="Sainte-Lucie">Sainte-Lucie</option>
                                        <option value="Saint-Vincent-et-les-Grenadines">Saint-Vincent-et-les-Grenadines</option>
                                        <option value="Salvador">Salvador</option>
                                        <option value="Suriname">Suriname</option>
                                        <option value="Trinité-et-Tobago">Trinité-et-Tobago</option>
                                        <option value="Uruguay">Uruguay</option>
                                        <option value="Venezuela">Venezuela</option>
                                    </optgroup>
                                    
                                    <optgroup label="🌏 Asie">
                                        <option value="Afghanistan">Afghanistan</option>
                                        <option value="Arabie saoudite">Arabie saoudite</option>
                                        <option value="Arménie">Arménie</option>
                                        <option value="Azerbaïdjan">Azerbaïdjan</option>
                                        <option value="Bahreïn">Bahreïn</option>
                                        <option value="Bangladesh">Bangladesh</option>
                                        <option value="Bhoutan">Bhoutan</option>
                                        <option value="Birmanie">Birmanie</option>
                                        <option value="Brunéi">Brunéi</option>
                                        <option value="Cambodge">Cambodge</option>
                                        <option value="Chine">Chine</option>
                                        <option value="Corée du Nord">Corée du Nord</option>
                                        <option value="Corée du Sud">Corée du Sud</option>
                                        <option value="Émirats arabes unis">Émirats arabes unis</option>
                                        <option value="Géorgie">Géorgie</option>
                                        <option value="Inde">Inde</option>
                                        <option value="Indonésie">Indonésie</option>
                                        <option value="Irak">Irak</option>
                                        <option value="Iran">Iran</option>
                                        <option value="Israël">Israël</option>
                                        <option value="Japon">Japon</option>
                                        <option value="Jordanie">Jordanie</option>
                                        <option value="Kazakhstan">Kazakhstan</option>
                                        <option value="Kirghizistan">Kirghizistan</option>
                                        <option value="Koweït">Koweït</option>
                                        <option value="Laos">Laos</option>
                                        <option value="Liban">Liban</option>
                                        <option value="Malaisie">Malaisie</option>
                                        <option value="Maldives">Maldives</option>
                                        <option value="Mongolie">Mongolie</option>
                                        <option value="Népal">Népal</option>
                                        <option value="Oman">Oman</option>
                                        <option value="Ouzbékistan">Ouzbékistan</option>
                                        <option value="Pakistan">Pakistan</option>
                                        <option value="Palestine">Palestine</option>
                                        <option value="Philippines">Philippines</option>
                                        <option value="Qatar">Qatar</option>
                                        <option value="Singapour">Singapour</option>
                                        <option value="Sri Lanka">Sri Lanka</option>
                                        <option value="Syrie">Syrie</option>
                                        <option value="Tadjikistan">Tadjikistan</option>
                                        <option value="Taïwan">Taïwan</option>
                                        <option value="Thaïlande">Thaïlande</option>
                                        <option value="Timor oriental">Timor oriental</option>
                                        <option value="Turkménistan">Turkménistan</option>
                                        <option value="Turquie">Turquie</option>
                                        <option value="Viêt Nam">Viêt Nam</option>
                                        <option value="Yémen">Yémen</option>
                                    </optgroup>
                                    
                                    <optgroup label="🌊 Océanie">
                                        <option value="Australie">Australie</option>
                                        <option value="Fidji">Fidji</option>
                                        <option value="Kiribati">Kiribati</option>
                                        <option value="Marshall">Marshall</option>
                                        <option value="Micronésie">Micronésie</option>
                                        <option value="Nauru">Nauru</option>
                                        <option value="Nouvelle-Zélande">Nouvelle-Zélande</option>
                                        <option value="Palaos">Palaos</option>
                                        <option value="Papouasie-Nouvelle-Guinée">Papouasie-Nouvelle-Guinée</option>
                                        <option value="Samoa">Samoa</option>
                                        <option value="Salomon">Salomon</option>
                                        <option value="Tonga">Tonga</option>
                                        <option value="Tuvalu">Tuvalu</option>
                                        <option value="Vanuatu">Vanuatu</option>
                                    </optgroup>
                                </select>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Mot de passe *</label>
                                        <input type="password" name="mot_de_passe" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Confirmer *</label>
                                        <input type="password" name="confirm_mdp" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-danger btn-block">S'inscrire</button>
                        </form>
                        
                        <p class="text-center mt-4">
                            Déjà un compte ? <a href="login.php">Connectez-vous</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
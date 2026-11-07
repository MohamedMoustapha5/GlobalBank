<?php
// fonctions.php - (tout votre code existant + ajout à la fin)

// ... vos fonctions existantes (genererRoutingNumber, etc.) ...

// ========== FONCTIONS DE GESTION DES LOGOS ==========

function createConfigurationTable() {
    global $pdo;
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS configuration (
                id INT AUTO_INCREMENT PRIMARY KEY,
                cle VARCHAR(100) NOT NULL UNIQUE,
                valeur TEXT,
                date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
                date_modification DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Récupère une valeur de configuration par clé
function getConfig($key, $default = null) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT valeur FROM configuration WHERE cle = ? LIMIT 1");
        $stmt->execute([$key]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && $row['valeur'] !== null) return $row['valeur'];
    } catch (Exception $e) {
        // ignore
    }
    return $default;
}

function ensureVirementColumns() {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SHOW COLUMNS FROM virements LIKE 'date_valeur'");
        $stmt->execute();
        if (!$stmt->fetch()) {
            $pdo->exec("ALTER TABLE virements ADD COLUMN date_valeur DATETIME DEFAULT CURRENT_TIMESTAMP AFTER date_creation");
        }
    } catch (Exception $e) {
        // ignore any schema errors
    }

    try {
        $stmt = $pdo->prepare("SHOW COLUMNS FROM virements LIKE 'motif_virement'");
        $stmt->execute();
        if (!$stmt->fetch()) {
            $pdo->exec("ALTER TABLE virements ADD COLUMN motif_virement TEXT DEFAULT NULL AFTER date_valeur");
        }
    } catch (Exception $e) {
        // ignore any schema errors
    }
}

// Enregistre ou met à jour une clé de configuration
function setConfig($key, $value) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as c FROM configuration WHERE cle = ?");
        $stmt->execute([$key]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($r && $r['c'] > 0) {
            $stmt = $pdo->prepare("UPDATE configuration SET valeur = ?, date_modification = NOW() WHERE cle = ?");
            return $stmt->execute([$value, $key]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO configuration (cle, valeur, date_creation) VALUES (?, ?, NOW())");
            return $stmt->execute([$key, $value]);
        }
    } catch (Exception $e) {
        return false;
    }
}

function getActiveLogo() {
    global $pdo;
    
    // Valeur par défaut
    $default_logo = 'images/logo.png';
    
    // Si pas de PDO ou pas de table, retourner défaut
    if (!isset($pdo)) {
        return $default_logo;
    }
    
    try {
        $stmt = $pdo->query("SELECT valeur FROM configuration WHERE cle = 'logo_actif' LIMIT 1");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result && !empty($result['valeur']) && file_exists($result['valeur'])) {
            return $result['valeur'];
        }
        return $default_logo;
    } catch (Exception $e) {
        return $default_logo;
    }
}

function setActiveLogo($logo_path) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM configuration WHERE cle = 'logo_actif'");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            $stmt = $pdo->prepare("UPDATE configuration SET valeur = ?, date_modification = NOW() WHERE cle = 'logo_actif'");
            return $stmt->execute([$logo_path]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO configuration (cle, valeur, date_creation) VALUES ('logo_actif', ?, NOW())");
            return $stmt->execute([$logo_path]);
        }
    } catch (Exception $e) {
        return false;
    }
}

function getAvailableLogos() {
    $logos = [];
    $directory = __DIR__ . '/images/';
    
    if (!is_dir($directory)) {
        return $logos;
    }
    
    $files = scandir($directory);
    $extensions = ['png', 'jpg', 'jpeg', 'gif', 'webp'];
    
    foreach ($files as $file) {
        if ($file == '.' || $file == '..') continue;
        
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (in_array($ext, $extensions)) {
            $logos[] = [
                'name' => $file,
                'path' => 'images/' . $file,
                'display_name' => pathinfo($file, PATHINFO_FILENAME)
            ];
        }
    }
    
    return $logos;
}

function uploadLogoFile($file) {
    $allowed = ['png', 'jpg', 'jpeg', 'gif', 'webp'];
    $directory = __DIR__ . '/images/';

    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return false;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) {
        return false;
    }

    if (!is_dir($directory)) {
        mkdir($directory, 0755, true);
    }

    $baseName = pathinfo($file['name'], PATHINFO_FILENAME);
    $baseName = preg_replace('/[^a-zA-Z0-9-_]/', '_', $baseName);
    $baseName = trim($baseName, '_-');
    if ($baseName === '') {
        $baseName = 'logo';
    }

    $targetFile = $directory . $baseName . '.' . $ext;
    $counter = 1;
    while (file_exists($targetFile)) {
        $targetFile = $directory . $baseName . '_' . $counter . '.' . $ext;
        $counter++;
    }

    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        return 'images/' . basename($targetFile);
    }

    return false;
}

function deleteLogoFile($relativePath) {
    $imagesDir = realpath(__DIR__ . '/images/');
    if (!$imagesDir) {
        return false;
    }

    $fullPath = realpath(__DIR__ . '/' . ltrim($relativePath, '/\\'));
    if (!$fullPath || strpos($fullPath, $imagesDir) !== 0) {
        return false;
    }

    $filename = basename($fullPath);
    if ($filename === 'logo.png') {
        return false;
    }

    if (!file_exists($fullPath)) {
        return false;
    }

    return unlink($fullPath);
}

function sendMailSMTP($to, $subject, $htmlBody, $altBody = '', $embeddedImages = []) {
    global $use_smtp, $mail_host, $mail_port, $mail_username, $mail_password, $mail_secure, $mail_from, $mail_from_name, $mail_smtp_debug, $mail_log_file;

    $debug_output = '';
    if ($use_smtp && class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
        // Prepare candidate transports to try (first is the configured one)
        $attempts = [];
        $attempts[] = [
            'host' => $mail_host,
            'port' => $mail_port,
            'secure' => $mail_secure
        ];

        // Common alternatives to try if auth fails
        $attempts[] = ['host' => $mail_host, 'port' => 587, 'secure' => 'tls'];
        $attempts[] = ['host' => $mail_host, 'port' => 465, 'secure' => 'ssl'];
        $attempts[] = ['host' => $mail_host, 'port' => 25,  'secure' => ''];

        $lastError = '';

        foreach ($attempts as $idx => $cfg) {
            $debug_output = "";
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host = $cfg['host'];
                $mail->SMTPAuth = true;
                $mail->Username = $mail_username;
                $mail->Password = $mail_password;
                $mail->SMTPSecure = $cfg['secure'];
                $mail->Port = $cfg['port'];
                $mail->Timeout = 20;
                $mail->SMTPKeepAlive = false;
                $mail->CharSet = 'UTF-8';
                if ($cfg['secure'] === '') {
                    $mail->SMTPAutoTLS = false;
                }

                // Use authenticated address as From when possible
                $fromAddress = !empty($mail_from) ? $mail_from : $mail_username;
                $mail->setFrom($fromAddress, $mail_from_name);
                $mail->addAddress($to);
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body = $htmlBody;
                $mail->AltBody = $altBody ?: strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody));
                if (!empty($embeddedImages) && is_array($embeddedImages)) {
                    foreach ($embeddedImages as $cid => $imagePath) {
                        if (file_exists($imagePath)) {
                            $mail->addEmbeddedImage($imagePath, $cid);
                        }
                    }
                }

                // Capture debug output
                if (!isset($mail_smtp_debug)) $mail_smtp_debug = 0;
                $mail->SMTPDebug = (int)$mail_smtp_debug;
                $mail->Debugoutput = function($str, $level) use (&$debug_output) {
                    $debug_output .= "[$level] $str\n";
                };

                $sent = $mail->send();

                // write debug to log if set
                if (!empty($mail_log_file)) {
                    @mkdir(dirname($mail_log_file), 0755, true);
                    @file_put_contents($mail_log_file, date('Y-m-d H:i:s') . " - SUCCESS (attempt $idx)\nHost: {$cfg['host']}:{$cfg['port']} secure={$cfg['secure']}\n" . $debug_output . "\n", FILE_APPEND);
                }

                if ($sent === true) {
                    return true;
                }

            } catch (Exception $e) {
                $lastError = 'PHPMailer erreur: ' . $e->getMessage() . "\nDebug:\n" . $debug_output;
                if (!empty($mail_log_file)) {
                    @mkdir(dirname($mail_log_file), 0755, true);
                    @file_put_contents($mail_log_file, date('Y-m-d H:i:s') . " - FAILURE (attempt $idx)\nHost: {$cfg['host']}:{$cfg['port']} secure={$cfg['secure']}\n" . $lastError . "\n", FILE_APPEND);
                }
                // If auth failed, try next candidate
            }
        }

        return $lastError ?: 'PHPMailer: envoi impossible (toutes tentatives échouées)';
    }

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= 'From: ' . $mail_from_name . ' <' . $mail_from . '>\r\n';

    return mail($to, $subject, $htmlBody, $headers) ? true : 'Impossible d\'envoyer le mail via mail()';
}
?>
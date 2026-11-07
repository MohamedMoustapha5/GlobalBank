<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/fonctions.php';

// Usage: php test_mail.php destinataire@exemple.com
if ($argc < 2) {
    echo "Usage: php test_mail.php destinataire@exemple.com\n";
    exit(1);
}

$to = $argv[1];
$subject = "Test SMTP - envoi de mail";
$body = '<html><body><h3>Test SMTP</h3><p>Ceci est un email de test envoyé depuis le script <strong>test_mail.php</strong>.</p></body></html>';

$result = sendMailSMTP($to, $subject, $body);

if ($result === true) {
    echo "Mail envoyé avec succès à $to\n";
    if (!empty($mail_log_file) && file_exists($mail_log_file)) {
        echo "Debug log: $mail_log_file\n";
        echo "--- Derniers logs ---\n";
        echo file_get_contents($mail_log_file);
    }
} else {
    echo "Échec envoi: \n" . $result . "\n";
    if (!empty($mail_log_file) && file_exists($mail_log_file)) {
        echo "Debug log: $mail_log_file\n";
        echo "--- Contenu du log ---\n";
        echo file_get_contents($mail_log_file);
    }
}

?>
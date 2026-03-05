<?php
require_once 'includes/db.php';

$utenti = [
    ['email' => 'cliente1@email.it',  'pass' => 'cliente123'],
    ['email' => 'cliente2@email.it',  'pass' => 'cliente123'],
    ['email' => 'tecnico1@centro.it', 'pass' => 'tecnico123'],
    ['email' => 'tecnico2@centro.it', 'pass' => 'tecnico123'],
    ['email' => 'logista1@centro.it', 'pass' => 'logista123'],
    ['email' => 'logista2@centro.it', 'pass' => 'logista123'],
    ['email' => 'admin@centro.it',    'pass' => 'admin123'],
];

foreach ($utenti as $u) {
    $hash = password_hash($u['pass'], PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("UPDATE utente SET Password = ? WHERE `E-mail` = ?");
    $stmt->execute([$hash, $u['email']]);
}
echo "Password aggiornate!";
?>
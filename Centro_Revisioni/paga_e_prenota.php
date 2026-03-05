<?php
require_once 'includes/db.php';
include 'includes/header.php';

if (!isset($_SESSION['id_user']) || $_SESSION['ruolo'] !== 'Cliente' || $_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: index.php");
    exit;
}

$targa = $_POST['targa'];
$data_ora = $_POST['data_ora'];
$id_utente = $_SESSION['id_user'];
$importo = 79.00; 
$causale = "Revisione Singola veicolo " . $targa;

try {
    $pdo->beginTransaction();
    $stmt_pagamento = $pdo->prepare("INSERT INTO pagamento (Data, Importo, Causale, Id_Utente) VALUES (NOW(), :importo, :causale, :id_utente)");
    $stmt_pagamento->execute([
        'importo' => $importo,
        'causale' => $causale,
        'id_utente' => $id_utente
    ]);

    $stmt_revisione = $pdo->prepare("INSERT INTO revisione (Data_ora, Esito, Targa) VALUES (:data_ora, 'Da effettuare', :targa)");
    $stmt_revisione->execute([
        'data_ora' => $data_ora,
        'targa' => $targa
    ]);

    $pdo->commit();
    echo "<div style='max-width: 600px; margin: 40px auto; background: #d4edda; color: #155724; padding: 20px; border-radius: 5px; text-align: center; border: 1px solid #c3e6cb;'>";
    echo "<h2>Prenotazione Completata!</h2>";
    echo "<p>Il pagamento è andato a buon fine e la revisione per la targa <strong>" . htmlspecialchars($targa) . "</strong> è stata confermata per il <strong>" . htmlspecialchars(date('d/m/Y H:i', strtotime($data_ora))) . "</strong>.</p>";
    echo "<a href='area_cliente.php' class='btn' style='background-color: #155724; margin-top: 15px; display: inline-block;'>Torna all'Area Cliente</a>";
    echo "</div>";

} catch (PDOException $e) {
    $pdo->rollBack();
    echo "<div style='color: red; padding: 20px; text-align: center;'>Errore durante l'elaborazione del pagamento o della prenotazione: " . $e->getMessage() . "</div>";
}

include 'includes/footer.php';
?>
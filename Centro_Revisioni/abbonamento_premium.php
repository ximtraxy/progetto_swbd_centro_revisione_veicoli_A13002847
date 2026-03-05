<?php
require_once 'includes/db.php';
include 'includes/header.php';

if (!isset($_SESSION['id_user']) || $_SESSION['ruolo'] !== 'Cliente') {
    header("Location: index.php");
    exit;
}

$targa = $_GET['targa'] ?? '';
$id_utente = $_SESSION['id_user'];
$messaggio = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['annulla_premium'])) {
    $stmt_annulla = $pdo->prepare("UPDATE abbonamento SET Stato = 'Scaduto' WHERE Targa = :targa AND Id_Utente = :id_utente AND Stato = 'Attivo'");
    $stmt_annulla->execute(['targa' => $targa, 'id_utente' => $id_utente]);
    $messaggio = "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 4px; margin-bottom: 20px;'>Abbonamento annullato.</div>";
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['acquista_premium'])) {
    $pdo->beginTransaction();
    $pdo->prepare("INSERT INTO pagamento (Data, Importo, Causale, Id_Utente) VALUES (NOW(), 150.00, 'Premium', :id_u)")->execute(['id_u' => $id_utente]);
    $pdo->prepare("INSERT INTO abbonamento (Data_Sottoscrizione, Data_Scadenza, Stato, Id_Utente, Targa) VALUES (CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), 'Attivo', :id_u, :targa)")->execute(['id_u' => $id_utente, 'targa' => $targa]);
    $pdo->commit();
    $messaggio = "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin-bottom: 20px;'>Premium Attivato!</div>";
}

$stmt_abb = $pdo->prepare("SELECT * FROM abbonamento WHERE Targa = :targa AND Id_Utente = :id_utente AND Stato = 'Attivo' AND Data_Scadenza >= CURDATE()");
$stmt_abb->execute(['targa' => $targa, 'id_utente' => $id_utente]);
$abbonamento_attivo = $stmt_abb->fetch();
?>

<div style="max-width: 600px; margin: 20px auto; background: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); text-align: center;">
    <h2>Servizio Premium</h2>
    <p>Veicolo: <strong><?php echo htmlspecialchars($targa); ?></strong></p>

    <?php echo $messaggio; ?>

    <?php if ($abbonamento_attivo): ?>
        <div style="background: #e6f7ff; border: 1px solid #91d5ff; padding: 20px; border-radius: 5px;">
            <h3>Abbonamento Attivo!</h3>
            <p>Scadenza: <strong><?php echo date('d/m/Y', strtotime($abbonamento_attivo['Data_Scadenza'])); ?></strong></p>
            <p>Copertura servizi logistici attiva.</p>
            <form action="abbonamento_premium.php?targa=<?php echo urlencode($targa); ?>" method="POST">
                <input type="hidden" name="annulla_premium" value="1">
                <button type="submit" class="btn" style="background: #dc3545;">Annulla Abbonamento</button>
            </form>
        </div>
    <?php else: ?>
        <div style="background: #fff3cd; padding: 20px; text-align: left;">
            <h3>Sottoscrivi il Piano Annuale</h3>
            <p>Costo: <strong>€ 150.00 / anno</strong></p>
            <ul>
                <li>Gestione Logistica: Ritiri e consegne a domicilio</li>
                <li>Accesso ai report multimediali di stato d'uso</li>
                <li>Dashboard scadenze imminenti</li>
            </ul>
            <form action="abbonamento_premium.php?targa=<?php echo urlencode($targa); ?>" method="POST">
                <input type="hidden" name="acquista_premium" value="1">
                <input type="text" placeholder="Carta di Credito" required style="width: 100%; padding: 8px; margin-bottom: 10px;">
                <button type="submit" class="btn" style="background: #ffc107; color: #333; width: 100%;">Acquista Ora</button>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
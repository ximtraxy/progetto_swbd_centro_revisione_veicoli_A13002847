<?php
require_once 'includes/db.php';
include 'includes/header.php';

if (!isset($_SESSION['id_user']) || $_SESSION['ruolo'] !== 'Admin') {
    header("Location: index.php");
    exit;
}

$success_msg = "";
$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['invia_notifica'])) {
    $id_destinatario = $_POST['destinatario'];
    $testo = trim($_POST['messaggio']);

    if (empty($id_destinatario) || empty($testo)) {
        $error_msg = "Compila tutti i campi prima di inviare.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO notifica (Testo, Data, Letta, Id_Utente) VALUES (?, NOW(), 0, ?)");
            $stmt->execute([$testo, $id_destinatario]);
            $success_msg = "Notifica inviata con successo al destinatario!";
        } catch (PDOException $e) {
            $error_msg = "Errore del database: " . $e->getMessage();
        }
    }
}

$stmt_utenti = $pdo->query("SELECT Id_User, Nome, Cognome, Ruolo FROM utente WHERE Ruolo != 'Admin' ORDER BY Ruolo, Cognome, Nome");
$utenti = $stmt_utenti->fetchAll();

$utenti_per_ruolo = [];
foreach ($utenti as $u) {
    $utenti_per_ruolo[$u['Ruolo']][] = $u;
}
?>

<div style="max-width: 600px; margin: 30px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
    <h2 style="color: #0056b3;">Centro Notifiche Globale</h2>
    <p style="color: #666;">Invia una comunicazione diretta a un utente registrato nel sistema.</p>

    <?php if ($success_msg): ?>
        <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin-bottom: 20px; text-align: center;">
            <strong><?php echo $success_msg; ?></strong>
        </div>
    <?php endif; ?>

    <?php if ($error_msg): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px; text-align: center;">
            <strong><?php echo $error_msg; ?></strong>
        </div>
    <?php endif; ?>

    <form method="POST" style="display: flex; flex-direction: column; gap: 15px;">
        <input type="hidden" name="invia_notifica" value="1">
        
        <div>
            <label style="font-weight: bold;">Seleziona Destinatario:</label><br>
            <select name="destinatario" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; outline: none; margin-top: 5px;">
                <option value="">-- Scegli un utente --</option>
                <?php foreach ($utenti_per_ruolo as $ruolo => $lista): ?>
                    <optgroup label="--- <?php echo strtoupper($ruolo); ?> ---">
                        <?php foreach ($lista as $utente): ?>
                            <option value="<?php echo $utente['Id_User']; ?>">
                                <?php echo htmlspecialchars($utente['Cognome'] . " " . $utente['Nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label style="font-weight: bold;">Testo del Messaggio:</label><br>
            <textarea name="messaggio" rows="4" required placeholder="Scrivi qui la tua comunicazione..." style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; outline: none; margin-top: 5px;"></textarea>
        </div>

        <button type="submit" class="btn" style="background: #0056b3; color: white; padding: 12px; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; font-size: 16px;">
            Invia Messaggio
        </button>
    </form>
    
    <br>
    <div style="text-align: center;">
        <a href="area_admin.php" style="text-decoration:none; color: #6c757d;">&larr; Torna alla Dashboard Admin</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
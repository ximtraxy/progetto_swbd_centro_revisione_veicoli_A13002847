<?php
require_once 'includes/db.php';
include 'includes/header.php';

if (!isset($_SESSION['id_user']) || $_SESSION['ruolo'] !== 'Logista') {
    header("Location: index.php");
    exit;
}

$id_incarico = $_GET['id'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['aggiorna_stato'])) {
    $nuovo_stato = $_POST['stato'];
    
    $stmt = $pdo->prepare("UPDATE INCARICO_LOGISTICO SET Stato = :stato WHERE Id_Incarico = :id");
    $stmt->execute(['stato' => $nuovo_stato, 'id' => $id_incarico]);
    
    if ($nuovo_stato === 'Veicolo Ritirato') {
        $stmt_targa = $pdo->prepare("SELECT Targa_Veicolo_Cliente FROM INCARICO_LOGISTICO WHERE Id_Incarico = :id");
        $stmt_targa->execute(['id' => $id_incarico]);
        $targa = $stmt_targa->fetchColumn();
        
        if ($targa) {
            $check_rev = $pdo->prepare("SELECT COUNT(*) FROM REVISIONE WHERE Targa = :targa AND Esito = 'Da effettuare'");
            $check_rev->execute(['targa' => $targa]);
            
            if ($check_rev->fetchColumn() == 0) {
                $insert_rev = $pdo->prepare("INSERT INTO REVISIONE (Data_ora, Esito, Targa) VALUES (NOW(), 'Da effettuare', :targa)");
                $insert_rev->execute(['targa' => $targa]);
            }
        }
    }
    
    echo "<p style='color:green; text-align:center;'>Stato aggiornato correttamente!</p>";
}

$stmt = $pdo->prepare("SELECT * FROM INCARICO_LOGISTICO WHERE Id_Incarico = :id");
$stmt->execute(['id' => $id_incarico]);
$inc = $stmt->fetch();

$check_reports = $pdo->prepare("SELECT Fase FROM REPORT_CONDIZIONI WHERE Id_Incarico = :id");
$check_reports->execute(['id' => $id_incarico]);
$reports_fatti = $check_reports->fetchAll(PDO::FETCH_COLUMN);

$checkin_completato = in_array('Check-in', $reports_fatti);
$checkout_completato = in_array('Check-out', $reports_fatti);
?>

<div style="max-width: 600px; margin: 30px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
    <h2>Dettaglio Incarico #<?php echo $id_incarico; ?></h2>
    <p>Veicolo Cliente: <strong><?php echo htmlspecialchars($inc['Targa_Veicolo_Cliente']); ?></strong></p>

    <form id="updateStatoForm" method="POST" style="background: #f9f9f9; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
        <label>Cambia Stato Incarico:</label><br>
        <select id="statoSelect" name="stato" style="width: 100%; padding: 8px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; outline: none;">
            <option value="In attesa" <?php if($inc['Stato']=='In attesa') echo 'selected'; ?>>In attesa</option>
            <option value="In transito" <?php if($inc['Stato']=='In transito') echo 'selected'; ?>>In transito</option>
            <option value="Veicolo Ritirato" <?php if($inc['Stato']=='Veicolo Ritirato') echo 'selected'; ?>>Veicolo Ritirato</option>
            <option value="Veicolo Riconsegnato" <?php if($inc['Stato']=='Veicolo Riconsegnato') echo 'selected'; ?>>Veicolo Riconsegnato</option>
            <option value="Completato" <?php if($inc['Stato']=='Completato') echo 'selected'; ?>>Completato</option>
        </select>
        <button type="submit" name="aggiorna_stato" class="btn" style="background-color: #004080; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer;">Aggiorna Stato</button>
    </form>

    <div style="display: flex; gap: 10px;">
        <?php if ($checkin_completato): ?>
            <span class="btn" style="flex: 1; text-align: center; background: #6c757d; color: white; padding: 10px; border-radius: 4px; cursor: not-allowed; opacity: 0.8; display: inline-block;">Check-in Completato ✓</span>
        <?php else: ?>
            <a href="redazione_report.php?id=<?php echo $id_incarico; ?>&fase=Check-in" class="btn" style="flex: 1; text-align: center; background: #20c997; color: white; padding: 10px; border-radius: 4px; text-decoration: none; display: inline-block;">Report Check-in</a>
        <?php endif; ?>

        <?php if ($checkout_completato): ?>
            <span class="btn" style="flex: 1; text-align: center; background: #6c757d; color: white; padding: 10px; border-radius: 4px; cursor: not-allowed; opacity: 0.8; display: inline-block;">Check-out Completato ✓</span>
        <?php else: ?>
            <a href="redazione_report.php?id=<?php echo $id_incarico; ?>&fase=Check-out" class="btn" style="flex: 1; text-align: center; background: #20c997; color: white; padding: 10px; border-radius: 4px; text-decoration: none; display: inline-block;">Report Check-out</a>
        <?php endif; ?>
    </div>
    <br><a href="area_logista.php" style="text-decoration:none; color: #666; display: inline-block; margin-top: 15px;">&larr; Torna alla lista</a>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById("updateStatoForm");
    const statoSelect = document.getElementById("statoSelect");

    if (form) {
        form.addEventListener("submit", function(event) {
            const nuovoStato = statoSelect.value;
            const conferma = confirm("Sei sicuro di voler aggiornare lo stato dell'incarico a '" + nuovoStato + "'?");
            
            if (!conferma) {
                event.preventDefault();
            }
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>
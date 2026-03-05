<?php
require_once 'includes/db.php';
include 'includes/header.php';

if (!isset($_SESSION['id_user']) || $_SESSION['ruolo'] !== 'Tecnico') {
    header("Location: index.php");
    exit;
}

$id_revisione = $_GET['id'] ?? '';
$messaggio = '';
$id_tecnico = $_SESSION['id_user']; // Recupero l'ID del tecnico loggato

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($id_revisione)) {
    $esito = $_POST['esito'];
    $note = trim($_POST['note']);
    $firma = trim($_POST['firma']);

    try {
        $pdo->beginTransaction();

        // Aggiornato: ora salviamo anche l'Id_Utente_Tecnico
        $stmt_rev = $pdo->prepare("UPDATE REVISIONE SET Esito = :esito, Note = :note, Firma_Tecnico = :firma, Id_Utente_Tecnico = :id_t WHERE Id_Revisione = :id");
        $stmt_rev->execute([
            'esito' => $esito,
            'note' => $note,
            'firma' => $firma,
            'id_t' => $id_tecnico,
            'id' => $id_revisione
        ]);

        if ($esito === 'Regolare') {
            $nuova_scadenza = date('Y-m-d', strtotime('+2 years'));
            
            $stmt_veicolo = $pdo->prepare("UPDATE VEICOLO SET Scadenza_Revisione = :nuova_scad WHERE Targa = (SELECT Targa FROM REVISIONE WHERE Id_Revisione = :id)");
            $stmt_veicolo->execute([
                'nuova_scad' => $nuova_scadenza,
                'id' => $id_revisione
            ]);
        }

        $pdo->commit();
        $messaggio = "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;'>
                        ✅ Esito registrato con successo! La scadenza del veicolo è stata aggiornata al " . ($esito === 'Regolare' ? date('d/m/Y', strtotime($nuova_scadenza)) : 'non aggiornata') . ".
                        <br><a href='area_tecnico.php' style='color: #155724; font-weight: bold;'>Torna alla lista incarichi</a>
                      </div>";
    } catch (PDOException $e) {
        $pdo->rollBack();
        $messaggio = "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px;'>Errore durante il salvataggio: " . $e->getMessage() . "</div>";
    }
}

$stmt_info = $pdo->prepare("SELECT r.*, v.Marca, v.Modello FROM REVISIONE r JOIN VEICOLO v ON r.Targa = v.Targa WHERE r.Id_Revisione = :id");
$stmt_info->execute(['id' => $id_revisione]);
$rev = $stmt_info->fetch();

if (!$rev) {
    echo "<h2>Errore: Revisione non trovata.</h2>";
    include 'includes/footer.php';
    exit;
}
?>

<div style="max-width: 600px; margin: 30px auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 15px rgba(0,0,0,0.1);">
    <h2 style="color: #004080; border-bottom: 2px solid #004080; padding-bottom: 10px;">Certificazione Revisione</h2>
    
    <p>Veicolo: <strong><?php echo htmlspecialchars($rev['Marca'] . " " . $rev['Modello'] . " [" . $rev['Targa'] . "]"); ?></strong></p>
    <p>Data Appuntamento: <strong><?php echo date('d/m/Y H:i', strtotime($rev['Data_ora'])); ?></strong></p>

    <?php echo $messaggio; ?>

    <?php if (empty($messaggio) || strpos($messaggio, 'Errore') !== false): ?>
    
    <div id="js-error" style="display: none; background: #ffcccc; color: #cc0000; padding: 10px; margin-bottom: 15px; border-radius: 4px; text-align: center; font-size: 14px;"></div>

    <form id="esitoForm" action="registra_esito.php?id=<?php echo $id_revisione; ?>" method="POST" style="display: flex; flex-direction: column; gap: 15px; margin-top: 20px;">
        
        <div>
            <label style="font-weight: bold;">Esito della Revisione:</label><br>
            <select id="esito" name="esito" required style="width: 100%; padding: 10px; border-radius: 4px; border: 1px solid #ccc; outline: none;">
                <option value="Regolare">Regolare (Superata)</option>
                <option value="Ripetere">Ripetere (Esito negativo - riparazioni necessarie)</option>
                <option value="Sospeso">Sospeso (Gravi mancanze di sicurezza)</option>
            </select>
        </div>

        <div>
            <label style="font-weight: bold;">Note Tecniche / Osservazioni:</label><br>
            <textarea id="note" name="note" rows="5" style="width: 100%; padding: 10px; border-radius: 4px; border: 1px solid #ccc; outline: none; transition: border 0.3s;"></textarea>
        </div>

        <div>
            <label style="font-weight: bold;">Firma del Tecnico (Nome e Cognome):</label><br>
            <input type="text" id="firma" name="firma" required style="width: 100%; padding: 10px; border-radius: 4px; border: 1px solid #ccc; outline: none; transition: border 0.3s;" value="<?php echo htmlspecialchars($_SESSION['nome']); ?>" readonly>
        </div>

        <button type="submit" class="btn" style="background-color: #004080; padding: 12px; font-size: 16px; font-weight: bold;">Salva e Invia Certificazione</button>
        <a href="area_tecnico.php" style="text-align: center; color: #666; text-decoration: none; font-size: 14px;">Annulla e torna indietro</a>
    </form>
    <?php endif; ?>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById("esitoForm");
    const esitoSelect = document.getElementById("esito");
    const noteTextarea = document.getElementById("note");
    const firmaInput = document.getElementById("firma");
    const errorBox = document.getElementById("js-error");

    esitoSelect.addEventListener("change", function() {
        if (this.value !== "Regolare") {
            noteTextarea.style.border = "2px solid red";
        } else {
            noteTextarea.style.border = "1px solid #ccc";
            errorBox.style.display = "none";
        }
    });

    noteTextarea.addEventListener("input", function() {
        if (esitoSelect.value !== "Regolare" && this.value.trim() === "") {
            this.style.border = "2px solid red";
        } else if (this.value.trim() !== "") {
            this.style.border = "2px solid green";
        }
    });

    if(form) {
        form.addEventListener("submit", function(event) {
            let errors = [];
            errorBox.style.display = "none";
            errorBox.innerHTML = "";

            if (esitoSelect.value !== "Regolare" && noteTextarea.value.trim() === "") {
                errors.push("Le note tecniche sono obbligatorie se l'esito è 'Ripetere' o 'Sospeso'.");
                noteTextarea.style.border = "2px solid red";
            }

            if (errors.length > 0) {
                event.preventDefault();
                errorBox.innerHTML = errors.join("<br>");
                errorBox.style.display = "block";
            } else {
                let conferma = confirm("ATTENZIONE: Sei sicuro di voler salvare questo esito in modo definitivo? La modifica modificherà la scadenza del veicolo.");
                if (!conferma) {
                    event.preventDefault();
                }
            }
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>
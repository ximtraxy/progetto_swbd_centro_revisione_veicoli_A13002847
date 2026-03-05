<?php
require_once 'includes/db.php';
include 'includes/header.php';

if (!isset($_SESSION['id_user']) || $_SESSION['ruolo'] !== 'Cliente') { 
    header("Location: index.php"); 
    exit; 
}

$targa = $_GET['targa'] ?? ($_POST['targa'] ?? '');

$stmt_abb = $pdo->prepare("
    SELECT a.* FROM abbonamento a 
    JOIN veicolo v ON a.Targa = v.Targa 
    WHERE a.Targa = :t AND a.Stato = 'Attivo' AND v.Id_Utente = :id_user
");
$stmt_abb->execute(['t' => $targa, 'id_user' => $_SESSION['id_user']]);

if (!$stmt_abb->fetch()) { 
    die("<div style='max-width: 600px; margin: 50px auto; text-align: center; background: #fff; padding: 30px; border-radius: 8px;'>
            <h2 style='color: #dc3545;'>Accesso Negato</h2>
            <p>Veicolo non autorizzato o abbonamento Premium non attivo.</p>
            <br>
            <a href='area_cliente.php' style='padding: 10px 15px; background: #6c757d; color: white; text-decoration: none; border-radius: 4px;'>&larr; Torna ai tuoi veicoli</a>
         </div>"); 
}

$success_msg = "";
$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['invia_richiesta'])) {
    $indirizzo = trim($_POST['indirizzo']);
    $data_ora = trim($_POST['data_ora']);

    if (strlen($indirizzo) < 5) {
        $error_msg = "Inserisci un indirizzo valido (almeno 5 caratteri).";
    } elseif (empty($data_ora)) {
        $error_msg = "Seleziona una data e un'ora validi.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO incarico_logistico (Data_Ora, Indirizzo, Stato, Targa_Veicolo_Cliente) VALUES (?, ?, 'In attesa', ?)");
            $stmt->execute([$data_ora, $indirizzo, $targa]);
            
            $success_msg = "Richiesta inviata con successo! L'officina prenderà in carico la tua prenotazione a breve.";
        } catch (PDOException $e) {
            $error_msg = "Errore DB: " . $e->getMessage();
        }
    }
}
?>

<div style="max-width: 600px; margin: 30px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
    <h2 style="color: #6f42c1;">Nuova Richiesta Ritiro/Consegna</h2>
    <p style="color: #555;">Prenota il servizio logistico Premium per il veicolo targato <strong><?php echo htmlspecialchars($targa); ?></strong>.</p>

    <?php if ($success_msg): ?>
        <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin-bottom: 20px; text-align: center;">
            <strong><?php echo $success_msg; ?></strong><br><br>
            <a href="gestione_appuntamenti.php?targa=<?php echo urlencode($targa); ?>" style="text-decoration: underline; color: #155724;">Torna ai tuoi appuntamenti</a>
        </div>
    <?php else: ?>

        <?php if ($error_msg): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px; text-align: center;">
                <?php echo $error_msg; ?>
            </div>
        <?php endif; ?>

        <div id="js-error" style="display: none; background: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border-radius: 4px; text-align: center; font-size: 14px;"></div>

        <form id="richiestaForm" method="POST" style="display: flex; flex-direction: column; gap: 15px;">
            <input type="hidden" name="invia_richiesta" value="1">
            <input type="hidden" name="targa" value="<?php echo htmlspecialchars($targa); ?>">
            
            <label style="font-weight: bold; margin-bottom: -10px;">Indirizzo per il ritiro:</label>
            <input type="text" id="indirizzo" name="indirizzo" placeholder="Es. Via Garibaldi 45, Napoli" required style="padding: 10px; border: 1px solid #ccc; border-radius: 4px; outline: none;">
            
            <label style="font-weight: bold; margin-bottom: -10px;">Data e Ora preferite:</label>
            <input type="datetime-local" id="data_ora" name="data_ora" required style="padding: 10px; border: 1px solid #ccc; border-radius: 4px; outline: none;">
            
            <div style="background: #e9ecef; padding: 10px; border-radius: 4px; font-size: 13px; color: #333;">
                <strong>Orari di disponibilità:</strong><br>
                Lunedì - Venerdì: 09:00 - 13:00 / 14:00 - 19:30<br>
                Sabato: 09:00 - 13:00<br>
                Domenica: Chiusi
            </div>

            <p style="font-size: 13px; color: #777;">* Un nostro incaricato arriverà all'indirizzo indicato portando l'auto sostitutiva.</p>

            <button type="submit" class="btn" style="background: #6f42c1; color: white; padding: 12px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px;">Invia Richiesta</button>
        </form>

    <?php endif; ?>

    <br>
    <div style="text-align: center;">
        <a href="gestione_appuntamenti.php?targa=<?php echo urlencode($targa); ?>" style="text-decoration:none; color: #6c757d;">&larr; Annulla e torna indietro</a>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById("richiestaForm");
    const indirizzoInput = document.getElementById("indirizzo");
    const dataInput = document.getElementById("data_ora");
    const errorBox = document.getElementById("js-error");

    function validaOrari(dateObj) {
        const day = dateObj.getDay(); 
        const ore = dateObj.getHours();
        const minuti = dateObj.getMinutes();
        const time = ore * 60 + minuti; 

        if (day === 0) {
            return "Il centro è chiuso la Domenica.";
        } else if (day >= 1 && day <= 5) {
            if (!((time >= 540 && time <= 780) || (time >= 840 && time <= 1170))) {
                return "Dal Lunedì al Venerdì gli orari sono 09:00-13:00 e 14:00-19:30.";
            }
        } else if (day === 6) {
            if (!(time >= 540 && time <= 780)) {
                return "Il Sabato siamo aperti solo dalle 09:00 alle 13:00.";
            }
        }
        return null;
    }

    if (form) {
        form.addEventListener("submit", function(event) {
            let errors = [];
            errorBox.style.display = "none";
            errorBox.innerHTML = "";

            if (indirizzoInput.value.trim().length < 5) {
                errors.push("L'indirizzo deve essere di almeno 5 caratteri.");
                indirizzoInput.style.border = "2px solid red";
            } else {
                indirizzoInput.style.border = "1px solid #ccc";
            }

            if (dataInput.value) {
                const inputDate = new Date(dataInput.value);
                const currentDate = new Date();
                
                if (inputDate <= currentDate) {
                    errors.push("Devi scegliere una data e un'ora nel futuro.");
                    dataInput.style.border = "2px solid red";
                } else {
                    const orarioErrore = validaOrari(inputDate);
                    if (orarioErrore) {
                        errors.push(orarioErrore);
                        dataInput.style.border = "2px solid red";
                    } else {
                        dataInput.style.border = "1px solid #ccc";
                    }
                }
            } else {
                errors.push("Inserisci una data e un'ora.");
            }

            if (errors.length > 0) {
                event.preventDefault();
                errorBox.innerHTML = errors.join("<br>");
                errorBox.style.display = "block";
            }
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>
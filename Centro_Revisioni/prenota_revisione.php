<?php
require_once 'includes/db.php';
include 'includes/header.php';

if (!isset($_SESSION['id_user']) || $_SESSION['ruolo'] !== 'Cliente') {
    header("Location: index.php");
    exit;
}

if (!isset($_GET['targa']) || empty($_GET['targa'])) {
    echo "<div style='color: red; padding: 20px;'>Errore: Nessun veicolo selezionato. <a href='area_cliente.php'>Torna indietro</a>.</div>";
    include 'includes/footer.php';
    exit;
}

$targa = $_GET['targa'];
$id_utente = $_SESSION['id_user'];

$stmt = $pdo->prepare("SELECT * FROM veicolo WHERE Targa = :targa AND Id_Utente = :id_utente");
$stmt->execute(['targa' => $targa, 'id_utente' => $id_utente]);
$veicolo = $stmt->fetch();

if (!$veicolo) {
    echo "<div style='color: red; padding: 20px;'>Errore: Veicolo non trovato o non autorizzato.</div>";
    include 'includes/footer.php';
    exit;
}
?>

<div style="max-width: 500px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
    <h2>Prenota Revisione</h2>
    <p>Stai prenotando la revisione per il veicolo: <strong><?php echo htmlspecialchars($veicolo['Marca'] . " " . $veicolo['Modello'] . " (" . $veicolo['Targa'] . ")"); ?></strong></p>

    <div id="js-error" style="display: none; background: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border-radius: 4px; text-align: center; font-size: 14px;"></div>

    <form id="prenotaForm" action="paga_e_prenota.php" method="POST" style="display: flex; flex-direction: column; gap: 15px;">
        <input type="hidden" name="targa" value="<?php echo htmlspecialchars($targa); ?>">
        
        <div>
            <label for="data_ora">Data e Ora appuntamento:</label><br>
            <input type="datetime-local" id="data_ora" name="data_ora" required style="width: 100%; padding: 8px;">
            
            <div style="background: #e9ecef; padding: 10px; border-radius: 4px; font-size: 13px; color: #333; margin-top: 10px;">
                <strong>Orari di disponibilità:</strong><br>
                Lunedì - Venerdì: 09:00 - 13:00 / 14:00 - 19:30<br>
                Sabato: 09:00 - 13:00<br>
                Domenica: Chiusi
            </div>
        </div>

        <div style="background: #f4f4f4; padding: 15px; border-radius: 4px; margin-top: 10px;">
            <h3>Dati Pagamento</h3>
            <p>Tariffa fissa revisione ministeriale: <strong>€ 79.00</strong></p>
            
            <label for="carta">Numero Carta di Credito (Simulazione):</label><br>
            <input type="text" id="carta" name="carta" placeholder="1234 5678 1234 5678" required style="width: 100%; padding: 8px; margin-bottom: 10px;">
            
            <div style="display: flex; gap: 10px;">
                <div style="flex: 1;">
                    <label>Scadenza:</label>
                    <input type="text" placeholder="MM/AA" required style="width: 100%; padding: 8px;">
                </div>
                <div style="flex: 1;">
                    <label>CVV:</label>
                    <input type="text" placeholder="123" required style="width: 100%; padding: 8px;">
                </div>
            </div>
        </div>

        <button type="submit" class="btn" style="background-color: #004080; font-size: 16px; margin-top: 10px; color: white; padding: 10px; border: none; border-radius: 4px; cursor: pointer;">Paga e Conferma Prenotazione</button>
    </form>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById("prenotaForm");
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
                dataInput.style.border = "2px solid red";
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
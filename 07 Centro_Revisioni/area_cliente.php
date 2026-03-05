<?php
require_once 'includes/db.php';
include 'includes/header.php';

if (!isset($_SESSION['id_user']) || $_SESSION['ruolo'] !== 'Cliente') {
    header("Location: index.php");
    exit;
}

$id_utente = $_SESSION['id_user'];
$messaggio = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['aggiungi_veicolo'])) {
    $targa = strtoupper(trim($_POST['targa']));
    $marca = trim($_POST['marca']);
    $modello = trim($_POST['modello']);
    $anno = (int)$_POST['anno'];
    $categoria = $_POST['categoria'];
    $scadenza = $_POST['scadenza_revisione'];

    if (!empty($targa) && !empty($marca) && !empty($modello) && !empty($anno) && !empty($scadenza)) {
        $stmt_check = $pdo->prepare("SELECT Targa FROM veicolo WHERE Targa = :targa");
        $stmt_check->execute(['targa' => $targa]);
        
        if ($stmt_check->rowCount() > 0) {
            $messaggio = "<div style='color: red; padding: 10px;'>Errore: Targa già registrata.</div>";
        } else {
            $stmt_insert = $pdo->prepare("INSERT INTO veicolo (Targa, Marca, Modello, Anno_Immatricolazione, Categoria, Id_Utente, Scadenza_Revisione) VALUES (:targa, :marca, :modello, :anno, :categoria, :id_utente, :scadenza)");
            try {
                $stmt_insert->execute([
                    'targa' => $targa, 
                    'marca' => $marca, 
                    'modello' => $modello, 
                    'anno' => $anno, 
                    'categoria' => $categoria, 
                    'id_utente' => $id_utente,
                    'scadenza' => $scadenza
                ]);
                $messaggio = "<div style='color: green; padding: 10px;'>Veicolo aggiunto al parco con scadenza aggiornata!</div>";
            } catch (PDOException $e) {
                $messaggio = "<div style='color: red; padding: 10px;'>Errore inserimento: " . $e->getMessage() . "</div>";
            }
        }
    }
}

$stmt_veicoli = $pdo->prepare("SELECT * FROM veicolo WHERE Id_Utente = :id_utente");
$stmt_veicoli->execute(['id_utente' => $id_utente]);
$veicoli = $stmt_veicoli->fetchAll();
?>

<div style="text-align: center; margin-top: 20px;">
    <h2>Gestione Parco Veicoli</h2>
    <p>Seleziona un veicolo per accedere ai servizi dedicati.</p>
    <?php echo $messaggio; ?>
</div>

<div style="max-width: 650px; margin: 20px auto; background: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
    <h3 style="margin-top: 0;">+ Registra Nuovo Veicolo</h3>
    
    <div id="js-error" style="display: none; background: #ffcccc; color: #cc0000; padding: 10px; margin-bottom: 15px; border-radius: 4px; text-align: center; font-size: 14px;"></div>

    <form id="addVehicleForm" action="area_cliente.php" method="POST" style="display: flex; gap: 10px; flex-wrap: wrap;">
        <input type="hidden" name="aggiungi_veicolo" value="1">
        <input type="text" id="targa" name="targa" placeholder="Targa" required style="flex: 1; padding: 8px; border: 1px solid #ccc; outline: none; transition: border 0.3s; border-radius: 4px;">
        <input type="text" name="marca" placeholder="Marca" required style="flex: 1; padding: 8px;">
        <input type="text" name="modello" placeholder="Modello" required style="flex: 1; padding: 8px;">
        <input type="number" id="anno" name="anno" placeholder="Anno" required style="width: 80px; padding: 8px; border: 1px solid #ccc; outline: none; transition: border 0.3s; border-radius: 4px;">
        <select name="categoria" required style="padding: 8px;">
            <option value="Auto">Auto</option>
            <option value="Moto">Moto</option>
            <option value="Commerciale">Commerciale</option>
        </select>
        
        <div style="width: 100%; display: flex; align-items: center; gap: 10px; margin-top: 5px;">
            <label for="scadenza_revisione" style="font-size: 14px;">Data Scadenza Revisione:</label>
            <input type="date" id="scadenza_revisione" name="scadenza_revisione" required style="flex: 1; padding: 8px;">
        </div>

        <button type="submit" class="btn" style="background-color: #28a745; width: 100%; margin-top: 10px;">Aggiungi Veicolo</button>
    </form>
</div>

<div style="display: flex; flex-direction: column; align-items: center; gap: 20px; margin-bottom: 40px;">
    <?php if (count($veicoli) > 0): ?>
        <?php foreach ($veicoli as $v): ?>
            <div style="background: #e6f2ff; border-left: 5px solid #004080; padding: 20px; border-radius: 5px; width: 100%; max-width: 800px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <div>
                        <h3 style="margin: 0; color: #004080;"><?php echo htmlspecialchars($v['Marca'] . " " . $v['Modello']); ?></h3>
                        <p style="margin: 5px 0; font-size: 13px; color: #555;">Scadenza Revisione: <strong><?php echo date('d/m/Y', strtotime($v['Scadenza_Revisione'])); ?></strong></p>
                    </div>
                    <span style="background: #004080; color: white; padding: 5px 10px; border-radius: 3px; font-weight: bold; font-family: monospace;">
                        <?php echo htmlspecialchars($v['Targa']); ?>
                    </span>
                </div>
                
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <a href="prenota_revisione.php?targa=<?php echo urlencode($v['Targa']); ?>" class="dash-btn" style="flex: 1; padding: 15px 5px; font-size: 13px;">Prenota Revisione</a>
                    <a href="abbonamento_premium.php?targa=<?php echo urlencode($v['Targa']); ?>" class="dash-btn" style="flex: 1; padding: 15px 5px; background-color: #ffc107; color: #333; font-size: 13px;">Gestione Premium</a>
                    <a href="storico_revisioni.php?targa=<?php echo urlencode($v['Targa']); ?>" class="dash-btn" style="flex: 1; padding: 15px 5px; background-color: #17a2b8; font-size: 13px;">Storico Revisioni</a>
                </div>

                <?php
                $check_p = $pdo->prepare("SELECT Id_Abbonamento FROM abbonamento WHERE Targa = :t AND Stato = 'Attivo'");
                $check_p->execute(['t' => $v['Targa']]);
                if ($check_p->fetch()): ?>
                    <div style="margin-top: 15px; padding-top: 15px; border-top: 1px dashed #004080; display: flex; gap: 10px; flex-wrap: wrap;">
                        <a href="gestione_appuntamenti.php?targa=<?php echo urlencode($v['Targa']); ?>" class="btn" style="background: #6f42c1; font-size: 12px; flex: 1;">Appuntamenti Logistica</a>
                        <a href="consultazione_report.php?targa=<?php echo urlencode($v['Targa']); ?>" class="btn" style="background: #20c997; font-size: 12px; flex: 1;">Report Condizioni</a>
                        <a href="dashboard_scadenze.php?targa=<?php echo urlencode($v['Targa']); ?>" class="btn" style="background: #fd7e14; font-size: 12px; flex: 1;">Monitoraggio Scadenze</a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Nessun veicolo registrato.</p>
    <?php endif; ?>
</div>


<script>
    //js
document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById("addVehicleForm");
    const targaInput = document.getElementById("targa");
    const annoInput = document.getElementById("anno");
    const errorBox = document.getElementById("js-error");

    const targaRegex = /^[A-Z0-9]{5,7}$/;
    const currentYear = new Date().getFullYear();

    targaInput.addEventListener("input", function() {
        this.value = this.value.toUpperCase().replace(/\s/g, '');
        
        if (this.value.length > 0 && !targaRegex.test(this.value)) {
            this.style.border = "2px solid red";
        } else if (targaRegex.test(this.value)) {
            this.style.border = "2px solid green";
        } else {
            this.style.border = "1px solid #ccc";
        }
    });

    annoInput.addEventListener("input", function() {
        let val = parseInt(this.value);
        if (this.value.length > 0 && (isNaN(val) || val < 1950 || val > currentYear)) {
            this.style.border = "2px solid red";
        } else if (val >= 1950 && val <= currentYear) {
            this.style.border = "2px solid green";
        } else {
            this.style.border = "1px solid #ccc";
        }
    });

    form.addEventListener("submit", function(event) {
        let errors = [];
        
        errorBox.style.display = "none";
        errorBox.innerHTML = "";

        if (!targaRegex.test(targaInput.value)) {
            errors.push("La targa deve contenere tra 5 e 7 caratteri alfanumerici continui.");
            targaInput.style.border = "2px solid red";
        }

        let annoVal = parseInt(annoInput.value);
        if (isNaN(annoVal) || annoVal < 1950 || annoVal > currentYear) {
            errors.push("L'anno di immatricolazione deve essere compreso tra 1950 e " + currentYear + ".");
            annoInput.style.border = "2px solid red";
        }

        if (errors.length > 0) {
            event.preventDefault();
            errorBox.innerHTML = errors.join("<br>");
            errorBox.style.display = "block";
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>
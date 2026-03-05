<?php
require_once 'includes/db.php';
include 'includes/header.php';

if (!isset($_SESSION['id_user']) || $_SESSION['ruolo'] !== 'Admin') { 
    header("Location: index.php"); 
    exit; 
}

$messaggio = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['aggiungi_auto'])) {
    $targa = strtoupper(trim($_POST['targa']));
    $marca = trim($_POST['marca']);
    $modello = trim($_POST['modello']);

    if (!empty($targa) && !empty($marca) && !empty($modello)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO AUTO_SOSTITUTIVA (Targa_Auto, Marca, Modello, Stato) VALUES (?, ?, ?, 'Libera')");
            $stmt->execute([$targa, $marca, $modello]);
            $messaggio = "<p style='color:green; font-weight:bold;'>✅ Veicolo $targa aggiunto con successo alla flotta!</p>";
        } catch (PDOException $e) {
            $messaggio = "<p style='color:red; font-weight:bold;'>❌ Errore: Targa già esistente o dati non validi.</p>";
        }
    }
}

if (isset($_POST['cambia_stato'])) {
    $stmt = $pdo->prepare("UPDATE AUTO_SOSTITUTIVA SET Stato = ? WHERE Targa_Auto = ?");
    $stmt->execute([$_POST['nuovo_stato'], $_POST['targa_update']]);
    $messaggio = "<p style='color:blue; font-weight:bold;'>ℹ️ Stato del veicolo aggiornato.</p>";
}

$auto = $pdo->query("SELECT * FROM AUTO_SOSTITUTIVA ORDER BY Marca ASC")->fetchAll();
?>

<div style="max-width: 900px; margin: 20px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
    <h2 style="color: #004080; border-bottom: 2px solid #004080; padding-bottom: 10px;">🚗 Gestione Flotta Auto Sostitutive</h2>
    
    <?php echo $messaggio; ?>

    <div style="background: #f0f4f8; padding: 20px; border-radius: 8px; margin: 20px 0; border: 1px solid #d1d9e0;">
        <h4 style="margin-top: 0;">+ Inserisci Nuovo Veicolo in Flotta</h4>
        
        <div id="js-error" style="display: none; background: #ffcccc; color: #cc0000; padding: 10px; margin-bottom: 15px; border-radius: 4px; text-align: center; font-size: 14px;"></div>

        <form id="flottaForm" method="POST" style="display: flex; gap: 10px; flex-wrap: wrap;">
            <input type="hidden" name="aggiungi_auto" value="1">
            <input type="text" id="targa" name="targa" placeholder="Targa (es. XY123ZW)" required style="padding: 10px; flex: 1; border: 1px solid #ccc; border-radius: 4px; outline: none; transition: border 0.3s;">
            <input type="text" name="marca" placeholder="Marca (es. Fiat)" required style="padding: 10px; flex: 1; border: 1px solid #ccc; border-radius: 4px;">
            <input type="text" name="modello" placeholder="Modello (es. 500L)" required style="padding: 10px; flex: 1; border: 1px solid #ccc; border-radius: 4px;">
            <button type="submit" class="btn" style="background: #28a745; padding: 10px 20px;">Registra Auto</button>
        </form>
    </div>

    <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
        <thead>
            <tr style="background: #004080; color: white; text-align: left;">
                <th style="padding: 12px;">Targa</th>
                <th style="padding: 12px;">Veicolo</th>
                <th style="padding: 12px;">Stato Attuale</th>
                <th style="padding: 12px;">Azioni Veloci</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($auto as $a): ?>
            <tr style="border-bottom: 1px solid #ddd;">
                <td style="padding: 12px; font-weight: bold;"><?php echo htmlspecialchars($a['Targa_Auto']); ?></td>
                <td style="padding: 12px;"><?php echo htmlspecialchars($a['Marca'] . " " . $a['Modello']); ?></td>
                <td style="padding: 12px;">
                    <span style="padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: bold; 
                        background: <?php echo ($a['Stato'] == 'Libera') ? '#d4edda' : (($a['Stato'] == 'In uso') ? '#fff3cd' : '#f8d7da'); ?>;
                        color: <?php echo ($a['Stato'] == 'Libera') ? '#155724' : (($a['Stato'] == 'In uso') ? '#856404' : '#721c24'); ?>;">
                        <?php echo $a['Stato']; ?>
                    </span>
                </td>
                <td style="padding: 12px;">
                    <form method="POST" style="display: flex; gap: 5px;" onsubmit="return confirm('Sicuro di voler cambiare lo stato di questa auto?');">
                        <input type="hidden" name="targa_update" value="<?php echo $a['Targa_Auto']; ?>">
                        <select name="nuovo_stato" style="padding: 5px; border-radius: 4px;">
                            <option value="Libera" <?php if($a['Stato']=='Libera') echo 'selected'; ?>>Libera</option>
                            <option value="In uso" <?php if($a['Stato']=='In uso') echo 'selected'; ?>>In uso</option>
                            <option value="Manutenzione" <?php if($a['Stato']=='Manutenzione') echo 'selected'; ?>>Manutenzione</option>
                        </select>
                        <button type="submit" name="cambia_stato" class="btn" style="padding: 5px 10px; font-size: 12px; background: #6c757d;">Aggiorna</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div style="margin-top: 30px;">
        <a href="area_admin.php" class="btn" style="background: #004080; text-decoration: none;">&larr; Torna al Pannello Admin</a>
    </div>
</div>


<script>
    //js
document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById("flottaForm");
    const targaInput = document.getElementById("targa");
    const errorBox = document.getElementById("js-error");

    const targaRegex = /^[A-Z0-9]{5,7}$/;

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

    if (form) {
        form.addEventListener("submit", function(event) {
            let errors = [];
            errorBox.style.display = "none";
            errorBox.innerHTML = "";

            if (!targaRegex.test(targaInput.value)) {
                errors.push("La targa deve contenere tra 5 e 7 caratteri alfanumerici senza spazi.");
                targaInput.style.border = "2px solid red";
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
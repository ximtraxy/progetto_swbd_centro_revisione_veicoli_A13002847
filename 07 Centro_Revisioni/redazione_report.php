<?php
require_once 'includes/db.php';
include 'includes/header.php';

if (!isset($_SESSION['id_user']) || $_SESSION['ruolo'] !== 'Logista') {
    header("Location: index.php");
    exit;
}

$id_incarico = $_GET['id'];
$fase = $_GET['fase']; 

$check_stmt = $pdo->prepare("SELECT Id_Report FROM REPORT_CONDIZIONI WHERE Id_Incarico = ? AND Fase = ? AND Firma_Accettazione IS NOT NULL AND Firma_Accettazione != ''");
$check_stmt->execute([$id_incarico, $fase]);
$gia_firmato = $check_stmt->fetch();

if ($_SERVER["REQUEST_METHOD"] == "POST" && !$gia_firmato) {
    $target_dir = "uploads/";
    $uploaded_files = [];
    
    foreach ($_FILES['foto']['name'] as $key => $val) {
        if ($_FILES['foto']['error'][$key] == 0) {
            $file_name = time() . "_" . basename($_FILES["foto"]["name"][$key]);
            $target_file = $target_dir . $file_name;
            if (move_uploaded_file($_FILES["foto"]["tmp_name"][$key], $target_file)) {
                $uploaded_files[] = $target_file;
            }
        }
    }

    if (!empty($uploaded_files)) {
        $url_media = implode(",", $uploaded_files);
        $desc_danni = trim($_POST['danni']);
        $firma = trim($_POST['firma']);

        $stmt = $pdo->prepare("INSERT INTO REPORT_CONDIZIONI (Fase, Descrizione_Danni, URL_Media, Data_Ora, Firma_Accettazione, Id_Incarico) 
                               VALUES (:fase, :danni, :url, NOW(), :firma, :id)");
        $stmt->execute([
            'fase' => $fase,
            'danni' => $desc_danni,
            'url' => $url_media,
            'firma' => $firma,
            'id' => $id_incarico
        ]);

        header("Location: redazione_report.php?id=$id_incarico&fase=$fase&success=1");
        exit;
    } else {
        echo "<div style='padding:20px; background:#f8d7da; color:#721c24; text-align:center;'>Errore nel caricamento dei file.</div>";
    }
}
?>

<div style="max-width: 500px; margin: 30px auto; padding: 20px; border: 1px solid #20c997; border-radius: 8px; background: #fff;">
    <h2>Redazione Report: <?php echo htmlspecialchars($fase); ?></h2>
    
    <?php if (isset($_GET['success'])): ?>
        <div style="padding:15px; background:#d4edda; color:#155724; text-align:center; border-radius:4px; margin-bottom:15px;">Report inviato con successo!</div>
    <?php endif; ?>

    <?php if ($gia_firmato): ?>
        <div style="padding:20px; background:#fff3cd; color:#856404; text-align:center; border: 1px solid #ffeeba; border-radius:4px;">
            <strong>⚠️ Report Bloccato</strong><br>
            Questo report è già stato completato e firmato. Non è più possibile apportare modifiche.
        </div>
        <br><a href="gestione_incarico_logistico.php?id=<?php echo $id_incarico; ?>" style="display:block; text-align:center; color: #20c997; font-weight:bold; text-decoration:none;">&larr; Torna all'incarico</a>
    <?php else: ?>

        <div id="js-error" style="display: none; background: #ffcccc; color: #cc0000; padding: 10px; margin-bottom: 15px; border-radius: 4px; text-align: center; font-size: 14px;"></div>

        <form id="reportForm" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 15px;">
            <div>
                <label>Descrizione Danni Riscontrati:</label><br>
                <textarea id="danni" name="danni" rows="4" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; outline: none;" placeholder="Es: Graffio portiera sinistra..."></textarea>
            </div>

            <div>
                <label>Carica Foto/Video (Seleziona più file):</label><br>
                <input type="file" id="foto" name="foto[]" multiple required style="width: 100%; padding: 8px; outline: none;">
            </div>

            <div>
                <label>Firma Accettazione Cliente:</label><br>
                <input type="text" id="firma" name="firma" placeholder="Nome e Cognome Cliente" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; outline: none;">
            </div>

            <button type="submit" class="btn" style="background: #20c997; padding: 12px; font-weight: bold; color: white; border: none; border-radius: 4px; cursor: pointer;">Invia Report Condizioni</button>
            <a href="gestione_incarico_logistico.php?id=<?php echo $id_incarico; ?>" style="text-align: center; color: #666; text-decoration: none;">Annulla</a>
        </form>
    <?php endif; ?>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById("reportForm");
    if(!form) return;

    const fotoInput = document.getElementById("foto");
    const firmaInput = document.getElementById("firma");
    const errorBox = document.getElementById("js-error");

    firmaInput.addEventListener("input", function() {
        this.style.border = (this.value.trim().length >= 2) ? "2px solid green" : "2px solid red";
    });

    form.addEventListener("submit", function(event) {
        let errors = [];
        errorBox.style.display = "none";
        errorBox.innerHTML = "";

        if (firmaInput.value.trim().length < 2) {
            errors.push("La firma deve contenere un nome valido.");
            firmaInput.style.border = "2px solid red";
        }

        if (fotoInput.files.length === 0) {
            errors.push("Devi caricare almeno un file.");
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
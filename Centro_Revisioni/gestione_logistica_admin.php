<?php
require_once 'includes/db.php';
include 'includes/header.php';

if (!isset($_SESSION['id_user']) || $_SESSION['ruolo'] !== 'Admin') { 
    header("Location: index.php"); 
    exit; 
}

$messaggio = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['assegna_incarico'])) {
    $id_incarico = $_POST['id_incarico'];
    $logista = $_POST['logista'];
    $auto_sost_val = empty($_POST['auto_sost']) ? null : $_POST['auto_sost'];

    try {
        $stmt = $pdo->prepare("UPDATE INCARICO_LOGISTICO SET Id_Utente_Autista = ?, Targa_Auto_Sost = ?, Stato = 'Assegnato' WHERE Id_Incarico = ?");
        $stmt->execute([$logista, $auto_sost_val, $id_incarico]);

        if ($auto_sost_val) {
            $stmt_auto = $pdo->prepare("UPDATE AUTO_SOSTITUTIVA SET Stato = 'In uso' WHERE Targa_Auto = ?");
            $stmt_auto->execute([$auto_sost_val]);
        }

        $messaggio = "<div style='background: #d4edda; color: #155724; text-align:center; padding: 10px; font-weight: bold; border-radius: 5px; margin-bottom: 20px;'>Incarico assegnato con successo!</div>";
    } catch (PDOException $e) {
        $messaggio = "<div style='background: #f8d7da; color: #721c24; text-align:center; padding: 10px; font-weight: bold; border-radius: 5px; margin-bottom: 20px;'>Errore nell'assegnazione dell'incarico. Verifica i dati inseriti.</div>";
    }
}

$logisti = $pdo->query("SELECT * FROM UTENTE WHERE Ruolo = 'Logista'")->fetchAll();
$auto_libere = $pdo->query("SELECT * FROM AUTO_SOSTITUTIVA WHERE Stato = 'Libera'")->fetchAll();

$richieste = $pdo->query("SELECT * FROM INCARICO_LOGISTICO WHERE Stato = 'In attesa' AND Id_Utente_Autista IS NULL ORDER BY Data_Ora ASC")->fetchAll();
?>

<div style="max-width: 800px; margin: 20px auto; padding: 20px; background: #fff; border-radius: 8px;">
    <h2>Gestione Incarichi in Attesa</h2>

    <?php echo $messaggio; ?>

    <?php if (count($richieste) > 0): ?>
        <?php foreach ($richieste as $req): ?>
            <div style="border: 1px solid #ccc; padding: 15px; margin-bottom: 15px; border-radius: 5px; background: #f9f9f9;">
                <p><strong>Veicolo Cliente:</strong> <?php echo htmlspecialchars($req['Targa_Veicolo_Cliente']); ?></p>
                <p><strong>Data Ritiro/Consegna:</strong> <?php echo date('d/m/Y H:i', strtotime($req['Data_Ora'])); ?></p>
                <p><strong>Indirizzo:</strong> <?php echo htmlspecialchars($req['Indirizzo']); ?></p>
                
                <form method="POST" style="display: flex; gap: 10px; margin-top: 15px; align-items: flex-end; flex-wrap: wrap;">
                    <input type="hidden" name="assegna_incarico" value="1">
                    <input type="hidden" name="id_incarico" value="<?php echo $req['Id_Incarico']; ?>">
                    
                    <div style="flex: 1; min-width: 200px;">
                        <label style="font-weight: bold; font-size: 14px; margin-bottom: 5px; display: block;">Assegna Logista:</label>
                        <select name="logista" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; outline: none;">
                            <option value="">-- Seleziona Logista --</option>
                            <?php foreach($logisti as $l): ?>
                                <option value="<?php echo htmlspecialchars($l['Id_User']); ?>"><?php echo htmlspecialchars($l['Nome']." ".$l['Cognome']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="flex: 1; min-width: 200px;">
                        <label style="font-weight: bold; font-size: 14px; margin-bottom: 5px; display: block;">Auto Sostitutiva:</label>
                        <select name="auto_sost" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; outline: none;">
                            <option value="">Nessuna</option>
                            <?php foreach($auto_libere as $a): ?>
                                <option value="<?php echo htmlspecialchars($a['Targa_Auto']); ?>"><?php echo htmlspecialchars($a['Marca']." ".$a['Modello']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" style="background: #28a745; color: white; border: none; padding: 9px 15px; border-radius: 4px; cursor: pointer; font-weight: bold;">Assegna</button>
                </form>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="text-align: center; color: #666; padding: 20px; background: #eee; border-radius: 5px;">Nessuna richiesta in attesa da assegnare.</p>
    <?php endif; ?>

    <br><a href="area_admin.php" style="text-decoration:none;">&larr; Indietro</a>
</div>

<?php include 'includes/footer.php'; ?>
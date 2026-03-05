<?php
require_once 'includes/db.php';
include 'includes/header.php';

$targa = $_GET['targa'] ?? '';

$stmt_check = $pdo->prepare("SELECT Id_Abbonamento FROM abbonamento WHERE Targa = :t AND Stato = 'Attivo'");
$stmt_check->execute(['t' => $targa]);
if (!$stmt_check->fetch()) { die("Accesso riservato agli abbonati Premium."); }

$stmt = $pdo->prepare("SELECT rc.* FROM report_condizioni rc 
                      JOIN incarico_logistico il ON rc.Id_Incarico = il.Id_Incarico 
                      WHERE il.Targa_Veicolo_Cliente = :t ORDER BY rc.Data_Ora DESC");
$stmt->execute(['t' => $targa]);
$reports = $stmt->fetchAll();
?>

<div style="max-width: 900px; margin: 20px auto; background: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
    <h2>📸 Report Stato d'Uso - <?php echo htmlspecialchars($targa); ?></h2>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 15px;">
        <?php if ($reports): foreach ($reports as $r): ?>
            <div style="border: 1px solid #ddd; padding: 15px; border-radius: 5px; background: #fdfdfd;">
                <h4 style="margin: 0; color: #20c997;"><?php echo htmlspecialchars($r['Fase']); ?></h4>
                <p style="font-size: 12px; color: #666;"><?php echo date('d/m/Y H:i', strtotime($r['Data_Ora'])); ?></p>
                
                <div style="background: #eee; height: 180px; display: flex; align-items: center; justify-content: center; margin: 10px 0; border-radius: 4px; overflow: hidden;">
                    <?php if (!empty($r['URL_Media'])): ?>
                        <img src="<?php echo htmlspecialchars($r['URL_Media']); ?>" alt="Foto Danno" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                    <?php else: ?>
                        <span style="font-size: 11px; color: #888;">Nessuna foto caricata</span>
                    <?php endif; ?>
                </div>
                
                <p style="font-size: 14px;"><strong>Danni:</strong> <?php echo htmlspecialchars($r['Descrizione_Danni']); ?></p>
                <p style="font-size: 12px; border-top: 1px solid #eee; padding-top: 5px;">Firmato: <?php echo htmlspecialchars($r['Firma_Accettazione']); ?></p>
            </div>
        <?php endforeach; else: ?>
            <p>Nessun report disponibile.</p>
        <?php endif; ?>
    </div>
    <br><a href="area_cliente.php" class="btn" style="background: #6c757d;">&larr; Indietro</a>
</div>

<?php include 'includes/footer.php'; ?>
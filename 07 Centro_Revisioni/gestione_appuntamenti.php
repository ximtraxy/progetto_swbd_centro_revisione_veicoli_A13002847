<?php
require_once 'includes/db.php';
include 'includes/header.php';

if (!isset($_SESSION['id_user']) || $_SESSION['ruolo'] !== 'Cliente') { 
    header("Location: index.php"); 
    exit; 
}

$targa = $_GET['targa'] ?? '';

// Verifica che il veicolo abbia l'abbonamento attivo
$stmt_abb = $pdo->prepare("SELECT * FROM abbonamento WHERE Targa = :t AND Stato = 'Attivo'");
$stmt_abb->execute(['t' => $targa]);
if (!$stmt_abb->fetch()) { 
    die("<div class='container' style='text-align: center; margin-top: 50px;'>
            <h2>Accesso Negato</h2>
            <p>Questo veicolo non ha un abbonamento Premium attivo.</p>
            <br>
            <a href='area_cliente.php' style='padding: 10px 15px; background: #6c757d; color: white; text-decoration: none; border-radius: 4px;'>&larr; Indietro</a>
         </div>"); 
}

// Estrazione dello storico incarichi logistici per questo veicolo
$stmt_log = $pdo->prepare("SELECT * FROM incarico_logistico WHERE Targa_Veicolo_Cliente = :t ORDER BY Data_Ora DESC");
$stmt_log->execute(['t' => $targa]);
$incarichi = $stmt_log->fetchAll();
?>

<div style="max-width: 800px; margin: 20px auto; background: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
    <h2>Servizi Logistici - <?php echo htmlspecialchars($targa); ?></h2>
    
    <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px; border-left: 5px solid #6f42c1;">
        <h4>Richiedi Ritiro/Consegna</h4>
        <p>Il servizio Premium include lo spostamento del mezzo e l'auto sostitutiva.</p>
        
        <a href="crea_richiesta.php?targa=<?php echo urlencode($targa); ?>" class="btn" style="background: #6f42c1; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; display: inline-block;">Nuova Richiesta</a>
    </div>

    <h3>Stato Incarichi</h3>
    <table style="width: 100%; border-collapse: collapse;">
        <tr style="background: #eee;">
            <th style="padding: 10px; text-align: left;">Data</th>
            <th style="padding: 10px; text-align: left;">Indirizzo</th>
            <th style="padding: 10px; text-align: left;">Stato</th>
        </tr>
        <?php if(count($incarichi) > 0): ?>
            <?php foreach ($incarichi as $i): ?>
            <tr style="border-bottom: 1px solid #ddd;">
                <td style="padding: 10px;"><?php echo date('d/m/Y H:i', strtotime($i['Data_Ora'])); ?></td>
                <td style="padding: 10px;"><?php echo htmlspecialchars($i['Indirizzo']); ?></td>
                <td style="padding: 10px;"><strong><?php echo htmlspecialchars($i['Stato']); ?></strong></td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="3" style="padding: 15px; text-align: center; color: #666;">Nessun incarico logistico registrato per questo veicolo.</td>
            </tr>
        <?php endif; ?>
    </table>
    <br><a href="area_cliente.php" class="btn" style="background: #6c757d; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px;">&larr; Indietro</a>
</div>

<?php include 'includes/footer.php'; ?>
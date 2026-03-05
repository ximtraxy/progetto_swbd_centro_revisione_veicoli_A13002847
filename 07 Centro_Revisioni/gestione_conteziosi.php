<?php
require_once 'includes/db.php';
include 'includes/header.php';

if (!isset($_SESSION['id_user']) || $_SESSION['ruolo'] !== 'Admin') {
    header("Location: index.php");
    exit;
}

$search_targa = $_GET['targa'] ?? '';

$query = "SELECT rc.*, il.Targa_Veicolo_Cliente, il.Indirizzo 
          FROM REPORT_CONDIZIONI rc 
          JOIN INCARICO_LOGISTICO il ON rc.Id_Incarico = il.Id_Incarico";

if (!empty($search_targa)) {
    $query .= " WHERE il.Targa_Veicolo_Cliente LIKE :targa";
}
$query .= " ORDER BY rc.Data_Ora DESC";

$stmt = $pdo->prepare($query);
if (!empty($search_targa)) {
    $stmt->execute(['targa' => "%$search_targa%"]);
} else {
    $stmt->execute();
}
$reports = $stmt->fetchAll();
?>

<div style="max-width: 1000px; margin: 20px auto; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
    <h2 style="color: #004080; border-bottom: 2px solid #004080; padding-bottom: 10px;">📁 Gestione Contenziosi e Storico Danni</h2>
    <p>Strumento di verifica per il confronto dei report stato d'uso (Check-in/out).</p>

    <form method="GET" style="margin: 20px 0; display: flex; gap: 10px; background: #f8f9fa; padding: 15px; border-radius: 5px;">
        <input type="text" name="targa" placeholder="Cerca per Targa..." value="<?php echo htmlspecialchars($search_targa); ?>" style="flex: 1; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
        <button type="submit" class="btn">Filtra Storico</button>
        <?php if($search_targa): ?>
            <a href="gestione_contenziosi.php" class="btn" style="background: #6c757d;">Reset</a>
        <?php endif; ?>
    </form>

    <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
        <thead>
            <tr style="background: #343a40; color: white; text-align: left;">
                <th style="padding: 12px;">Data/Ora</th>
                <th style="padding: 12px;">Veicolo</th>
                <th style="padding: 12px;">Fase</th>
                <th style="padding: 12px;">Danni Rilevati</th>
                <th style="padding: 12px;">Materiale Media</th>
                <th style="padding: 12px;">Firma Accettazione</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($reports): foreach ($reports as $r): ?>
            <tr style="border-bottom: 1px solid #ddd; font-size: 14px;">
                <td style="padding: 12px;"><?php echo date('d/m/Y H:i', strtotime($r['Data_Ora'])); ?></td>
                <td style="padding: 12px;"><strong><?php echo htmlspecialchars($r['Targa_Veicolo_Cliente']); ?></strong></td>
                <td style="padding: 12px;">
                    <span style="padding: 3px 8px; border-radius: 4px; color: white; background: <?php echo ($r['Fase'] == 'Check-in') ? '#17a2b8' : '#28a745'; ?>;">
                        <?php echo $r['Fase']; ?>
                    </span>
                </td>
                <td style="padding: 12px;"><?php echo nl2br(htmlspecialchars($r['Descrizione_Danni'])); ?></td>
                <td style="padding: 12px; font-style: italic; color: #666;">
                    <?php echo htmlspecialchars($r['URL_Media']); ?>
                </td>
                <td style="padding: 12px; font-weight: bold;"><?php echo htmlspecialchars($r['Firma_Accettazione']); ?></td>
            </tr>
            <?php endforeach; else: ?>
            <tr>
                <td colspan="6" style="padding: 20px; text-align: center; color: #999;">Nessun report trovato per i criteri selezionati.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div style="margin-top: 30px;">
        <a href="area_admin.php" class="btn" style="background: #004080;">&larr; Torna al Pannello Admin</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
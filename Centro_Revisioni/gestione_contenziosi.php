<?php
require_once 'includes/db.php';
include 'includes/header.php';

if (!isset($_SESSION['id_user']) || $_SESSION['ruolo'] !== 'Admin') {
    header("Location: index.php");
    exit;
}

$reports = $pdo->query("SELECT rc.*, il.Targa_Veicolo_Cliente 
                        FROM REPORT_CONDIZIONI rc 
                        JOIN INCARICO_LOGISTICO il ON rc.Id_Incarico = il.Id_Incarico 
                        ORDER BY rc.Data_Ora DESC")->fetchAll();
?>

<div style="max-width: 1000px; margin: 20px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
    <h2>📁 Archivio Report Condizioni (Contenziosi)</h2>
    <p>Documentazione legale e prove multimediali per la gestione dei danni.</p>
    
    <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
        <thead>
            <tr style="background: #343a40; color: white;">
                <th style="padding: 12px; text-align: left;">Data</th>
                <th style="padding: 12px; text-align: left;">Targa</th>
                <th style="padding: 12px; text-align: left;">Fase</th>
                <th style="padding: 12px; text-align: left;">Descrizione Danni</th>
                <th style="padding: 12px; text-align: left;">Media</th>
                <th style="padding: 12px; text-align: left;">Firma</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($reports as $r): ?>
            <tr style="border-bottom: 1px solid #ddd; font-size: 13px;">
                <td style="padding: 12px;"><?php echo date('d/m/Y H:i', strtotime($r['Data_Ora'])); ?></td>
                <td style="padding: 12px; font-weight: bold;"><?php echo htmlspecialchars($r['Targa_Veicolo_Cliente']); ?></td>
                <td style="padding: 12px;">
                    <span style="padding: 4px 8px; border-radius: 4px; background: <?php echo ($r['Fase'] == 'Check-in') ? '#e3f2fd' : '#f1f8e9'; ?>;">
                        <?php echo htmlspecialchars($r['Fase']); ?>
                    </span>
                </td>
                <td style="padding: 12px;"><?php echo nl2br(htmlspecialchars($r['Descrizione_Danni'])); ?></td>
                <td style="padding: 12px; min-width: 150px;">
                    <?php 
                    if (!empty($r['URL_Media'])) {
                        $file_list = explode(',', $r['URL_Media']);
                        $found = false;

                        foreach ($file_list as $file_path) {
                            $file_path = trim($file_path); 
                            if (!empty($file_path) && file_exists($file_path)) {
                                $found = true;
                                $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
                                
                                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                                    echo '<a href="'.$file_path.'" target="_blank" style="margin-right: 5px;">';
                                    echo '<img src="'.$file_path.'" style="width: 60px; height: 45px; object-fit: cover; border-radius: 3px; border: 1px solid #ccc; margin-bottom: 3px;">';
                                    echo '</a>';
                                } else {
                                    echo '<a href="'.$file_path.'" target="_blank" style="display: block; font-size: 11px; color: #007bff; margin-bottom: 3px;">📄 Vedi Video</a>';
                                }
                            }
                        }
                        if (!$found) echo '<span style="color: #999;">File non trovati</span>';
                    } else {
                        echo '<span style="color: #999;">Nessun media</span>';
                    }
                    ?>
                </td>
                <td style="padding: 12px; font-style: italic; color: #555;"><?php echo htmlspecialchars($r['Firma_Accettazione']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php if (count($reports) === 0): ?>
        <p style="text-align: center; padding: 30px; color: #666;">Nessun report di condizioni presente in archivio.</p>
    <?php endif; ?>

    <br>
    <a href="area_admin.php" style="text-decoration:none; color: #6c757d; font-weight: bold;">&larr; Torna al Pannello Amministratore</a>
</div>

<?php include 'includes/footer.php'; ?>
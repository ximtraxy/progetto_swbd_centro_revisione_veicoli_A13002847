<?php
require_once 'includes/db.php';
include 'includes/header.php';

if (!isset($_SESSION['id_user']) || $_SESSION['ruolo'] !== 'Admin') { 
    header("Location: index.php"); 
    exit; 
}

$update_scadenze = $pdo->prepare("UPDATE abbonamento SET Stato = 'Scaduto' WHERE Stato = 'Attivo' AND Data_Scadenza < CURDATE()");
$update_scadenze->execute();

$premium = $pdo->query("SELECT a.*, u.Nome, u.Cognome FROM abbonamento a JOIN utente u ON a.Id_Utente = u.Id_User ORDER BY a.Stato ASC, a.Data_Scadenza DESC")->fetchAll();
?>

<div style="max-width: 900px; margin: 20px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
    <h2 style="color: #004080; border-bottom: 2px solid #004080; padding-bottom: 10px;">Monitoraggio Abbonamenti Premium</h2>
    <p style="color: #666;">Elenco storico e attuale dei veicoli con servizio Premium.</p>

    <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
        <thead>
            <tr style="background: #28a745; color: white; text-align: left;">
                <th style="padding: 12px;">Cliente</th>
                <th style="padding: 12px;">Targa Veicolo</th>
                <th style="padding: 12px;">Data Sottoscrizione</th>
                <th style="padding: 12px;">Stato DB</th>
                <th style="padding: 12px;">Scadenza Abbonamento</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($premium as $p): ?>
            <tr style="border-bottom: 1px solid #ddd; background: <?php echo ($p['Stato'] == 'Scaduto') ? '#fdfdfe' : '#ffffff'; ?>;">
                <td style="padding: 12px;"><?php echo htmlspecialchars($p['Nome']." ".$p['Cognome']); ?></td>
                <td style="padding: 12px; font-weight: bold;"><?php echo htmlspecialchars($p['Targa']); ?></td>
                <td style="padding: 12px;"><?php echo date('d/m/Y', strtotime($p['Data_Sottoscrizione'])); ?></td>
                <td style="padding: 12px;">
                    <span style="font-weight: bold; color: <?php echo ($p['Stato'] == 'Attivo') ? '#28a745' : '#dc3545'; ?>;">
                        <?php echo htmlspecialchars($p['Stato']); ?>
                    </span>
                </td>
                <td style="padding: 12px;">
                    <?php 
                    $scadenza = strtotime($p['Data_Scadenza']);
                    $oggi = time();
                    $giorni_rimanenti = round(($scadenza - $oggi) / (60 * 60 * 24));
                    
                    echo date('d/m/Y', $scadenza); 
                    
                    if ($p['Stato'] == 'Scaduto') {
                        echo " <span style='color: #721c24; background: #f8d7da; padding: 2px 6px; border-radius: 4px; font-size: 11px; margin-left: 10px;'>Scaduto</span>";
                    } elseif ($giorni_rimanenti <= 30) {
                        echo " <span style='color: #856404; background: #fff3cd; padding: 2px 6px; border-radius: 4px; font-size: 11px; margin-left: 10px;'>Scade a breve ($giorni_rimanenti gg)</span>";
                    } else {
                        echo " <span style='color: #155724; background: #d4edda; padding: 2px 6px; border-radius: 4px; font-size: 11px; margin-left: 10px;'>Regolare</span>";
                    }
                    ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <?php if(count($premium) == 0): ?>
        <p style="text-align: center; padding: 20px; color: #888;">Nessun abbonamento Premium presente nel sistema.</p>
    <?php endif; ?>

    <br><a href="area_admin.php" class="btn" style="background: #004080; text-decoration: none; color: white; padding: 10px 15px; border-radius: 4px;">&larr; Torna al Pannello Admin</a>
</div>

<?php include 'includes/footer.php'; ?>
<?php
require_once 'includes/db.php';
include 'includes/header.php';

$targa = $_GET['targa'] ?? '';

$stmt = $pdo->prepare("SELECT v.Scadenza_Revisione, a.Data_Scadenza 
                       FROM VEICOLO v 
                       LEFT JOIN ABBONAMENTO a ON v.Targa = a.Targa AND a.Stato = 'Attivo'
                       WHERE v.Targa = :t");
$stmt->execute(['t' => $targa]);
$dati = $stmt->fetch();

if (!$dati || !$dati['Data_Scadenza']) {
    die("<div class='container'><h2>Accesso Negato</h2><p>Servizio riservato ai veicoli con abbonamento Premium attivo.</p><a href='area_cliente.php'>Indietro</a></div>");
}

$oggi = time();
$scadenza_premium = strtotime($dati['Data_Scadenza']);
$giorni_premium = round(($scadenza_premium - $oggi) / 86400);

$scadenza_revisione = strtotime($dati['Scadenza_Revisione']);
$giorni_revisione = round(($scadenza_revisione - $oggi) / 86400);
?>

<div style="max-width: 800px; margin: 20px auto; background: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); text-align: center;">
    <h2>Monitoraggio Scadenze Veicolo</h2>
    <p>Cruscotto preventivo per la targa: <strong><?php echo htmlspecialchars($targa); ?></strong></p>

    <div style="display: flex; gap: 20px; justify-content: center; margin: 30px 0;">
        
        <div style="flex: 1; padding: 20px; background: #fff3cd; border: 5px solid #fd7e14; border-radius: 15px;">
            <h4 style="margin: 0; color: #856404;">Abbonamento Premium</h4>
            <span style="font-size: 48px; font-weight: bold;"><?php echo $giorni_premium; ?></span><br>
            <span>giorni rimanenti</span>
            <p style="font-size: 13px; margin-top: 10px;">Scade il: <?php echo date('d/m/Y', $scadenza_premium); ?></p>
        </div>

        <div style="flex: 1; padding: 20px; background: #d1ecf1; border: 5px solid #0c5460; border-radius: 15px;">
            <h4 style="margin: 0; color: #0c5460;">Revisione Ministeriale</h4>
            <span style="font-size: 48px; font-weight: bold; color: <?php echo ($giorni_revisione < 30) ? 'red' : 'inherit'; ?>;">
                <?php echo $giorni_revisione; ?>
            </span><br>
            <span>giorni alla scadenza</span>
            <p style="font-size: 13px; margin-top: 10px;">Scadenza ufficiale: <strong><?php echo date('d/m/Y', $scadenza_revisione); ?></strong></p>
        </div>

    </div>

    <div style="text-align: left; background: #f8f9fa; padding: 15px; border-radius: 5px; border-left: 5px solid #fd7e14;">
        <h4>Note di Monitoraggio:</h4>
        <ul style="font-size: 14px; line-height: 1.6;">
            <li>Il sistema interroga la data di scadenza ufficiale salvata nel profilo del veicolo.</li>
            <li>In caso di revisione superata, il Tecnico aggiornerà automaticamente la data a +2 anni.</li>
            <?php if ($giorni_revisione < 0): ?>
                <li style="color: red; font-weight: bold;">ATTENZIONE: La revisione ministeriale risulta SCADUTA!</li>
            <?php endif; ?>
        </ul>
    </div>
    
    <br><a href="area_cliente.php" class="btn" style="background: #6c757d;">&larr; Torna alla Gestione Veicoli</a>
</div>

<?php include 'includes/footer.php'; ?>
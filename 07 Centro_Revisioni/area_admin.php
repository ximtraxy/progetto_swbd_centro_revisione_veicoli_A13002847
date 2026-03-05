<?php
require_once 'includes/db.php';
include 'includes/header.php';

if (!isset($_SESSION['id_user']) || $_SESSION['ruolo'] !== 'Admin') {
    header("Location: index.php");
    exit;
}

$ricavi_tot = $pdo->query("SELECT SUM(Importo) FROM PAGAMENTO")->fetchColumn() ?: 0;
$flotta = $pdo->query("SELECT COUNT(*) as tot, SUM(CASE WHEN Stato='Libera' THEN 1 ELSE 0 END) as ok FROM AUTO_SOSTITUTIVA")->fetch();
$prod_staff = $pdo->query("SELECT u.Nome, u.Cognome, COUNT(r.Id_Revisione) as n 
                           FROM UTENTE u JOIN REVISIONE r ON u.Id_User = r.Id_Utente_Tecnico 
                           WHERE r.Esito != 'Da effettuare' GROUP BY u.Id_User")->fetchAll();

$scadenze = $pdo->query("SELECT Targa, Scadenza_Revisione FROM VEICOLO 
                         WHERE Scadenza_Revisione <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) 
                         ORDER BY Scadenza_Revisione ASC LIMIT 5")->fetchAll();
?>

<div style="text-align: center; margin-bottom: 30px;">
    <h2>Pannello Amministratore</h2>
    <p>Monitoraggio Avanzato e Gestione Strategica</p>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 30px;">
    <div style="background: #e7f3ff; padding: 15px; border-radius: 8px; border: 1px solid #b3d7ff; text-align: center;">
        <h4 style="margin:0;">Ricavi</h4>
        <p style="font-size: 22px; font-weight: bold; margin: 10px 0;">€ <?php echo number_format($ricavi_tot, 2, ',', '.'); ?></p>
    </div>
    <div style="background: #f0fff4; padding: 15px; border-radius: 8px; border: 1px solid #c3e6cb; text-align: center;">
        <h4 style="margin:0;">Flotta Libera</h4>
        <p style="font-size: 22px; font-weight: bold; margin: 10px 0;"><?php echo $flotta['ok']; ?> / <?php echo $flotta['tot']; ?></p>
    </div>
    <div style="background: #fff3cd; padding: 15px; border-radius: 8px; border: 1px solid #ffeeba; text-align: center;">
        <h4 style="margin:0;">Alert Scadenze</h4>
        <p style="font-size: 22px; font-weight: bold; margin: 10px 0;"><?php echo count($scadenze); ?></p>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 25px;">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
        <h3 style="grid-column: span 2; border-bottom: 2px solid #eee;">Gestione Moduli</h3>
        <a href="gestione_abbonamenti.php" class="btn" style="background: #28a745; padding: 20px; text-align: center;">Gestione Abbonamenti Premium</a>
        <a href="gestione_personale.php" class="btn" style="background: #17a2b8; padding: 20px; text-align: center;">Gestione Personale (Tecnici/Logisti)</a>
        <a href="gestione_flotta.php" class="btn" style="background: #6c757d; padding: 20px; text-align: center;">Gestione Flotta Auto Sostitutive</a>
        <a href="gestione_logistica_admin.php" class="btn" style="background: #fd7e14; padding: 20px; text-align: center;">Assegnazione Incarichi Logistici</a>
        <a href="gestione_contenziosi.php" class="btn" style="background: #343a40; padding: 20px; text-align: center;">Gestione Contenziosi/Danni</a>
        <a href="invia_notifiche.php" class="btn" style="background: #0056b3; padding: 20px; text-align: center; color: white;">Centro Invio Notifiche</a>
    </div>

    <div style="background: #fff; padding: 15px; border: 1px solid #ddd; border-radius: 8px;">
        <h3>Produttività Staff</h3>
        <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
            <?php foreach($prod_staff as $ps): ?>
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 8px;"><?php echo htmlspecialchars($ps['Nome']." ".$ps['Cognome']); ?></td>
                <td style="padding: 8px; text-align: right;"><strong><?php echo $ps['n']; ?> rev.</strong></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
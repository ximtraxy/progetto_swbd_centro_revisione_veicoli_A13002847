<?php
require_once 'includes/db.php';
include 'includes/header.php';

if (!isset($_SESSION['id_user']) || $_SESSION['ruolo'] !== 'Logista') {
    header("Location: index.php");
    exit;
}

$id_logista = $_SESSION['id_user'];

$stmt = $pdo->prepare("SELECT il.*, v.Marca, v.Modello 
                       FROM INCARICO_LOGISTICO il 
                       JOIN VEICOLO v ON il.Targa_Veicolo_Cliente = v.Targa 
                       WHERE il.Id_Utente_Autista = :id_l 
                       AND il.Stato != 'Completato' 
                       ORDER BY il.Data_Ora ASC");
$stmt->execute(['id_l' => $id_logista]);
$incarichi = $stmt->fetchAll();
?>

<div style="text-align: center; margin-top: 20px;">
    <h2>Area Logistica: Gestione Trasporti</h2>
    <p>Elenco ritiri e riconsegne veicoli Premium assegnati al tuo profilo.</p>
</div>

<div style="display: flex; flex-direction: column; align-items: center; gap: 20px; margin-top: 30px; margin-bottom: 40px;">
    <?php if (count($incarichi) > 0): ?>
        <?php foreach ($incarichi as $i): ?>
            <div style="background: #fffaf0; border-left: 5px solid #fd7e14; padding: 20px; border-radius: 8px; width: 100%; max-width: 850px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center;">
                
                <div style="flex: 2;">
                    <h3 style="margin: 0; color: #856404;"><?php echo htmlspecialchars($i['Marca'] . " " . $i['Modello'] . " (" . $i['Targa_Veicolo_Cliente'] . ")"); ?></h3>
                    <p style="margin: 5px 0;">📍 Indirizzo: <strong><?php echo htmlspecialchars($i['Indirizzo']); ?></strong></p>
                    <p style="margin: 5px 0; font-size: 14px;">Auto Sostitutiva: <strong><?php echo htmlspecialchars($i['Targa_Auto_Sost'] ?? 'Nessuna'); ?></strong></p>
                    <p style="margin: 5px 0; font-size: 14px;">Data/Ora: <strong><?php echo date('d/m/Y H:i', strtotime($i['Data_Ora'])); ?></strong></p>
                </div>

                <div style="flex: 1; text-align: right;">
                    <div style="margin-bottom: 10px;">
                        <span style="background: #fd7e14; color: white; padding: 5px 10px; border-radius: 4px; font-weight: bold; font-size: 12px;">
                            <?php echo strtoupper($i['Stato']); ?>
                        </span>
                    </div>
                    <a href="gestione_incarico_logistico.php?id=<?php echo $i['Id_Incarico']; ?>" class="btn" style="background-color: #fd7e14;">Gestisci Incarico</a>
                </div>
                
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Non ci sono incarichi logistici pendenti.</p>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
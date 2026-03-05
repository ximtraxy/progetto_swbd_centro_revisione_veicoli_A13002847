<?php
require_once 'includes/db.php';
include 'includes/header.php';

if (!isset($_SESSION['id_user']) || $_SESSION['ruolo'] !== 'Tecnico') {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT r.*, v.Marca, v.Modello, v.Categoria 
                       FROM REVISIONE r 
                       JOIN VEICOLO v ON r.Targa = v.Targa 
                       WHERE r.Esito = 'Da effettuare' 
                       ORDER BY r.Data_ora ASC");
$stmt->execute();
$incarichi = $stmt->fetchAll();
?>

<div style="text-align: center; margin-top: 20px;">
    <h2>Area Tecnico: Gestione Incarichi</h2>
    <p>Visualizzazione delle revisioni pendenti.</p>
</div>

<div style="display: flex; flex-direction: column; align-items: center; gap: 20px; margin-top: 30px; margin-bottom: 40px;">
    <?php if (count($incarichi) > 0): ?>
        <?php foreach ($incarichi as $i): ?>
            <div style="background: #f0fff4; border-left: 5px solid #28a745; padding: 20px; border-radius: 8px; width: 100%; max-width: 800px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center;">
                
                <div>
                    <h3 style="margin: 0; color: #155724; display: flex; align-items: center;">
                        <?php echo htmlspecialchars($i['Marca'] . " " . $i['Modello']); ?>
                        <span style="background: #17a2b8; color: white; padding: 4px 10px; border-radius: 12px; font-size: 13px; margin-left: 15px; font-weight: normal;">
                            <?php echo htmlspecialchars($i['Categoria']); ?>
                        </span>
                    </h3>
                    <p style="margin: 8px 0 5px 0;">Targa: <strong><?php echo htmlspecialchars($i['Targa']); ?></strong></p>
                    <p style="margin: 5px 0; font-size: 14px; color: #666;">
                        Appuntamento: <strong><?php echo date('d/m/Y H:i', strtotime($i['Data_ora'])); ?></strong>
                    </p>
                </div>

                <div>
                    <a href="registra_esito.php?id=<?php echo $i['Id_Revisione']; ?>" class="btn" style="background-color: #28a745; padding: 12px 20px; font-weight: bold; text-decoration: none; color: white; border-radius: 4px;">
                        Registra Esito
                    </a>
                </div>
                
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div style="text-align: center; padding: 40px; background: #f8f9fa; border-radius: 10px; border: 1px dashed #ccc;">
            <p style="font-size: 18px; color: #666;">Non ci sono revisioni in attesa al momento.</p>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
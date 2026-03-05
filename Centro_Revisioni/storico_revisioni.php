<?php
require_once 'includes/db.php';
include 'includes/header.php';

if (!isset($_SESSION['id_user']) || $_SESSION['ruolo'] !== 'Cliente') {
    header("Location: index.php");
    exit;
}

if (!isset($_GET['targa'])) {
    header("Location: area_cliente.php");
    exit;
}

$targa = $_GET['targa'];
$id_utente = $_SESSION['id_user'];

$stmt_check = $pdo->prepare("SELECT * FROM veicolo WHERE Targa = :targa AND Id_Utente = :id_utente");
$stmt_check->execute(['targa' => $targa, 'id_utente' => $id_utente]);
if ($stmt_check->rowCount() == 0) {
    echo "<div style='color: red; padding: 20px;'>Veicolo non autorizzato.</div>";
    include 'includes/footer.php';
    exit;
}

$messaggio = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['elimina_id'])) {
    $id_revisione_da_eliminare = $_POST['elimina_id'];
    
    $stmt_delete = $pdo->prepare("DELETE FROM revisione WHERE Id_Revisione = :id_rev AND Targa = :targa");
    try {
        $stmt_delete->execute(['id_rev' => $id_revisione_da_eliminare, 'targa' => $targa]);
        $messaggio = "<div style='background: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border-radius: 4px;'>Revisione eliminata con successo dal database!</div>";
    } catch (PDOException $e) {
        $messaggio = "<div style='background: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border-radius: 4px;'>Errore durante l'eliminazione.</div>";
    }
}

$stmt_revisioni = $pdo->prepare("SELECT * FROM revisione WHERE Targa = :targa ORDER BY Data_ora DESC");
$stmt_revisioni->execute(['targa' => $targa]);
$revisioni = $stmt_revisioni->fetchAll();
?>

<div style="max-width: 800px; margin: 20px auto; background: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
    <h2>Storico Revisioni per la targa: <span style="color: #004080;"><?php echo htmlspecialchars($targa); ?></span></h2>
    
    <?php echo $messaggio; ?>
    <a href="area_cliente.php" class="btn" style="background: #6c757d; margin-bottom: 20px; display: inline-block;">&larr; Torna ai Veicoli</a>

    <?php if (count($revisioni) > 0): ?>
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <tr style="background-color: #004080; color: white;">
                <th style="padding: 10px;">Data e Ora</th>
                <th style="padding: 10px;">Esito</th>
                <th style="padding: 10px;">Note Tecnico</th>
                <th style="padding: 10px;">Azione</th>
            </tr>
            <?php foreach ($revisioni as $rev): ?>
                <tr style="border-bottom: 1px solid #ddd;">
                    <td style="padding: 10px;"><?php echo date('d/m/Y H:i', strtotime($rev['Data_ora'])); ?></td>
                    <td style="padding: 10px;">
                        <?php 
                        $colore = '#333';
                        if($rev['Esito'] == 'Regolare') $colore = 'green';
                        if($rev['Esito'] == 'Da effettuare') $colore = 'orange';
                        if($rev['Esito'] == 'Ripetere' || $rev['Esito'] == 'Sospeso') $colore = 'red';
                        echo "<strong style='color: $colore;'>" . htmlspecialchars($rev['Esito']) . "</strong>"; 
                        ?>
                    </td>
                    <td style="padding: 10px;"><?php echo htmlspecialchars($rev['Note'] ?? 'Nessuna nota'); ?></td>
                    <td style="padding: 10px;">
                        <form action="storico_revisioni.php?targa=<?php echo urlencode($targa); ?>" method="POST" onsubmit="return confirm('Sei sicuro di voler eliminare questa revisione dal sistema?');">
                            <input type="hidden" name="elimina_id" value="<?php echo $rev['Id_Revisione']; ?>">
                            <button type="submit" style="background: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;">Elimina</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>Non ci sono revisioni registrate per questo veicolo.</p>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
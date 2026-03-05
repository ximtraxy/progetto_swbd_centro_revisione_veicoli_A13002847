<?php
require_once 'includes/db.php';
include 'includes/header.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: index.php");
    exit;
}

$id_utente = $_SESSION['id_user'];

$stmt = $pdo->prepare("SELECT * FROM notifica WHERE Id_Utente = :id_utente ORDER BY Data DESC");
$stmt->execute(['id_utente' => $id_utente]);
$notifiche = $stmt->fetchAll();

$update_stmt = $pdo->prepare("UPDATE notifica SET Letta = 1 WHERE Id_Utente = :id_utente AND Letta = 0");
$update_stmt->execute(['id_utente' => $id_utente]);
?>

<div style="max-width: 800px; margin: 30px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
    <h2 style="color: #0056b3; border-bottom: 2px solid #0056b3; padding-bottom: 10px;">Le tue Notifiche</h2>

    <?php if (count($notifiche) > 0): ?>
        <div style="display: flex; flex-direction: column; gap: 15px; margin-top: 20px;">
            <?php foreach ($notifiche as $n): ?>
                <div style="padding: 15px; border-radius: 5px; border-left: 5px solid <?php echo ($n['Letta'] == 0) ? '#28a745' : '#ccc'; ?>; background: <?php echo ($n['Letta'] == 0) ? '#f0fdf4' : '#f9f9f9'; ?>;">
                    <p style="margin: 0; font-size: 16px; color: #333;">
                        <?php if ($n['Letta'] == 0): ?>
                            <span style="background: #28a745; color: white; padding: 2px 6px; border-radius: 4px; font-size: 11px; margin-right: 5px; vertical-align: middle;">NUOVA</span>
                        <?php endif; ?>
                        <?php echo htmlspecialchars($n['Testo']); ?>
                    </p>
                    <p style="margin: 8px 0 0 0; font-size: 12px; color: #888;">
                        <?php echo date('d/m/Y H:i', strtotime($n['Data'])); ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div style="padding: 30px; text-align: center; background: #f4f4f4; border-radius: 5px; margin-top: 20px; color: #666; font-size: 16px;">
            Non hai nessuna notifica al momento.
        </div>
    <?php endif; ?>

    <br>
    <a href="index.php" style="text-decoration:none; color: #6c757d; font-weight: bold;">&larr; Torna alla Home</a>
</div>

<?php include 'includes/footer.php'; ?>
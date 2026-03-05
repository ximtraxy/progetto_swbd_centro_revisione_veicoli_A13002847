<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$notifiche_non_lette = 0;
if (isset($_SESSION['id_user']) && isset($pdo)) {
    $stmt_notifiche = $pdo->prepare("SELECT COUNT(*) FROM notifica WHERE Id_Utente = ? AND Letta = 0");
    $stmt_notifiche->execute([$_SESSION['id_user']]);
    $notifiche_non_lette = $stmt_notifiche->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Centro Revisione Veicoli</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>Centro Revisione Veicoli</h1>
    </header>
    <nav>
        <a href="index.php">Home</a>
        <?php if(isset($_SESSION['id_user'])): ?>
            <?php if($_SESSION['ruolo'] == 'Cliente'): ?>
                <a href="area_cliente.php">Area Cliente</a>
            <?php elseif($_SESSION['ruolo'] == 'Tecnico'): ?>
                <a href="area_tecnico.php">Incarichi Officina</a>
            <?php elseif($_SESSION['ruolo'] == 'Logista'): ?>
                <a href="area_logista.php">Gestione Logistica</a>
            <?php elseif($_SESSION['ruolo'] == 'Admin'): ?>
                <a href="area_admin.php">Pannello Amministratore</a>
            <?php endif; ?>
            
            <a href="notifiche.php">
                Notifiche 
                <?php if ($notifiche_non_lette > 0): ?>
                    <span style="background: red; color: white; border-radius: 50%; padding: 2px 6px; font-size: 12px; font-weight: bold;">
                        <?php echo $notifiche_non_lette; ?>
                    </span>
                <?php endif; ?>
            </a>

            <a href="logout.php" style="float: right;">Logout (<?php echo htmlspecialchars($_SESSION['nome']); ?>)</a>
        <?php else: ?>
            <a href="login.php">Login</a>
        <?php endif; ?>
    </nav>
    <div class="container">
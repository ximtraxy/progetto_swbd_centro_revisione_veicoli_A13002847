<?php
require_once 'includes/db.php';
include 'includes/header.php';
?>

<h2>Benvenuto nel portale del Centro Revisioni</h2>
<p>Gestisci le tue revisioni, prenota ritiri a domicilio e consulta i report del tuo veicolo in modo semplice e veloce.</p>

<?php if(!isset($_SESSION['id_user'])): ?>
    <div style="margin-top: 20px;">
        <p>Per accedere ai servizi e gestire i tuoi veicoli, effettua l'accesso.</p>
        <a href="login.php" class="btn">Vai al Login</a>
    </div>
<?php else: ?>
    <div style="margin-top: 20px; padding: 15px; background: #e6f2ff; border-left: 4px solid #004080;">
        <h3>Accesso effettuato come: <?php echo htmlspecialchars($_SESSION['ruolo']); ?></h3>
        <p>Usa il menù di navigazione in alto per accedere alle funzionalità del tuo profilo.</p>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
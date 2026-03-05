<?php
require_once 'includes/db.php';
include 'includes/header.php';

if (!isset($_SESSION['id_user']) || $_SESSION['ruolo'] !== 'Admin') { 
    header("Location: index.php"); 
    exit; 
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nuovo_staff'])) {
    $password_hash = password_hash($_POST['pass'], PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO UTENTE (Nome, Cognome, `E-mail`, Password, Ruolo) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$_POST['nome'], $_POST['cognome'], $_POST['email'], $password_hash, $_POST['ruolo']]);
    echo "<p style='color:green; text-align:center;'>Nuovo operatore aggiunto!</p>";
}

$staff = $pdo->query("SELECT * FROM UTENTE WHERE Ruolo IN ('Tecnico', 'Logista')")->fetchAll();
?>

<div style="max-width: 800px; margin: 20px auto; padding: 20px; background: #fff; border-radius: 8px;">
    <h2>Gestione Personale</h2>
    
    <div id="js-error" style="display: none; background: #ffcccc; color: #cc0000; padding: 10px; margin-bottom: 15px; border-radius: 4px; text-align: center; font-size: 14px;"></div>

    <form id="staffForm" method="POST" style="background: #f8f9fa; padding: 15px; border-radius: 5px; display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
        <input type="hidden" name="nuovo_staff" value="1">
        <input type="text" id="nome" name="nome" placeholder="Nome" required style="padding: 8px; border: 1px solid #ccc; border-radius: 4px; outline: none; transition: border 0.3s;">
        <input type="text" id="cognome" name="cognome" placeholder="Cognome" required style="padding: 8px; border: 1px solid #ccc; border-radius: 4px; outline: none; transition: border 0.3s;">
        <input type="email" id="email" name="email" placeholder="Email" required style="padding: 8px; border: 1px solid #ccc; border-radius: 4px; outline: none; transition: border 0.3s;">
        <input type="password" id="pass" name="pass" placeholder="Password" required style="padding: 8px; border: 1px solid #ccc; border-radius: 4px; outline: none; transition: border 0.3s;">
        <select name="ruolo" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px; outline: none;">
            <option value="Tecnico">Tecnico</option>
            <option value="Logista">Logista</option>
        </select>
        <button type="submit" class="btn" style="background: #17a2b8; padding: 8px;">Aggiungi Staff</button>
    </form>

    <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
        <tr style="background: #004080; color: white;">
            <th style="padding: 10px;">Nome Completo</th>
            <th style="padding: 10px;">Email</th>
            <th style="padding: 10px;">Ruolo</th>
        </tr>
        <?php foreach($staff as $s): ?>
        <tr style="border-bottom: 1px solid #ddd;">
            <td style="padding: 10px;"><?php echo htmlspecialchars($s['Nome']." ".$s['Cognome']); ?></td>
            <td style="padding: 10px;"><?php echo htmlspecialchars($s['E-mail']); ?></td>
            <td style="padding: 10px;"><strong><?php echo htmlspecialchars($s['Ruolo']); ?></strong></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <br><a href="area_admin.php" style="text-decoration:none;">&larr; Indietro</a>
</div>


<script>
document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById("staffForm");
    const nomeInput = document.getElementById("nome");
    const cognomeInput = document.getElementById("cognome");
    const emailInput = document.getElementById("email");
    const passInput = document.getElementById("pass");
    const errorBox = document.getElementById("js-error");

    const nameRegex = /^[A-Za-z\s']{2,}$/;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    function validateInput(input, regex) {
        input.addEventListener("input", function() {
            if (input.value.length > 0 && !regex.test(input.value)) {
                input.style.border = "2px solid red";
            } else if (regex.test(input.value)) {
                input.style.border = "2px solid green";
            } else {
                input.style.border = "1px solid #ccc";
            }
        });
    }

    validateInput(nomeInput, nameRegex);
    validateInput(cognomeInput, nameRegex);
    validateInput(emailInput, emailRegex);

    passInput.addEventListener("input", function() {
        if (this.value.length > 0 && this.value.length < 6) {
            this.style.border = "2px solid red";
        } else if (this.value.length >= 6) {
            this.style.border = "2px solid green";
        } else {
            this.style.border = "1px solid #ccc";
        }
    });

    if (form) {
        form.addEventListener("submit", function(event) {
            let errors = [];
            errorBox.style.display = "none";
            errorBox.innerHTML = "";

            if (!nameRegex.test(nomeInput.value.trim())) {
                errors.push("Il nome deve contenere almeno 2 caratteri alfabetici.");
                nomeInput.style.border = "2px solid red";
            }

            if (!nameRegex.test(cognomeInput.value.trim())) {
                errors.push("Il cognome deve contenere almeno 2 caratteri alfabetici.");
                cognomeInput.style.border = "2px solid red";
            }

            if (!emailRegex.test(emailInput.value)) {
                errors.push("Inserisci un indirizzo email valido.");
                emailInput.style.border = "2px solid red";
            }

            if (passInput.value.length < 6) {
                errors.push("La password deve essere di almeno 6 caratteri.");
                passInput.style.border = "2px solid red";
            }

            if (errors.length > 0) {
                event.preventDefault();
                errorBox.innerHTML = errors.join("<br>");
                errorBox.style.display = "block";
            }
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>
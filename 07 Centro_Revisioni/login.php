<?php
require_once 'includes/db.php';
include 'includes/header.php';

if(isset($_SESSION['id_user'])) {
    header("Location: index.php");
    exit;
}

$errore = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    if (empty($email) || empty($password)) {
        $errore = "Inserisci email e password.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM utente WHERE `E-mail` = :email");
        $stmt->execute(['email' => $email]);
        $utente = $stmt->fetch();
        
        if ($utente && password_verify($password, $utente['Password'])) {
            $_SESSION['id_user'] = $utente['Id_User'];
            $_SESSION['nome'] = $utente['Nome'];
            $_SESSION['ruolo'] = $utente['Ruolo'];
            
            header("Location: index.php");
            exit;
        } else {
            $errore = "Credenziali non valide.";
        }
    }
}
?>

<div style="max-width: 400px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); margin-top: 40px;">
    <h2 style="text-align: center; color: #004080;">Accesso al Sistema</h2>
    
    <?php if(!empty($errore)): ?>
        <div style="background: #ffcccc; color: #cc0000; padding: 10px; margin-bottom: 15px; border-radius: 4px; text-align: center;">
            <?php echo $errore; ?>
        </div>
    <?php endif; ?>

    <div id="js-error" style="display: none; background: #ffcccc; color: #cc0000; padding: 10px; margin-bottom: 15px; border-radius: 4px; text-align: center; font-size: 14px;"></div>

    <form id="loginForm" action="login.php" method="POST" style="display: flex; flex-direction: column; gap: 15px;">
        <div>
            <label for="email">E-mail:</label><br>
            <input type="email" id="email" name="email" required autocomplete="email" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; outline: none; transition: border 0.3s;">
        </div>
        
        <div>
            <label for="password">Password:</label><br>
            <input type="password" id="password" name="password" required autocomplete="current-password" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; outline: none; transition: border 0.3s;">
        </div>
        
        <button type="submit" class="btn" style="width: 100%; margin-top: 10px; padding: 12px; font-size: 16px;">Accedi</button>
    </form>

    <div style="margin-top: 20px; text-align: center; font-size: 14px; border-top: 1px solid #eee; padding-top: 15px;">
        <p style="margin-bottom: 5px;">Sei un nuovo cliente?</p>
        <a href="registrazione.php" style="color: #004080; font-weight: bold; text-decoration: none;">Registra il tuo profilo e i tuoi veicoli</a>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const loginForm = document.getElementById("loginForm");
    const emailInput = document.getElementById("email");
    const passwordInput = document.getElementById("password");
    const errorBox = document.getElementById("js-error");

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    emailInput.addEventListener("input", function() {
        if (emailInput.value.length > 0 && !emailRegex.test(emailInput.value)) {
            emailInput.style.border = "2px solid red";
        } else if (emailRegex.test(emailInput.value)) {
            emailInput.style.border = "2px solid green";
        } else {
            emailInput.style.border = "1px solid #ccc";
        }
    });

    passwordInput.addEventListener("input", function() {
        if (passwordInput.value.trim().length > 0) {
            passwordInput.style.border = "2px solid green";
        } else {
            passwordInput.style.border = "1px solid #ccc";
        }
    });

    loginForm.addEventListener("submit", function(event) {
        let messaggiErrore = [];

        errorBox.style.display = "none";
        errorBox.innerHTML = "";

        if (!emailRegex.test(emailInput.value)) {
            messaggiErrore.push("Inserisci un indirizzo email valido (es. nome@dominio.it).");
            emailInput.style.border = "2px solid red";
        }

        if (passwordInput.value.trim() === "") {
            messaggiErrore.push("La password non può essere vuota.");
            passwordInput.style.border = "2px solid red";
        }

        if (messaggiErrore.length > 0) {
            event.preventDefault();
            errorBox.innerHTML = messaggiErrore.join("<br>");
            errorBox.style.display = "block";
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>
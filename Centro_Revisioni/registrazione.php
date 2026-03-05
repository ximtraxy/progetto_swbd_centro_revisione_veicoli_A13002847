<?php
require_once 'includes/db.php';
include 'includes/header.php';

if(isset($_SESSION['id_user'])) {
    header("Location: index.php");
    exit;
}

$messaggio = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome']);
    $cognome = trim($_POST['cognome']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($nome) || empty($cognome) || empty($email) || empty($password)) {
        $messaggio = "<div style='color: red;'>Tutti i campi sono obbligatori.</div>";
    } else {
        $stmt_check = $pdo->prepare("SELECT * FROM utente WHERE `E-mail` = :email");
        $stmt_check->execute(['email' => $email]);
        
        if ($stmt_check->rowCount() > 0) {
            $messaggio = "<div style='color: red;'>Questa e-mail è già registrata.</div>";
        } else {
            $password_hash = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $pdo->prepare("INSERT INTO utente (Nome, Cognome, `E-mail`, Password, Ruolo) VALUES (:nome, :cognome, :email, :password, 'Cliente')");
            
            try {
                $stmt->execute([
                    'nome' => $nome,
                    'cognome' => $cognome,
                    'email' => $email,
                    'password' => $password_hash
                ]);
                $messaggio = "<div style='color: green;'>Registrazione completata con successo! Ora puoi effettuare il <a href='login.php'>Login</a>.</div>";
            } catch (PDOException $e) {
                $messaggio = "<div style='color: red;'>Errore durante la registrazione. Riprova.</div>";
            }
        }
    }
}
?>

<div style="max-width: 400px; margin: 0 auto;">
    <h2>Registrazione Nuovo Cliente</h2>
    
    <?php echo $messaggio; ?>

    <div id="js-error" style="display: none; background: #ffcccc; color: #cc0000; padding: 10px; margin-bottom: 15px; border-radius: 4px; text-align: center; font-size: 14px;"></div>

    <form id="registerForm" action="registrazione.php" method="POST" style="display: flex; flex-direction: column; gap: 15px; margin-top: 15px;">
        <div>
            <label for="nome">Nome:</label><br>
            <input type="text" id="nome" name="nome" required style="width: 100%; padding: 8px; border: 1px solid #ccc; outline: none; transition: border 0.3s; border-radius: 4px;">
        </div>
        
        <div>
            <label for="cognome">Cognome:</label><br>
            <input type="text" id="cognome" name="cognome" required style="width: 100%; padding: 8px; border: 1px solid #ccc; outline: none; transition: border 0.3s; border-radius: 4px;">
        </div>

        <div>
            <label for="email">E-mail:</label><br>
            <input type="email" id="email" name="email" required style="width: 100%; padding: 8px; border: 1px solid #ccc; outline: none; transition: border 0.3s; border-radius: 4px;">
        </div>
        
        <div>
            <label for="password">Password:</label><br>
            <input type="password" id="password" name="password" required style="width: 100%; padding: 8px; border: 1px solid #ccc; outline: none; transition: border 0.3s; border-radius: 4px;">
        </div>
        
        <button type="submit" class="btn" style="width: 100%; margin-top: 10px; background: #28a745; padding: 12px;">Crea Account</button>
    </form>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const registerForm = document.getElementById("registerForm");
    const nomeInput = document.getElementById("nome");
    const cognomeInput = document.getElementById("cognome");
    const emailInput = document.getElementById("email");
    const passwordInput = document.getElementById("password");
    const errorBox = document.getElementById("js-error");

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const nameRegex = /^[A-Za-z\s']{2,}$/;

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
        if (passwordInput.value.length > 0 && passwordInput.value.length < 6) {
            passwordInput.style.border = "2px solid red";
        } else if (passwordInput.value.length >= 6) {
            passwordInput.style.border = "2px solid green";
        } else {
            passwordInput.style.border = "1px solid #ccc";
        }
    });

    registerForm.addEventListener("submit", function(event) {
        let messaggiErrore = [];

        errorBox.style.display = "none";
        errorBox.innerHTML = "";

        if (!nameRegex.test(nomeInput.value.trim())) {
            messaggiErrore.push("Il nome deve contenere almeno 2 caratteri alfabetici.");
            nomeInput.style.border = "2px solid red";
        }

        if (!nameRegex.test(cognomeInput.value.trim())) {
            messaggiErrore.push("Il cognome deve contenere almeno 2 caratteri alfabetici.");
            cognomeInput.style.border = "2px solid red";
        }

        if (!emailRegex.test(emailInput.value)) {
            messaggiErrore.push("Inserisci un indirizzo email valido.");
            emailInput.style.border = "2px solid red";
        }

        if (passwordInput.value.length < 6) {
            messaggiErrore.push("La password deve essere di almeno 6 caratteri.");
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
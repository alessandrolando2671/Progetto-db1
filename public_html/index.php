<?php
    # (Inizialmente questo file era un semplice html)

    # Si avvia una sessione
    session_start();

    // Si controlla se la sessione è già stata creata precedentemente, con le variabili di sessione
    // impostati, evitando di tornare volontariamente in questo file php di login
    if(isset($_SESSION['nome'])) {
        header('Location: homepage.php');
        exit;
    }
?>
<!DOCTYPE html>
<html lang="it">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login</title>
        <link rel="stylesheet" href="index.css">
    </head>
    <body>

        <div class="login-container">
            <h2>Accedi</h2>
            <form action="homepage.php" method="POST">
                <div class="input-group">
                    <label for="username">Your name</label>
                    <input type="text" id="username" name="nome" placeholder="Inserisci il tuo nome" required>
                </div>
                <div class="input-group">
                    <label for="surname">Your surname</label>
                    <input type="text" id="surname" name="cognome" placeholder="Inserisci il tuo cognome" required>
                </div>
                <div class="input-group">
                    <label for="password">Your password</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                </div>
                <button type="submit" class="login-btn">Login</button>
            </form>
                
            <div class="form-footer">
                <a href="">Password dimenticata?</a>
                <p>Contatta l'amministratore di sistema.</p>
            </div>
        </div>
    </body>
</html>
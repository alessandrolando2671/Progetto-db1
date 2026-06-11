<?php
    require "controllo.php";
    require "conn.php";

    /*
     * 
     *  Questo file serve per stampare un modulo (form) per iniziare a inserire i dati del veicolo,
     *  e poi per inserire i dati del (possibile) conducente e del proprietario.
     *  Se l'utente clicca il tasto avanti, si passerà tutti i dati acquisiti nelle 
     *  variabili e si passeranno a questo stesso file 
     * 
     */

    // Si controlla se non si è mandata una variabile 'targa', cosichè si può stampare 
    // un form per iniziare a inserire i dati di un veicolo
    if(isset($_POST['codice_evento'])) {
        echo '
        <br>
        <form action="soggetto_form.php" method="post" class="modulo">
            <div class="container1">
                <div class="container2">
                    <h3 style="justify-self:center">Veicolo</h3>
                    <div style="display:flex; flex-direction: column; align-items:center">
                        <label for="targa">Targa:</label>
                        <input type="text" id="targa" name="targa" maxlength="7" required><br>
                        <label for="cilindrata">Cilindrata:</label>
                        <input type="number" id="cilindrata" min="0" name="cilindrata" required><br>
                        <label for="colore">Colore:</label>
                        <input type="text" id="colore" name="colore" maxlength="20" required><br>
                        <label for="modello">Modello:</label>
                        <input type="text" id="modello" maxlength="25" name="modello"><br>
                        <label for="categoria">Categoria:</label>
                        <input text="input" name="categoria" maxlength="30" id="categoria" required><br>
                        <label>Proprietario:</label>
                    </div>
                    <input type="radio" name="proprietario" value="persona" id="persona" checked>
                    <label for="persona">Persona</label><br>
                    <input type="radio" name="proprietario" value="ente" id="ente">
                    <label for="ente">Ente</label><br><br>
                    <input type="checkbox" id="conducente" name="conducente">
                    <label for="conducente">Aggiungi conducente</label>
                </div>
            </div>
            <br>
            <div style="display:flex; justify-content:center">
                <input type="hidden" name="codice_evento" value="'.$_POST['codice_evento'].'">
                <input type="hidden" name="aggiungi_veicolo">
                <input type="submit" value="Avanti">
            </div>
        </form>
        <form action="event_list.php" method="post" style="display:flex; justify-content:center">
            <button>Annulla</button> 
        </form>';
    } else {
        // .. Se per "sbaglio" si è entrati in questo file senza la corretta procudura
        echo '<br>
        <form action="homepage.php" method="post" style="display:flex; justify-content:center">
            <button class="button">Torna alla homepage</button>
        </form>';
    }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Veicolo</title>
        <link rel="stylesheet" href="f.css">
    </head>
</html>
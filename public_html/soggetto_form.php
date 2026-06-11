<?php
    require "controllo.php";
    require "conn.php";

    /*
     * 
     *  Questo file serve per inserire i dati solamente nelle tabella Persona 
     *  (conducente e/o proprietario) o Ente (proprietario) 
     * 
     */

    // Viene preparata la query per l'inserimento dei dati del conducente e/o del proprietario
    $inserisciPersona = $conn -> prepare(
        'INSERT INTO Persona(CF, Nome, Cognome, Eta, Statura, Genere, Data_nascita, 
        Patente, Email, Telefono, Residenza) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );

    // Viene preparata la query per l'aggiornamento dei dati del conducente e/o del proprietario (se già presente)
    // oppure di una persona presente in un evento
    $aggiornaPersona = $conn -> prepare(
        'UPDATE Persona SET Nome = ?, Cognome = ?, Eta = ?, Statura = ?,
        Genere = ?, Data_nascita = ?, Patente = ?, Email = ?,
        Telefono = ?, Residenza = ? WHERE CF = ?'
    );

    // Viene preparata la query per l'inserimento dei dati del proprietario ente
    $inserisciEnte = $conn -> prepare(
        'INSERT INTO Ente(CF, Nome, Tipologia, Servizio, N_sedi, Email, Telefono, Residenza)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );

    // Viene preparata la query per l'aggiornamento dei dati del proprietario ente (se già presente)
    $aggiornaEnte = $conn -> prepare(
        'UPDATE Ente SET Nome = ?, Tipologia = ?, Servizio = ?, N_sedi = ?, 
        Email = ?, Telefono = ?, Residenza = ? WHERE CF = ?'
    );

    // Viene preparata la query per l'inserimento dei dati del veicolo
    $inserisciVeicolo = $conn -> prepare(
        'INSERT INTO Veicolo(Targa, Colore, Cilindrata, Modello, Categoria)
        VALUES (?, ?, ?, ?, ?)'
    );

    // Viene preparata la query per l'aggiornamento dei dati del veicolo (se già presente)
    $aggiornaVeicolo = $conn -> prepare(
        'UPDATE Veicolo SET Colore = ?, Cilindrata = ?, Modello = ?, Categoria = ? WHERE Targa = ?'
    );

    $form = '<br>
    <form action="soggetto_form.php" method="post" class="modulo">
        <div class="container1">
            <div class="container2">';

    if(isset($_POST['aggiungi_veicolo'])) {
        # Se la richiesta arriva dal file veicolo_form.php. Questa condizione si verifica nel
        # momento si vuole inserire veicolo e proprietario e (se voluto) il conducente allo stesso tempo.

        # Se tutto va bene, verrà inserito il record del veicolo prima di tutto. 
        # Altrimenti, se già presente, ci sarà un errore e quindi facciamo un "aggiornamento di valori"
        try {
            $inserisciVeicolo -> bind_param(
                'ssiss', $_POST['targa'], $_POST['colore'], $_POST['cilindrata'], $_POST['modello'], $_POST['categoria']
            );
            $inserisciVeicolo -> execute();

            # Viene aggiunta il riferimento (Foreign Key) per il veicolo coinvolto nell'evento nella tabella Coinvolge
            $conn -> query(
                'INSERT INTO Coinvolge(Codice_evento, Targa_veicolo) 
                VALUES ('.$_POST['codice_evento'].', "'.$_POST['targa'].'")'
            );
        } catch(Exception $e) {
            $aggiornaVeicolo -> bind_param(
                'sisss', $_POST['colore'], $_POST['cilindrata'], $_POST['modello'], $_POST['categoria'], $_POST['targa']
            );

            $aggiornaVeicolo -> execute();
        }
    }

    ###  SI CONTROLLA SE SI VUOLE AGGIUNGERE UN PROPRITARIO  ###
    if(isset($_POST['proprietario'])) {

        // Si controlla se si può eseguire la query di inserimento (quindi si è mandata una variabile )
        if(isset($_POST['inserisci'])) {
            if(isset($_POST['tipologia'])) {
                # Condizione per inserire oppure no i dati nella tabella proprietario Ente
                # Se, invece, esiste 'almeno' la variabile 'tipologia', allora vuol dire che sono stati
                # mandati variabili da questo stesso file, per iniziare ad inserire i dati del proprietario ente        

                $email = $_POST['email'] ? $_POST['email'] : null;

                # Se tutto va bene, verrà inserito il record del proprietario ente. 
                # Altrimenti, se già presente, ci sarà un errore e quindi facciamo un "aggiornamento"
                try {
                    $inserisciEnte -> bind_param(
                        'ssssisis', $_POST['cf'], $_POST['nome'], $_POST['tipologia'], $_POST['servizio'],
                        $_POST['n_sedi'], $email, $_POST['telefono'], $_POST['residenza']
                    );
                    $inserisciEnte -> execute();
                } catch(Exception $e) {
                    $aggiornaEnte -> bind_param(
                        'sssisiss', $_POST['nome'], $_POST['tipologia'], $_POST['servizio'],
                        $_POST['n_sedi'], $email, $_POST['telefono'], $_POST['residenza'], $_POST['cf']
                    );
                    $aggiornaEnte -> execute();
                }

                // Viene aggiunta il riferimento (Foreign Key) per il proprietario ente nella tabella Veicolo
                $conn -> query('UPDATE Veicolo SET CF_ente = "'.$_POST['cf'].'" WHERE Targa = "'.$_POST['targa'].'"');
            } else {
                # Condizione per inserire oppure no i dati nella tabella proprietario Persona 
                # Se, invece, esiste 'almeno' la variabile 'cognome', allora vuol dire che sono stati
                # mandati variabili da questo stesso file, per iniziare ad inserire i dati del proprietario persona

                $email = $_POST['email'] ? $_POST['email'] : null;
                $patente = $_POST['patente'] ? $_POST['patente'] : null;

                # ... Solito discorso per il proprietario persona
                try {
                    $inserisciPersona -> bind_param(
                        'sssidssssis', $_POST['cf'], $_POST['nome'], $_POST['cognome'], $_POST['eta'],
                        $_POST['statura'], $_POST['genere'], $_POST['datanascita'], $patente,
                        $email, $_POST['telefono'], $_POST['residenza']
                    );
                    $inserisciPersona -> execute();
                } catch(Exception $e) { // ...altrimenti un "aggiornamento di valori"
                    $aggiornaPersona -> bind_param(
                        'ssidssssiss', $_POST['nome'], $_POST['cognome'], $_POST['eta'],
                        $_POST['statura'], $_POST['genere'], $_POST['datanascita'], $patente,
                        $email, $_POST['telefono'], $_POST['residenza'], $_POST['cf']
                    );
                    $aggiornaPersona -> execute();
                }

                // Viene aggiunta il riferimento (Foreign Key) per il proprietario Persona nella tabella Veicolo
                $conn -> query('UPDATE Veicolo SET CF_persona = "'.$_POST['cf'].'" WHERE Targa = "'.$_POST['targa'].'"');
            }

            if(!isset($_POST['conducente'])) {
                header("Location: ../Progetto/event_list.php");
            }
        } else {
            // Se la variabile 'inserisci' non esiste allora si stampa è un form per il proprietario (persona o ente, a
            // seconda di quello che si è scelto di inserire)

            $form .= '<h3>Proprietario</h3>';

            if($_POST['proprietario'] == 'persona') {
                $form .=
                '<div style="display:flex; align-items:center; gap: 30px">
                    <div style="display:flex; flex-direction: column">
                        <label for="cf">Codice fiscale:</label>
                        <input type="text" id="cf" maxlength="20" name="cf" required><br>
                        <label for="nome">Nome</label>
                        <input type="text" id="nome" name="nome" maxlength="20" required><br>
                        <label for="cognome">Cognome:</label>
                        <input type="text" id="cognome" name="cognome" maxlength="20" required><br>
                        <label for="eta">Età:</label>
                        <input type="number" id="eta" name="eta" required><br>
                        <label for="statura">Statura:</label>
                        <input type="text" name="statura" id="statura" required><br>
                        <label for="genere">Genere:</label>
                        <input type="text" name="genere" maxlength="10" id="genere" required><br>
                    </div>
                    <div style="display:flex; flex-direction: column">
                        <label for="datanascita">Data di nascita:</label>
                        <input type="date" name="datanascita" id="datanascita" required><br>
                        <label for="patente">Patente:</label>
                        <input type="text" name="patente" id="patente" maxlength="15" placeholder="Opzionale"><br>
                        <label for="email">Email:</label>
                        <input type="text" name="email" id="email" maxlength="50" placeholder="Opzionale"><br>
                        <label for="telefono">Telefono:</label>
                        <input type="text" name="telefono" id="telefono" required><br>
                        <label for="residenza">Residenza:</label>
                        <input type="text" name="residenza" maxlength="45" id="residenza" required><br>
                        <input type="hidden" name="proprietario"> 
                    </div>
                </div>';
            } else { // ...altrimenti come proprietario un'ente
                $form .=
                '<div style="display:flex; align-items:center; gap: 30px">
                    <div style="display:flex; flex-direction: column">
                        <label for="cf">Codice fiscale:</label>
                        <input type="text" id="cf" name="cf" maxlength="30" required><br>
                        <label for="nome">Nome</label>
                        <input type="text" maxlength="20" id="nome" name="nome" required><br>
                        <label for="tipologia">Tipologia:</label>
                        <div>
                            <input type="radio" name="tipologia" value="Pubblico" id="pubblico" checked>
                            <label for="pubblico">Pubblico</label><br>
                            <input type="radio" name="tipologia" value="Privato" id="privato">
                            <label for="privato">Privato</label><br><br>
                        </div>
                        <label for="servizio">Servizio:</label>
                        <input type="text" maxlength="20" id="servizio" name="servizio" required><br>
                    </div>
                    <div style="display:flex; flex-direction: column">
                        <label for="n_sedi">Numero sedi:</label>
                        <input type="number" name="n_sedi" id="n_sedi" min="0" required><br>
                        <label for="email">Email:</label>
                        <input type="text" placeholder="Opzionale" maxlength="50" name="email" id="email"><br>
                        <label for="telefono">Numero di telefono:</label>
                        <input type="text" name="telefono" id="telefono"><br>
                        <label for="residenza">Residenza:</label>
                        <input type="text" name="residenza" maxlength="45" id="genere"><br>
                        <input type="hidden" name="proprietario">
                    </div>
                </div>';
            }

            $form .= '</div>'; // (chiude il 'contenitore2')

            // Se non si passa la variabile 'veicolo', allora vuole dire che si vuole 
            // solamente aggiungere il proprietario, quindi entrando in questa condizione
            // si completa il form.
            if(!isset($_POST['aggiungi_veicolo'])) {
                // Si passa pure la targa da questo stesso file per aggiungerlo al veicolo intestato
                $form .= '</div>
                    <input type="hidden" name="targa" value="'.$_POST['targa'].'">
                    <input type="submit" name="inserisci" value="Inserisci">
                </form>
                <form action="event_list.php" method="post" style="display:flex; justify-content:center">
                    <button>Torna indietro</button>
                </form>';

                echo $form;
            } else if(isset($_POST['conducente'])) {
                // Se è presente la variabile 'veicolo' e si vuole aggiungere
                // un conducente, apriamo un secondo 'contenitore2'
                $form .= '<div class="container2">';
            } else {
                // Altrimenti se è presente la variabile 'veicolo' e non si vuole
                // aggiungere un conducente
                $form .= '</div>
                    <input type="hidden" name="targa" value="'.$_POST['targa'].'">
                    <input type="submit" name="inserisci" value="Inserisci">
                </form>
                <form action="delete.php" method="post" style="display:flex;justify-content:center">
                    <input type="hidden" name="targa" value="'.$_POST['targa'].'">
                    <button name="annulla" value="'.$_POST['targa'].'">Annulla</button>
                </form>';
                echo $form;
            }
        }
    } 

    # Si controlla se si vuole aggiungere un conducente
    if(isset($_POST['conducente'])) {

        // Se viene mandata una seconda variabile 'inserisci' da questo stesso file allora
        // si può inserire un record nella tabella del conducente del veicolo
        if(isset($_POST['inserisci'])) {       

            $email = $_POST['email_c'] ? $_POST['email_c'] : null;

            # Se tutto va bene, verrà inserito il record del conducente. 
            # Altrimenti, se già presente, ci sarà un errore e quindi facciamo un "aggiornamento"
            try {
                $inserisciPersona -> bind_param(
                    'sssidssssis', $_POST['cf_c'], $_POST['nome_c'], $_POST['cognome_c'], $_POST['eta_c'],
                    $_POST['statura_c'], $_POST['genere_c'], $_POST['datanascita_c'], $_POST['patente_c'],
                    $email, $_POST['telefono_c'], $_POST['residenza_c']
                );
                $inserisciPersona -> execute();
            } catch(Exception $e) { // ...altrimenti un "aggiornamento di valori"
                $aggiornaPersona -> bind_param(
                    'ssidssssiss', $_POST['nome_c'], $_POST['cognome_c'], $_POST['eta_c'],
                    $_POST['statura_c'], $_POST['genere_c'], $_POST['datanascita_c'], $_POST['patente_c'],
                    $email, $_POST['telefono_c'], $_POST['residenza_c'], $_POST['cf_c']
                );
                $aggiornaPersona -> execute();
            }

            // Viene aggiunta il riferimento (Foreign Key) per il conducente nella tabella Veicolo
            $conn -> query('UPDATE Veicolo SET CF_conducente = "'.$_POST['cf_c'].'" WHERE Targa = "'.$_POST['targa'].'"');

            header('Location: ../Progetto/event_list.php');
        } else {
            // Se la variabile 'inserisci' non esiste allora si stampa è un form per il conducente 
            $form .= '
                        <h3>Conducente</h3>
                        <div style="display:flex; align-items:center; gap: 30px">
                            <div style="display:flex; flex-direction: column">
                                <label for="cf">Codice fiscale:</label>
                                <input type="text" id="cf" maxlength="20" name="cf_c" required><br>
                                <label for="nome">Nome</label>
                                <input type="text" id="nome" name="nome_c" maxlength="20" required><br>
                                <label for="cognome">Cognome:</label>
                                <input type="text" id="cognome" name="cognome_c" maxlength="20" required><br>
                                <label for="eta">Età:</label>
                                <input type="number" id="eta" name="eta_c" min="18" required><br>
                                <label for="statura">Statura:</label>
                                <input type="text" name="statura_c" id="statura" required><br>
                                <label for="genere">Genere:</label>
                                <input type="text" name="genere_c" maxlength="10" id="genere" required><br>
                            </div>
                            <div style="display:flex; flex-direction: column">
                                <label for="datanascita">Data di nascita:</label>
                                <input type="date" name="datanascita_c" id="datanascita" required><br>
                                <label for="patente">Patente:</label>
                                <input type="text" name="patente_c" id="patente" maxlength="15"><br>
                                <label for="email">Email:</label>
                                <input type="text" name="email_c" id="email" maxlength="50" placeholder="Opzionale"><br>
                                <label for="telefono">Telefono:</label>
                                <input type="text" name="telefono_c" id="telefono" required><br>
                                <label for="residenza">Residenza:</label>
                                <input type="text" name="residenza_c" maxlength="45" id="residenza" required><br>
                            </div>
                        </div>
                    </div>
                </div>
                <br>
                <div style="display: flex; justify-content:center">
                    <input type="hidden" name="targa" value="'.$_POST['targa'].'">
                    <input type="hidden" name="conducente">
                    <input type="submit" name="inserisci" value="Inserisci">
                </div>
            </form>';

            if(isset($_POST['aggiungi_veicolo'])) {
                // Se esiste la variabile 'veicolo', allora si vuole aggiungere le intere
                // informazioni dirette del proprietario e del conducente.
                // Quindi si aggiungere un bottone nel caso si voglia annullare l'operazione
                $form .= 
                '<form action="delete.php" method="post" style="display:flex; justify-content:center">
                    <button name="annulla" value="'.$_POST['targa'].'">Annulla</button>
                </form>';
            } else {
                // .. altrimenti si vuole aggiungere solamente un conducente di
                // un veicolo già presente
                $form .= 
                '<form action="event_list.php" method="post" style="display:flex; justify-content:center">
                    <button>Torna indietro</button>
                </form>';
            }
            echo $form;
        }
    } else if(isset($_POST['personaPresente'])) {
        # Questa condizione serve se si vuole aggiungere i dati di una persona
        # al momento di un evento

        // Se in questo stesso file, si manda una variabile 'inserisci' proprio eseguire
        // la query di inserimento del record nella tabella Persona
        if(isset($_POST['inserisci'])) {

                $email = $_POST['email'] ? $_POST['email'] : null;
                $patente = $_POST['patente'] ? $_POST['patente'] : null;

                try {
                    $inserisciPersona -> bind_param(
                        'sssidssssis', $_POST['cf'], $_POST['nome'], $_POST['cognome'], $_POST['eta'],
                        $_POST['statura'], $_POST['genere'], $_POST['datanascita'], $patente,
                        $email, $_POST['telefono'], $_POST['residenza']
                    );
                    $inserisciPersona -> execute();
                } catch(Exception $e) {
                    $aggiornaPersona -> bind_param(
                        'ssidssssiss', $_POST['nome'], $_POST['cognome'], $_POST['eta'],
                        $_POST['statura'], $_POST['genere'], $_POST['datanascita'], $patente,
                        $email, $_POST['telefono'], $_POST['residenza'], $_POST['cf']
                    );
                    $aggiornaPersona -> execute();
                }

                // Viene aggiunta il riferimento (Foreign Key) per la persona nella tabella Evento
                $conn -> query('UPDATE Evento SET CF_persona = "'.$_POST['cf'].'" WHERE Codice = "'.$_POST['codice_evento'].'"');

                header('Location: ../Progetto/event_list.php');
        } else { // ..altrimenti viene stampato un form
            $form = '<br>
            <form action="soggetto_form.php" method="post" class="modulo">
                <div class="container1">
                    <div class="container2">
                        <div style="display:flex; align-items:center; gap: 30px">
                            <div style="display:flex; flex-direction: column">
                                <label for="cf">Codice fiscale:</label>
                                <input type="text" id="cf" maxlength="20" name="cf" required><br>
                                <label for="nome">Nome</label>
                                <input type="text" id="nome" name="nome" maxlength="20" required><br>
                                <label for="cognome">Cognome:</label>
                                <input type="text" id="cognome" name="cognome" maxlength="20" required><br>
                                <label for="eta">Età:</label>
                                <input type="number" id="eta" name="eta" required><br>
                                <label for="statura">Statura:</label>
                                <input type="text" name="statura" id="statura" required><br>
                                <label for="genere">Genere:</label>
                                <input type="text" name="genere" maxlength="10" id="genere" required><br>
                            </div>
                            <div style="display:flex; flex-direction: column">
                                <label for="datanascita">Data di nascita:</label>
                                <input type="date" name="datanascita" id="datanascita" required><br>
                                <label for="patente">Patente:</label>
                                <input type="text" name="patente" id="patente" maxlength="15" placeholder="Opzionale"><br>
                                <label for="email">Email:</label>
                                <input type="text" name="email" id="email" maxlength="50" placeholder="Opzionale"><br>
                                <label for="telefono">Telefono:</label>
                                <input type="text" name="telefono" id="telefono" required><br>
                                <label for="residenza">Residenza:</label>
                                <input type="text" name="residenza" maxlength="45" id="residenza" required><br>
                            </div>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="personaPresente">
                <input type="hidden" name="codice_evento" value="'.$_POST['codice_evento'].'">
                <input type="submit" name="inserisci" value="Inserisci">
            </form>
            <form action="event_list.php" method="post" style="display:flex; justify-content:center">
                <button>Torna indietro</button>
            </form>';
            echo $form;
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Soggetti</title>
        <link rel="stylesheet" href="f.css">
    </head>
</html>
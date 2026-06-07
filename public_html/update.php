<?php
    require "controllo.php"; // Importa ed esegue il file "controllo"
    require "conn.php"; // Importa $conn

    /*
     * 
     *  Questo file serve per modificare (quindi aggiornare record) dei valori delle tabelle
     *  
     */
    
    // Si prepara una query per l'aggiornamento del conducente/proprietario 
    // oppure di una persona coinvolta in un evento
    $aggiornaPersona = $conn -> prepare(
        'UPDATE Persona SET CF = ?, Nome = ?, Cognome = ?, Eta = ?,
        Statura = ?, Genere = ?, Data_nascita = ?, Patente = ?, Email = ?,
        Telefono = ?, Residenza = ? WHERE Persona.CF = ?'
    );
    
   // Si prepara una query per l'aggiornamento dell'ente proprietario
    $aggiornaEnte = $conn -> prepare(
        'UPDATE Ente SET CF = ?, Nome = ?, Tipologia = ?, Servizio = ?,
        N_sedi = ?, Email = ?, Telefono = ?, Residenza = ? WHERE Ente.CF = ?'
    );

    // Si prepara una query per l'aggiornamento dell'evento
    $aggiornaEvento = $conn -> prepare(
        'UPDATE Evento SET Num_strada = ?, Class_strada = ?, Tratto = ?,
        DataOrario = ?, Tempo = ?, Posizione = ?, Descrizione = ? WHERE Codice = ?'
    );

    // Si prepara una query per l'aggiornamento del veicolo
    $aggiornaVeicolo = $conn -> prepare(
        'UPDATE Veicolo SET Targa = ?, Colore = ?, Cilindrata = ?, Modello = ?, Categoria = ? WHERE Veicolo.Targa = ?'
    );

    ###  SE SI VUOLE MODIFICARE UN EVENTO  ###
    if(isset($_POST['evento'])) {

        // Se si manda una seconda variabile, da questo file, si esegue
        // una query per aggiornare l'evento
        if(isset($_POST['aggiorna'])) {

            try {
                $aggiornaEvento -> bind_param(
                    'isisssss', $_POST['num_strada'], $_POST['classificazione'], $_POST['tratto'],
                    $_POST['timestamp'], $_POST['tempo'], $_POST['posizione'], $_POST['descrizione'],
                    $_POST['evento']
                );
                $aggiornaEvento -> execute();
            
                // Facciamo anche la modifica alla tabella "associativa" Assegnato, se nel caso
                // si decide di assegnare pattuglie diverse. Prima di tutto elimiano i record
                // delle pattuglie vecchie che erano state assegnate, per aggiungere
                // quelle nuove
                $conn -> query('DELETE FROM Assegnato WHERE Codice_evento = "'.$_POST['evento'].'"');

                // Query: estraggo la caserma dove lavora l'utente connesso
                $caserma = $conn -> query(
                    'SELECT Caserma_nome, Caserma_residenza FROM Agente WHERE Nome = "'.$_SESSION['nome'].'"
                    and Cognome = "'.$_SESSION['cognome'].'" and Password = "'.$_SESSION['password'].'"'
                );
                
                $caserma = $caserma -> fetch_assoc();
                
                for($i = 1; $i <= $_POST['num_pattuglie']; $i++) {
                    if(isset($_POST['pattuglia'.$i])) {
                        // Si aggiunge il record con la nuova pattuglia i-esima assegnata all'evento
                        $conn -> query('INSERT INTO Assegnato(Num_pattuglia, Nome_caserma, Residenza_caserma, Codice_evento)
                        VALUES ('.$i.', "'.$caserma['Caserma_nome'].'", "'.$caserma['Caserma_residenza'].'", '.$_POST['evento'].')');
                    }
                }

                header("Location: ../Progetto/event_list.php");
            } catch(Exception $e) {
                echo '<br>
                <p style="text-align:center; color: #f0ffff">Strada non esistente</p>
                <form action="event_list.php" method="post">
                    <button class="button">Torna indietro</button>
                </form>';
            }
        } else {
            # Se non è stata mandata un seconda variabile ('aggiorna') per confermare
            # la modifica, allora stampiamo un form con inserendo i valori attuali 
            # di ogni dato dell'evento memorizzato (eccetto il suo codice)

            $codice_evento = $_POST['evento'];

            // Query: estraggo tutte le infomazione dell'evento specifico
            $ris = $conn -> query(
                'SELECT * FROM Evento WHERE Codice = "'.$codice_evento.'"'
            );

            $ris = $ris -> fetch_assoc(); // Rinizializzo l'oggetto in un array associativo

            $form = '
                <br>
                <form action="update.php" method="post" class="modulo">
                    <div class="container1">
                        <div class="container2">
                            Strada.
                            <p style="justify-self:center; margin: 0; padding: 0">Classificazione:</p>
                            <input type="radio" id="statale" name="classificazione" value="SS" '.($ris['Class_strada'] == 'SS'? 'checked' : '').'>
                            <label for="statale">Statale</label><br>
                            <input type="radio" id="provinciale" name="classificazione" value="SP" '.($ris['Class_strada'] == 'SP'? 'checked' : '').'>
                            <label for="provinciale">Provinciale</label><br>
                            <input type="radio" id="comunale" name="classificazione" value="SC" '.($ris['Class_strada'] == 'SC'? 'checked' : '').'>
                            <label for="comunale">Comunale</label><br>
                            <input type="radio" name="classificazione" value="SR" id="regionale" '.($ris['Class_strada'] == 'SR'? 'checked' : '').'>
                            <label for="regionale">Regionale</label><br>
                            <label for="numero" style="display:block">Numero: </label>
                            <input type="number" name="num_strada" value="'.$ris['Num_strada'].'" id="number"><br><br>
                            <label for="data" style="display:block">Data e orario: </label>
                            <input type="datetime-local" name="timestamp" value="'.$ris['DataOrario'].'" id="data" required><br><br>
                            <label for="tempo" style="display:block">Tempo:</label>
                            <input type="text" name="tempo" id="tempo" value="'.$ris['Tempo'].'" required><br><br>
                            <label for="tratto" style="display:block">Tratto di strada:</label>
                            <input type="number" name="tratto" value="'.$ris['Tratto'].'" id="tratto" required><br><br>
                            <label for="posizione" style="display: block">Posizione: </label>
                            <input type="text" name="posizione" id="posizione" value="'.$ris['Posizione'].'" required><br><br>
                            <label for="descrizione" style="display: block">Descrizione: </label>
                            <textarea name="descrizione" style="resize: none" id="descrizione" required>'.$ris['Descrizione'].'</textarea><br>
                        </div>
                        <div class="container2">
                            Assegna pattuglie<br>';
            // Si estraggono il numero di pattuglie disponibili nella caserma dove lavora l'utente connesso
            $query = 
                'SELECT Pa.* FROM Pattuglia as Pa JOIN Agente as Ag ON Pa.Caserma_nome = Ag.Caserma_nome and 
                Pa.Caserma_residenza = Ag.Caserma_residenza and Ag.Nome = "'.$_SESSION['nome'].'" and 
                Ag.Cognome = "'.$_SESSION['cognome'].'" and Ag.Password = "'.$_SESSION['password'].'"';

            $pattuglie_disponibili = $conn -> query($query); // Si esegue la query

            if($pattuglie_disponibili -> num_rows > 0) {
                while($row = $pattuglie_disponibili -> fetch_assoc()) {
                    $form .= // Viene inserito, nel form, un checkbox di pattuglie disponibili
                    '<input type="checkbox" id="pattuglia'.$row['Numero'].'" name="pattuglia'.$row['Numero'].'">
                    <label for="pattuglia'.$row['Numero'].'">Pattuglia '.$row['Numero'].',</label>
                    <label for="pattuglia'.$row['Numero'].'">Turno '.$row['Turno'].'</label><br>';
                }
                // Query: estrae il numero di record presenti nella tabella Pattuglia della caserma.
                // Da una "vista" di nome Contatore, che è la query precedentemente dichiarata $query.
                // Questo dato servirà per iterare fino pattuglia n-esima della caserma,
                // per controllare se è stata mandata la pattuglia i-esami dal form per essere inserita
                $Num_pattuglie = $conn -> query(
                    'SELECT count(*) as Numero FROM ('.$query.') as Contatore'
                );
                $form .= '
                    <input type="hidden" name="num_pattuglie" value="'.$Num_pattuglie -> fetch_assoc()['Numero'].'">
                ';
            } else {
                $form .= '<p style="text-align:center">Non ci sono pattuglie</p>';
            }
            $form .= '
                    </div>
                </div>
                <br>
                <input type="hidden" name="aggiorna" value="aggiorna">
                <input type="hidden" name="evento" value="'.$_POST['evento'].'">
                <input type="submit" value="Modfifica">
            </form>
                    
            <form action="event_list.php" method="post" style="display:flex; justify-content:center">
                <button class="button">Annulla</button>
            </form>';
            echo $form;
        }
    }

    ###  SE SI VUOLE MODIFICARE UN VEICOLO  ###
    else if(isset($_POST['veicolo'])) {

        // Se si manda una seconda variabile, da questo file, per eseguire
        // una query per aggiornare il veicolo
        if(isset($_POST['aggiorna'])) {

            $aggiornaVeicolo -> bind_param(
                'ssisss', $_POST['targa'], $_POST['colore'], $_POST['cilindrata'],
                $_POST['modello'], $_POST['categoria'], $_POST['veicolo']
            );

            $aggiornaVeicolo -> execute();

            header('Location: ../Progetto/event_list.php');
        } else { // ..altrimenti stampo un form con i valori già aggiunti

            // Query: estraggo tutte le informazioni del veicolo specificato
            $veicolo = $conn -> query('SELECT * FROM Veicolo WHERE Targa = "'.$_POST['veicolo'].'"');

            $veicolo = $veicolo -> fetch_assoc(); // Rinizializzo l'oggetto in un array associativo

            echo '<br>
            <form action="update.php" method="post" class="modulo">
                <div class="container2">
                    <h3 style="justify-self:center">Veicolo</h3>
                    <div style="display:flex; flex-direction: column; align-items:center">
                        <label for="targa">Targa:</label>
                        <input type="text" id="targa" name="targa" value="'.$veicolo['Targa'].'" maxlenght="7" required><br>
                        <label for="cilindrata">Cilindrata:</label>
                        <input type="number" id="cilindrata" min="0" name="cilindrata" value="'.$veicolo['Cilindrata'].'" required><br>
                        <label for="colore">Colore:</label>
                        <input type="text" id="colore" name="colore" maxlength="20" value="'.$veicolo['Colore'].'" required><br>
                        <label for="modello">Modello:</label>
                        <input type="text" id="modello" name="modello" maxlength="25" value="'.$veicolo['Modello'].'"><br>
                        <label for="categoria">Categoria:</label>
                        <input text="input" name="categoria" id="categoria" maxlength="30" value="'.$veicolo['Categoria'].'" required><br>
                    </div>
                </div>
                <br>
                <input type="hidden" name="veicolo" value="'.$_POST['veicolo'].'">
                <input type="submit" name="aggiorna" value="Modifica">
            </form>
            <br>
            <form action="event_list.php" method="post" style="display:flex; justify-content:center">
                <button>Torna indietro</button> 
            </form>';
        }
    } else if(isset($_POST['persona'])) {
        # Se si vuole modificare una persona al momento di un evento o di un proprietario o di un conducente

        // Se in questo stesso file, ma soprattutto nella seconda condizione,  
        // si manda una variabile 'aggiorna', si esegue proprio la query di aggiornamento
        // del record nella tabella Persona
        if(isset($_POST['aggiorna'])) {

            $email = $_POST['email'] ? $_POST['email'] : null;

            $patente = $_POST['patente'] ? $_POST['patente'] : null;

            // Se dalla seconda condizione non si manda una variabile 'patente', allora
            // vuol dire che non è stato scelto di aggiornare un conducente, e la patente
            // sarà quella che è stato deciso di inserire (niente oppure si)
            if(!isset($_POST['patente'])) {
                $patente = $_POST['patente'];
            }
            
            $aggiornaPersona -> bind_param(
                'sssidssssiss', $_POST['cf'], $_POST['nome'], $_POST['cognome'], $_POST['eta'],
                $_POST['statura'], $_POST['genere'], $_POST['datanascita'], $patente,
                $email, $_POST['telefono'], $_POST['residenza'], $_POST['persona']
            );
            $aggiornaPersona -> execute();

            header('Location: ../Progetto/event_list.php');
        } else { // ..altrimenti viene stampato un form con i valori già aggiunti

            $persona = $conn -> query('SELECT * FROM Persona WHERE CF = "'.$_POST['persona'].'"');

            $persona = $persona -> fetch_assoc();

            $form = '<br>
            <form action="update.php" method="post" class="modulo">
                <div class="container1">
                    <div class="container2">
                        <div style="display:flex; align-items:center; gap: 30px">
                            <div style="display:flex; flex-direction: column">
                                <label for="cf">Codice fiscale:</label>
                                <input type="text" id="cf" maxlength="20" name="cf" value="'.$persona['CF'].'" required><br>
                                <label for="nome">Nome</label>
                                <input type="text" id="nome" name="nome" value="'.$persona['Nome'].'" maxlength="20" required><br>
                                <label for="cognome">Cognome:</label>
                                <input type="text" id="cognome" name="cognome" value="'.$persona['Cognome'].'" maxlength="20" required><br>
                                <label for="eta">Età:</label>';
                                if(isset($_POST['patente'])) { // Se è un conducente deve avere minimo 18 anni
                                    $form .= '
                                        <input type="number" id="eta" name="eta" value="'.$persona['Eta'].'" min="18" required><br>';
                                } else {
                                    $form .= '
                                        <input type="number" id="eta" name="eta" value="'.$persona['Eta'].'" required><br>';
                                }
                                $form .= '
                                <label for="statura">Statura:</label>
                                <input type="text" name="statura" value="'.$persona['Statura'].'" id="statura" required><br>
                                <label for="genere">Genere:</label>
                                <input type="text" name="genere" maxlength="10" value="'.$persona['Genere'].'" id="genere" required><br>
                            </div>
                            <div style="display:flex; flex-direction: column">
                                <label for="datanascita">Data di nascita:</label>
                                <input type="date" name="datanascita" id="datanascita" value="'.$persona['Data_nascita'].'" required><br>
                                <label for="patente">Patente:</label>';
                                if(isset($_POST['conducente'])) { // Se è un conducente deve avere la patente
                                    $form .= '
                                        <input type="hidden" name="patente">
                                        <input type="text" name="patente" value="'.$persona['Patente'].'" id="patente" maxlength="15" required><br>';
                                } else {
                                    $form .= '
                                        <input type="text" name="patente" id="patente" '.($persona['Patente'] ? 'value="'.$persona['Patente'].'"' : '').' maxlength="15" placeholder="Opzionale"><br>';
                                }
                                $form .= '
                                <label for="email">Email:</label>
                                <input type="text" name="email" id="email" '.($persona['Email'] ? 'value="'.$persona['Email'].'"' : '').' maxlength="50" placeholder="Opzionale"><br>
                                <label for="telefono">Telefono:</label>
                                <input type="text" name="telefono" id="telefono" value="'.$persona['Telefono'].'" required><br>
                                <label for="residenza">Residenza:</label>
                                <input type="text" name="residenza" maxlength="45" id="residenza" value="'.$persona['Residenza'].'" required><br>
                            </div>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="persona" value="'.$_POST['persona'].'">
                <input type="submit" name="aggiorna" value="Modifica">
            </form>
            <form action="event_list.php" method="post" style="display:flex; justify-content:center">
                <button>Torna indietro</button>
            </form>';
            echo $form;
        }
    } else if(isset($_POST['ente'])) {
        # Se si vuole modificare un ente

        // Se in questo stesso file si manda una variabile 'aggiorna', si esegue proprio
        // la query di aggiornamento del record nella tabella Persona
        if(isset($_POST['aggiorna'])) {

            $email = $_POST['email'] ? $_POST['email'] : null;

            $aggiornaEnte -> bind_param(
                'ssssisiss', $_POST['cf'], $_POST['nome'], $_POST['tipologia'], $_POST['servizio'],
                $_POST['n_sedi'], $email, $_POST['telefono'], $_POST['residenza'], $_POST['ente']
            );

            $aggiornaEnte -> execute();

            header('Location: ../Progetto/event_list.php');
        } else { // ..altrimenti viene stampato un form con i valori già inseriti

            $ente = $conn -> query('SELECT * FROM Ente WHERE CF = "'.$_POST['ente'].'"');

            $ente = $ente -> fetch_assoc();

            $form = '<br>
                <form action="update.php" method="post" class="modulo">
                    <div class="container1">
                        <div class="container2">
                            <div style="display:flex; align-items:center; gap: 30px">
                                <div style="display:flex; flex-direction: column">
                                    <label for="cf">Codice fiscale:</label>
                                    <input type="text" id="cf" name="cf" value="'.$ente['CF'].'" maxlength="30" required><br>
                                    <label for="nome">Nome</label>
                                    <input type="text" maxlength="20" id="nome" name="nome" value="'.$ente['Nome'].'" required><br>
                                    <label for="tipologia">Tipologia:</label>
                                    <div>
                                        <input type="radio" name="tipologia" value="Pubblico" id="pubblico" '.($ente['Tipologia'] == 'Pubblico' ? 'checked' : '').'>
                                        <label for="pubblico">Pubblico</label><br>
                                        <input type="radio" name="tipologia" value="Privato" id="privato" '.($ente['Tipologia'] == 'Privato' ? 'checked' : '').'>
                                        <label for="privato">Privato</label><br><br>
                                    </div>
                                    <label for="servizio">Servizio:</label>
                                    <input type="text" maxlength="20" id="servizio" name="servizio" value="'.$ente['Servizio'].'" required><br>
                                </div>
                                <div style="display:flex; flex-direction: column">
                                    <label for="n_sedi">Numero sedi:</label>
                                    <input type="number" name="n_sedi" id="n_sedi" min="0" value="'.$ente['N_sedi'].'" required><br>
                                    <label for="email">Email:</label>
                                    <input type="text" placeholder="Opzionale" maxlength="50" name="email" '.($ente['Email'] ? 'value="'.$ente['Email'].'"' : '').' id="email"><br>
                                    <label for="telefono">Numero di telefono:</label>
                                    <input type="text" name="telefono" id="telefono" value="'.$ente['Telefono'].'" required><br>
                                    <label for="residenza">Residenza:</label>
                                    <input type="text" name="residenza" maxlength="45" value="'.$ente['Residenza'].'" id="residenza"><br>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="ente" value="'.$_POST['ente'].'">
                    <input type="submit" name="aggiorna" value="Modifica">
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
        <title>Modifica</title>
        <link rel="stylesheet" href="f.css">
    </head>
</html>
<?php 
    require "homepage.php"; // Importa ed esegue il file php
    require "conn.php"; // Importa $conn

    // Controlla se esiste 'almeno' questa variabile, passato a questo stesso file php,
    // che serve per inserire un record Evento nel db. Se così non fosse manda l'else
    if(isset($_POST['timestamp'])) {
        
        $timestamp = $_POST['timestamp'];
        $num_strada = $_POST['num_strada'];
        $class_strada = $_POST['classificazione'];
        $nome_agente = $_SESSION['nome'];
        $cognome_agente = $_SESSION['cognome'];
        $password = $_SESSION['password'];
        $tempo = $_POST['tempo'];
        $posizione = $_POST['posizione'];
        $descrizione = $_POST['descrizione'];
        $tratto = $_POST['tratto'];
        
        // Una query per l'inserimento nella tabella Evento
        $stmt = $conn -> prepare(
            'INSERT INTO Evento(Nome_agente, Cognome_agente, Password_agente, Num_strada, Class_strada,
            Tratto, DataOrario, Tempo, Posizione, Descrizione) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt -> bind_param(
            'sssisissss', $nome_agente, $cognome_agente, $password, $num_strada, $class_strada, $tratto, 
            $timestamp, $tempo, $posizione, $descrizione
        );

        # Inserisco un try..catch.. : se durante l'inserimento c'è un errore, perchè il numero di strada
        # e la sua classificazione non corrispondono, allora catturiamo questa eccezione e mandiamo
        # un messaggio d'errore
        try {
            $stmt -> execute();

            // Query con operatore aggragato max(): essendo che il codice è chiave primaria della tabella
            // e viene incrementato automaticamente all'inserimento di un nuovo record, allora estraiamo 
            // l'ultimo numero del codice (max) per fare in modo di inserirlo nella tabella "associativa"
            // Assegnato, se nel caso è stato deciso di assegnare un numero di pattuglie all'evento
            $UltimoCodiceEvento = $conn -> query('SELECT max(Codice) as Codice FROM Evento');

            // Query: estraggo la caserma dove lavora l'operatore connesso
            $caserma = $conn -> query(
                'SELECT Caserma_nome, Caserma_residenza FROM Agente as Ag WHERE Ag.Nome = "'.$_SESSION['nome'].'" and
                Ag.Cognome = "'.$_SESSION['cognome'].'" and Ag.Password = "'.$_SESSION['password'].'"'
            );

            $caserma = $caserma -> fetch_assoc(); // Rinizializzo il risultato in un array associativo
            $UltimoCodiceEvento = $UltimoCodiceEvento -> fetch_assoc();

            // Un ciclo for per controllare se, mandato dallo stesso file, esiste la variabile pattuglia i-esima
            // per inserire la i-esima pattuglia nella tabella "associativa" Assegnato con il codice dell'evento
            // che si è creato
            for($i = 1; $i <= $_POST['num_pattuglie']; $i++) {
                if(isset($_POST['pattuglia'.$i])) {
                    $conn -> query(
                        'INSERT INTO Assegnato(Num_pattuglia, Nome_caserma, Residenza_caserma, Codice_evento)
                        VALUES ('.$i.', "'.$caserma['Caserma_nome'].'", 
                        "'.$caserma['Caserma_residenza'].'", '.$UltimoCodiceEvento['Codice'].')'
                    );
                }
            }
            echo '<p style="text-align:center">Informazione aggiunta con successo.</p>';            
        } catch(Exception $e) {
            echo '<p style="text-align:center">Strada non esistente.</p>';
        }
    } else {
        // Un semplice form per l'inserimento dei dati per un evento
        $form_evento = '
        <br>
        <form action="event_add.php" method="post">
            <div style="display:flex; justify-content:center; gap: 30px">
                <div id="content" style="display: flex; flex-direction: column; align-items:center; background-color: cornflowerblue">
                    Strada.
                    <div style="max-width: 150px; display: flex; justify-content:center; gap: 2px">
                        <select name="classificazione" style="width:70%">
                            <option value="A" style="text-align:center">Autostrada</option>
                            <option value="E" style="text-align:center">Itinerario</option>
                            <option value="SS" style="text-align:center">Statale</option>
                            <option value="SP" style="text-align:center">Provinciale</option>
                            <option value="SC" style="text-align:center">Comunale</option>
                            <option value="SR" style="text-align:center">Regionale</option>
                        </select>
                        <input type="number" name="num_strada" style="width: 30%;" value="0">
                    </div>
                    <br>
                    <label for="data">Data e orario: </label>
                    <input type="datetime-local" name="timestamp" id="data" required><br>
                    <label for="tempo">Tempo:</label>
                    <input type="text" name="tempo" id="tempo" required><br>
                    <label for="tratto">Tratto di strada:</label>
                    <input type="number" name="tratto" value="0" id="tratto" required><br>
                    <label for="posizione">Posizione: </label>
                    <input type="text" name="posizione" id="posizione" required><br>
                    <label for="descrizione">Descrizione: </label>
                    <textarea name="descrizione" style="resize: none" id="descrizione" required></textarea><br>
                </div>
                <div id="content" style="background-color: cornflowerblue">
                    Assegna pattuglie.<br>';
                    // JOIN: una query che estrae tutti i record delle pattuglie, con cui l'utente e le pattuglie sono nella stessa caserma
                    $query = 
                        'SELECT Pa.* FROM Pattuglia as Pa JOIN Agente as Ag ON Pa.Caserma_nome = Ag.Caserma_nome and 
                        Pa.Caserma_residenza = Ag.Caserma_residenza and Ag.Nome = "'.$_SESSION['nome'].'" and 
                        Ag.Cognome = "'.$_SESSION['cognome'].'" and Ag.Password = "'.$_SESSION['password'].'"';

                    $pattuglie_disponibili = $conn -> query($query); // Si esegue la query

                    if($pattuglie_disponibili -> num_rows > 0) {
                        while($row = $pattuglie_disponibili -> fetch_assoc()) {
                            $form_evento .= // Viene inserito, nel form, un checkbox di pattuglie disponibili
                            '<input type="checkbox" id="pattuglia'.$row['Numero'].'" name="pattuglia'.$row['Numero'].'" value="'.$row['Numero'].'">
                            <label for="pattuglia'.$row['Numero'].'">Pattuglia '.$row['Numero'].',</label>
                            <label for="pattuglia'.$row['Numero'].'">Turno '.$row['Turno'].'</label><br>';
                        }
                        // Query: estrae il numero di record presenti nella tabella Pattuglia della caserma.
                        // Da una "vista" di nome Contatore, che è la query precedentemente dichiarata $query
                        $Num_pattuglie = $conn -> query(
                            'SELECT count(*) as Numero FROM ('.$query.') as Contatore'
                        );
                        $form_evento .= '<input type="hidden" name="num_pattuglie" value="'.$Num_pattuglie -> fetch_assoc()['Numero'].'">';
                    } else {
                        $form_evento .= '<p>Non ci sono pattuglie</p>';
                    }
                    $form_evento .= '
                </div>
            </div>
            <br>
            <div style="display:flex; justify-content:center">
                <input type="submit" value="Inserisci">
            </div>
        </form>';
        echo $form_evento;
    }
?>
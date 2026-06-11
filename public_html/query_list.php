<?php
    require "controllo.php"; // Importa ed esegue il "controllo"
    require "conn.php"; // Importa $conn

    /*
     * 
     * Questo file serve principalmente per un'interrogazione al db
     * 
     */

    #
    # Se dal file consulta.php viene inviata una querystring ?query=strada
    #
    if($_POST['query'] == 'strada') {
        $ris = $conn -> query('SELECT * FROM Strada'); // Si esegue la query di ogni strada presente

        if($ris -> num_rows > 0) {
            // Un semplice output, con tabella html, dei risultati
            $tabella_ris = '
            <br>
            <h2>STRADE</h2>
            <table>
                <tr>
                    <th>Strada</th>
                    <th>Lunghezza</th>
                    <th>Larghezza</th>
                    <th>Stato</th>
                    <th>Categoria</th>
                    <th>Limite minimo</th>
                    <th>Limite massimo</th>
                    <th>Conteggio eventi</th>
                </tr>';
            
            $i = 1;
            // Con il metodo fetch_assoc() si crea un array associativo dal risultato della query
            while($rows = $ris -> fetch_assoc()) {
                $tabella_ris .= '
                <tr class="color_row'.$i.'"> 
                    <td>'.$rows['Classificazione'].' '.$rows['Numero'].'</td>
                    <td>'.$rows['Lunghezza'].' km</td>
                    <td>'.$rows['Larghezza'].' km</td>
                    <td>'.$rows['Stato'].'</td>
                    <td>'.$rows['Categoria'].'</td>
                    <td>'.$rows['Limite_min_velocita'].'</td>
                    <td>'.$rows['Limite_max_velocita'].'</td>';
                    
                // Query per il conteggio degli eventi in una specifica strada
                $n_eventi = $conn -> query(
                    'SELECT count(*) as N_eventi FROM Evento WHERE 
                    Evento.Num_strada = '.$rows['Numero'].' and 
                    Evento.Class_strada = "'.$rows['Classificazione'].'"'
                );

                $tabella_ris .= '
                    <td>'.$n_eventi -> fetch_assoc()['N_eventi'].'</td>
                </tr>';
                $i = $i ? 0 : 1;
            }
            echo $tabella_ris.'</table>';
        }
        else {
            echo "<br><p>Non ci sono informazioni sulle strade</p>";
        }
    } 

    #
    # Se arriva dal file consulta.php per controllare tutti o i propri eventi della caserma
    # oppure se si vuole controllare eventi di altre caserme
    #
    else if($_POST['query'] == 'evento' || $_POST['query'] == 'Myreport' || $_POST['query'] == 'Altri report') {

        // Una piccola stampa per una mini-sezione
        echo
        '<br>
        <h2>EVENTI</h2>
        <form action="query_list.php" style="display:flex; justify-content:center; gap: 10px" method="post">
            <button name="query" class="btn" style="width: 20%" value="evento">I report di questa caserma</button>
            <button name="query" class="btn" style="width: 20%" value="Myreport">I tuoi report</button><br>
        </form>';

        // Se si vuole controllare ulteriori eventi di altre caserme: un piccolo menù a
        // tendina di un elenco di altre caserme
        $elenco_caserme = 
        '<form action="query_list.php" style="display:flex; justify-content:center" method="post">
            <label for="caserma" style="margin-right:10px; color: #f0ffff">Caserma: </label>
            <select name="caserme" id="caserma" style="margin-right: 10px">';
        
        // Operazione di JOIN tra Caserma e Agente: estraggo tutte le caserme che l'utente,
        // connesso in piattaforma, non ci lavora. Il risultato verrà poi aggiunto a un menù a tendina.
        $AltreCaserme = $conn -> query(
            'SELECT Ca.Nome, Ca.Residenza 
            FROM Caserma as Ca JOIN Agente as Ag ON Ca.Nome != Ag.Caserma_nome and Ca.Residenza != Ag.Caserma_residenza
            WHERE Ag.Nome = "'.$_SESSION['nome'].'" and Ag.Cognome = "'.$_SESSION['cognome'].'" and Ag.Password = "'.$_SESSION['password'].'"'
        );

        if($AltreCaserme -> num_rows > 0) {
            while($row = $AltreCaserme -> fetch_assoc()) {
                $elenco_caserme .= 
                '<option value="'.$row['Nome'].'+'.$row['Residenza'].'">'.$row['Nome'].' '.$row['Residenza'].'</option>';
            }
            $elenco_caserme .= '</select>';
        }
        $elenco_caserme .= '
                <button class="btn" name="query" value="Altri report">Altri report</button>
            </form>';
        echo $elenco_caserme;

        // Una tabella contenente i risultati della query
        $tabella_ris = 
        '<br>
        <table>
            <tr>
                <th>Data e Orario</th>
                <th>Strada</th>
                <th>Gestito da</th>
                <th>Condizioni atmosferiche</th>
                <th>Tratto di strada</th>
                <th>Posizione</th>
                <th>Descrizione aggiuntiva</th>
                <th>Persona riportata</th>
                <th>Numero pattuglie assegnate</th>
                <th>Veicoli</th>
            </tr>';
        
        // Se dallo stesso file si vuole sapere i propri report ?query=Myreport
        if($_POST['query'] == "Myreport") {
            // Query: estraggo solo i propri report
            $ris = $conn -> query(
                'SELECT * FROM Evento WHERE Cognome_agente = "'.$_SESSION['cognome'].'" 
                and Nome_agente = "'.$_SESSION['nome'].'" and Password_agente = "'.$_SESSION['password'].'"'
            );
        } else if($_POST['query'] == 'Altri report') {

            // Crea un array di elementi scomponendo la stringa passato con il metodo post
            // cosichè è possibile sapere il suo nome e residenza della caserma in modo separato
            $ArrayCaserma = explode('+', $_POST['caserme']);

            $ris = $conn -> query(
                'SELECT * FROM Evento as Ev JOIN Agente as Ag ON Ev.Nome_agente = Ag.Nome and
                Ev.Cognome_agente = Ag.Cognome and Ev.Password_agente = Ag.Password and
                Ag.Caserma_nome = "'.$ArrayCaserma[0].'" and Ag.Caserma_residenza = "'.$ArrayCaserma[1].'"'
            );
        } else {
            // Query: estraggo il Nome e la Residenza della caserma dove lavora l'utente in piattaforma
            $caserma = $conn -> query(
                "SELECT Caserma_nome, Caserma_residenza FROM Agente WHERE 
                Nome = '".$_SESSION['nome']."' and Cognome = '".$_SESSION['cognome']."' 
                and Password = '".$_SESSION['password']."'"
            );

            $caserma = $caserma -> fetch_assoc(); // Rinizializzo il risultato in un array associativo
            
            // Operazione di JOIN: eseguo un join per le due tabelle con i campi con 
            // i valori in comune, ma estraggo (WHERE) solo gli eventi che sono presenti nella caserma
            $ris = $conn -> query(
                'SELECT * FROM Evento as Ev JOIN Agente as Ag ON Ev.Nome_agente = Ag.Nome 
                and Ev.Cognome_agente = Ag.Cognome and Ev.Password_agente = Ag.Password 
                WHERE Ag.Caserma_nome = "'.$caserma['Caserma_nome'].'" 
                and Ag.Caserma_residenza = "'.$caserma['Caserma_residenza'].'"'
            );
        }

        if($ris -> num_rows > 0) {
            $i = 1;
            while($row = $ris -> fetch_assoc()) {
                $tabella_ris .= 
                '<tr class="color_row'.$i.'"> 
                    <td>'.$row['DataOrario'].'</td>
                    <td>'.$row['Class_strada'].' '.$row['Num_strada'].'</td>
                    <td>'.$row['Cognome_agente'].' '.$row['Nome_agente'].'</td>
                    <td>'.$row['Tempo'].'</td>
                    <td>'.$row['Tratto'].' km</td>
                    <td>'.$row['Posizione'].'</td>
                    <td>'.$row['Descrizione'].'</td>
                    <td>
                        <form action="query_list.php" method="post" style="display: inline">
                            <input type="hidden" name="codice_evento" value="'.$row['Codice'].'">
                            <input type="hidden" name="query" value="personaPresente">
                            <input type="submit" value="Controlla">
                        </form>
                    </td>
                    <td>' // Query: estraggo il numero di pattuglie assegnate all'evento
                        .($conn -> query(
                            'SELECT count(*) as N_pattuglie FROM Assegnato WHERE Codice_evento = "'.$row['Codice'].'"'
                        )) -> fetch_assoc()['N_pattuglie'].
                    '</td>
                    <td>
                        <form action="query_list.php" method="post" style="display: inline">
                            <input type="hidden" name="codice_evento" value="'.$row['Codice'].'">
                            <input type="hidden" name="query" value="veicoli">
                            <input type="submit" value="Controlla" value="veicoli">
                        </form>
                    </td>
                </tr>';
                $i = $i ? 0 : 1;
            }
            echo $tabella_ris.'</table>';
        } else {
            echo '<br><p>Non ci sono eventi</p>';
        }
    } 
    
    #
    # Se dal file consulta.php viene inviata una querystring ?query=agente
    #
    else if($_POST['query'] == 'agente') { 
        
        $body = 
        '<br>
        <h2>COLLEGHI</h2>
        <table>
            <tr>
                <th>Utente</th>
                <th>Età</th>
                <th>Statura</th>
                <th>Genere</th>
                <th>Residenza</th>
                <th>Telefono</th>
                <th>Email</th>
                <th>Caserma</th>
                <th>Numeri di eventi scritti</th>
            </tr>';
        
        // Si sta per eseguire una query annidata: la prima interrogazione annidata estrarrà il nome della caserma
        // dove lavora l'utente e l'altra dove risiede la caserma. Infine, dopo il risultato finale si manda in output
        // i dati dei colleghi
        
        // Si esegue la query annidata: l'estrazione di tutti i dati dei colleghi dove lavora l'utente
        $ris = $conn -> query(
            "SELECT * FROM Agente WHERE Caserma_nome = (SELECT Caserma_nome FROM Agente WHERE Nome = '".$_SESSION['nome']."'
            and Cognome = '".$_SESSION['cognome']."' and Password = '".$_SESSION['password']."') and
            Caserma_residenza = (SELECT Caserma_residenza FROM Agente WHERE Nome = '".$_SESSION['nome']."'
            and Cognome = '".$_SESSION['cognome']."' and Password = '".$_SESSION['password']."');"
        ); 

        if($ris -> num_rows > 1) {
            $i = 1;
            while($row = $ris -> fetch_assoc()) {
                $body .= '
                <tr class="color_row'.$i.'">
                    <td>'.$row['Cognome'].' '.$row['Nome'].'</td>
                    <td>'.$row['Eta'].'</td>
                    <td>'.$row['Statura'].'</td>
                    <td>'.$row['Genere'].'</td>
                    <td>'.$row['Residenza'].'</td>
                    <td>'.$row['Telefono'].'</td>
                    <td>'.$row['Email'].'</td>
                    <td>'.$row['Caserma_nome'].'. '.$row['Caserma_residenza'].'</td>
                    <td>' // Eseguo la query che fa un conteggio di tutti eventi riportati
                    // da uno specifico agente. Dopodiché conteverto l'intero risultato della
                    // query in un array associativo ed estraggo il valore della sua colonna.
                    .($conn -> query(
                        "SELECT * FROM Evento WHERE Nome_agente = '".$row['Nome']."'
                        and Cognome_agente = '".$row['Cognome']."' and 
                        Password_agente = '".$row['Password']."'"
                    )) -> num_rows.
                    '</td>';
                $body .= '</tr>';
                $i = $i ? 0 : 1;
            }
            echo $body.'</table>';

        } else {
            echo '<br><p>Non ci sono colleghi registrati</p>';
        }
    } 

    #
    # Se dal file consulta.php viene inviata una querystring ?query=pattuglia
    #
    else if($_POST['query'] == 'pattuglia') { 
        $tabella_ris = '
        <br>
        <h2>PATTUGLIE DISPONIBILI</h2>
        <table>
            <tr>
                <th>Id</th>
                <th>Inizio e fine turno</th>
                <th>Fascia giornaliera</th>
                <th>Strada</th>
            </tr>';
        
        // Operazione di JOIN: lega tra loro due tabelle che hanno gli stessi valori nei campi specificati,
        $ris = $conn -> query(
            "SELECT Pa.* FROM Pattuglia as Pa JOIN Agente as Ag ON Pa.Caserma_Nome = Ag.Caserma_nome 
            and Pa.Caserma_residenza = Ag.Caserma_residenza WHERE Ag.Nome = '".$_SESSION['nome']."' and Ag.Cognome = '".$_SESSION['cognome'].
            "' and Ag.Password = '".$_SESSION['password']."'"
        );

        if($ris -> num_rows > 0) {
            $i = 1;
            while($row = $ris -> fetch_assoc()) {
                $tabella_ris .= 
                '<tr class="color_row'.$i.'">
                    <td>'.$row['Numero'].'</td>
                    <td>'.$row['Inizio_turno'].'--'.$row['Fine_turno'].'</td>
                    <td>'.$row['Turno'].'</td>
                    <td>';
                    // Query: si estrae la strada in cui la pattuglia svolge il suo lavoro 
                    // durante il suo turno
                    $supervisiona = $conn -> query(
                        'SELECT Num_strada, Class_strada FROM Supervisiona WHERE 
                        Num_pattuglia = "'.$row['Numero'].'" and Nome_caserma = "'.$row['Caserma_nome'].'"
                        and Residenza_caserma = "'.$row['Caserma_residenza'].'"'
                    );
                    if($supervisiona -> num_rows > 0) {
                        while($recordSupervisiona = $supervisiona -> fetch_assoc()) {
                            $tabella_ris .= $recordSupervisiona['Class_strada'].' '.$recordSupervisiona['Num_strada'].', ';
                        }
                    } else {
                        $tabella_ris .= 'nessuna';
                    }
                    $tabella_ris .= '</td>
                </tr>';
                $i = $i ? 0 : 1;
            }
            echo $tabella_ris.'</table>'; // Si stampano le informazioni delle pattuglie
        } else {
            echo '<br><p>Non sono presenti pattuglie</p>';
        }
    } 
    
    #
    # Se dal file consulta.php viene inviata una querystring ?query=caserma: vengono 
    # stampate tutte le altre caserme del territorio
    #
    else if($_POST['query'] == 'caserma') { 
        $body = '
        <br>
        <h2>CASERME</h2>
        <table>
            <tr>
                <th>Nome</th>
                <th>Residenza</th>
                <th>Telefono</th>
                <th>Email</th>
                <th>Operatori registrati</th>
            </tr>';
        
        $ris = $conn -> query('SELECT * FROM Caserma'); // Una query: tutte le infomazioni delle caserme in territorio

        if($ris -> num_rows > 0) {
            $i = 1;
            while($row = $ris -> fetch_assoc()) {
                $body .= 
                    '<tr class="color_row'.$i.'">
                        <td>'.$row['Nome'].'</td>
                        <td>'.$row['Residenza'].'</td>
                        <td>'.$row['TelefonoFax'].'</td>
                        <td>'.$row['Email'].'</td>
                        <td>
                            <form action="query_list.php" method="post" style="display: inline">
                                <input type="hidden" name="nome_caserma" value="'.$row['Nome'].'">
                                <input type="hidden" name="residenza_caserma" value="'.$row['Residenza'].'">
                                <input type="submit" name="query" value="Visualizza">
                            </form>
                        </td>
                    </tr>';
                $i = $i ? 0 : 1;
            }
            echo $body.'</table>';
        } else {
            echo '<p>Non sono presenti caserme nel territorio</p>';
        }
    } 
    
    #
    # Se dallo stesso file si vuole sapere l'elenco degli utenti registrati al db
    # in un'altra caserma
    #
    else if($_POST['query'] == 'Visualizza') {

        $tabella_ris =
        '<br>
        <h2>UTENTI REGISTRATI</h2>
        <table>
            <tr>
                <th>Utente</th>
                <th>Età</th>
                <th>Residenza</th>
                <th>Genere</th>
                <th>Statura</th>
                <th>Telefono</th>
                <th>Email</th>
            </tr>';
        
        // Si esegue una query di tutti operatori registrati nella caserma scelta
        $ris = $conn -> query( 
            'SELECT * FROM Agente WHERE Caserma_nome = "'.$_POST['nome_caserma'].'" 
            and Caserma_residenza = "'.$_POST['residenza_caserma'].'"'
        );

        $i = 1;
        if($ris -> num_rows > 0) {
            while($row = $ris -> fetch_assoc()) {
                $tabella_ris .=
                '<tr class="color_row'.$i.'">
                    <td>'.$row['Cognome'].' '.$row['Nome'].'</td>
                    <td>'.$row['Eta'].'</td>
                    <td>'.$row['Residenza'].'</td>
                    <td>'.$row['Genere'].'</td>
                    <td>'.$row['Statura'].'</td>
                    <td>'.$row['Telefono'].'</td>
                    <td>'.$row['Email'].'</td>
                </tr>';
                $i = $i ? 0 : 1;
            }
            echo $tabella_ris.'</table>';
        } else {
            echo '<br><p>Non sono presenti utenti registrati</p>';
        }

        echo '<br>
        <form action="query_list.php" method="post" class="btn-indietro">
            <button class="btn" name="query" value="caserma">Torna indietro</button>
        </form>';
    } else if($_POST['query'] == 'veicoli') {
        $tabella_ris = '<br>
        <h2>VEICOLI</h2>
        <table>
            <tr>
                <th>Targa</th>
                <th>Colore</th>
                <th>Cilindrata</th>
                <th>Modello</th>
                <th>Categoria</th>
                <th>Conducente</th>
                <th>Proprietario</th>
            </tr>';
        
            $ris = $conn -> query(
                'SELECT Ve.* FROM Veicolo as Ve JOIN Coinvolge as Co ON 
                Co.Codice_evento = "'.$_POST['codice_evento'].'" and
                Ve.Targa = Co.Targa_veicolo'
            );

            if($ris -> num_rows > 0) {
                $i = 1;
                while($row = $ris -> fetch_assoc()) {
                    $tabella_ris .= '
                    <tr class="color_row'.$i.'">
                        <td>'.$row['Targa'].'</td>
                        <td>'.$row['Colore'].'</td>
                        <td>'.$row['Cilindrata'].'</td>
                        <td>'.$row['Modello'].'</td>
                        <td>'.$row['Categoria'].'</td>
                        <td>
                            <form action="query_list.php" method="post" style="display:inline">
                                <input type="hidden" name="query" value="conducente">
                                <input type="hidden" name="targa" value="'.$row['Targa'].'">
                                <input type="submit" value="Controlla">
                            </form>
                        </td>
                        <td>
                            <form action="query_list.php" method="post" style="display:inline">
                                <input type="hidden" name="query" value="proprietario">
                                <input type="hidden" name="targa" value="'.$row['Targa'].'">
                                <input type="submit" value="Controlla">
                            </form>
                        </td>
                    </tr>';
                    $i = $i ? 0 : 1;
                }
                echo $tabella_ris.'</table>';
            } else {
                echo '<br><p>Non sono presenti veicoli</p>';
            }
            echo '<br>
            <form action="query_list.php" method="post" class="btn-indietro">
                <button class="btn" name="query" value="evento">Torna indietro</button>
            </form>';
    } else if($_POST['query'] == 'conducente') {
        # Se da questo file si vuole sapere le informazioni del conducente

        $conducente = $conn -> query(
            'SELECT Pe.* FROM Persona as Pe JOIN Veicolo as Ve ON Pe.CF = Ve.CF_conducente
            WHERE Ve.Targa = "'.$_POST['targa'].'"'
        );

        if($conducente -> num_rows == 1) {
            $conducente = $conducente -> fetch_assoc();
            $tabella_ris = '<br>
            <h2>CONDUCENTE</h2>
            <table>
                <tr style="border: solid 1px black; background: dodgerblue">
                    <th>Codice fiscale</th>
                    <th>Nome</th>
                    <th>Cognome</th>
                    <th>Età</th>
                    <th>Statura</th>
                    <th>Genere</th>
                    <th>Data di nascita</th>
                    <th>Patente</th>
                    <th>Email</th>
                    <th>Telefono</th>
                    <th>Residenza</th>
                </tr>
                <tr>
                    <td>'.$conducente['CF'].'</td>
                    <td>'.$conducente['Nome'].'</td>
                    <td>'.$conducente['Cognome'].'</td>
                    <td>'.$conducente['Eta'].'</td>
                    <td>'.$conducente['Statura'].'</td>
                    <td>'.$conducente['Genere'].'</td>
                    <td>'.$conducente['Data_nascita'].'</td>
                    <td>'.$conducente['Patente'].'</td>
                    <td>'.($conducente['Email'] ? $conducente['Email'] : 'nessuna').'</td>
                    <td>'.$conducente['Telefono'].'</td>
                    <td>'.$conducente['Residenza'].'</td>
                </tr>
            </table>';
            echo $tabella_ris;
        } else {
            echo '<br><p>Non è presente un conducente</p>';
        }
        echo '<br>
        <form action="query_list.php" method="post" class="btn-indietro">
            <button class="btn" name="query" value="evento">Torna indietro</button>
        </form>';
    } else if($_POST['query'] == 'proprietario') {
        # Se da questo file si vuole sapere le infomazioni del proprietario

        // Prima controlliamo se il proprietario è una persona o un ente
        $cod_proprietario = $conn -> query(
            'SELECT CF_ente, CF_persona FROM Veicolo WHERE Targa = "'.$_POST['targa'].'"'
        );

        $cod_proprietario = $cod_proprietario -> fetch_assoc();

        if($cod_proprietario['CF_ente']) {

            $ente = $conn -> query(
                'SELECT * FROM Ente WHERE CF = "'.$cod_proprietario['CF_ente'].'"'
            );
                $ente = $ente -> fetch_assoc();
                $tabella_ris = '<br>
                <h2>Proprietario (ente)</h2>
                <table>
                    <tr>
                        <th>Codice fiscale</th>
                        <th>Nome</th>
                        <th>Tipologia</th>
                        <th>Servizio</th>
                        <th>Numero di sedi</th>
                        <th>Email</th>
                        <th>Telefono</th>
                        <th>Residenza</th>
                    </tr>
                    <tr>
                        <td>'.$ente['CF'].'</td>
                        <td>'.$ente['Nome'].'</td>
                        <td>'.$ente['Tipologia'].'</td>
                        <td>'.$ente['Servizio'].'</td>
                        <td>'.$ente['N_sedi'].'</td>
                        <td>'.($ente['Email'] ? $ente['Email'] : null).'</td>
                        <td>'.$ente['Telefono'].'</td>
                        <td>'.$ente['Residenza'].'</td>
                    </tr>
                </table>';
                echo $tabella_ris;
            
        } else if($cod_proprietario['CF_persona']) {

            $persona = $conn -> query(
                'SELECT * FROM Persona WHERE CF = "'.$cod_proprietario['CF_persona'].'"'
            );

            $persona = $persona -> fetch_assoc();

            $tabella_ris = '<br>
            <h2>Proprietario (persona)</h2>
            <table>
                <tr>
                    <th>Codice fiscale</th>
                    <th>Nome</th>
                    <th>Cognome</th>
                    <th>Età</th>
                    <th>Statura</th>
                    <th>Genere</th>
                    <th>Data di nascita</th>
                    <th>Patente</th>
                    <th>Email</th>
                    <th>Telefono</th>
                    <th>Residenza</th>
                </tr>
                <tr>
                    <td>'.$persona['CF'].'</td>
                    <td>'.$persona['Nome'].'</td>
                    <td>'.$persona['Cognome'].'</td>
                    <td>'.$persona['Eta'].'</td>
                    <td>'.$persona['Statura'].'</td>
                    <td>'.$persona['Genere'].'</td>
                    <td>'.$persona['Data_nascita'].'</td>
                    <td>'.($persona['Patente'] ? $persona['Patente'] : 'nessuna').'</td>
                    <td>'.($persona['Email'] ? $persona['Email'] : 'nessuna').'</td>
                    <td>'.$persona['Telefono'].'</td>
                    <td>'.$persona['Residenza'].'</td>
                </tr>
            </table>';
            echo $tabella_ris;          
        } else {
            echo '<br><p>Non è presente ancora un proprietario</p>';
        }
        echo '<br>
        <form action="query_list.php" method="post" class="btn-indietro">
            <button class="btn" name="query" value="evento">Torna indietro</button>
        </form>';
    } else if($_POST['query'] == 'personaPresente') {
        # In questo stesso file, si vuole sapere la persona che ha causato tale evento
        
        // Prima di tutto si controlla se è davvero presente il CF della persona
        $personaPresente = $conn -> query(
            'SELECT Pe.* FROM Persona as Pe JOIN Evento as Ev ON Ev.CF_persona = Pe.CF
            WHERE Ev.Codice = "'.$_POST['codice_evento'].'"'
        );

        if($personaPresente -> num_rows == 1) {
            $personaPresente = $personaPresente -> fetch_assoc();
            $tabella_ris = '
            <table>
                <tr>
                    <th>Codice fiscale</th>
                    <th>Nome</th>
                    <th>Cognome</th>
                    <th>Età</th>
                    <th>Statura</th>
                    <th>Genere</th>
                    <th>Data di nascita</th>
                    <th>Patente</th>
                    <th>Email</th>
                    <th>Telefono</th>
                    <th>Residenza</th>
                </tr>
                <tr>
                    <td>'.$personaPresente['CF'].'</td>
                    <td>'.$personaPresente['Nome'].'</td>
                    <td>'.$personaPresente['Cognome'].'</td>
                    <td>'.$personaPresente['Eta'].'</td>
                    <td>'.$personaPresente['Genere'].'</td>
                    <td>'.$personaPresente['Data_nascita'].'</td>
                    <td>'.($personaPresente['Patente'] ? $personaPresente['Patente'] : 'nessuna').'</td>
                    <td>'.($personaPresente['Email'] ? $personaPresente['Email'] : 'nessuna').'</td>
                    <td>'.$personaPresente['Telefono'].'</td>
                    <td>'.$personaPresente['Residenza'].'</td>
                </tr>
            </table>';
        } else {
            echo '<br><p>Non c\'è una persona registrata</p>';
        }
      
        echo '<br>
        <form action="query_list.php" method="post" class="btn-indietro">
            <button class="btn" name="query" value="evento">Torna indietro</button>
        </form>';
    }

    // Una stampa di un form con un bottone per tornare alla homepage
    echo 
    '<br>
    <form action="homepage.php" method="post" style="display:flex; justify-content:center">
        <button class="btn">Torna alla homepage</button>
    </form>';
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Consulta</title>
        <link rel="stylesheet" href="query_list.css">
        </style>
    </head>
</html>
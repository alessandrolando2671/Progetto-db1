<?php
    require "controllo.php"; // Importa ed esegue il "controllo"
    require "conn.php"; // Importa $conn

    /*
    
        Questo file è usato principalmente per eliminare dei record dalle tabelle del db

    */

    // Se dal file event_list.php si vuole eliminare il record con il codice dell'evento
    if(isset($_POST['codice'])) {

        # Eliminando dalla tabella Evento, si elimina pure tutti i record della 
        # tabella "associativa" Coinvolge perché partecipavano all'evento, poichè è presente
        # una politica di  cascade nella FK Codice_evento della tabella Coinvolge
        $eliminaEvento = $conn -> prepare("DELETE FROM Evento WHERE Codice = ?");
        $eliminaEvento -> bind_param('i', $_POST['codice']);
        $eliminaEvento -> execute(); // Si esegue la query  da eliminare

    } else if(isset($_POST['targa'])) {

        # Se dal file veicolo_list.php si vuole eliminare un record di un veicolo
        $eliminaVeicolo = $conn -> prepare("DELETE FROM Veicolo WHERE Targa = ?");
        $eliminaVeicolo -> bind_param('s', $_POST['targa']);
        $eliminaVeicolo -> execute();

    } else if(isset($_POST['annulla'])) {

        # Dal momento che si vuole aggiungere un veicolo e si decide, poi, di annullare l'operazione.
        # In questa condzione viene eseguita una query per eliminare il record del veicolo e il record 
        # nella tabella Coinvolge, che era stato precedentemente aggiunto.
        $eliminaRecord = $conn -> prepare('DELETE FROM Coinvolge WHERE Targa = ?');
        $eliminaRecord -> bind_param('s',  $_POST['annulla']);
        $eliminaRecord -> execute();

        # ... Inoltre, in MySQL, nella tabella Coinvolge è presente una politica di
        # cascade. Quindi eliminando il record del veicolo, si elimina pure il record nella
        # tabella Coinvolge che faceva riferimento

    }  else if(isset($_POST['ente'])) {

        # Se si vuole eliminare le infomazioni di una ente
        $eliminaEnte = $conn -> prepare('DELETE FROM Ente WHERE CF = ?');
        $eliminaEnte -> bind_param('s', $_POST['ente']);
        $eliminaEnte -> execute();

    } else if(isset($_POST['persona'])) {
        
        # Se si vuole eliminare le infomazioni di una persona proprietario o le infomazioni di una 
        # persona coinvolta in un evento o le informazioni di un conducente
        $eliminaPersona = $conn -> prepare('DELETE FROM Persona WHERE CF = ?');
        $eliminaPersona -> bind_param('s', $_POST['persona']);
        $eliminaPersona -> execute();

    }
    
    header("Location: ../Progetto/event_list.php");
?>
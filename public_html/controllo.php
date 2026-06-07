<?php
    # File per un controllo, cioè "entrare come si deve" attraverso la pagina di login (index.php)
    # oppure per navigare tra le pagine della piattaforma con le proprie variabili di sessione
    # impostate dopo essere entrati correttamente dalla pagina di login
    
    // Inizia una sessione: serve per tenere traccia degli utenti che sono dentro la piattaforma.
    session_start();

    // Questa condizione serve per controllare se l'utente sta tentando di entrare dal login ($_POST['nome'])
    // oppure se sta navigando tra le sezioni della piattaforma. Se così non fosse, viene respinto.
    if(!isset($_SESSION['nome']) and !isset($_SESSION['cognome']) and 
    !isset($_SESSION['password']) and !isset($_POST['nome'])) {
        session_destroy();
        die("<h1>Accesso negato!</h1>");
    } else if(isset($_SESSION['nome'])) {
        if(isset($_POST['logout'])) { // Verifica se l'utente ha le "variabili" di sessione e vuole uscire
            session_destroy();
            header("Location: ../Progetto");
        }
    } else {
        include "conn.php"; // Importa $conn

        // Si esegue una query per controllare se l'utente esiste all'interno del db
        $risultato = $conn -> query(
            'SELECT count(*) as Utente FROM Agente WHERE Nome = "'.$_POST['nome'].'" and
            Cognome = "'.$_POST['cognome'].'" and Password = "'.$_POST['password'].'"'
        );

        if($risultato -> fetch_assoc()['Utente']) { // Se l'utente esiste si creano le sue variabili di sessione
            $_SESSION['nome'] = $_POST['nome'];
            $_SESSION['cognome'] = $_POST['cognome'];
            $_SESSION['password'] = $_POST['password'];
        } else {
            header("Location: not_found.html"); // Se l'utente non esiste o la password è sbagliata, passa alla pagina html not_found
        }
    }
?>
<?php
    # File per la connessione al DB
    
    $servername = "mysql";
    $username = "root";
    $password = "root";
    $database = "PoliziaStradale";

    // Crea connessione al database
    $conn = new mysqli($servername, $username, $password, $database);

    // Verifica connessione
    if($conn->connect_error) {
        die("connessione fallita: " . $conn->connect_error);
    }
?>
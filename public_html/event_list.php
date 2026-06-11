<?php 
    require "controllo.php"; // Importa ed esegue il "controllo"
    require "conn.php"; // Importa $conn

    # Questo file serve per estrarre tutti i dati degli eventi scritti dall'utente connesso,
    # e per vedere se l'utente vuole fare alcune aggiunte, modifiche o cancellazioni di record
    # di alune tabelle che "partecipano" alla tabella Evento, come la tabella Veicolo

    // Si esegue una query di tutti eventi inseriti dall'agente
    $ris = $conn -> query(
        "SELECT * FROM Evento WHERE Nome_agente = '".$_SESSION['nome']."'
        and Cognome_agente = '".$_SESSION['cognome']."'
        and Password_agente = '".$_SESSION['password']."'"
    );

    if($ris -> num_rows > 0) {
        $tabella_ris =
        '<h2>I TUOI REPORT</h2>
        <table text-align: center; border-collapse: collapse; justify-self:center">
            <tr background: dodgerblue">
                <th>Data e Orario</th>
                <th>Strada</th>
                <th>Tempo</th>
                <th>Tratto di strada</th>
                <th>Posizione</th>
                <th>Descrizione</th>
                <th>Persona presente</th>
                <th>Pattuglie assegnate</th>
                <th>Veicoli</th>
                <th></th>
            </tr>';
        $i = 1;
        while($row = $ris -> fetch_assoc()) {
            $tabella_ris .= 
            '<tr class="color_row'.$i.'">
                <td>'.$row['DataOrario'].'</td>
                <td>'.$row['Class_strada'].' '.$row['Num_strada'].'</td>
                <td>'.$row['Tempo'].'</td>
                <td>'.$row['Tratto'].' km</td>
                <td>'.$row['Posizione'].'</td>
                <td>'.$row['Descrizione'].'</td>
                <td>
                    <form action="soggetti_list.php" method="post" style="display:inline">
                        <input type="hidden" name="personaPresente" value="personaPresente">
                        <input type="hidden" name="codice_evento" value="'.$row['Codice'].'">
                        <input type="submit" value="Controlla">
                    </form>
                </td>
                <td>';
                    // Operazione di JOIN: estraggo dalle pattuglie, che partecipano all'evento,
                    // il loro numero identificativo
                    $PattuglieAssegnate = $conn -> query(
                        'SELECT Num_pattuglia FROM  Assegnato WHERE Codice_evento = "'.$row['Codice'].'"'
                    );
                    if($PattuglieAssegnate -> num_rows > 0) {
                        while($recordPattuglie = $PattuglieAssegnate -> fetch_assoc()) {
                            $tabella_ris .= $recordPattuglie['Num_pattuglia'].', ';
                        }
                    } else {
                        $tabella_ris .= 'nessuna';
                    }
                $tabella_ris .= '</td>
                <td>
                    <form action="veicoli_list.php" method="post" style="display: inline">
                        <button name="codice" value="'.$row['Codice'].'">Controlla</button>
                    </form>
                </td>
                <td>
                    <form action="delete.php" method="post" style="display: inline">
                        <button name="codice" value="'.$row['Codice'].'">Elimina</button>
                    </form>
                    <form action="update.php" method="post" style="display: inline">
                        <button name="evento" value="'.$row['Codice'].'">Modifica</button>
                    </form>
                </td>
            <tr>';
            $i = $i == 0 ? 1 : 0;
        }

        echo $tabella_ris.'</table>';
    } else {
        echo '<br><p>Non ci sono eventi da eliminare o modificare</p>';
    }
    
    echo '<br>
    <form action="homepage.php" method="post" style="display:flex; justify-content:center">
        <button class="btn">Torna alla homepage</button>
    </form>';
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Eventi</title>
        <style>
            body {
                background: #1c2841;
                padding-top: 15px;
            }
            table {
                width: 95%;
                margin: 30px auto;
                border-collapse: collapse; /* Toglie lo spazio tra le celle */
                background: rgba(255, 255, 255, 0.05); /* Sfondo semi-trasparente */
                backdrop-filter: blur(10px);
                border-radius: 15px;
                overflow: hidden; /* Importante per mantenere gli angoli arrotondati */
                color: white;
                font-family: sans-serif;
            }
            p, h2 {
                color: #f0ffff;
                text-align: center;
            }
            .btn {
                padding: 0.3em 0.9rem;
                width: 100%;
                font-size: 0.43cm;
                font-weight: 600;
                background: transparent;
                color: #00abf0;
                border-radius: 35px;
                border: 2px solid #00abf0;
                font-family: sans-serif;
                cursor: pointer;
                transition: 0.5s;
                position: relative;
                overflow: hidden;
                z-index: 1;
            }

            .color_row1 {
                background: skyblue;
            }
            .btn::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                width: 0;
                height: 100%;
                background: #00abf0;
                transition: 0.5s;
                z-index: -1;
            }

            .btn{
                display: inline-block;
                margin-top: 20px;
                width: auto;
                padding: 10px 25px;
                border: 1px solid #3498db;
                color: #3498db;
                text-decoration: none;
                border-radius: 50px;
                transition: 0.3s;
                text-align: center;
            }

            .btn:hover {
                background: #3498db;
                color: white;
                box-shadow: 0 0 15px rgba(52, 152, 219, 0.4);
            }
            select {
                border-radius: 20px;
                text-align:center;
            }
            td {
                padding: 12px 15px;
                text-align: center;
                border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            }

            /* Effetto zebra moderno */
            tr:nth-child(even) {
                background-color: rgba(255, 255, 255, 0.02);
            }

            /* Effetto hover: la riga si illumina quando ci passi sopra */
            tr:hover {
                background-color: rgba(255, 255, 255, 0.1);
                transition: 0.3s;
            }
            th {
                background-color: #3498db; /* Il blu di sfondo attuale */
                color: #ffffff;            /* TESTO BIANCO: ora si legge perfettamente */
                font-weight: bold;         /* Grassetto */
                text-transform: uppercase; /* Tutto maiuscolo per un look più tecnico */
                padding: 15px;
                font-size: 0.9rem;
                letter-spacing: 1px;       /* Distanzia leggermente le lettere */
            }
        </style>
    </head>
</html>
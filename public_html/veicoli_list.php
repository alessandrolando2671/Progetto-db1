<?php
    require "controllo.php"; // Importa ed esegue il "controllo"
    require "conn.php"; // Importa $conn

    /*
     * 
     *  Questo file serve per dare un elenco dei veicoli coinvolti nell'evento scelto
     * 
     */

    // Operatione di JOIN: vengono estratti tutti i veicoli che partecipano all'evento
    $veicoloCoinvolti = $conn -> query(
        'SELECT Ve.* FROM Coinvolge as Co JOIN Veicolo as Ve ON 
        Co.Codice_evento = "'.$_POST['codice'].'" and Co.Targa_veicolo = Ve.Targa'
    );

    if($veicoloCoinvolti -> num_rows) { // Viene fatto un output di tutti i veicoli coinvolti
        $tabella_ris = '
        <br>
        <h2>VEICOLI</h2>
        <table>
            <tr>
                <th>Targa</th>
                <th>Colore</th>
                <th>Cilindrata</th>
                <th>Modello</th>
                <th>Categoria</th>
                <th>Conducente e Proprietario</th>
                <th></th>
            </tr>';
            $i = 1;
            while($row = $veicoloCoinvolti -> fetch_assoc()) {
                $tabella_ris .= '
                    <tr class="color_row'.$i.'">
                        <td>'.$row['Targa'].'</td>
                        <td>'.$row['Colore'].'</td>
                        <td>'.$row['Cilindrata'].'</td>
                        <td>'.$row['Modello'].'</td>
                        <td>'.$row['Categoria'].'</td>
                        <td>
                            <form action="soggetti_list.php" method="post" style="display:inline">
                                <button name="targa" value="'.$row['Targa'].'">Visualizza</button>
                            </form>
                        </td>
                        <td>
                            <form action="delete.php" method="post" style="display:inline">
                                <button name="targa" value="'.$row['Targa'].'">Rimuovi</button>
                            </form>
                            <form action="update.php" method="post" style="display:inline">
                                <button name="veicolo" value="'.$row['Targa'].'">Modifica</button>
                            </form>
                        </td>
                    </tr>';
                $i = $i == 0 ? $i++ : 0;
            }
        echo $tabella_ris.'</table>';
    } else {
        echo '<br><p style="text-align:center">Non ci sono veicoli coinvolti</p>';
    }

    echo '<br>
    <div style="justify-content:center; display:flex; gap: 10px">
        <form action="veicolo_form.php" method="post">
            <input type="hidden" name="codice_evento" value="'.$_POST['codice'].'">
            <button class="btn">Aggiungi veicolo</button>
        </form>
        <form action="event_list.php" method="post">
            <button class="btn">Torna indietro</button>
        </form>
    </div>';
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Veicoli</title>
        <style>
            body {
                background: #1c2841;
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
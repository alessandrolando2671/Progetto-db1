<?php
    require 'controllo.php';
    require 'conn.php';


    /*
     *
     *  Questo file serve per vedere le informazioni del proprietario del 
     *  veicolo e del conducente (se presente) che, al momento dell'evento,
     *  era nel veicolo. Oppure per vedere le informazioni della persona 
     *  presente in un determinato evento. 
     * 
     */

    
    ###  SE VIENE MANDATA UNA VARIABILE PER SAPERE LA PERSONA PRESENTE IN UN EVENTO  ###
    if(isset($_POST['personaPresente'])) {
        $personaPresente = $conn -> query(
            'SELECT Pe.* FROM Persona as Pe JOIN Evento as Ev ON Ev.CF_persona = Pe.CF
            WHERE Ev.Codice = "'.$_POST['codice_evento'].'"'
        );

        if($personaPresente -> num_rows) {
            $personaPresente = $personaPresente -> fetch_assoc();

            $tabella_ris = '<br>
            <h2>SOGGETTO</h2>
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
                    <th></th>
                </tr>
                <tr>
                    <td>'.$personaPresente['CF'].'</td>
                    <td>'.$personaPresente['Nome'].'</td>
                    <td>'.$personaPresente['Cognome'].'</td>
                    <td>'.$personaPresente['Eta'].'</td>
                    <td>'.$personaPresente['Statura'].'</td>
                    <td>'.$personaPresente['Genere'].'</td>
                    <td>'.$personaPresente['Data_nascita'].'</td>
                    <td>'.($personaPresente['Patente'] ? $personaPresente['Patente'] : 'nessuna').'</td>
                    <td>'.($personaPresente['Email'] ? $personaPresente['Email'] : 'nessuna').'</td>
                    <td>'.$personaPresente['Telefono'].'</td>
                    <td>'.$personaPresente['Residenza'].'</td>
                    <td>
                        <form action="delete.php" method="post" style="display:inline">
                            <button name="persona" value="'.$personaPresente['CF'].'">Elimina</button>
                        </form>
                        <form action="update.php" method="post" style="display:inline">
                            <button name="persona" value="'.$personaPresente['CF'].'">Modifica</button>
                        </form>
                    </td>
                </tr>
            </table>';
            echo $tabella_ris;
        } else {
            echo '<br>
            <p>Non c\'è una persona riportata</p>
            <form action="soggetto_form.php" method="post" style="display:flex; justify-content:center">
                <input type="hidden" name="codice_evento" value="'.$_POST['codice_evento'].'">
                <button class="btn" name="personaPresente">Aggiungi</button>
            </form>';
        }
    } else { ###  ...ALTRIMENTI, SI VUOLE SAPERE IL CONDUCENTE E/O PROPRIETARIO  ###

        // Query: si estrae le chiavi esterne del proprietario (Persona o Ente) e del conducente (se presente)
        $ris_FK = $conn -> query(
            'SELECT CF_conducente, CF_ente, CF_persona FROM Veicolo WHERE Targa = "'.$_POST['targa'].'"'
        );

        $ris_FK = $ris_FK -> fetch_assoc(); // Rinizializzo l'oggetto in un array associativo
    
        # Se dal risultato della query la Foreign Key non è null per persona (proprietario)
        if($ris_FK['CF_persona']) {

            // Query: estraggo le informazioni della persona proprietaria
            $proprietarioPersona = $conn -> query(
                'SELECT * FROM Persona WHERE CF = "'.$ris_FK['CF_persona'].'"'
            );

            $proprietarioPersona = $proprietarioPersona -> fetch_assoc(); // Rinizializzo l'oggetto in un array associativo

            $tabella_ris_proprietario = '<br>
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
                    <td>'.$proprietarioPersona['CF'].'</td>
                    <td>'.$proprietarioPersona['Nome'].'</td>
                    <td>'.$proprietarioPersona['Cognome'].'</td>
                    <td>'.$proprietarioPersona['Eta'].'</td>
                    <td>'.$proprietarioPersona['Statura'].'</td>
                    <td>'.$proprietarioPersona['Genere'].'</td>
                    <td>'.$proprietarioPersona['Data_nascita'].'</td>
                    <td>'.($proprietarioPersona['Patente'] ? $proprietarioPersona['Patente'] : 'No').'</td>
                    <td>'.($proprietarioPersona['Email'] ? $proprietarioPersona['Email'] : 'No').'</td>
                    <td>'.$proprietarioPersona['Telefono'].'</td>
                    <td>'.$proprietarioPersona['Residenza'].'</td>
                </tr>
            </table>
            <br>
            <div style="display:flex; justify-content:center; gap: 20px">
                <form action="update.php" method="post">
                    <button class="btn" name="persona" value="'.$proprietarioPersona['CF'].'">Modifica</button>
                </form>
                <form action="delete.php" method="post">
                    <button name="persona" class="btn" value="'.$proprietarioPersona['CF'].'">Elimina</button>
                </form>
            </div>';
            echo $tabella_ris_proprietario;
        } else if($ris_FK['CF_ente']) {
            # Se dal risultato della query la Foreign Key non è null per ente (proprietario)

            // Query: estraggo le informazione dell'ente proprietario
            $proprietarioEnte = $conn -> query(
                'SELECT * FROM Ente WHERE CF = "'.$ris_FK['CF_ente'].'"'
            );

            $proprietarioEnte = $proprietarioEnte -> fetch_assoc(); // Rinizializzo l'oggetto in un array associativo

            $tabella_ris_proprietario = '<br>
            <h2>Proprietario (ente)</h2>
            <table>
                <tr style="border: solid 1px black; background: dodgerblue">
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
                    <td>'.$proprietarioEnte['CF'].'</td>
                    <td>'.$proprietarioEnte['Nome'].'</td>
                    <td>'.$proprietarioEnte['Tipologia'].'</td>
                    <td>'.$proprietarioEnte['Servizio'].'</td>
                    <td>'.$proprietarioEnte['N_sedi'].'</td>
                    <td>'.($proprietarioEnte['Email'] ? $proprietarioEnte['Email'] : 'No').'</td>
                    <td>'.$proprietarioEnte['Telefono'].'</td>
                    <td>'.$proprietarioEnte['Residenza'].'</td>
                </tr>
            </table>
            <br>
            <div style="display:flex; justify-content:center; gap: 20px">
                <form action="update.php" method="post">
                    <button name="ente" class="btn" name="ente" value="'.$proprietarioEnte['CF'].'">Modifica</button>
                </form>
                <form action="delete.php" method="post">
                    <button name="ente" class="btn" name="ente" value="'.$proprietarioEnte['CF'].'">Elimina</button>
                </form>
            </div>';
            echo $tabella_ris_proprietario;
        } else {
            # ..altrimenti, è possibile che nessun informazione sul proprietario (persona o ente)
            # è stata ancora aggiunta
            echo '<br>
            <p>Non sono presenti proprietari registrati</p>
            <form style="justify-content:center; width: 35%; display:flex; gap: 10px" action="soggetto_form.php" method="post">
                <input type="hidden" name="targa" value="'.$_POST['targa'].'">
                <button class="btn" name="proprietario" value="persona">Aggiungi proprietario persona</button>
                <button class="btn" name="proprietario" value="ente">Aggiungi proprietario ente</button>
            </form>';
        }

        // Query: estraggo le informazione del soggetto conducente
        $conducente = $conn -> query(
            'SELECT * FROM Persona WHERE CF = "'.$ris_FK['CF_conducente'].'"'
        );

        $conducente = $conducente -> fetch_assoc(); // Rinizializzo l'oggetto in un array associativo

        # Se dal risultato della query è presente il conducente
        if($ris_FK['CF_conducente']) {
            $tabella_ris_conducente = '<br>
            <h2>Conducente</h2>
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
                    <td>'.$conducente['CF'].'</td>
                    <td>'.$conducente['Nome'].'</td>
                    <td>'.$conducente['Cognome'].'</td>
                    <td>'.$conducente['Eta'].'</td>
                    <td>'.$conducente['Statura'].'</td>
                    <td>'.$conducente['Genere'].'</td>
                    <td>'.$conducente['Data_nascita'].'</td>
                    <td>'.($conducente['Patente'] ? $conducente['Patente'] : 'No').'</td>
                    <td>'.($conducente['Email'] ? $conducente['Email'] : 'No').'</td>
                    <td>'.$conducente['Telefono'].'</td>
                    <td>'.$conducente['Residenza'].'</td>
                </tr>
            </table>
            <br>
            <div style="display:flex; justify-content:center; gap: 20px">
                <form action="update.php" method="post">
                    <input type="hidden" name="patente">
                    <input type="hidden" name="persona" value="'.$conducente['CF'].'">
                    <button class="btn">Modifica</button>
                </form>
                <form action="delete.php" method="post">
                    <button class="btn" name="persona" value="'.$conducente['CF'].'">Elimina</button>
                </form>
            </div>';
            echo $tabella_ris_conducente;
        } else {
            # ..altrimenti, possibile che nessun informazione sul conducente
            # è stata ancora aggiunta     
            echo '<br>
            <p>Non sono presenti conducenti registrati</p>
            <form style="display:flex; justify-content:center" action="soggetto_form.php" method="post">
                <input type="hidden" name="conducente">
                <input type="hidden" name="targa" value="'.$_POST['targa'].'">
                <button class="btn">Aggiungi conducente</button>
            </form>';
        }
    }

    // Un bottone per tornare alla lista dei propri eventi
    echo '<br>
    <form style="display:flex; justify-content:center" action="event_list.php" method="post">
        <button class="btn">Torna indietro</button>
    </form>';
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Soggetto</title>
        <link rel="stylesheet" href="query_list.css">
    </head>
</html>
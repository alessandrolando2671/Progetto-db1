<?php
    require "controllo.php"; // Include ed esegue il "controllo"
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>HomePage</title>
        <style>
            body {
                margin: 0;
                padding: 0;
            }
            #content, table, .sezione {
                justify-self: center;
                border: solid;
                border-style: outset;
                box-shadow: 1px 1px black;
                background-color:gray;
                border-radius: 8px;
                padding: 0.25cm;
            }

            .sezione {
                background: rgba(0, 0, 0, 0.6);
                backdrop-filter: blur(10px);
                display:flex;
                justify-content: center;
                flex-direction: row;
                gap: 20px;
            }

            .btn {
                padding: 10px 25px;
                border-radius: 50px;
                border: 1px solid #3498db;
                background: transparent;
                color: white;
                text-decoration: none;
                transition: 0.3s;
                font-size: 0.9rem;
                text-transform: uppercase;
            }

            .btn:hover {
               background: #3498db;
                box-shadow: 0 0 15px rgba(52, 152, 219, 0.5);
            }
            .backgroundImage {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                min-height: 100vh;
                background-image: url('image.jpg');
                background-repeat: no-repeat;
                background-size: cover;
                z-index: -1;
                filter: blur(2.5px);
            }
            h1 {
                color: #ffffff;
                font-size: 4rem;
                font-weight: 900;
                text-transform: uppercase;
                letter-spacing: 4px;
                margin-bottom: 0;
                text-shadow: 2px 4px 10px rgba(0,0,0,1); /* Essenziale per staccare dallo sfondo */
            }
            p {
                color: rgba(255, 255, 255, 0.7);
                font-size: 1.2rem;
                font-weight: 300;
                margin-top: 5px;
            }
            .header-container {
                padding: 60px 20px;
                text-align: center;
                color: white;
            }
        </style>
    </head>
    <body>
        <!-- Homepage -->
        <div class="backgroundImage"></div>
        <div class="header-container">
            <h1>HOMEPAGE</h1>
            <p>Qui trovi tutte le sezioni della piattaforma.</p>
        </div>
        <div class="sezione">
            <form action="homepage.php" method="post" class="homepage">
                <button name="logout" class="btn">Logout</button>
            </form>
            <form action="event_add.php" method="post" id="inserisci">
                <button class="btn">Inserisci eventi</button>
            </form>
            <form action="event_list.php" method="post" id="cancella">
                <button class="btn">Modifica/cancella eventi</button>
            </form>
            <form action="homepage.php" method="post" id="consulta">
                <input type="hidden" name="id" value="consulta">
                <button class="btn">Consulta</button>
            </form>
        </div>
        <?php
            if(isset($_POST['id'])) {
                if($_POST['id'] == 'consulta') {
                    echo '
                        <html>
                        <head> 
                                <style>
                .consulta {
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    gap: 20px;
                    background: rgba(0, 0, 0, 0.4);
                    backdrop-filter: blur(10px);
                    padding: 30px;
                    flex-wrap:wrap;
                    border-radius: 15px;
                }

                button {
                    background: #3498db;
                    border: none;
                    color: white;
                    padding: 8px 15px;
                    border-radius: 5px;
                    cursor: pointer;
                    font-size: 0.8rem;
                }
                .card {
                    background: rgba(255, 255, 255, 0.05);
                    border: 1px solid rgba(255, 255, 255, 0.1);
                    padding: 20px;
                    border-radius: 12px;
                    transition: transform 0.3s ease;
                }

                .card:hover {
                    transform: translateY(-10px); /* La card si alza quando ci passi sopra */
                    background: rgba(255, 255, 255, 0.1);
                }

                .card h3 {
                    color: #3498db; /* Un bell\'azzurro o il tuo verde */
                    font-size: 1rem;
                    margin-bottom: 15px;
                }
                </style>  
                        </head><body>
                        <br>
                        <div class="consulta">
                            <div class="card">
                                <h3>Informazioni strade</h3>
                                <form action="query_list.php" method="post">
                                    <button name="query" value="strada">Visualizza</button>
                                </form>
                            </div>
                            <div class="card">
                                <h3>Informazioni pattuglie</h3>
                                <form action="query_list.php" method="post">
                                    <button name="query" value="pattuglia">Visualizza</button>
                                </form>
                            </div>
                            <div class="card">
                                <h3>Informazioni eventi</h3>
                                <form action="query_list.php" method="post">
                                    <button name="query" value="evento">Visualizza</button>
                                </form>
                            </div>
                            <div class="card">
                                <h3>Informazioni agenti</h3>
                                <form action="query_list.php" method="post">
                                    <button name="query" value="agente">Visualizza</button>
                                </form>
                            </div>
                            <div class="card">
                                <h3>Informazioni caserme</h3>
                                <form action="query_list.php" method="post">
                                    <button name="query" value="caserma">Visualizza</button>
                                </form>
                            </div>
                        </div>
                    </body></html>';
                }
            }
        ?>  
    </body>
</html>
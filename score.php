<?php

require_once('./lib/library_app.php');
require_once('./lib/library_general.php');


// Bufferisation des sorties
ob_start();

// Démarrage ou reprise de la session
session_start();

echo '<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>SIDASH - scores</title>
        <link rel="icon" type="image/jpeg" href="favicon.jpeg">
        <link rel="stylesheet" href="style/style.css">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Noto">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" integrity="sha512-MV7K8+y+gLIBoVD59lQIYicR65iaqukzvf/nwasF0nqhPay5w/9lJmVM2hMDcnK1OnMGCdVK+iQrJ7lzPJQd1w==" crossorigin="anonymous" referrerpolicy="no-referrer">
    </head>

    <body>
        <img class="bg" src="./imgs/background.png" alt="Image de background du jeu">

        <div class="info">
            <a href="pop-up.php"><i class="fa-solid fa-circle-info"></i></a>
        </div>

        <div id="container"> 
            <img src="./imgs/baniere.png" width="500" height="250" alt="SIDASH">
            <h2 id="slogan" >Il suffit d\'une fois ... </h2>',
            '<table>
                <tr>
                    <th>Classement</th>
                    <th>Pseudo</th>
                    <th>Score</th>
                    <th>Date</th>
                </tr>';

            // Affichage du score
            $bd = bdConnect();
            $sql = "SELECT * FROM games ORDER BY score DESC LIMIT 10";
            $res = bdSendRequest($bd, $sql);

            $i = 1;
            while($row = mysqli_fetch_assoc($res)){
                $sql = "SELECT pseudo FROM users WHERE ID = " . $row['player'];
                $res2 = bdSendRequest($bd, $sql);
                $pseudo = mysqli_fetch_assoc($res2)['pseudo'];

                echo '<tr>
                        <td>', $i, '</td>
                        <td>', $pseudo, '</td>
                        <td>', $row['score'], '</td>
                        <td>', $row['datum'], '</td>
                    </tr>';

                $i++;
            }

            
            mysqli_close($bd);

            echo '</table>
            <br>
            <a id="score" href="./index.php">Retour à l\'accueil &#127968;</a>
        </div>
    </body>
</html>';
    
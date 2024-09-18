<?php

require_once('./lib/library_app.php');
require_once('./lib/library_general.php');


// Bufferisation des sorties
ob_start();

// Démarrage ou reprise de la session
session_start();

if(isset($_POST['btn-jouer'])){
    if(!parametresControle('post', ['btn-jouer', 'pseudo'])) {
        sessionExit();
    }

    $_SESSION['pseudo'] = trim($_POST['pseudo']);
    $pseudo = $_SESSION['pseudo'];

    $pseudo = htmlspecialchars($pseudo, ENT_QUOTES, 'UTF-8');
    
    $bd = bdConnect();

    $sql = "SELECT * FROM users WHERE pseudo = '$pseudo'";

    $res = bdSendRequest($bd, $sql);

    if(mysqli_num_rows($res) == 0){
        $sql = "INSERT INTO users (pseudo)
                VALUES ('$pseudo')";

        bdSendRequest($bd, $sql);
    }
    
    mysqli_close($bd);
    header('Location: game.php');
    exit();

} else {
    
    if(isset($_SESSION['pseudo']) && isset($_GET['score'])){
        $score = $_GET['score'];
        $pseudo = $_SESSION['pseudo'];

        $pseudo = htmlspecialchars($pseudo, ENT_QUOTES, 'UTF-8');

        $datum = date('Y-m-d H:i:s');
        
        $bd = bdConnect();

        $sql = "SELECT * FROM users WHERE pseudo = '$pseudo'";

        $res = bdSendRequest($bd, $sql);

        $ID = mysqli_fetch_assoc($res)['ID'];

        $sql = "INSERT INTO games (player, score, datum)
                VALUES ('$ID', '$score', '$datum')";

        bdSendRequest($bd, $sql);
        
        mysqli_close($bd);

        header('Location: index.php');
        exit();
    }

    echo '<!DOCTYPE html>
    <html lang="fr">
        <head>
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>SIDASH</title>
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
                <h2 id="slogan" >Il suffit d\'une fois ... </h2>

                <form action="index.php" method="post">
                    <input type="text" name="pseudo" value="', $_SESSION['pseudo'] ?? "", '" placeholder="Votre pseudo"/>
                    <br>
                    <input type="submit" name="btn-jouer" value="JOUER">
                </form>

                <a id="score" href="./score.php">Meilleurs scores &#127942;</a>
            </div>
            
        </body>
    </html>';
}
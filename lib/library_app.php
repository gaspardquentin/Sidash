<?php
/*********************************************************
 *        Bibliothèque de fonctions spécifiques          *
 *         à l'application "acuinet.fr"                  *
 *********************************************************/

// Force l'affichage des erreurs
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting( E_ALL );

// Phase de développement (IS_DEV = true) ou de production (IS_DEV = false)
define ('IS_DEV', false);

/** Constantes : les paramètres de connexion au serveur MariaDB */
define ('BD_NAME', 'sidash');
define ('BD_USER', 'acuinezadmin');
define ('BD_PASS', 'La2Guerre0De2La4Richesse');
define ('BD_SERVER', 'localhost'); // 'localhost' ou 'acuinezadmin.mysql.db'

// Définit le fuseau horaire par défaut à utiliser. Disponible depuis PHP 5.1
date_default_timezone_set('Europe/Paris');


// Clé de chiffrement pour les urls (pour l'algorithme AES-128 en mode CBC)
define('CLE_CHIFFREMENT', 'z31uU2g22y/XhFpfuilzEw==');

//_______________________________________________________________
/**
 * Affichage du début de la page HTML (head + menu + header).
 *
 * @param  string  $titre       le titre de la page (<head> et <h1>)
 * @param  string  $prefixe     le préfixe du chemin relatif vers la racine du site
 *
 * @return void
 */
function affEntete(string $titre, string $prefixe = '..') : void {

    echo
'<!--
    _____ _  _ ___ __  __ ____________
   / ____/ / / /  _/ | / / ____/_  __/
  / /   / / / // //  |/ / __/   / /   
 / /___/ /_/ // // /|  / /___  / /    
 \____/\____/___/_/ |_/_____/ /_/     
 -->
 ',
        '<!doctype html>',
        '<html lang="fr">',
            '<head>',
                '<meta charset="UTF-8">',
                '<title>Rire 2 Délire | ', $titre, '</title>',
                '<link rel="stylesheet" type="text/css" href="', $prefixe,'/style.css">',
            '</head>',
            '<body>';

    // affMenu($prefixe);

    // echo        '<header>',
    //                 '<img src="', $prefixe, '/assets/pictures/titre.png" alt=" Image du titre | Rire 2 Délire" width="780" height="83">',
    //                 '<h1>', $titre, '</h1>',
    //             '</header>';
}

//_______________________________________________________________
/**
 * Affichage du menu de navigation.
 *
 * @param  string  $prefixe     le préfixe du chemin relatif vers la racine du site
 *
 * @return void
 */
function affMenu(string $prefixe = '..') : void {

    echo    '<nav><ul>',
                '<li><a href="', $prefixe, '/index.php">Accueil</a></li>',
                '<li><a href="', $prefixe, '/php/actus.php">Toute l\'actu</a></li>',
                '<li><a href="', $prefixe, '/php/recherche.php">Recherche</a></li>';
    if (estAuthentifie()){
        echo    '<li><a href="#">', htmlProtegerSorties($_SESSION['pseudo']),'</a>',
                    '<ul>',
                        '<li><a href="', $prefixe, '/php/compte.php">Mon profil</a></li>',
                        $_SESSION['redacteur'] ? "<li><a href='$prefixe/php/nouveau.php'>Nouvel article</a></li>" : '',
                        '<li><a href="', $prefixe, '/php/deconnexion.php">Se déconnecter</a></li>',
                    '</ul>',
                '</li>';
    }
    else {
        echo    '<li><a href="', $prefixe, '/php/connexion.php">Se connecter</a></li>';
    }
    echo    '</ul></nav>';
}

//_______________________________________________________________
/**
 * Affichage du pied de page.
 *
 * @return  void
 */
function affPiedDePage() : void {

    echo        '<footer>&copy; Rire 2 Délire - Juin 2024 - Tous droits réservés</footer>',
            '</body></html>';
}

//_______________________________________________________________
/**
* Détermine si l'utilisateur est authentifié
*
* @return bool     true si l'utilisateur est authentifié, false sinon
*/
function estAuthentifie(): bool {
    return  isset($_SESSION['pseudo']);
}


//_______________________________________________________________
/**
 * Termine une session et effectue une redirection vers la page transmise en paramètre
 *
 * Cette fonction est appelée quand l'utilisateur se déconnecte "normalement" et quand une
 * tentative de piratage est détectée. On pourrait améliorer l'application en différenciant ces
 * 2 situations. Et en cas de tentative de piratage, on pourrait faire des traitements pour
 * stocker par exemple l'adresse IP, etc.
 *
 * @param string    $page URL de la page vers laquelle l'utilisateur est redirigé
 *
 * @return void
 */
function sessionExit(string $page = '../index.php'): void {

    // suppression de toutes les variables de session
    $_SESSION = array();

    if (ini_get("session.use_cookies")) {
        // suppression du cookie de session
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 86400,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    session_destroy();

    header("Location: $page");
    exit();
}


//_______________________________________________________________
/**
 * Affiche la pagination de la page
 *
 * @param  string   $titre     Le titre de l'article.
 * @param  int      $id        L'id de l'article.
 * @param  string   $resume    Le résumé de l'article.
 *
 * @return void
 */
function affUnArticle(string $titre, int $id, string $resume): void {
    $titre = htmlProtegerSorties($titre);
    $resume = htmlProtegerSorties($resume);

    // Chiffrement de l'id pour le passage dans l'URL
    $id_chiffre = chiffrerSignerURL($id);

    echo '<article class="resume">',
    '<img src="../upload/', $id, '.jpg" alt="Photo d\'illustration | ', $titre, '" onerror="this.onerror=null; this.src=\'../images/none.jpg\';">',
    '<h3>', $titre, '</h3>',
    '<p>', $resume, '</p>',
    '<footer><a href="../php/article.php?id=', $id_chiffre, '">Lire l\'article</a></footer>',
    '</article>';
}



//_______________________________________________________________
/**
 * Parcours les articles à afficher sur la page actuelle et les affiches par mois de création.
 *
 * @param  array   $articles     Les articles à parcourir.
 *
 * @return void
 */
function ParcoursEtAffArticlesParMois(array $articles): void {
    foreach ($articles as $mois => $articlesDuMois) {
        echo '<section>',
        '<h2>', $mois, '</h2>';
        
        // Parcourir les articles du mois
        foreach ($articlesDuMois as $article) {
            affUnArticle($article['arTitre'], $article['arID'], $article['arResume']);
        }
        echo '</section>';
    }
}

//_______________________________________________________________
/**
 * Affichage d'un message d'erreur dans une zone dédiée de la page.
 *
 * @param  string  $msg    le message d'erreur à afficher.
 *
 * @return void
 */
function affErreur(string $message) : void {
    echo
        '<main>',
            '<section>',
                '<h2>Oups, il y a eu une erreur...</h2>',
                '<p>La page que vous avez demandée a terminé son exécution avec le message d\'erreur suivant :</p>',
                '<blockquote>', $message, '</blockquote>',
            '</section>',
        '</main>';
        affPiedDePage();
}
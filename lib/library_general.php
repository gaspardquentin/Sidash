<?php

/*********************************************************
 *        Bibliothèque de fonctions génériques
 *
 * Les régles de nommage sont les suivantes.
 * Les noms des fonctions respectent la notation camel case.
 *
 * Ils commencent en général par un terme définisant le "domaine" de la fonction :
 *  aff   la fonction affiche du code html / texte destiné au navigateur
 *  html  la fonction renvoie du code html / texte
 *  bd    la fonction gère la base de données
 *
 * Les fonctions qui ne sont utilisés que dans un seul script
 * sont définies dans le script et les noms de ces fonctions se
 * sont suffixées avec la lettre 'L'.
 *
 *********************************************************/

//____________________________________________________________________________
/**
 * Arrêt du script si erreur de base de données
 *
 * Affichage d'un message d'erreur, puis arrêt du script
 * Fonction appelée quand une erreur 'base de données' se produit :
 *      - lors de la phase de connexion au serveur MySQL ou MariaDB
 *      - ou lorsque l'envoi d'une requête échoue
 *
 * @param array    $err    Informations utiles pour le débogage
 *
 * @return void
 */
function bdErreurExit(array $err):void {
    ob_end_clean(); // Suppression de tout ce qui a pu être déja généré

    echo    '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8">',
            '<title>Erreur',
            IS_DEV ? ' base de données': '', '</title>',
            '</head><body>';
    if (IS_DEV){
        // Affichage de toutes les infos contenues dans $err
        echo    '<h4>', $err['titre'], '</h4>',
                '<pre>',
                    '<strong>Erreur mysqli</strong> : ',  $err['code'], "\n",
                    $err['message'], "\n";
        if (isset($err['autres'])){
            echo "\n";
            foreach($err['autres'] as $cle => $valeur){
                echo    '<strong>', $cle, '</strong> :', "\n", $valeur, "\n";
            }
        }
        echo    "\n",'<strong>Pile des appels de fonctions :</strong>', "\n", $err['appels'],
                '</pre>';
    }
    else {
        echo 'Une erreur s\'est produite';
    }

    echo    '</body></html>';

    if (! IS_DEV){
        // Mémorisation des erreurs dans un fichier de log
        $fichier = @fopen('error.log', 'a');
        if($fichier){
            fwrite($fichier, '['.date('d/m/Y').' '.date('H:i:s')."]\n");
            fwrite($fichier, $err['titre']."\n");
            fwrite($fichier, "Erreur mysqli : {$err['code']}\n");
            fwrite($fichier, "{$err['message']}\n");
            if (isset($err['autres'])){
                foreach($err['autres'] as $cle => $valeur){
                    fwrite($fichier,"{$cle} :\n{$valeur}\n");
                }
            }
            fwrite($fichier,"Pile des appels de fonctions :\n");
            fwrite($fichier, "{$err['appels']}\n\n");
            fclose($fichier);
        }
    }
    exit(1);        // ==> ARRET DU SCRIPT
}

//____________________________________________________________________________
/**
 * Ouverture de la connexion à la base de données en gérant les erreurs.
 *
 * En cas d'erreur de connexion, une page "propre" avec un message d'erreur
 * adéquat est affiché ET le script est arrêté.
 *
 * @return mysqli  objet connecteur à la base de données
 */
function bdConnect(): mysqli {
    // pour forcer la levée de l'exception mysqli_sql_exception
    // si la connexion échoue
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    try{
        $conn = mysqli_connect(BD_SERVER, BD_USER, BD_PASS, BD_NAME);
    }
    catch(mysqli_sql_exception $e){
        $err['titre'] = 'Erreur de connexion';
        $err['code'] = $e->getCode();
        // $e->getMessage() est encodée en ISO-8859-1, il faut la convertir en UTF-8
        $err['message'] = mb_convert_encoding($e->getMessage(), 'UTF-8', 'ISO-8859-1');
        $err['appels'] = $e->getTraceAsString(); //Pile d'appels
        $err['autres'] = array('Paramètres' =>   'BD_SERVER : '. BD_SERVER
                                                    ."\n".'BD_USER : '. BD_USER
                                                    ."\n".'BD_PASS : '. BD_PASS
                                                    ."\n".'BD_NAME : '. BD_NAME);
        bdErreurExit($err); // ==> ARRET DU SCRIPT
    }
    try{
        //mysqli_set_charset() définit le jeu de caractères par défaut à utiliser lors de l'envoi
        //de données depuis et vers le serveur de base de données.
        mysqli_set_charset($conn, 'utf8');
        return $conn;     // ===> Sortie connexion OK
    }
    catch(mysqli_sql_exception $e){
        $err['titre'] = 'Erreur lors de la définition du charset';
        $err['code'] = $e->getCode();
        $err['message'] = mb_convert_encoding($e->getMessage(), 'UTF-8', 'ISO-8859-1');
        $err['appels'] = $e->getTraceAsString();
        bdErreurExit($err); // ==> ARRET DU SCRIPT
    }
}

//____________________________________________________________________________
/**
 * Envoie une requête SQL au serveur de BdD en gérant les erreurs.
 *
 * En cas d'erreur, une page propre avec un message d'erreur est affichée et le
 * script est arrêté. Si l'envoi de la requête réussit, cette fonction renvoie :
 *      - un objet de type mysqli_result dans le cas d'une requête SELECT
 *      - true dans le cas d'une requête INSERT, DELETE ou UPDATE
 *
 * @param   mysqli              $bd     Objet connecteur sur la base de données
 * @param   string              $sql    Requête SQL
 *
 * @return  mysqli_result|bool          Résultat de la requête
 */
function bdSendRequest(mysqli $bd, string $sql): mysqli_result|bool {
    try{
        return mysqli_query($bd, $sql);
    }
    catch(mysqli_sql_exception $e){
        $err['titre'] = 'Erreur de requête';
        $err['code'] = $e->getCode();
        $err['message'] = $e->getMessage();
        $err['appels'] = $e->getTraceAsString();
        $err['autres'] = array('Requête' => $sql);
        bdErreurExit($err);    // ==> ARRET DU SCRIPT
    }
}

/**
 *  Protection des sorties (code HTML généré à destination du client).
 *
 *  Fonction à appeler pour toutes les chaines provenant de :
 *      - de saisies de l'utilisateur (formulaires)
 *      - de la bdD
 *  Permet de se protéger contre les attaques XSS (Cross site scripting)
 *  Convertit tous les caractères éligibles en entités HTML, notamment :
 *      - les caractères ayant une signification spéciales en HTML (<, >, ...)
 *      - les caractères accentués
 *
 *  Si on lui transmet un tableau, la fonction renvoie un tableau où toutes les chaines
 *  qu'il contient sont protégées, les autres données du tableau ne sont pas modifiées.
 *
 * @param  array|string  $content   la chaine à protéger ou un tableau contenant des chaines à protéger
 *
 * @return array|string             la chaîne protégée ou le tableau
 */
function htmlProtegerSorties(array|string $content): array|string {
    if (is_array($content)) {
        foreach ($content as &$value) {
            if (is_array($value) || is_string($value)){
                $value = htmlProtegerSorties($value);
            }
        }
        unset ($value); // à ne pas oublier (de façon générale)
        return $content;
    }
    // $content est de type string
    return htmlentities($content, ENT_QUOTES, encoding:'UTF-8');
}


//___________________________________________________________________
/**
 * Contrôle des clés présentes dans les tableaux $_GET ou $_POST - piratage ?
 *
 * Soit $x l'ensemble des clés contenues dans $_GET ou $_POST
 * L'ensemble des clés obligatoires doit être inclus dans $x.
 * De même $x doit être inclus dans l'ensemble des clés autorisées,
 * formé par l'union de l'ensemble des clés facultatives et de
 * l'ensemble des clés obligatoires. Si ces 2 conditions sont
 * vraies, la fonction renvoie true, sinon, elle renvoie false.
 * Dit autrement, la fonction renvoie false si une clé obligatoire
 * est absente ou si une clé non autorisée est présente; elle
 * renvoie true si "tout va bien"
 *
 * @param string    $tabGlobal          'post' ou 'get'
 * @param array     $clesObligatoires   tableau contenant les clés qui doivent obligatoirement être présentes
 * @param array     $clesFacultatives   tableau contenant les clés facultatives
 *
 * @return bool                         true si les paramètres sont corrects, false sinon
 */
function parametresControle(string $tabGlobal, array $clesObligatoires, array $clesFacultatives = []): bool{
    $x = strtolower($tabGlobal) == 'post' ? $_POST : $_GET;

    $x = array_keys($x);
    // $clesObligatoires doit être inclus dans $x
    if (count(array_diff($clesObligatoires, $x)) > 0){
        return false;
    }
    // $x doit être inclus dans
    // $clesObligatoires Union $clesFacultatives
    if (count(array_diff($x, array_merge($clesObligatoires, $clesFacultatives))) > 0){
        return false;
    }
    return true;
}

//___________________________________________________________________
/**
 * Teste si une valeur est une valeur entière
 *
 * @param   mixed    $x     valeur à tester
 *
 * @return  bool     true si entier, false sinon
 */
function estEntier(mixed $x):bool {
    return is_numeric($x) && ($x == (int) $x);
}

//___________________________________________________________________
/**
 * Teste si un entier est compris entre 2 autres
 *
 * Les bornes $min et $max sont incluses.
 *
 * @param   int    $x  valeur à tester
 * @param   int    $min  valeur minimale
 * @param   int    $max  valeur maximale
 *
 * @return  bool   true si $min <= $x <= $max
 */
function estEntre(int $x, int $min, int $max):bool {
    return ($x >= $min) && ($x <= $max);
}

//___________________________________________________________________
/**
 * Renvoie un tableau contenant le nom des mois (utile pour certains affichages)
 *
 * @return array    Tableau à indices numériques contenant les noms des mois
 */
function getArrayMonths() : array {
    return array('Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre');
}

//___________________________________________________________________
/**
 * Vérification des champs texte des formulaires
 * - utilisé par la page inscription.php
 *
 * @param  string        $texte     texte à vérifier
 * @param  string        $nom       chaîne à ajouter dans celle qui décrit l'erreur
 * @param  array         $erreurs   tableau dans lequel les erreurs sont ajoutées
 * @param  ?int          $long      longueur maximale du champ correspondant dans la base de données
 * @param  ?string       $expReg    expression régulière que le texte doit satisfaire
 *
 * @return  void
 */
function verifierTexte(string $texte, string $nom, array &$erreurs, ?int $long = null, ?string $expReg = null) : void{
    if (empty($texte)){
        $erreurs[] = "$nom ne doit pas être vide.";
    }
    else {
        if(strip_tags($texte) != $texte){
            $erreurs[] = "$nom ne doit pas contenir de tags HTML.";
        }
        else if ($expReg !== null && ! preg_match($expReg, $texte)){
            $erreurs[] = "$nom n'est pas valide.";
        }
        if ($long !== null && mb_strlen($texte, encoding:'UTF-8') > $long){
            $erreurs[] = "$nom ne peut pas dépasser $long caractères.";
        }
    }
}

//___________________________________________________________________
/**
 * Affiche une ligne d'un tableau permettant la saisie d'un champ input de type 'text', 'password', 'date' ou 'email'
 *
 * La ligne est constituée de 2 cellules :
 * - la 1ère cellule contient un label permettant un "contrôle étiqueté" de l'input
 * - la 2ème cellule contient l'input
 *
 * @param string    $libelle        Le label associé à l'input
 * @param array     $attributs      Un tableau associatif donnant les attributs de l'input sous la forme nom => valeur
 * @param string    $prefixId       Le préfixe utilisé pour l'id de l'input, ce qui donne un id égal à {$prefixId}{$attributs['name']}
 *
 * @return  void
 */
function affLigneInput(string $libelle, array $attributs = array(), string $prefixId = 'text'): void{
    echo    '<tr>',
                '<td><label for="', $prefixId, $attributs['name'], '">', $libelle, '</label></td>',
                '<td><input id="', $prefixId, $attributs['name'], '"';

    foreach ($attributs as $cle => $value){
        echo ' ', $cle, ($value !== null ? "='{$value}'" : '');
    }
    echo '></td></tr>';
}


//___________________________________________________________________
/**
 * Chiffre et signe une valeur pour la passer dans une URL en utilisant l'algorithme AES-128 en mode CBC.
 *
 * @param string $val La valeur à chiffrer
 * 
 * @return string La valeur chiffrée encodée URL
 */
function chiffrerSignerURL(string $val) : string {
	$ivlen = openssl_cipher_iv_length($cipher='AES-128-CBC');
	$iv = openssl_random_pseudo_bytes($ivlen);
	$x = openssl_encrypt($val, $cipher, base64_decode(CLE_CHIFFREMENT), OPENSSL_RAW_DATA, $iv);
	$x = $iv.$x;
	$x = base64_encode($x);
	return urlencode($x);
}

//___________________________________________________________________
/**
 * Déchiffre une valeur chiffrée avec la chiffrerSignerURL()
 *
 * @param string $x La valeur à déchiffrer
 * 
 * @return string|false La valeur déchiffrée ou false si erreur
 */
function dechiffrerSignerURL(string $x) : string|false {
	$x = base64_decode($x); // Décodage de la valeur encodée URL
    $ivlen = openssl_cipher_iv_length($cipher='AES-128-CBC');
    $iv = substr($x, 0, $ivlen);
    $x = substr($x, $ivlen);
    return openssl_decrypt($x, $cipher, base64_decode(CLE_CHIFFREMENT), OPENSSL_RAW_DATA, $iv);
}


//_______________________________________________________________
/**
 * Conversion d'une date format AAAAMMJJHHMM au format mois AAAA
 *
 * @param  int      $date   la date à afficher.
 *
 * @return string           la chaîne qui représente la date
 */
function dateIntToStringL(int $date): string {
    $mois = substr($date, -8, 2);
    $annee = substr($date, 0, -8);

    $months = getArrayMonths();

    return $months[$mois - 1] . ' ' . $annee;
}


//_______________________________________________________________
/**
 * Vérification pour l'upload d'une image
 *  - Vérification de la taille du fichier, si elle est supérieure à 100 Ko
 *  - Vérification de l’extension du fichier (JPG)
 *  - Vérification du contenu du fichier avec son type MIME
 *  - Vérifie si les dimensions correspondent au format 4/3
 *
 * @param   array   $erreurs    tableau associatif contenant les erreurs de saisie
 * 
 * @return  void
 */
function verifUpload(array &$erreurs): void {
    if ($_FILES['file']['error'] === 0) {

        // Vérification de la taille du fichier, si elle est supérieure à 100 Ko
        $maxSize = 100 * 1024; // 100 Ko
        $file_size = $_FILES['file']['size'];
        if ($file_size > $maxSize) {
            $erreurs[] = 'La taille de l\'image dépasse 100 Ko.';
        }

        // Vérification de l’extension du fichier (JPG)
        $oks = array('.jpg');
        $nom = $_FILES['file']['name'];
        $ext = strtolower(substr($nom, strrpos($nom, '.')));
        if (! in_array($ext, $oks)) {
            $erreurs[] = 'Le fichier n\'est pas au format JPG.';
        }

        // Vérification du contenu du fichier avec son type MIME
        $oks = array('image/jpeg');
        $type = mime_content_type($_FILES['file']['tmp_name']);
        if (! in_array($type, $oks)) {
            $erreurs[] = 'Le contenu du fichier n\'est pas autorisé.';
        }

        if (empty($erreurs)) {
            // Vérifie si les dimensions correspondent au format 4/3
            $image_info = getimagesize($_FILES['file']['tmp_name']);
            // Calculer le ratio largeur/hauteur
            $ratio = $image_info[0] / $image_info[1];
            // Définir la marge d'erreur acceptable
            $marge_erreur = 0.1;
            if (abs($ratio - 4/3) > $marge_erreur) {
                $erreurs[] = 'Les dimensions de l\'image ne correspondent pas au format 4/3.';
            }
        }
        
    } else {
        $erreurs[] = 'Erreur lors du téléchargement de l\'image, réessayer.';
    }
}

//_______________________________________________________________
/**
 * Vérification du droit d'écriture sur le répertoire $uploadDir
 * - Si le répertoire n'existe pas, on le créer
 * - Si le répertoire n'est pas accessible en écriture, on le rend accessible
 *
 * string  $uploadDir  répertoire de stockage des images
 * 
 * @return  void
 */
function verifDroitEcriture(string $uploadDir): void {
    if (!file_exists($uploadDir)) {
        // Le répertoire n'existe pas, on le créer
        mkdir($uploadDir, 0700, true);
    }
    if (!is_writable($uploadDir)) {
        chmod($uploadDir, 0700);
    }
}


//_______________________________________________________________
/**
 * Vérification de la présence, de la validité et déchiffrage d'un paramètre GET
 *
 * @param   string  $cle    clé du paramètre GET
 * @param   string  $page   nom de la page
 * 
 * @return  int     identifiant de l'article déchiffré
 */
function verifGet(string $cle, string $page): int {
    if (! parametresControle('get', ["$cle"])){
        affErreur('Il faut utiliser une URL de la forme : https://acuinet.fr/' . $page . '.php?' . $cle . '=XXX');
        exit(1); // ==> fin de la fonction
    }

    // Déchiffrement de l'URL
    $id = dechiffrerSignerURL($_GET["$cle"]);

    if (! estEntier($id)){
        affErreur('L\'identifiant doit être un entier');
        exit(1); // ==> fin de la fonction
    }

    if ($id <= 0){
        affErreur('L\'identifiant doit être un entier strictement positif');
        exit(1); // ==> fin de la fonction
    }

    return $id;
}


//_______________________________________________________________
/**
 * Redimensionnement de l'image (si besoin) et upload de celle-ci
 *
 * @param   int     $ID         identifiant de l'article
 * @param   string  $uploadDir  répertoire de stockage des images
 * 
 * @return  void
 */
function depotFile(int $ID, string $uploadDir) {
    // Obtenir les dimensions de l'image
    $image = $_FILES['file']['tmp_name'];
    $image_info = getimagesize($image);
    $width_orig = $image_info[0];
    $height_orig = $image_info[1];

    // Définition des dimensions pour l'image redimensionnée
    $new_width = 248; // Largeur
    $new_height = 186; // Hauteur

    $Dest = $uploadDir . $ID . '.jpg';

    if ($width_orig > $new_width) {
        // redimensionner l'image et la stoker

        $image = imagecreatefromjpeg($image);
        // Créer une nouvelle image redimensionnée
        $new_image = imagecreatetruecolor($new_width, $new_height);
        imagecopyresampled($new_image, $image, 0, 0, 0, 0, $new_width, $new_height, $width_orig, $height_orig);
        
        imagejpeg($new_image, $Dest);
        // Libérer la mémoire
        imagedestroy($image);
        imagedestroy($new_image);

    } else {
        // Stockage de l'image sans la redimensionner
        is_uploaded_file($image);
        move_uploaded_file($image, $Dest);
    }
}
<?php
require_once '../src/model/dbAPI.php';
require_once '../src/model/utils.php';

$profileId = null;
//se non è stato passato un id utente, reindirizza alla home
if (!isset($_GET['user'])) {
    header('Location: /');
    exit();
}

$profileId = $_GET['user'];
$db = new DBAccess();
$dbOK = $db->open_connection();

// GET PROFILE DATA BY ID
$user = $db->get_user_by_username($profileId);

if (!$user) {
    // throw new Exception("Utente non trovato");
    header('Location: /404');
    exit();
}else{
    $user = $user[0];
}

$PAGE_TITLE = $user["username"]. " - BookOverflow";

$template = file_get_contents('./html/templatePage.html');
$header = getHeaderSection();
$profilo = file_get_contents('./html/profilo.html');
$footer = file_get_contents('./html/footer.html');

$profilo = str_replace('<!-- [userNome] -->', $user['nome'], $profilo);
$profilo = str_replace('<!-- [userCognome] -->', $user['cognome'], $profilo);
$profilo = str_replace('<!-- [userAvatarPath] -->', $user['path_immagine'], $profilo);
$profilo = str_replace('<!-- [userUsername] -->', $user['username'], $profilo);

$location = $db->get_provincia_comune_by_ids($user['provincia'], $user['comune']);
$profilo = str_replace('<!-- [userLuogo] -->', $location['comune'].', '.$location['provincia'], $profilo);

//TODO: calc user rating
$userRating = $db->get_user_rating_by_email($user['email']);
$profilo = str_replace('<!-- [userRating] -->', $userRating, $profilo);
$profilo = str_replace('<!-- [userRatingStars] -->', ratingStars($userRating), $profilo);

$page = str_replace('<!-- [pageTitle] -->', $PAGE_TITLE, $template);
$page = str_replace('<!-- [header] -->', $header, $page);
$page = str_replace('<!-- [footer] -->', $footer, $page);
$page = str_replace('<!-- [content] -->', $profilo, $page);
echo $page;
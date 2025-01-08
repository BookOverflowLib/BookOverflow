<?php
require_once '../src/paths.php';
require_once $GLOBALS['MODEL_PATH'] . 'dbAPI.php';
require_once $GLOBALS['MODEL_PATH'] . 'utils.php';

//se non è stato passato un id utente, reindirizza alla home
if (!isset($_GET['user'])) {
	header('Location: /accedi');
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
} else {
	$user = $user[0];
}

$page = getTemplatePage($user["username"]);
$profilo = file_get_contents($GLOBALS['TEMPLATES_PATH'] . 'profilo.html');

$profilo = str_replace('<!-- [userNome] -->', $user['nome'], $profilo);
$profilo = str_replace('<!-- [userCognome] -->', $user['cognome'], $profilo);
$profilo = str_replace('<!-- [userAvatarPath] -->', $user['path_immagine'], $profilo);
$profilo = str_replace('<!-- [userUsername] -->', $user['username'], $profilo);

$location = $db->get_provincia_comune_by_ids($user['provincia'], $user['comune']);
$profilo = str_replace('<!-- [userLuogo] -->', $location['comune'] . ', ' . $location['provincia'], $profilo);

$userRating = $db->get_user_rating_by_email($user['email']);
if (!$userRating) {
	$userRating = "0.0";
} else if (!$userRating[0]['media_valutazioni']) {
	$userRating = "0.0";
} else {
	$userRating = $userRating[0]['media_valutazioni'];
}

$profilo = str_replace('<!-- [userRating] -->', $userRating, $profilo);
$profilo = str_replace('<!-- [userRatingStars] -->', ratingStars($userRating), $profilo);

// GENERI
$generi = $db->get_generi_by_username($user['username']);
$generiString = '';
if ($generi && $generi != null && $generi[0]['generi_preferiti'] != null && $generi[0]['generi_preferiti'] != '[]') {
	$fileGeneri = file_get_contents('../utils/bisac.json');
	$fileGeneri = json_decode($fileGeneri, true);
	$generiPreferiti = $generi[0]['generi_preferiti'];
	$generiPreferiti = json_decode($generiPreferiti, true);
	foreach ($generiPreferiti as $genereKey) {
		$genere = $fileGeneri[$genereKey];
		$generiString .= '<div class="button-genere"><span aria-hidden="true">' . $genere['emoji'] . "</span> " . $genere['name'] . '</div>';
	}
}else{
	$generiString = '<p>Non c\'è ancora nessun genere preferito!</p>';
}
$profilo = str_replace('<!-- [userGeneri] -->', $generiString, $profilo);


$page = str_replace('<!-- [content] -->', $profilo, $page);
echo $page;
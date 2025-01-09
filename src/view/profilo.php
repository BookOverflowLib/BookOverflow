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

ensure_session();

$isTuoProfilo = isset($_SESSION['user']) ? ($_GET['user'] == $_SESSION['user']) : false;

// USER DATA
$db = new DBAccess();
$dbOK = $db->open_connection();

$user = $db->get_user_by_username($profileId);

if (!$user || $user == null) {
	// throw new Exception("Utente non trovato");
	header('Location: /404');
	exit();
}
$user = $user[0];

// GENERAZIONE PAGINA
$page = getTemplatePage($user["username"]);
$profilo = file_get_contents($GLOBALS['TEMPLATES_PATH'] . 'profilo.html');

// Sostituzione dati personali
$sostituzioni = [
	'<!-- [userNome] -->' => $user['nome'],
	'<!-- [userCognome] -->' => $user['cognome'],
	'<!-- [userAvatarPath] -->' => $user['path_immagine'],
	'<!-- [userUsername] -->' => $user['username']
];

foreach ($sostituzioni as $placeholder => $value) {
	$profilo = str_replace($placeholder, $value, $profilo);
}

// Località
$location = $db->get_provincia_comune_by_ids($user['provincia'], $user['comune']);
$profilo = str_replace('<!-- [userLuogo] -->', $location['comune'] . ', ' . $location['provincia'], $profilo);

// Valutazione utente
$userRating = $db->get_user_rating_by_email($user['email']);
$ratingValue = "0.0";

if ($userRating && isset($userRating[0]['media_valutazioni'])) {
	$ratingValue = $userRating[0]['media_valutazioni'] ?: "0.0";
}

$profilo = str_replace('<!-- [userRating] -->', $ratingValue, $profilo);
$profilo = str_replace('<!-- [userRatingStars] -->', ratingStars($ratingValue), $profilo);

// Generi preferiti
$generi = $db->get_generi_by_username($user['username']);
$profilo = str_replace('<!-- [userGeneri] -->', getGeneriPreferiti($generi), $profilo);

// Se è il proprio profilo
// aggiungi pulsanti per modifica profilo e generi
if ($isTuoProfilo) {
	//logout
	$logoutButton = '<form action="/api/logout" method="POST"><input type="submit" class="button-layout" value="Esci" /></form>';
	$profilo = str_replace('<!-- [logoutButton] -->', $logoutButton, $profilo);
	//modifica generi
	$modificaGeneriButton = '<a href="/profilo/' . $user['username'] . '/seleziona-generi" class="button-layout">Modifica i generi</a>';
	$profilo = str_replace('<!-- [generiPreferitiButton] -->', $modificaGeneriButton, $profilo);
	//libri offerti
	$libriOffertiButton = '<a href="/profilo/' . $user['username'] . '/libri-offerti" class="button-layout">Modifica la lista</a>';
	$profilo = str_replace('<!-- [libriOffertiButton] -->', $libriOffertiButton, $profilo);
	
	$libriDesideratiButton = '<a href="/profilo/' . $user['username'] . '/libri-desiderati" class="button-layout">Modifica la lista</a>';
	$profilo = str_replace('<!-- [libriDesideratiButton] -->', $libriDesideratiButton, $profilo);
	
} else {
	$profilo = str_replace('<!-- [logoutButton] -->', '', $profilo);
	$profilo = str_replace('<!-- [generiPreferitiButton] -->', '', $profilo);
	
	// TODO: aggiungere aria-label cose blabla
	$libriOffertiButton = '<a href="/profilo/' . $user['username'] . '/libri-offerti" class="button-layout">Mostra tutti</a>';
	$profilo = str_replace('<!-- [libriOffertiButton] -->', $libriOffertiButton, $profilo);

	$libriDesideratiButton = '<a href="/profilo/' . $user['username'] . '/libri-desiderati" class="button-layout">Mostra tutti</a>';
	$profilo = str_replace('<!-- [libriDesideratiButton] -->', $libriDesideratiButton, $profilo);
}


// Page output
$page = str_replace('<!-- [content] -->', $profilo, $page);
echo $page;
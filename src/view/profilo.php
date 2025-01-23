<?php
require_once __DIR__ . '/' . '../paths.php';
require_once $GLOBALS['MODEL_PATH'] . 'dbAPI.php';
require_once $GLOBALS['MODEL_PATH'] . 'utils.php';

ensure_session();
$profileId = getProfileId();
$isTuoProfilo = isTuoProfilo($profileId);

$db = new DBAccess();
try {
	$db->open_connection();
} catch (Exception $e) {
	handleError("Errore durante la connessione al database");
}

$user = getUser($db, $profileId);
$page = generatePage($user, $isTuoProfilo, $db);

echo $page;

function getProfileId()
{
	if (!isset($_GET['user'])) {
		if (!isset($_SESSION['user'])) {
			$prefix = getPrefix();
			header('Location: ' . $prefix . '/accedi');
			exit();
		} else {
			$_GET['user'] = $_SESSION['user'];
		}
	}
	return $_GET['user'];
}

function handleError($message)
{
	$_SESSION['error'] = $message;
	$prefix = getPrefix();
	header('Location: ' . $prefix . '/404');
	exit();
}

function getUser($db, $profileId)
{
	$user = $db->get_user_by_identifier($profileId);
	if ($user == null) {
		handleError("Utente non trovato");
	}
	return $user[0];
}

function generatePage($user, $isTuoProfilo, $db)
{
	$page = getTemplatePage($user["username"]);
	$profilo = file_get_contents($GLOBALS['TEMPLATES_PATH'] . 'profilo.html');
	
	$profilo = replacePlaceholders($profilo, $user, $db);
	$profilo = replaceLocation($profilo, $user);
	$profilo = replaceRating($profilo, $user, $db);
	$profilo = replaceGeneri($profilo, $user, $db);
	$profilo = replaceLibri($profilo, $user, $db);
	
	if ($isTuoProfilo) {
		$profilo = addTuoProfiloButtons($profilo);
	} else {
		$profilo = addOtherProfiloButtons($profilo, $user);
	}
	
	return str_replace('<!-- [content] -->', $profilo, $page);
}

function replacePlaceholders($profilo, $user, $db)
{
	$sostituzioni = [
		'<!-- [userNome] -->' => $user['nome'],
		'<!-- [userCognome] -->' => $user['cognome'],
		'<!-- [userAvatarPath] -->' => $user['path_immagine'],
		'<!-- [userUsername] -->' => $user['username']
	];
	
	foreach ($sostituzioni as $placeholder => $value) {
		$profilo = str_replace($placeholder, $value, $profilo);
	}
	
	return $profilo;
}

function replaceLocation($profilo, $user)
{
	$location = getLocationName($user['provincia'], $user['comune']);
	return str_replace('<!-- [userLuogo] -->', $location, $profilo);
}

function replaceRating($profilo, $user, $db)
{
	$userRating = $db->get_user_rating_by_email($user['email']);
	$ratingValue = $userRating && isset($userRating[0]['media_valutazioni']) ? $userRating[0]['media_valutazioni'] : "0.0";
	
	$profilo = str_replace('<!-- [userRating] -->', $ratingValue, $profilo);
	return str_replace('<!-- [userRatingStars] -->', ratingStars($ratingValue), $profilo);
}

function replaceGeneri($profilo, $user, $db)
{
	$generi = $db->get_generi_by_username($user['username']);
	return str_replace('<!-- [userGeneri] -->', getGeneriPreferiti($generi), $profilo);
}

function replaceLibri($profilo, $user, $db)
{
	$libri_offerti_db = $db->get_libri_offerti_by_username($user['username']);
	$profilo = str_replace('<!-- [libriOffertiLista] -->', getLibriCopertinaGrande($libri_offerti_db, 4), $profilo);
	
	$libri_desiderati_db = $db->get_libri_desiderati_by_username($user['username']);
	return str_replace('<!-- [libriDesideratiLista] -->', getLibriCopertinaGrande($libri_desiderati_db, 4), $profilo);
}

function addTuoProfiloButtons($profilo)
{
	$scambiButton = '<a href="/profilo/' . $_SESSION['user'] . '/scambi" class="button-layout ">I tuoi scambi</a>';
	$profilo = str_replace('<!-- [scambiButton] -->', $scambiButton, $profilo);
	
	$logoutButton = '<form action="/api/logout" method="POST"><input type="submit" class="button-layout secondary" value="Esci" aria-label="Esci dal tuo profilo"/></form>';
	$profilo = str_replace('<!-- [logoutButton] -->', $logoutButton, $profilo);
	
	$modificaGeneriButton = '<a href="/profilo/' . $_SESSION['user'] . '/seleziona-generi" class="button-layout">Modifica i generi</a>';
	$profilo = str_replace('<!-- [generiPreferitiButton] -->', $modificaGeneriButton, $profilo);
	
	$libriOffertiButton = '<a href="/profilo/' . $_SESSION['user'] . '/libri-offerti" class="button-layout" aria-label="MOdifica la lista dei libri offerti">Modifica la lista</a>';
	$profilo = str_replace('<!-- [libriOffertiButton] -->', $libriOffertiButton, $profilo);
	
	$libriDesideratiButton = '<a href="/profilo/' . $_SESSION['user'] . '/libri-desiderati" class="button-layout" aria-label="Modifica la lista dei desideri">Modifica la lista</a>';
	return str_replace('<!-- [libriDesideratiButton] -->', $libriDesideratiButton, $profilo);
	
	
}

function addOtherProfiloButtons($profilo, $user)
{
	$contattaButton = getContattaButton($profilo, $user);
	$profilo = str_replace('<!-- [scambiButton] -->', $contattaButton, $profilo);
	
	$profilo = str_replace('<!-- [logoutButton] -->', '', $profilo);
	$profilo = str_replace('<!-- [generiPreferitiButton] -->', '', $profilo);
	
	$libriOffertiButton = '<a href="/profilo/' . $user['username'] . '/libri-offerti" class="button-layout" aria-label="Mostra tutti i libri offerti">Mostra tutti</a>';
	$profilo = str_replace('<!-- [libriOffertiButton] -->', $libriOffertiButton, $profilo);
	
	$libriDesideratiButton = '<a href="/profilo/' . $user['username'] . '/libri-desiderati" class="button-layout" aria-label="Mostra tutta la lista dei desideri">Mostra tutti</a>';
	return str_replace('<!-- [libriDesideratiButton] -->', $libriDesideratiButton, $profilo);
}

function getContattaButton($profilo, $user)
{
	$oggettoMail = '[BookOverflow] Scambio libri';
	$formatMailHref = 'mailto:' . $user['email'] . '?subject=' . $oggettoMail;
	return '<a href="' . $formatMailHref . '" class="button-layout secondary external-link">Contatta via mail</a>';
}
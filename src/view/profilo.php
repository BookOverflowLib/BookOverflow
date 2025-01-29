<?php
require_once __DIR__ . '/' . '../paths.php';
require_once $GLOBALS['MODEL_PATH'] . 'dbAPI.php';
require_once $GLOBALS['MODEL_PATH'] . 'utils.php';

ensure_session();
$profileId = getProfileId();
if ($profileId === 'admin' && $_SESSION['user'] !== 'admin') {
	header('Location: ' . getPrefix() . '/404');
}

$isTuoProfilo = isTuoProfilo($profileId);

$db = new DBAccess();
try {
	$db->open_connection();
} catch (Exception $e) {
	handleError("Errore durante la connessione al database");
}

$user = getUser($db, $profileId);

if (isset($_SESSION['user']) && $_SESSION['user'] === 'admin' && $profileId === 'admin') {
	$page = generatePageAdmin($user);
} else {
	$page = generatePage($user, $isTuoProfilo, $db);
	$page = getBannerNuovoProfilo($isTuoProfilo, $page);
	$page = iniziaEsplorare($isTuoProfilo, $page);
	$page = addErrorsToPage($page);
	$page = dialogSure($page, "il tuo account", "Perderai le tue liste, i tuoi scambi e tutti i dati del tuo account");
}
$page = populateWebdirPrefixPlaceholders($page);

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

	$profilo = replacePlaceholders($profilo, $user);
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

function replacePlaceholders($profilo, $user)
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

	$ratingValue = "0.0";
	if ($userRating && isset($userRating[0]['media_valutazioni'])) {
		$ratingValue = $userRating[0]['media_valutazioni'];
		$ratingValue = number_format($ratingValue, 1);
	}

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
	$prefix = getPrefix();
	$scambiButton = <<<HTML
	<a href="{$prefix}/profilo/{$_SESSION['user']}/scambi" class="button-layout">I tuoi scambi <span aria-hidden="true"><img src="{$prefix}/assets/imgs/scambio-arrows.svg" alt=""/></span></a>
	HTML;

	$profilo = str_replace('<!-- [scambiButton] -->', $scambiButton, $profilo);

	$prefix = getPrefix();
	$recensioniButton = '<a href="' . $prefix . '/profilo/' . $_SESSION['user'] . '/recensioni" class="button-layout">Recensioni ricevute <span aria-hidden="true"><img src="'.$prefix.'/assets/imgs/message-box.svg" alt=""/></span></a>';
	$profilo = str_replace('<!-- [recensioniButton] -->', $recensioniButton, $profilo);

	$logoutButton = '<form action="' . $prefix . '/api/logout" method="POST"><button type="submit" class="button-layout secondary logout" aria-label="Esci dal tuo profilo">Esci</button></form>';
	$profilo = str_replace('<!-- [logoutButton] -->', $logoutButton, $profilo);

	$eliminaUtenteButton = '<button type="button" class="button-layout destructive elimina-utente" data-username="' . $_SESSION['user'] . '"/>Elimina account <span aria-hidden="true"><img src="'.$prefix.'/assets/imgs/trash.svg" alt=""/></span></button>';
	$profilo = str_replace('<!-- [eliminaUtenteButton] -->', $eliminaUtenteButton, $profilo);

	$modificaGeneriButton = '<a href="' . $prefix . '/profilo/' . $_SESSION['user'] . '/seleziona-generi" class="button-layout">Modifica i generi</a>';
	$profilo = str_replace('<!-- [generiPreferitiButton] -->', $modificaGeneriButton, $profilo);

	$libriOffertiButton = '<a href="' . $prefix . '/profilo/' . $_SESSION['user'] . '/libri-offerti" class="button-layout" aria-label="Modifica la lista dei libri offerti">Modifica la lista</a>';
	$profilo = str_replace('<!-- [libriOffertiButton] -->', $libriOffertiButton, $profilo);

	$libriDesideratiButton = '<a href="' . $prefix . '/profilo/' . $_SESSION['user'] . '/libri-desiderati" class="button-layout" aria-label="Modifica la lista dei desideri">Modifica la lista</a>';
	return str_replace('<!-- [libriDesideratiButton] -->', $libriDesideratiButton, $profilo);
}

function addOtherProfiloButtons($profilo, $user)
{
	$prefix = getPrefix();
	$contattaButton = getContattaButton($profilo, $user);
	$profilo = str_replace('<!-- [scambiButton] -->', $contattaButton, $profilo);

	$profilo = str_replace('<!-- [logoutButton] -->', '', $profilo);
	$profilo = str_replace('<!-- [generiPreferitiButton] -->', '', $profilo);

	$libriOffertiButton = '<a href="' . $prefix . '/profilo/' . $user['username'] . '/libri-offerti" class="button-layout" aria-label="Mostra tutti i libri offerti">Mostra tutti</a>';
	$profilo = str_replace('<!-- [libriOffertiButton] -->', $libriOffertiButton, $profilo);

	$libriDesideratiButton = '<a href="' . $prefix . '/profilo/' . $user['username'] . '/libri-desiderati" class="button-layout" aria-label="Mostra tutta la lista dei desideri">Mostra tutti</a>';
	return str_replace('<!-- [libriDesideratiButton] -->', $libriDesideratiButton, $profilo);
}

function getContattaButton($profilo, $user)
{
	$oggettoMail = '[BookOverflow] Scambio libri';
	$formatMailHref = 'mailto:' . $user['email'] . '?subject=' . $oggettoMail;
	return '<a href="' . $formatMailHref . '" class="button-layout secondary external-link">Contatta via mail</a>';
}

function getBannerNuovoProfilo($isTuoProfilo, $page)
{
	$db = new DBAccess();
	if (!$isTuoProfilo) {
		return str_replace('<!-- [bannerCompletaProfilo] -->', '', $page);
	}
	$hasGeneriPreferiti = $db->check_user_has_generi_preferiti($_SESSION['user']);
	$hasLibriOfferti = $db->check_user_has_libri_offerti($_SESSION['user']);
	$hasLibriDesiderati = $db->check_user_has_libri_desiderati($_SESSION['user']);

	if ($hasGeneriPreferiti && $hasLibriOfferti && $hasLibriDesiderati) {
		return str_replace('<!-- [bannerCompletaProfilo] -->', '', $page);
	}

	$list = $hasGeneriPreferiti ? '' : '<li>i tuoi generi preferiti</li>';
	$list .= $hasLibriOfferti ? '' : '<li>i libri che offri</li>';
	$list .= $hasLibriDesiderati ? '' : '<li>i libri che desideri</li>';

	$icon = <<<HTML
	<svg
		xmlns="http://www.w3.org/2000/svg"
		width="24"
		height="24"
		viewBox="0 0 24 24"
		fill="none"
		stroke="currentColor"
		stroke-width="2"
		stroke-linecap="round"
		stroke-linejoin="round"
		class="lucide lucide-notebook-pen">
		<path
			d="M13.4 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-7.4" />
		<path d="M2 6h4" />
		<path d="M2 10h4" />
		<path d="M2 14h4" />
		<path d="M2 18h4" />
		<path
			d="M21.378 5.626a1 1 0 1 0-3.004-3.004l-5.01 5.012a2 2 0 0 0-.506.854l-.837 2.87a.5.5 0 0 0 .62.62l2.87-.837a2 2 0 0 0 .854-.506z" />
	</svg>
	HTML;
	$banner = <<<HTML
	<div class="sezione-stretta" id="completa-profilo">
		<div class="message-box">
			{$icon}
			<div>
				<h2>Completa il tuo profilo</h2>
				<p class="center">Aggiungi subito:</p>
				<ul>
					{$list}
				</ul>
			</div>
		</div>
	</div>
	HTML;

	return str_replace('<!-- [bannerCompletaProfilo] -->', $banner, $page);
}

function iniziaEsplorare($isTuoProfilo, $page)
{
	if (!$isTuoProfilo) {
		return str_replace('<!-- [iniziaEsplorare] -->', '', $page);
	}
	$prefix = getPrefix();
	$sec = <<<HTML
	<section class="sezione-stretta">
		<div id="inizia-esplorare">
			<p class="">Inizia subito ad esplorare!</p>
			<a class="button-layout" href="{$prefix}/esplora">Esplora</a>
		</div>
	</section>
	HTML;
	return str_replace('<!-- [iniziaEsplorare] -->', $sec, $page);
}

function redirect(string $error = null): never
{
	if ($error) {
		$_SESSION['error'] = $error;
		header('Location: ' . $GLOBALS['prefix'] . '/profilo/' . $_SESSION['user'] . '/seleziona-generi');
	} else {
		header('Location: ' . $GLOBALS['prefix'] . '/profilo/' . $_SESSION['user'] . '#generi');
	}
	exit();
}

function generatePageAdmin($user)
{
	$page = getTemplatePage($_SESSION['user']);
	$profilo = file_get_contents($GLOBALS['TEMPLATES_PATH'] . 'profilo-admin.html');

	$prefix = getPrefix();
	return str_replace('<!-- [content] -->', $profilo, $page);

}
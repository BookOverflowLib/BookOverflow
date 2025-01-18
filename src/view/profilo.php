<?php
require_once '../src/paths.php';
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
			header('Location: /accedi');
			exit();
		} else {
			$_GET['user'] = $_SESSION['user'];
		}
	}
	return $_GET['user'];
}

function isTuoProfilo($profileId)
{
	return isset($_SESSION['user']) && $profileId == $_SESSION['user'];
}

function handleError($message)
{
	$_SESSION['error'] = $message;
	header('Location: /404');
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
		$profilo = addScambiSection($profilo, $db);
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
	$logoutButton = '<form action="/api/logout" method="POST"><input type="submit" class="button-layout" value="Esci" /></form>';
	$profilo = str_replace('<!-- [logoutButton] -->', $logoutButton, $profilo);

	$modificaGeneriButton = '<a href="/profilo/' . $_SESSION['user'] . '/seleziona-generi" class="button-layout">Modifica i generi</a>';
	$profilo = str_replace('<!-- [generiPreferitiButton] -->', $modificaGeneriButton, $profilo);

	$libriOffertiButton = '<a href="/profilo/' . $_SESSION['user'] . '/libri-offerti" class="button-layout">Modifica la lista</a>';
	$profilo = str_replace('<!-- [libriOffertiButton] -->', $libriOffertiButton, $profilo);

	$libriDesideratiButton = '<a href="/profilo/' . $_SESSION['user'] . '/libri-desiderati" class="button-layout">Modifica la lista</a>';
	$profilo = str_replace('<!-- [libriDesideratiButton] -->', $libriDesideratiButton, $profilo);

	$sezioneScambi = <<<HTML
    <section id="storico-scambi">
        <div class="intestazione">
            <h2>I miei scambi</h2>
        </div>
        <div class="storico-table">
            <!-- [storicoScambi] --> 
        </div>
    </section>
    HTML;
	return str_replace('<!-- [sezioneScambi] -->', $sezioneScambi, $profilo);
}

function addScambiSection($profilo, $db)
{
	$storicoScambi = $db->get_scambi_by_user($_SESSION['user']);
	$storicoScambiHTML = "";

	foreach ($storicoScambi as $scambio) {
		$storicoScambiHTML .= generateScambioRow($scambio, $db);
	}

	return str_replace('<!-- [storicoScambi] -->', $storicoScambiHTML, $profilo);
}

function generateScambioRow($scambio, $db)
{
	$libroProp = $db->get_copia_by_id($scambio['idCopiaProp'])[0];
	$libroAcc = $db->get_copia_by_id($scambio['idCopiaAcc'])[0];
	$utenteAccettatore = $db->get_user_by_identifier($scambio['emailAccettatore'])[0];
	$utenteProponente = $db->get_user_by_identifier($scambio['emailProponente'])[0];

	$isScambioRicevuto = $utenteAccettatore['username'] === $_SESSION['user'];

	$scambio_buttons = generateScambioButtons($scambio, $isScambioRicevuto);
	$scambio_utente = generateScambioUtente($isScambioRicevuto, $utenteAccettatore, $utenteProponente);
	$user_libro = $isScambioRicevuto ? $libroAcc : $libroProp;
	$other_libro = $isScambioRicevuto ? $libroProp : $libroAcc;

	return <<<HTML
    <div class="storico-row">   
    	<div class="storico-books">
        <div class="storico-dai">
            <p>Dai:</p>
            <div>
                <img src="{$user_libro['path_copertina']}" alt="" width="50">
                <div>
                    <p class="bold">{$user_libro['titolo']}</p>
                    <p class="italic">{$user_libro['autore']}</p>
                </div>
            </div>
        </div>
        <img src="/assets/imgs/scambio-arrows.svg" alt="" id="scambio-arrows">
        <div class="storico-ricevi">
            <p>Ricevi:</p>
            <div>
                <img src="{$other_libro['path_copertina']}" alt="" width="50">
                <div>
                    <p class="bold">{$other_libro['titolo']}</p>
                    <p class="italic">{$other_libro['autore']}</p>
                </div>
            </div>
        </div>
        </div> 

        {$scambio_utente}
        {$scambio_buttons}
    </div>
    HTML;
}

function generateScambioButtons($scambio, $isScambioRicevuto)
{
	if ($isScambioRicevuto) {
		if ($scambio['stato'] === 'in attesa') {
			return <<<HTML
			<div class="storico-buttons accetta">
				<form action="/api/accetta-scambio" method="POST">
					<input type="hidden" name="id_scambio" value="{$scambio['ID']}" />
					<input type="submit" class="button-layout" value="Accetta" />
				</form>
				<form action="/api/rifiuta-scambio" method="POST">
					<input type="hidden" name="id_scambio" value="{$scambio['ID']}" />
					<input type="submit" class="button-layout secondary" value="Rifiuta" />
				</form>
			</div>
			HTML;
		}
	} else {
		if ($scambio['stato'] === 'in attesa') {
			return <<<HTML
            <div class="storico-buttons">
                <p>In attesa di risposta</p>
                <form action="/api/rimuovi-scambio" method="POST">
                    <input type="hidden" name="id_scambio" value="{$scambio['ID']}" />
                    <input type="submit" class="button-layout secondary" value="Annulla scambio" />
                </form>
            </div>
            HTML;
		}
	}
	if ($scambio['stato'] === 'rifiutato') {
		return <<<HTML
            <div class="storico-buttons">
                <p>Scambio rifiutato</p>
            </div>
            HTML;
	} elseif ($scambio['stato'] === 'accettato') {
		return <<<HTML
            <div class="storico-buttons">
                <p>Scambio accettato</p>
            </div>
            HTML;
	}
	return '';
}

function generateScambioUtente($isScambioRicevuto, $utenteAccettatore, $utenteProponente)
{
	$utente = $isScambioRicevuto ? $utenteProponente : $utenteAccettatore;
	return <<<HTML
    <div class="storico-utente-scambio">
        <p>Scambio con:</p>
        <div>
            <img src="{$utente['path_immagine']}" alt="" width="50">
            <div>
                <p>{$utente['nome']} {$utente['cognome']}</p>
                <p class="italic">@{$utente['username']}</p>
            </div>
        </div>
    </div>
    HTML;
}

function addOtherProfiloButtons($profilo, $user)
{
	$profilo = str_replace('<!-- [logoutButton] -->', '', $profilo);
	$profilo = str_replace('<!-- [generiPreferitiButton] -->', '', $profilo);

	$libriOffertiButton = '<a href="/profilo/' . $user['username'] . '/libri-offerti" class="button-layout">Mostra tutti</a>';
	$profilo = str_replace('<!-- [libriOffertiButton] -->', $libriOffertiButton, $profilo);

	$libriDesideratiButton = '<a href="/profilo/' . $user['username'] . '/libri-desiderati" class="button-layout">Mostra tutti</a>';
	return str_replace('<!-- [libriDesideratiButton] -->', $libriDesideratiButton, $profilo);
}
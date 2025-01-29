<?php
require_once __DIR__ . '/' . '../paths.php';
require_once $GLOBALS['MODEL_PATH'] . 'dbAPI.php';
require_once $GLOBALS['MODEL_PATH'] . 'utils.php';

ensure_session();
if (!is_admin()) {
    header('Location: ' . getPrefix() . '/profilo');
    exit;
}

$db = new DBAccess();


$page = getTemplatePage("I tuoi scambi");

$page_utenti = file_get_contents($GLOBALS['TEMPLATES_PATH'] . 'gestione-utenti.html');
$page_utenti = addUserManageSection($page_utenti, $db);
$page_utenti = dialogSure($page_utenti);


$page = str_replace('<!-- [content] -->', $page_utenti, $page);
$page = populateWebdirPrefixPlaceholders($page);
$page = addErrorsToPage($page);
echo $page;


function addUserManageSection($page, $db)
{
    $prefix = getPrefix();
    $utentiRegistrati = $db->get_users();
    $utentiRegistratiHTML = "";

    foreach ($utentiRegistrati as $utente) {
        $utentiRegistratiHTML .= generateUserRow($utente, $db);
    }

    if ($utentiRegistratiHTML == '') {
        $utentiRegistratiHTML = <<<HTML
		<div class="empty-list">
			<p>Non ci sono utenti registrati</p>
		</div>
		HTML;
    }
    return str_replace('<!-- [utentiRegistrati] -->', $utentiRegistratiHTML, $page);
}

function generateUserRow($utente, $db)
{
    $location = getLocationName($utente['provincia'], $utente['comune']);

    $prefix = getPrefix();
    return <<<HTML
    <div class="user-row" id="user-{$utente['username']}">
        <a class="user-data" href="{$prefix}/profilo/{$utente['username']}">
            <img src="{$utente['path_immagine']}" alt="">
            <div>
                <p class="bold">{$utente['nome']} {$utente['cognome']}</p>
                <p><span aria-hidden="true">@</span><span class="sr-only">Username</span>{$utente['username']}</p>
                <p><span class="sr-only" lang="en">Email</span>{$utente['email']}</p>
                <p><span class="sr-only">Residenza</span>{$location}</p>
            </div>
        </a>
        <div class="user-buttons">
            <a class="button-layout" href="{$prefix}/profilo/{$utente['username']}/scambi" aria-label="Visualizza scambi {$utente['username']}">Visualizza scambi</a>
            <button class="button-layout destructive elimina-utente" type="button" data-username="{$utente['username']}">Elimina utente</button>
        </div>
    </div>
    HTML;
}

function dialogSure($page)
{
	$dialog_content = <<<HTML
	<h2>Sei sicuro di voler eliminare l'utente?</h2>
	<p class="input-error-regular">Eliminerai completamente il profilo e tutti i dati al suo interno</p>
	<div class="dialog-buttons">
				<input
					class="button-layout destructive"
					id="aggiungi-libro"
					type="submit"
					value="Elimina" />
				<button
					class="button-layout-light"
					formnovalidate
					id="close-dialog"
					type="reset">
					Annulla
				</button>
			</div>
	HTML;

    return str_replace('<!-- [seiSicuro] -->', $dialog_content, $page);
}

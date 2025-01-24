<?php
require_once __DIR__ . '/' . '../paths.php';
require_once $GLOBALS['MODEL_PATH'] . 'dbAPI.php';
require_once $GLOBALS['MODEL_PATH'] . 'utils.php';

ensure_session();
if ($_GET['user'] != $_SESSION['user']) {
	$prefix = getPrefix();
	header('Location: ' . $prefix . '/profilo/' . $_SESSION['user']);
	exit;
}

$db = new DBAccess();


$page = getTemplatePage("I tuoi scambi");

$scambi = file_get_contents($GLOBALS['TEMPLATES_PATH'] . 'scambi.html');
$scambi = addScambiSection($scambi, $db);


$page = str_replace('<!-- [content] -->', $scambi, $page);
$page = populateWebdirPrefixPlaceholders($page);
echo $page;


function addScambiSection($profilo, $db)
{
	$prefix = getPrefix();
	$storicoScambi = $db->get_scambi_by_user($_SESSION['user']);
	$storicoScambiHTML = "";
	
	foreach ($storicoScambi as $scambio) {
		$storicoScambiHTML .= generateScambioRow($scambio, $db);
	}
	
	if ($storicoScambiHTML == '') {
		$storicoScambiHTML = <<<HTML
		<div class="empty-list">	
			<p>Non hai ancora fatto scambi!</p>
			<a href="{$prefix}/esplora">Inzia subito ad esplorare</a>
		</div>
		HTML;
		
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
	
    $prefix = getPrefix();
	return <<<HTML
    <div class="storico-row" id="scambio-{$scambio['ID']}">
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
        <img src="{$prefix}/assets/imgs/scambio-arrows.svg" alt="" id="scambio-arrows">
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
	$prefix = getPrefix();
	if ($isScambioRicevuto) {
		if ($scambio['stato'] === 'in attesa') {
			return <<<HTML
			<div class="storico-buttons accetta">
				<form action="{$prefix}/api/accetta-scambio" method="POST">
					<input type="hidden" name="id_scambio" value="{$scambio['ID']}" />
					<input type="submit" class="button-layout" value="Accetta" />
				</form>
				<form action="{$prefix}/api/rifiuta-scambio" method="POST">
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
                <form action="{$prefix}/api/rimuovi-scambio" method="POST">
                    <input type="hidden" name="id_scambio" value="{$scambio['ID']}" />
                    <input type="submit" class="button-layout secondary" value="Annulla scambio" />
                </form>
            </div>
            HTML;
		}
	}
	if ($scambio['stato'] === 'rifiutato' || $scambio['stato'] === 'accettato') {
		return <<<HTML
            <div class="storico-buttons">
                <p>Scambio {$scambio['stato']}</p>
                <button class="button-layout button-recensione" type="button">Scrivi una recensione</button>
            </div>
            HTML;
	}
	return '';
}

function generateScambioUtente($isScambioRicevuto, $utenteAccettatore, $utenteProponente)
{
	$prefix = getPrefix();
	$utente = $isScambioRicevuto ? $utenteProponente : $utenteAccettatore;
	return <<<HTML
    <div class="storico-utente-scambio">
        <p>Scambio con:</p>
        <div>
            <a href="{$prefix}/profilo/{$utente['username']}">
            <img src="{$utente['path_immagine']}" alt="" width="50">
            <div class="dati-utente">
                <p class="bold">{$utente['nome']} {$utente['cognome']}</p>
                <p>@{$utente['username']}</p>
            </div>
            </a>
        </div>
    </div>
    HTML;
}

function getRecensioneDialog()
{
	
	$recensioneDialog = <<<HTML
	<dialog id="recensione-dialog">
		<div class="dialog-window">
			<h2>Scrivi una recensione a {$user}</h2>
			<form action={$form_action} method="post">
				<label for="titolo" class="visually-hidden">Cerca un libro</label>
				<input type="search"
					name="cerca"
					id="cerca"
					placeholder="Cerca un libro ..."
					autocomplete="off"
					/>
				<span class="sr-only" role="alert" aria-atomic="false" id="sr-risultati"></span>
				<div id="book-results">
					<p>Nessun risultato</p>
				</div>
				{$select_condizioni}
				<input type="hidden" name="ISBN" value="">
				<input type="hidden" name="titolo" value="">
				<input type="hidden" name="autore" value="">
				<input type="hidden" name="editore" value="">
				<input type="hidden" name="anno" value="">
				<input type="hidden" name="genere" value="">
				<input type="hidden" name="descrizione" value="">
				<input type="hidden" name="lingua" value="">
				<input type="hidden" name="path_copertina" value="">
				<div class="dialog-buttons">
					<input type="submit" id="aggiungi-libro" class="button-layout" value="Aggiungi libro">
					<button id="close-dialog" class="button-layout-light" type="reset" formnovalidate>Annulla</button>
				</div>
			</form>
		</div>
	</dialog>
	HTML;
	return str_replace('<!-- [cercaLibriDialog] -->', $cercaLibriDialog, $libri_utente);
}

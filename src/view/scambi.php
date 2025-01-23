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
echo $page;


function addScambiSection($profilo, $db)
{
	$storicoScambi = $db->get_scambi_by_user($_SESSION['user']);
	$storicoScambiHTML = "";
	
	foreach ($storicoScambi as $scambio) {
		$storicoScambiHTML .= generateScambioRow($scambio, $db);
	}
	
	if ($storicoScambiHTML == '') {
		$storicoScambiHTML = <<<HTML
		<div class="empty-list">	
			<p>Non hai ancora fatto scambi!</p>
			<a href="/esplora">Inzia subito ad esplorare</a>
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
        <img src="./assets/imgs/scambio-arrows.svg" alt="" id="scambio-arrows">
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
            <a href="/profilo/{$utente['username']}">
            <img src="{$utente['path_immagine']}" alt="" width="50">
            <div>
                <p class="bold">{$utente['nome']} {$utente['cognome']}</p>
                <p>@{$utente['username']}</p>
            </div>
            </a>
        </div>
    </div>
    HTML;
}

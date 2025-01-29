<?php
require_once __DIR__ . '/' . '../paths.php';
require_once $GLOBALS['MODEL_PATH'] . 'dbAPI.php';
require_once $GLOBALS['MODEL_PATH'] . 'utils.php';

ensure_session();
if (!is_logged_in()) {
	header('Location: ' . getPrefix() . '/profilo');
	exit;
}
if ($_GET['user'] != $_SESSION['user']) {
	$prefix = getPrefix();
	header('Location: ' . $prefix . '/profilo/' . $_SESSION['user']);
	exit;
}

$db = new DBAccess();

$page = getTemplatePage("Recensioni ricevute");

$recensioni = file_get_contents($GLOBALS['TEMPLATES_PATH'] . 'recensioni.html');
$recensioni = addRecensioniSection($recensioni, $db);

$page = str_replace('<!-- [content] -->', $recensioni, $page);
$page = populateWebdirPrefixPlaceholders($page);
$page = addErrorsToPage($page);
echo $page;

function addRecensioniSection($profilo, $db)
{
	$prefix = getPrefix();
	$storicoRecensioni = $db->get_review_by_user($_SESSION['user']);
	$storicoRecensioniHTML = "";

	// var_dump($storicoRecensioni);
	foreach ($storicoRecensioni as $recensione) {
		$storicoRecensioniHTML .= generateRecensioneCard($recensione, $db);
	}

	if ($storicoRecensioniHTML == '') {
		$storicoRecensioniHTML = <<<HTML
		<div class="empty-list">	
			<p>Non hai ancora ricevuto recensioni!</p>
			<a href="{$prefix}/profilo/{$_SESSION['user']}/scambi">Prima completa uno scambio.</a>
		</div>
		HTML;
	}
	return str_replace('<!-- [storicoRecensioni] -->', $storicoRecensioniHTML, $profilo);
}

function generateRecensioneRow($recensione, $db)
{
	$prefix = getPrefix();
	return <<<HTML
    <div class="storico-row" id="recensione-{$recensione['recensito']}-{$recensione['idScambio']}">
        <div>
            <p>Recensore: {$recensione['recensore']}</p>
        </div>
        <div>
            <p>Data: {$recensione['dataPubblicazione']}</p>
        </div>
        <div>
            <p>Valutazione: {$recensione['valutazione']}</p>            
        </div>
        <div>
            <p>Contenuto: {$recensione['contenuto']}</p>
        </div>
        <div class="storico-buttons">
            <form action="{$prefix}/profilo/{$_SESSION['user']}/scambi/#scambio-{$recensione['idScambio']}" method="POST">
                <input type="submit" class="button-layout primary" value="Visualizza scambio"/>
            </form>
        </div>
    </div>            
    HTML;
}

function generateRecensioneCard($recensione)
{
	$prefix = getPrefix();
	$stelle = ratingStars($recensione['valutazione']);
	$dataRecensione = new DateTime($recensione['dataPubblicazione']);
	$dataRecensione = $dataRecensione->format('d/m/Y');
	return <<<HTML
	<div class="recensione-card">
		<a class="dati-recensore" href="{$prefix}/profilo/{$recensione['recensore']}">
			<img src="{$recensione['immagine_recensore']}" alt="">
			<div>
				<p class="bold"><span class="sr-only">Recensore username</span><span aria-hidden="true">@</span>{$recensione['recensore']}</p>
				<p>{$dataRecensione}</p>
				<div class="user-rating">
					<div class="stars">
						{$stelle}
					</div>
					<p>
					<span class="sr-only">Valutazione data</span>({$recensione['valutazione']})
					</p>
				</div>
			</div>
		</a>
		<p class="contenuto-recensione">
			{$recensione['contenuto']}
		</p>
		<div class="bottoni-recensione text-center">
			<a href="{$prefix}/profilo/{$_GET['user']}/scambi/#scambio-{$recensione['idScambio']}" class="button-layout">Visualizza scambio</a>
		</div>
	</div>
	HTML;
}

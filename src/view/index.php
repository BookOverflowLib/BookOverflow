<?php
require_once '../src/paths.php';
require_once $GLOBALS['MODEL_PATH'] . 'dbAPI.php';
require_once $GLOBALS['MODEL_PATH'] . 'utils.php';

$db = new DBAccess();
$dbOK = $db->open_connection();


$mostTradedCoversHTML = '';
if ($dbOK) {
	$mostTradedCovers = $db->get_most_traded_with_cover("4");
	if (!is_null($mostTradedCovers) && is_array($mostTradedCovers)) {
		foreach ($mostTradedCovers as $cover) {
			$mostTradedCoversHTML .= '
                <div class="libro">
	    			<img src="' . $cover['url'] . '" alt="" width="150" />
	    						<p class="titolo-libro">' . $cover['titolo'] . '</p>
	    						<p class="autore-libro">' . $cover['autore'] . '</p>
	    		</div>';
		}
	}
} else {
	$mostTradedCoversHTML = '<p>Siamo spiacenti ma il conenuto richiesto non è disponibile o non esiste. Risolveremo al più presto e dopodiché potremo voltare pagina.</p>';
}

$page = getTemplatePage();
$index = file_get_contents($GLOBALS['TEMPLATES_PATH'] . 'index.html');

$page = str_replace('<!-- [content] -->', $index, $page);
$page = str_replace('<!-- [mostTraded] -->', $mostTradedCoversHTML, $page);
echo $page;
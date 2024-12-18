<?php
require_once '../src/model/dbAPI.php';

$header = file_get_contents('./html/header.html');
$index = file_get_contents('./html/index.html');
$footer = file_get_contents('./html/footer.html');

$db = new DBAccess();
$dbOK = $db->open_connection();

$mostTradedCoversHTML = '';
if ($dbOK) {
    $mostTradedCovers = $db->get_most_traded_with_cover("4");
    foreach ($mostTradedCovers as $cover) {
        $mostTradedCoversHTML .= '
            <div class="libro">
				<img src="' . $cover['url'] . '" alt="" width="150" />
							<p class="titolo-libro">' . $cover['titolo'] . '</p>
							<p class="autore-libro">' . $cover['autore'] . '</p>
						</div>';
    }
} else {
    // TODO: gestire errore connesione
}

$currentPage = $_SERVER['REQUEST_URI'];

$navbarReferences = array(
    array('href' => '/', 'text' => 'Home'),
    array('href' => '/esplora', 'text' => 'Esplora'),
    array('href' => '/contatti', 'text' => 'Contatti'),
    array('href' => '/profilo', 'text' => 'Profilo')
);

$li = '';
foreach ($navbarReferences as $ref) {
    if ($currentPage != $ref['href']) {
        $li .= '<li class="active"><a href="' . $ref['href'] . '">' . $ref['text'] . '</a></li>';
    } else {

        $li .= '<li>' . $ref['text'] . '</li>';
    }
}
$header = str_replace('<!-- [navbar] -->', $li, $header);
$index = str_replace('<!-- [header] -->', $header, $index);
$index = str_replace('<!-- [footer] -->', $footer, $index);
$index = str_replace('<!-- [mostTraded] -->', $mostTradedCoversHTML, $index);
echo $index;
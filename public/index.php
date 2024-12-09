<?php
require_once '../src/model/dbAPI.php';

use DB\DBAccess;

$index = file_get_contents('./html/index.html');

$db = new DBAccess();
$dbOK = $db->open_connection();

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

echo str_replace('<!--[mostTraded]-->', $mostTradedCoversHTML, $index);

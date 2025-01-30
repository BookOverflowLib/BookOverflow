<?php
require_once __DIR__ . '/' . '../paths.php';
require_once $GLOBALS['MODEL_PATH'] . 'dbAPI.php';
require_once $GLOBALS['MODEL_PATH'] . 'utils.php';
require_once $GLOBALS['MODEL_PATH'] . 'nyt-libri.php';

// $pp = showBooksInfo();
//insert_NYT_books();
ensure_session();

$page = getTemplatePage("Esplora tutti");
$esplora = file_get_contents($GLOBALS['TEMPLATES_PATH'] . 'esplora-tutti.html');
$esplora = str_replace('<!-- [esploraTuttiTitolo] -->', 'Esplora tutti', $esplora);

$sottotitolo = 'Tutti i libri messi a disposizione dagli utenti nella piattaforma!';
$esplora = str_replace('<!-- [sottotitolo] -->', $sottotitolo, $esplora);

$prefix = getPrefix();
$ricerca = <<<HTML
<h2>Ricerca</h2>
<form action='{$prefix}/esplora/esplora-tutti' method='GET'>
    <input type='search' name='searchInput' id='searchInput' placeholder='cerca per titolo, autore o ISBN ...'/>
    <input  type='submit' id='ricercaButton' class='button-layout' value='Cerca'>
    <input type='submit' name='generiPreferitiButton' id='generiPreferitiButton' class='button-layout secondary' value="Filtra per generi preferiti">
    <input type='submit' name='resetButton' id='resetButton' class='button-layout danger' value="Azzera ricerca">
    </form>
HTML;
$esplora = str_replace('<!-- [ricerca] -->', $ricerca, $esplora);

$db = new DBAccess();

if (isset($_GET) && isset($_GET['searchInput']) && !empty($_GET['searchInput'])) {
    var_dump("ricerca");
    $searchInput = $_GET['searchInput'];

    try {
        $tutti = $db->search_books($searchInput);
    } catch (Exception $e) {
        $_SESSION['error'] = exceptionToError($e, "ricerca non riuscita");
    }
} elseif (isset($_GET) && isset($_GET['generiPreferitiButton']) && isset($_SESSION['user'])) {
    try {
        $tutti = $db->get_books_by_preferences($_SESSION['user']);
        var_dump($tutti);
    } catch (Exception $e) {
        $_SESSION['error'] = exceptionToError($e, "Errore: Ricerca per generi preferiti non riuscita");
    }
} elseif (isset($_GET) && isset($_GET['resetButton'])) {
    var_dump("reset");
    unset($_GET['searchInput']);
    unset($_GET['generiPreferitiButton']);

    $tutti = $db->get_libri_offerti();
} else {
    var_dump("tutti");
    $tutti = $db->get_libri_offerti();
}

$esplora = str_replace('<!-- [caroselloTuttiLibri] -->', getLibriCopertinaGrande($tutti, 999), $esplora);

$page = str_replace('<!-- [content] -->', $esplora, $page);
$page = populateWebdirPrefixPlaceholders($page);
echo $page;

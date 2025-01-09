<?php
require_once '../src/paths.php';
require_once $GLOBALS['MODEL_PATH'] . 'dbAPI.php';
require_once $GLOBALS['MODEL_PATH'] . 'utils.php';

//check if user is logged in
ensure_session();

$page = getTemplatePage("Libri offerti");

$libri_offerti = file_get_contents($GLOBALS['TEMPLATES_PATH'] . 'libri-offerti.html');

$db = new DBAccess();



$libri_offerti_utente = $db->get_libri_offerti_by_username($_GET['user']);

$libri_offerti_html = '';
if (!$libri_offerti_utente) {
    $libri_offerti_html = '<p>Non ci sono ancora libri offerti</p>';
} else {
    foreach ($libri_offerti_utente as $libro) {
        $isbn = $libro['ISBN'];
        $titolo = $libro['titolo'];
        $autore = $libro['autore'];
        $path_copertina = $libro['path_copertina'];
        $condizioni = $libro['condizioni'];
        $disponibile = $libro['disponibile'] ? 'Disponibile' : 'Non disponibile';

        $libroRowTemplate = <<<HTML
        <div class="book-row">
            <div class="book-info">
                <img
                    src="{$path_copertina}"
                    alt=""
                    width="50" />
                <div>
                    <p>{$titolo}</p>
                    <p class="italic">{$autore}</p>
                </div>
            </div>
            <div class="book-copy-info">
                <p>Stato: {$disponibile}</p>
                <p>Condizioni: {$condizioni}</p>
            </div>
            <div class="book-buttons">
                <form action="/api/rimuovi-libro" method="post">
                    <input type="hidden" name="isbn" value="{$isbn}">
                    <!-- [bookButtons] -->
                </form>
            </div>
        </div>
        HTML;
        $libri_offerti_html .= $libroRowTemplate;
    }
}

$libri_offerti = str_replace('<!-- [libriOfferti] -->', $libri_offerti_html, $libri_offerti);


// aggiungi bottoni solo se è il suo profilo

if (check_ownership()) {
    $aggiungiLibro = '<button class="button-layout" id="aggiungi-libro-button">Aggiungi un libro</button>';
    $libri_offerti = str_replace('<!-- [aggiungiLibroButton] -->', $aggiungiLibro, $libri_offerti);

    $cercaLibriDialog = <<<HTML
    <dialog id="aggiungi-libro-dialog">
        <div class="dialog-window">
            <h2>Cerca un libro</h2>
            <form action="/api/aggiungi-libro" method="post">
                <label for="titolo" class="visually-hidden">Cerca un libro</label>
                <input type="search"
                    name="cerca"
                    id="cerca"
                    placeholder="Cerca un libro ..." />
                <div id="book-results">
                    <p>Nessun risultato</p>
                </div>
                <select name="condizioni" id="condizioni" required>
                    <option value="" disabled selected>Seleziona le condizioni</option>
                    <hr>
                    <option value="nuovo">Nuovo</option>
                    <option value="come nuovo">Come nuovo</option>
                    <option value="usato ma ben conservato">Usato ma ben conservato</option>
                    <option value="usato">Usato</option>
                    <option value="danneggiato">Danneggiato</option>
                </select>
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
                    <button id="close-dialog" class="button-layout-light">Annulla</button>
                    <input type="submit" id="aggiungi-libro" class="button-layout" value="Aggiungi libro">
                </div>
            </form>
        </div>
    </dialog>
    HTML;

    $libri_offerti = str_replace('<!-- [cercaLibriDialog] -->', $cercaLibriDialog, $libri_offerti);

    $modificaEliminaButtons = <<<HTML
    <!-- <button class="button-layout">Modifica</button> -->
    <!-- <form action="/api/rimuovi-libro" method="post"> -->
        <!-- <input type="hidden" name="isbn" value=""> -->
        <input type="submit" class="button-layout danger bold" value="Elimina">
    <!-- </form> -->
    HTML;
    $libri_offerti = str_replace('<!-- [bookButtons] -->', $modificaEliminaButtons, $libri_offerti);
}

$page = str_replace('<!-- [content] -->', $libri_offerti, $page);
echo $page;
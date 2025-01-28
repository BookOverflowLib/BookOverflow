<?php
require_once __DIR__ . '/' . '../paths.php';
require_once $GLOBALS['MODEL_PATH'] . 'dbAPI.php';
require_once $GLOBALS['MODEL_PATH'] . 'utils.php';

ensure_session();

is_logged_in();

$page = getTemplatePage("Esplora");
$esplora = file_get_contents($GLOBALS['TEMPLATES_PATH'] . 'esplora.html');

$esplora = posizionaCategorie($esplora);

$esplora = getLibriOfferti($esplora);

$esplora = getPiuScambiati($esplora);

$esplora = getMatchPerTe($esplora);

$esplora = getPotrebbePiacerti($esplora);


$page = str_replace('<!-- [content] -->', $esplora, $page);
$page = populateWebdirPrefixPlaceholders($page);
echo $page;

function getMatchPerTe($page)
{
    $db = new DBAccess();
    if (is_logged_in()) {
        $match_per_te = $db->get_match_per_te_by_user($_SESSION['user']);
        return str_replace('<!-- [matchPerTe] -->', getLibriCopertinaGrande($match_per_te, 4), $page);
    } else {
        $prefix = getPrefix();
        $not_logged = <<<HTML
        <p class="carosello-libri-vuoto text-center"><a href="{$prefix}/accedi">Accedi per visualizzare contenuti dedicati a te</a></p>
        HTML;
        return str_replace('<!-- [matchPerTe] -->', $not_logged, $page);
    }
}

function getPotrebbePiacerti($page)
{
    $db = new DBAccess();
    if (is_logged_in()) {
        $match_potrebbe_piacerti = $db->get_potrebbe_piacerti_by_user($_SESSION['user']);
        return str_replace('<!-- [matchPotrebbePiacerti] -->', getLibriCopertinaGrande($match_potrebbe_piacerti, 4), $page);
    } else {
        $prefix = getPrefix();
        $not_logged = <<<HTML
        <p class="carosello-libri-vuoto text-center"><a href="{$prefix}/accedi">Accedi per visualizzare contenuti dedicati a te</a></p>
        HTML;
        return str_replace('<!-- [matchPotrebbePiacerti] -->', $not_logged, $page);
    }
}

function getLibriOfferti($page)
{
    $db = new DBAccess();
    $libri_offerti = $db->get_libri_offerti();
    return str_replace('<!-- [libriOfferti] -->', getLibriCopertinaGrande($libri_offerti, 4), $page);
}

function getPiuScambiati($page)
{
    $db = new DBAccess();
    $libri_piu_scambiati = $db->get_piu_scambiati();
    return str_replace('<!-- [libriPiuScambiati] -->', getLibriCopertinaGrande($libri_piu_scambiati, 4), $page);
}

function posizionaCategorie($page)
{
    $array = [
        'libriOfferti' => ['titolo' => 'Libri offerti', 'link' => 'libri-offerti', 'marker' => 'libriOfferti'],
        'libriPiuScambiati' => ['titolo' => 'Libri più scambiati', 'link' => 'piu-scambiati', 'marker' => 'libriPiuScambiati'],
        'matchPerTe' => ['titolo' => 'Match per te', 'link' => 'per-te', 'marker' => 'matchPerTe'],
        'potrebbePiacerti' => ['titolo' => 'Potrebbe piacerti anche', 'link' => 'potrebbe-piacerti', 'marker' => 'matchPotrebbePiacerti']
    ];

    $esploraCategorie = '';
    // PER CAMBIARE L'ORDINE
    if (is_logged_in()) {
        $esploraCategorie .= getSezioneEsploraTemplate($array['matchPerTe']['titolo'], $array['matchPerTe']['link'], $array['matchPerTe']['marker']);
        $esploraCategorie .= getSezioneEsploraTemplate($array['potrebbePiacerti']['titolo'], $array['potrebbePiacerti']['link'], $array['potrebbePiacerti']['marker']);
        $esploraCategorie .= getSezioneEsploraTemplate($array['libriOfferti']['titolo'], $array['libriOfferti']['link'], $array['libriOfferti']['marker']);
        $esploraCategorie .= getSezioneEsploraTemplate($array['libriPiuScambiati']['titolo'], $array['libriPiuScambiati']['link'], $array['libriPiuScambiati']['marker']);
    } else {
        $esploraCategorie .= getSezioneEsploraTemplate($array['libriOfferti']['titolo'], $array['libriOfferti']['link'], $array['libriOfferti']['marker']);
        $esploraCategorie .= getSezioneEsploraTemplate($array['libriPiuScambiati']['titolo'], $array['libriPiuScambiati']['link'], $array['libriPiuScambiati']['marker']);
        $esploraCategorie .= getSezioneEsploraTemplate($array['matchPerTe']['titolo'], $array['matchPerTe']['link'], $array['matchPerTe']['marker']);
        $esploraCategorie .= getSezioneEsploraTemplate($array['potrebbePiacerti']['titolo'], $array['potrebbePiacerti']['link'], $array['potrebbePiacerti']['marker']);
    }
    return str_replace('<!-- [esploraCategorie] -->', $esploraCategorie, $page);
}

function getSezioneEsploraTemplate($titolo, $link, $marker)
{
    $titoloLower = strtolower($titolo);
    return <<<HTML
    <section>
        <article class="sezione-stretta">
            <div class="intestazione">
                <h2>{$titolo}</h2>
                <a
                    aria-label="Mostra tutti i {$titoloLower}"
                    href="<!-- [prefix] -->/esplora/{$link}"
                    >Mostra tutti</a
                >
            </div>
            <div class="carosello-libri">
                <!-- [{$marker}] -->
            </div>
        </article>
    </section>
    HTML;
}
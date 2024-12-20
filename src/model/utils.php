<?php

/**
 * Genera una stringa HTML per visualizzare il rating sotto forma di stelle
 *
 * @param float $rating Valore del rating (compreso tra 0.0 e 5.0)
 * @return string HTML con gli svg delle stelle
 * @throws Exception Se il rating non è compreso tra 0 e 5
 */
function ratingStars($rating): string
{
    if ($rating > 5 || $rating < 0) {
        throw new Exception("Rating non nei vincoli", 1);
    }

    $n_full_star = floor($rating); //PARTE INTERA
    $n_partial_star = $rating - $n_full_star; //PARTE FRAZIONARIA
    $star_svg = file_get_contents("../assets/imgs/star.svg");

    $total_star = 5;
    $rating_stars = "";

    // STELLE PIENE
    for ($i = 0; $i < $n_full_star; $i++) {
        $tmp_star = str_replace("{{star-offset}}", "100", $star_svg);
        $tmp_star = str_replace("{{id}}", "1", $tmp_star);

        $rating_stars .= $tmp_star;
        $total_star--;
    }

    // STELLA PERCENTUALE
    $par_star = str_replace("{{star-offset}}", strval($n_partial_star * 100), $star_svg);
    $par_star = str_replace("{{id}}", "2", $par_star);
    $rating_stars .= $par_star;
    $total_star--;

    //STELLE VUOTE
    while ($total_star > 0) {
        $tmp_star = str_replace("{{star-offset}}", "0", $star_svg);
        $tmp_star = str_replace("{{id}}", "3", $tmp_star);
        $rating_stars .= $tmp_star;
        $total_star--;
    }

    return $rating_stars;
}

/**
 * Genera gli elementi \<li\> della navbar rimuovendo il link dalla pagina corrente (circolari!)
 *
 * @return string HTML contenente i vari elementi \<li\>
 */
function getNavBarLi(): string
{
    $currentPage = $_SERVER['REQUEST_URI'];

    $navbarReferences = array(
        array('href' => '/', 'text' => 'Home'),
        array('href' => '/esplora', 'text' => 'Esplora'),
        array('href' => '/chi-siamo', 'text' => 'Chi siamo'),
        array('href' => '/profilo', 'text' => 'Profilo')
    );

    $li = '';
    foreach ($navbarReferences as $ref) {
        if ($currentPage != $ref['href']) {
            $li .= '<li><a href="' . $ref['href'] . '">' . $ref['text'] . '</a></li>';
        } else {
            $li .= '<li class="activePage">' . $ref['text'] . '</li>';
        }
    }
    return $li;
}

/**
 * Restituisce l'header con la navbar aggiornata
 *
 * @return string HTML dell'header con la navbar sostituita
 */
function getHeaderSection(): string
{
    $header = file_get_contents('./html/header.html');
    // Genera i link della navbar
    $li = getNavBarLi();
    return str_replace('<!-- [navbar] -->', $li, $header);
}

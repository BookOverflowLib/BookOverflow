<?php
function ratingStars($rating)
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

    echo $rating_stars;
}

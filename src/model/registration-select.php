<?php
require_once '../src/model/dbAPI.php';

function optionProvince(): string
{
    $html_output = "";
    $db = new DBAccess(); // ma che diamine? dubito sia giusto
    try {
        $array_province = $db->get_province();

        if ($array_province) {
            foreach ($array_province as $province) {
                $html_output .= "<option value='$province[id]'>$province[nome]</option>";
            }
        }
    } catch (Exception $e) {
        echo $e->getMessage();
    }
    return $html_output;
}

function optionComuni($province_id): string
{
    $html_output = "";
    $db = new DBAccess();
    try {
        $array_citta = $db->get_comune_by_provincia($province_id);

        if ($array_citta) {
            foreach ($array_citta as $citta) {
                $html_output .= "<option value='$citta[id]'>$citta[nome]</option>";
            }
        }
    } catch (Exception $e) {
        echo $e->getMessage();
    }
    return $html_output;
}

<?php
require_once __DIR__ . '/' . '../model/dbAPI.php';

function optionProvince($selectedId = null): string
{
	$html_output = "";
	$db = new DBAccess(); // ma che diamine? dubito sia giusto
	try {
		$html_output .= optionProvinceNoGroup($selectedId);
	} catch (Exception $e) {
		$_SESSION['error'] = exceptionToError($e, "caricamento delle province non riuscito");
	}
	return $html_output;
}

function optionGroupRegioniProvince(): string
{
	$html_output = "";
	$db = new DBAccess();
	try {
		$array_province = $db->get_province();
		$array_province_by_regione = [];
		foreach ($array_province as $key => $value) {
			$array_province_by_regione[$value['regione']][] = $value;
		}
		ksort($array_province_by_regione);
		foreach ($array_province_by_regione as $regione => $province) {
			$regione = htmlspecialchars($regione);
			$html_output .= "<optgroup label='$regione'>";
			foreach ($province as $provincia) {
				$html_output .= "<option value='$provincia[id]'>$provincia[nome]</option>";
			}
			$html_output .= "</optgroup>";
		}
	} catch (Exception $e) {
		$_SESSION['error'] = exceptionToError($e, "caricamento delle province non riuscito");
	}
	return $html_output;
}

function optionProvinceNoGroup($selectedId = null): string
{
	$html_output = "";
	$db = new DBAccess();
	try {
		$array_province = $db->get_province();
		foreach ($array_province as $provincia) {
			if ($selectedId == $provincia['id']) {
				$html_output .= "<option value='{$provincia['id']}' selected>{$provincia['nome']}</option>";

			} else {
				$html_output .= "<option value='{$provincia['id']}'>{$provincia['nome']}</option>";
			}
		}
	} catch (Exception $e) {
		$_SESSION['error'] = exceptionToError($e, "caricamento delle province non riuscito");
	}
	return $html_output;
}

function optionComuni($province_id, $comune_id = null): string
{
	$html_output = "";
	$db = new DBAccess();
	try {
		$array_citta = $db->get_comune_by_provincia($province_id);

		if ($array_citta) {
			foreach ($array_citta as $citta) {
				if($citta['id'] == $comune_id){
					$html_output .= "<option value='$citta[id]' selected>$citta[nome]</option>";
				}else{
					$html_output .= "<option value='$citta[id]'>$citta[nome]</option>";
				}
			}
		}
	} catch (Exception $e) {
		$_SESSION['error'] = exceptionToError($e, "caricamento dei comuni non riuscito");
	}
	return $html_output;
}

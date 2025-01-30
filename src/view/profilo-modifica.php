<?php
require_once __DIR__ . '/' . '../paths.php';
require_once $GLOBALS['MODEL_PATH'] . 'dbAPI.php';
require_once $GLOBALS['MODEL_PATH'] . 'utils.php';
require_once $GLOBALS['MODEL_PATH'] . 'registration-select.php';


ensure_session();

$isTuoProfilo = isTuoProfilo($_GET['user']);

if (!$isTuoProfilo) {
	header('Location: ' . getPrefix() . '/profilo/' . $_SESSION['user']);
	exit();
}

$page = getTemplatePage('Modifica account');
$modificaProfilo = file_get_contents($GLOBALS['TEMPLATES_PATH'] . 'profilo-modifica.html');

$db = new DBAccess();
$userData = ($db->get_user_by_identifier($_GET['user']))[0];


$page = str_replace('<!-- [content] -->', $modificaProfilo, $page);
$page = getCampiDati($page, $userData);
$page = addErrorsToPage($page);
$page = populateWebdirPrefixPlaceholders($page);
echo $page;

function getCampiDati($page, $user)
{
	$nome = <<<HTML
	<div>
		<label for="nome">Nome</label>
		<input
			autocomplete="given-name"
			class="form-input"
			id="nome"
			name="nome"
			placeholder="Es. Mario"
			pattern="^[A-Za-z\sÀ-ÿ'’]{2,50}$"
			required
			title="Deve contenere almeno 2 caratteri, non sono ammessi numeri o caratteri speciali"
			aria-describedby="nome_help"
			value="{$user['nome']}"
			type="text" />
		<p id="nome_help" class="form_help">
			Deve contenere almeno 2 caratteri, non sono ammessi
			numeri o caratteri speciali
		</p>
	</div>
	HTML;

	$cognome = <<<HTML
	<div>
		<label for="cognome">Cognome</label>
		<input
			autocomplete="family-name"
			class="form-input"
			id="cognome"
			name="cognome"
			placeholder="Es. Rossi"
			pattern="^[A-Za-z\sÀ-ÿ'’]{2,50}$"
			required
			title="Deve contenere almeno 2 caratteri, non sono ammessi numeri o caratteri speciali"
			aria-describedby="cognome_help"
			value="{$user['cognome']}"
			type="text" />
		<p id="cognome_help" class="form_help">
			Deve contenere almeno 2 caratteri, non sono ammessi
			numeri o caratteri speciali
		</p>
	</div>
	HTML;

	$provinceList = optionProvince($user['provincia']);
	$comuniList = optionComuni($user['provincia'], $user['comune']);
	$provinciaComune = <<<HTML
	<div>
		<label for="provincia">Provincia</label>
		<select
			class="form-input"
			id="provincia"
			name="provincia"
			aria-describedby="provincia_help"
			required>
			<option value="" selected disabled>
				Seleziona una provincia
			</option>
			<hr />
			{$provinceList}
		</select>
		<p id="provincia_help" class="form_help">
			La provincia in cui risiedi, sarà utile agli altri
			utenti per capire le modalità di scambio
		</p>
	</div>
	<div>
		<label for="comune">Comune</label>
		<select
			class="form-input"
			id="comune"
			name="comune"
			aria-describedby="comune_help"
			required>
			<option value="" selected disabled>
				Seleziona un comune
			</option>
			<hr />
			{$comuniList}
		</select>
		<p id="comune_help" class="form_help">
			Il comune in cui risiedi, sarà utile agli altri utenti
			per capire le modalità di scambio
		</p>
	</div>
	HTML;

	$password = <<<HTML
	<div>
		<label for="password" lang="en">Nuova password</label>
		<input
			autocomplete="new-password"
			class="form-input"
			id="password"
			name="password"
			minlength="8"
			pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$"
			title="Deve contenere almeno 8 caratteri, una lettera maiuscola, una lettera minuscola, un numero e un carattere speciale"
			type="password" />
		<div id="password_help" class="form_help">
			<p>Deve contenere:</p>
			<ul>
				<li>Almeno 8 caratteri</li>
				<li>Una lettera maiuscola (A-Z)</li>
				<li>Una lettera minuscola (a-z)</li>
				<li>Un numero (0-9)</li>
				<li>Un carattere speciale</li>
			</ul>
		</div>
	</div>
	<!-- conferma password -->
	<div>
		<label for="conferma_password">Conferma nuova <span lang="en">password</span></label>
		<input
			autocomplete="new-password"
			class="form-input"
			id="conferma_password"
			name="conferma_password"
			type="password" />
	</div>
	HTML;

	$conferma = '<input class="button-layout" type="submit" value="Conferma modifiche" />';
	$prefix = getPrefix();
	$annulla = <<<HTML
	<a href="{$prefix}/profilo/{$user['username']}" class="button-layout-light text-center">Annulla</a>
	HTML;
	$campi = $nome . $cognome . $provinciaComune . $password . $conferma. $annulla;
	return str_replace('<!-- [campiDati] -->', $campi, $page);
}
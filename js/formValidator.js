
function saveInputValueSessionStorage(input) {
	sessionStorage.setItem(input.name, input.value);
}

function restoreInputValueSessionStorage(input) {
	var value = sessionStorage.getItem(input.name);
	if (value) {
		input.value = value;
	}
}

export function saveAllInputValues() {
	console.log("saving all input values");
	var inputs = [
		document.getElementById('nome'),
		document.getElementById('cognome'),
		document.getElementById('provincia'),
		document.getElementById('comune'),
		document.getElementById('username'),
		document.getElementById('email')
	];

	for (var i = 0; i < inputs.length; i++) {
		saveInputValueSessionStorage(inputs[i]);
	}
}

export function restoreAllInputValues() {
	var inputs = [
		document.getElementById('nome'),
		document.getElementById('cognome'),
		document.getElementById('provincia'),
		document.getElementById('comune'),
		document.getElementById('username'),
		document.getElementById('email')
	];

	for (var i = 0; i < inputs.length; i++) {
		restoreInputValueSessionStorage(inputs[i]);
	}
}


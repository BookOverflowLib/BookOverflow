let provinciaSelect = document.querySelector('select[name="provincia"]')
if (provinciaSelect) {
	provinciaSelect.addEventListener('change', function () {
		getComuniByProvincia(this.value)
	})
}


function getComuniByProvincia(provincia) {
	let cittaSelect = document.querySelector('select[name="comune"]')
	//create payload to send to server
	let fd = new FormData()
	fd.set('provinciaSelezionata', provincia)

	// send the ajax request using fetch
	prefisso = getPrefix(window.location.pathname)
	fetch(window.location.origin + '/' + prefisso + 'api/ottieni-comuni', { method: 'post', body: fd })
		.then(response => response.json())
		.then(json => {
			let options = '<option value="">Seleziona un comune</option><hr />'
			cittaSelect.innerHTML = options + json.comuni
		})
		.then(() => {
			if (sessionStorage.getItem("comune")) {
				cittaSelect.value = sessionStorage.getItem("comune")
			}
		})
}

function getPrefix(url) {
	const parts = url.split('/');
	if (parts.length > 1 && parts[1] !== '' && parts[1] !== 'registrati' && parts[1] !== 'profilo' && parts[1] !== 'api') {
		return `${parts[1]}/`;
	}
	return '';
}
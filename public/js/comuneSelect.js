let provinciaSelect = document.querySelector('select[name="provincia"]')
if (provinciaSelect) {
	provinciaSelect.addEventListener('change', function (e) {
		//find target Select menu
		let cittaSelect = document.querySelector('select[name="comune"]')

		//create payload to send to server
		let fd = new FormData()
		fd.set('provinciaSelezionata', this.value)

		// send the ajax request using fetch
		fetch('ottieniComuni.php', { method: 'post', body: fd })
			.then(response => response.json())
			.then(json => {
				let options = '<option value="">Seleziona un comune</option><hr />'
				cittaSelect.innerHTML = options + json.comuni
			})
	})
}

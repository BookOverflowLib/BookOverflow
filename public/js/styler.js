document.addEventListener('scroll', function() {
	const header = document.getElementsByClassName('header')[0]
	if (window.scrollY > 30) {
		header.classList.add('activeHeader')
	} else {
		header.classList.remove('activeHeader')
	}
})
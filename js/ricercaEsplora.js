document.addEventListener('DOMContentLoaded', () => {
	const reset = document.getElementById('reset');
	reset.addEventListener('click', () => {
		window.location.href = window.location.pathname;
	});
});
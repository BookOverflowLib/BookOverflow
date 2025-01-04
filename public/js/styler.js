
function getThemeFromSettings({ localStorageTheme, systemSettingDark }) {
	if (localStorageTheme !== null) {
		return localStorageTheme;
	}
	if (systemSettingDark.matches) {
		return "dark";
	}
	return "light";
}

const html = document.querySelector("html");
let localStorageTheme = localStorage.getItem("theme");
const systemSettingDark = window.matchMedia("(prefers-color-scheme: dark)");
const settingsTheme = getThemeFromSettings({ localStorageTheme, systemSettingDark });
html.setAttribute("data-theme", settingsTheme);

// when the page is loaded
document.addEventListener('DOMContentLoaded', function () {

	let currentThemeSetting = getThemeFromSettings({ localStorageTheme, systemSettingDark });
	const themeToggleButton = document.getElementById('theme-toggle');
	const toggleButtonIconChiaro = document.querySelectorAll('#theme-toggle > span')[0];
	const toggleButtonIconScuro = document.querySelectorAll('#theme-toggle > span')[1];

	if (settingsTheme === 'dark') {
		toggleButtonIconChiaro.classList.add('active');
		toggleButtonIconScuro.classList.remove('active');
	} else {
		toggleButtonIconScuro.classList.add('active');
		toggleButtonIconChiaro.classList.remove('active');
	}

	themeToggleButton.addEventListener('click', function () {
		const newTheme = currentThemeSetting === "dark" ? "light" : "dark";
		document.querySelector("html").setAttribute("data-theme", newTheme);

		toggleButtonIconChiaro.classList.toggle('active');
		toggleButtonIconScuro.classList.toggle('active');

		themeToggleButton.ariaPressed === "true" ? themeToggleButton.ariaPressed = "false" : themeToggleButton.ariaPressed = "true";
		// update in local storage
		localStorage.setItem("theme", newTheme);

		// update the currentThemeSetting in memory
		currentThemeSetting = newTheme;
	})

})

document.addEventListener('scroll', function () {
	const header = document.getElementsByClassName('header-container')[0]
	if (window.scrollY > 30) {
		header.classList.add('activeHeader')
	} else {
		header.classList.remove('activeHeader')
	}
})

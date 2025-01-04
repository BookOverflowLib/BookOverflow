
function calculateSettingAsThemeString({ localStorageTheme, systemSettingDark }) {
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
html.setAttribute("data-theme", calculateSettingAsThemeString({ localStorageTheme, systemSettingDark }));

// when the page is loaded
document.addEventListener('DOMContentLoaded', function () {

	localStorageTheme = localStorage.getItem("theme");

	let currentThemeSetting = calculateSettingAsThemeString({ localStorageTheme, systemSettingDark });

	const themeToggleButton = document.getElementById('theme-toggle');

	themeToggleButton.addEventListener('click', function () {
		const newTheme = currentThemeSetting === "dark" ? "light" : "dark";
		document.querySelector("html").setAttribute("data-theme", newTheme);

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

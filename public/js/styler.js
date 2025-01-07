function getThemeFromSettings({ localStorageTheme, systemSettingDark }) {
	if (localStorageTheme !== null) {
		return localStorageTheme;
	}
	if (systemSettingDark.matches) {
		return "dark";
	}
	return "light";
}

// TODO: pls fix me :( im too ugly
function toggleTheme() {
	let localStorageTheme = localStorage.getItem("theme");
	const systemSettingDark = window.matchMedia("(prefers-color-scheme: dark)");
	let currentThemeSetting = getThemeFromSettings({ localStorageTheme, systemSettingDark });
	const themeToggleButton = document.getElementsByClassName('theme-toggle');
	const normalThemeToggleButton = themeToggleButton[0];
	const hamburgerThemeToggleButton = themeToggleButton[1];
	const normalToggleButtonIconChiaro = normalThemeToggleButton.querySelectorAll('span')[0];
	const normalToggleButtonIconScuro = normalThemeToggleButton.querySelectorAll('span')[2];
	const hamburgerToggleButtonIconChiaro = hamburgerThemeToggleButton.querySelectorAll('span')[0];
	const hamburgerToggleButtonIconScuro = hamburgerThemeToggleButton.querySelectorAll('span')[2];

	console.table(normalToggleButtonIconChiaro, normalToggleButtonIconScuro, hamburgerToggleButtonIconChiaro, hamburgerToggleButtonIconScuro);

	if (currentThemeSetting === 'dark') {
		normalToggleButtonIconChiaro.classList.add('active');
		normalToggleButtonIconScuro.classList.remove('active');
		hamburgerToggleButtonIconChiaro.classList.add('active');
		hamburgerToggleButtonIconScuro.classList.remove('active');
	} else {
		normalToggleButtonIconScuro.classList.add('active');
		normalToggleButtonIconChiaro.classList.remove('active');
		hamburgerToggleButtonIconScuro.classList.add('active');
		hamburgerToggleButtonIconChiaro.classList.remove('active');
	}

	normalThemeToggleButton.addEventListener('click', function () {
		const newTheme = currentThemeSetting === "dark" ? "light" : "dark";
		document.querySelector("html").setAttribute("data-theme", newTheme);

		normalToggleButtonIconChiaro.classList.toggle('active');
		normalToggleButtonIconScuro.classList.toggle('active');


		themeToggleButton.ariaPressed === "true" ? themeToggleButton.ariaPressed = "false" : themeToggleButton.ariaPressed = "true";
		// update in local storage
		localStorage.setItem("theme", newTheme);

		// update the currentThemeSetting in memory
		currentThemeSetting = newTheme;
	})
	hamburgerThemeToggleButton.addEventListener('click', function () {
		const newTheme = currentThemeSetting === "dark" ? "light" : "dark";
		document.querySelector("html").setAttribute("data-theme", newTheme);

		hamburgerToggleButtonIconChiaro.classList.toggle('active');
		hamburgerToggleButtonIconScuro.classList.toggle('active');

		themeToggleButton.ariaPressed === "true" ? themeToggleButton.ariaPressed = "false" : themeToggleButton.ariaPressed = "true";
		// update in local storage
		localStorage.setItem("theme", newTheme);

		// update the currentThemeSetting in memory
		currentThemeSetting = newTheme;
	})
}

function setCorrectThemeBeforeLoading() {
	const html = document.querySelector("html");
	let localStorageTheme = localStorage.getItem("theme");
	const systemSettingDark = window.matchMedia("(prefers-color-scheme: dark)");
	const settingsTheme = getThemeFromSettings({ localStorageTheme, systemSettingDark });
	html.setAttribute("data-theme", settingsTheme);
}



setCorrectThemeBeforeLoading();

// when the page is loaded
document.addEventListener('DOMContentLoaded', function () {

	toggleTheme();

})

document.addEventListener('scroll', function () {
	const header = document.getElementsByClassName('header-container')[0]
	if (window.scrollY > 30) {
		header.classList.add('active')
	} else {
		if (!document.getElementById('hamburger-menu').classList.contains('active')) {
			header.classList.remove('active')
		}
	}
})

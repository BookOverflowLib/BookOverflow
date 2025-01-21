function getThemeFromSettings({localStorageTheme, systemSettingDark}) {
    return localStorageTheme !== null ? localStorageTheme : (systemSettingDark.matches ? "dark" : "light");
}

function toggleTheme() {
    const localStorageTheme = localStorage.getItem("theme");
    const systemSettingDark = window.matchMedia("(prefers-color-scheme: dark)");
    let currentThemeSetting = getThemeFromSettings({localStorageTheme, systemSettingDark});
    const themeToggleButtons = document.querySelectorAll('.theme-toggle');
    const [normalThemeToggleButton, hamburgerThemeToggleButton] = themeToggleButtons;
    const normalToggleButtonIcons = normalThemeToggleButton.querySelectorAll('span');
    const hamburgerToggleButtonIcons = hamburgerThemeToggleButton.querySelectorAll('span');

    function updateIcons(theme) {
        const isDark = theme === 'dark';
        normalToggleButtonIcons[0].classList.toggle('active', isDark);
        normalToggleButtonIcons[2].classList.toggle('active', !isDark);
        hamburgerToggleButtonIcons[0].classList.toggle('active', isDark);
        hamburgerToggleButtonIcons[2].classList.toggle('active', !isDark);
    }

    function toggleThemeSetting() {
        const newTheme = currentThemeSetting === "dark" ? "light" : "dark";
        document.documentElement.setAttribute("data-theme", newTheme);
        updateIcons(newTheme);
        localStorage.setItem("theme", newTheme);
        currentThemeSetting = newTheme;
    }

    updateIcons(currentThemeSetting);

    normalThemeToggleButton.addEventListener('click', toggleThemeSetting);
    hamburgerThemeToggleButton.addEventListener('click', toggleThemeSetting);
}

function setCorrectThemeBeforeLoading() {
    const html = document.querySelector("html");
    let localStorageTheme = localStorage.getItem("theme");
    const systemSettingDark = window.matchMedia("(prefers-color-scheme: dark)");
    const settingsTheme = getThemeFromSettings({localStorageTheme, systemSettingDark});
    html.setAttribute("data-theme", settingsTheme);
}

setCorrectThemeBeforeLoading();

// when the page is loaded
document.addEventListener('DOMContentLoaded', toggleTheme);

document.addEventListener('scroll', () => {
    const header = document.querySelector('.header-container');
    if (window.scrollY > 30) {
        header.classList.add('active');
    } else if (!document.getElementById('hamburger-menu').classList.contains('active')) {
        header.classList.remove('active');
    }
});
import {restoreAllInputValues, saveAllInputValues} from './formValidator.js';


window.onload = function () {
    const form = document.getElementById('registrati');

    //fillSuggestion(formChecks);
    if (sessionStorage.getItem("nome")) {
        restoreAllInputValues();
        let provinciaSelect = document.querySelector('select[name="provincia"]')
        provinciaSelect.dispatchEvent(new Event('change'));
    }

    form.addEventListener('submit', function () {
        saveAllInputValues();
    //    return checkForm("registrati", "api/registra-utente", formChecks);
    });
};

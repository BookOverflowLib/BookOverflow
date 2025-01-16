import {checkForm, fillSuggestion} from './formValidator.js';

// [0]: hint
// [1]: regex
// [2]: error
// [3]: isValid
var formChecks = {
    //todo: move to dedicated JS function
    identifier: [
        "Ex: mariorossi@gmail.com",
        "*",
        "",
        false
    ],
    password: [
        "Ex: Password123!",
        /.*/,
        "",
        false
    ]
};

window.onload = function () {
    fillSuggestion(formChecks);

    const form = document.getElementById('accedi');
    form.addEventListener('submit', function () {
        return checkForm("accedi", "/api/accesso-utente", formChecks);
    });
};
import { fillSuggestion, checkForm } from './formValidator.js';

// [0]: hint
// [1]: regex
// [2]: error
// [3]: isValid
var formChecks = {
    email: [
        "Ex: mariorossi@gmail.com",
        /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,
        "Inserire un'email valida",
        false
    ],
    password: [
        "Ex: Password123",
        /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()\-+=])[A-Za-z\d!@#$%^&*()\-+=]{12,50}$/,
        "La password deve essere lunga tra 12 e 50 caratteri, contenere almeno una lettera maiuscola (A-Z), una lettera minuscola (a-z), un numero (0-9) e un carattere speciale tra i seguenti: !@#$%^&*()-+=.",
        false
    ]
};

window.onload = function () {
    fillSuggestion(formChecks);

    const form = document.getElementById('accedi');
    form.addEventListener('submit', function (event) {
        return checkForm("accedi", "/api/accesso-utente", formChecks);
    });
};
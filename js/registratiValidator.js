import {checkForm, fillSuggestion, restoreAllInputValues, saveAllInputValues} from './formValidator.js';

// [0]: hint
// [1]: regex
// [2]: error
// [3]: isValid
var formChecks = {
    nome: [
        "Es: Mario",
        /^[A-Za-z\s']{2,50}$/,
        "Inserire un nome di lunghezza almeno 2 e massimo 50, non sono ammessi numeri o caratteri speciali",
        false
    ],
    cognome: [
        "Es: Rossi",
        /^[A-Za-z\s']{2,50}$/,
        "Inserire un cognome di lunghezza almeno 2 e massimo 50, non sono ammessi numeri o caratteri speciali",
        false
    ],
    username: [
        "Es: MarioRossi",
        /^[^\s\r\n]{2,50}$/,
        "Inserire un username di lunghezza almeno 2 e massimo 50, sono ammessi caratteri speciali e numeri",
        false
    ],
    email: [
        "Es: mariorossi@gmail.com",
        /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,
        "Inserire un'email valida",
        false
    ],
    password: [
        "Es: Password123!",
        /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()\-+=])[A-Za-z\d!@#$%^&*()\-+=]{8,50}$/,
        "La password deve essere lunga tra 8 e 50 caratteri, contenere almeno una lettera maiuscola (A-Z), una lettera minuscola (a-z), un numero (0-9) e un carattere speciale tra i seguenti: !@#$%^&*()-+=.",
        false
    ],
    conferma_password: [
        "Es: Password123!",
        /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()\-+=])[A-Za-z\d!@#$%^&*()\-+=]{8,50}$/,
        "La password deve essere lunga tra 8 e 50 caratteri, contenere almeno una lettera maiuscola (A-Z), una lettera minuscola (a-z), un numero (0-9) e un carattere speciale tra i seguenti: !@#$%^&*()-+=.",
        false
    ]
};

window.onload = function () {
    const form = document.getElementById('registrati');

    fillSuggestion(formChecks);
    if (sessionStorage.getItem("nome")) {
        restoreAllInputValues();
        let provinciaSelect = document.querySelector('select[name="provincia"]')
        provinciaSelect.dispatchEvent(new Event('change'));
    }

    form.addEventListener('submit', function () {
        saveAllInputValues();
        return checkForm("registrati", "api/registra-utente", formChecks);
    });

};

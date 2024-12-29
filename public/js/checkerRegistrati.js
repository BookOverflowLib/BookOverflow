// [0]: hint
// [1]: regex
// [2]: error
// [3]: isValid
var formChecks = {
    nome: [
        "Ex: Mario",
        /^[A-Za-z]{2,50}$/,
        "Inserire un nome di lunghezza almeno 2 e massimo 50, non sono ammessi numeri o caratteri speciali",
        false
    ],
    cognome: [
        "Ex: Rossi",
        /^[A-Za-z' ]{2,50}$/,
        "Inserire un cognome di lunghezza almeno 2 e massimo 50, non sono ammessi numeri o caratteri speciali",
        false
    ],
    // all characters are allowed
    username: [
        "Ex: MarioRossi",
        /^[^\s\r\n]{2,50}$/,
        "Inserire un username di lunghezza almeno 2 e massimo 50, sono ammessi caratteri speciali e numeri",
        false
    ],
    email: [
        "Ex: mariorossi@gmail.com",
        /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,
        "Inserire un'email valida",
        false
    ],
    password: [
        "Ex: Password123",
        /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()\-+=])[A-Za-z\d!@#$%^&*()\-+=]{8,20}$/,
        "La password deve essere lunga tra 8 e 20 caratteri, contenere almeno una lettera maiuscola (A-Z), una lettera minuscola (a-z), un numero (0-9) e un carattere speciale tra i seguenti: !@#$%^&*()-+=.",
        false
    ],
    conferma: [
        "Ex: Password123",
        /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()\-+=])[A-Za-z\d!@#$%^&*()\-+=]{8,20}$/,
        "La password deve essere lunga tra 8 e 20 caratteri, contenere almeno una lettera maiuscola (A-Z), una lettera minuscola (a-z), un numero (0-9) e un carattere speciale tra i seguenti: !@#$%^&*()-+=.",
        false
    ]
};

window.onload = function fillSuggestion() {
    for (var id in formChecks) {
        var input = document.getElementById(id);
        setSuggestion(input, 0);
        input.onblur = function () {
            checkRegex(this);
        };
    }
}

/* 
* mode = 0, modalità suggerimento
* mode = 1, modalità errore
* mode = 2, modalità input vuoto
*/
function setSuggestion(input, mode) {
    var parent = input.parentNode;
    var newNode = document.createElement("span");
    newNode.id = input.id + "-sugg";

    switch (mode) {
        case 0:
            newNode.textContent = formChecks[input.id][0];
            newNode.className = "input-hint";
            break;
        case 1:
            newNode.textContent = formChecks[input.id][2];
            newNode.className = "input-error";
            break;
        case 2:
            newNode.textContent = "Campo obbligatorio";
            newNode.className = "input-error";
            break;
        default:
            break;
    }

    parent.insertBefore(newNode, parent.lastChild.previousSibling);
}

function checkRegex(input) {
    // TODO: isValid true but it shouldn't??? maybe checkForm is not working properly
    var regex = formChecks[input.id][1];
    var text = input.value;
    var suggestionElement = document.getElementById(input.id + "-sugg");

    if (!input.value) {
        formChecks[input.id][3] = false;
        if (suggestionElement) {
            suggestionElement.remove();
        }
        setSuggestion(input, 2);
        input.focus();
        input.select();
    } else if (text.search(regex) != 0) {
        formChecks[input.id][3] = false;
        if (suggestionElement) {
            suggestionElement.remove();
        }
        setSuggestion(input, 1);
        input.focus();
        input.select();
    } else if (!formChecks[input.id][3]) {
        formChecks[input.id][3] = true;
        if (suggestionElement) {
            suggestionElement.remove();
        }
    }
}

function checkForm() {
    var form = document.getElementById("form-registrati");
    var inputs = form.getElementsByTagName("input");

    for (var i = 0; i < inputs.length; i++) {
        if (!formChecks[inputs[i].id][3]) {
            return false;
        }
    }

    return true;
}

function checkPasswordConfirm() {
    // TODO: implement
    var password = document.getElementById("password");
    var conferma = document.getElementById("conferma");

    return true;
}
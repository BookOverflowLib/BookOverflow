var formChecks = {
    nome: [
        "Ex: Mario",
        /^[A-Za-z]{2,50}$/,
        "Inserire un nome di lunghezza almeno 2 e massimo 50, non sono ammessi numeri o caratteri speciali",
    ],
    cognome: [
        "Ex: Rossi",
        /^[A-Za-z' ]{2,50}$/,
        "Inserire un cognome di lunghezza almeno 2 e massimo 50, non sono ammessi numeri o caratteri speciali",
    ],
    // all characters are allowed
    username: [
        "Ex: MarioRossi",
        /^[^\s\r\n]{1,50}$/,
        "Inserire un username di lunghezza almeno 2 e massimo 50, sono ammessi caratteri speciali e numeri",
    ],
    email: [
        "Ex: mariorossi@gmail.com",
        /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,
        "Inserire un'email valida",
    ],
    password: [
        "Ex: Password123",
        /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()\-+=])[A-Za-z\d!@#$%^&*()\-+=]{8,20}$/,
        "La password deve essere lunga tra 8 e 20 caratteri, contenere almeno una lettera maiuscola (A-Z), una lettera minuscola (a-z), un numero (0-9) e un carattere speciale tra i seguenti: !@#$%^&*()-+=.",
    ],
    confermaPassword: [
        "Ex: Password123",
        /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()\-+=])[A-Za-z\d!@#$%^&*()\-+=]{8,20}$/,
        "La password deve essere lunga tra 8 e 20 caratteri, contenere almeno una lettera maiuscola (A-Z), una lettera minuscola (a-z), un numero (0-9) e un carattere speciale tra i seguenti: !@#$%^&*()-+=.",
    ]
};

window.onload = function fillSuggestion() {
    for (var id in formChecks) {
        console.log(id);
        var input = document.getElementById(id);
        setSuggestion(input, 0);
        input.onblur = function () {
            checkRegex(this);
        };
    }
}

function checkRegex(input) {
    var regex = formChecks[input.id][1];
    var text = input.value;

    // return value of search: the index of the first match between the regular expression and the given string, or -1 if no match was found
    if (text.search(regex) != 0) {
        // tolgo suggerimento o errore precedente
        var parent = input.parentNode;
        parent.removeChild(parent.children[2]);

        setSuggestion(input, 1);
        input.focus();
        input.select();
        return false;
    }
    return true;
}

function checkForm() {
    var form = document.forms[0];
    var inputs = form.getElementsByTagName("input");

    for (var i = 0; i < inputs.length; i++) {
        if (!checkRegex(inputs[i])) {
            return false;
        }
    }
    return true;
}

/* 
* mode = 0, modalità input
* mode = 1, modalità errore 
*/
function setSuggestion(input, mode) {
    // tag con il suggerimento o l'errore
    var node;
    
    try {
        // padre dell'input
        var parent = input.parentNode;
        node = document.createElement("span");
        if (!mode) {
            node = document.createTextNode(formChecks[input.id][0]);
            // TODO: definire classe suggerimento
            node.className = "input-hint";
        } else {
            node = document.createTextNode(formChecks[input.id][2]);
            // TODO: definire classe errore
            node.className = "input-error";
        }
    } catch (TypeError) {
        node = document.createTextNode("Campo obbligatorio");
        // TODO: definire classe errore
        node.className = "input-error";
    }
}
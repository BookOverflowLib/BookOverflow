export function fillSuggestion(formChecks) {
    for (var id in formChecks) {
        var input = document.getElementById(id);
        setSuggestion(input, 0, formChecks);
        input.onblur = function () {
            checkRegex(this, formChecks);
        };

        if (id === "conferma") {
            input.onblur = function () {
                checkPassword("password", "conferma", formChecks);
            };
        }
    }
}

/* 
* mode = 0, modalità suggerimento
* mode = 1, modalità errore
* mode = 2, modalità input vuoto
* mode = 3, modalità password non corrispondente
*/
function setSuggestion(input, mode, formChecks) {
    var parent = input.parentNode;
    var newNode = document.createElement("p");
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
        case 3:
            newNode.textContent = "Le password non corrispondono";
            newNode.className = "input-error";
            break;
        default:
            break;
    }
    //parent.insertBefore(newNode, parent.lastChild); //TODO: maybe after is better?
    parent.insertBefore(newNode, parent.lastChild.previousSibling);
}

function checkRegex(input, formChecks) {
    var regex = formChecks[input.id][1];
    var text = input.value;
    var suggestionElement = document.getElementById(input.id + "-sugg");

    if (!input.value) {
        formChecks[input.id][3] = false;
        if (suggestionElement) {
            suggestionElement.remove();
        }
        setSuggestion(input, 2, formChecks);
        input.focus();
        input.select();
    } else if (text.search(regex) !== 0) {
        formChecks[input.id][3] = false;
        if (suggestionElement) {
            suggestionElement.remove();
        }
        setSuggestion(input, 1, formChecks);
        input.focus();
        input.select();
    } else if (!formChecks[input.id][3]) {
        formChecks[input.id][3] = true;
        if (suggestionElement) {
            suggestionElement.remove();
        }
    }
}

function checkPassword(passwordId, passwordConfId, formChecks) {
    var password = document.getElementById(passwordId);
    var conferma = document.getElementById(passwordConfId);

    if (password.value !== conferma.value) {
        if (document.getElementById("conferma-sugg")) {
            document.getElementById("conferma-sugg").remove();
        }
        setSuggestion(conferma, 3);
        formChecks["conferma"][3] = false;
    } else {
        if (document.getElementById("conferma-sugg")) {
            document.getElementById("conferma-sugg").remove();
        }
        formChecks["conferma"][3] = true;
    }
}

function saveInputValueSessionStorage(input) {
    sessionStorage.setItem(input.name, input.value);
}

function restoreInputValueSessionStorage(input) {
    var value = sessionStorage.getItem(input.name);
    if (value) {
        input.value = value;
    }
}

export function saveAllInputValues() {
    console.log("saving all input values");
    var inputs = [
        document.getElementById('nome'),
        document.getElementById('cognome'),
        document.getElementById('provincia'),
        document.getElementById('comune'),
        document.getElementById('username'),
        document.getElementById('email')
    ];

    for (var i = 0; i < inputs.length; i++) {
        saveInputValueSessionStorage(inputs[i]);
    }
}

export function restoreAllInputValues() {
    var inputs = [
        document.getElementById('nome'),
        document.getElementById('cognome'),
        document.getElementById('provincia'),
        document.getElementById('comune'),
        document.getElementById('username'),
        document.getElementById('email')
    ];

    for (var i = 0; i < inputs.length; i++) {
        restoreInputValueSessionStorage(inputs[i]);
    }
}

function clearInputValueSessionStorage() {
    var inputs = ['nome', 'cognome', 'provincia', 'comune', 'username', 'email'];

    inputs.forEach(function (input) {
        sessionStorage.removeItem(input);
    });
}

export function checkForm(formId, redirect, formChecks) {
    var form = document.getElementById(formId);
    var inputs = form.getElementsByTagName("input");

    for (var i = 0; i < inputs.length; i++) {
        if (inputs[i].hasAttribute("id") && !formChecks[inputs[i].id][3] && formChecks[inputs[i]]) {
            return false;
        }
    }

    clearInputValueSessionStorage();
    form.setAttribute("action", redirect);
    return true;
}
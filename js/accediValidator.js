import { checkForm, fillSuggestion } from './formValidator.js';

// [0]: hint
// [1]: regex
// [2]: error
// [3]: isValid
var formChecks = {
    identifier: [
        "Es: mariorossi o mariorossi@gmail.com",
        "*",
        "",
        false
    ],
    password: [
        "",
        /.*/,
        "",
        false
    ]
};

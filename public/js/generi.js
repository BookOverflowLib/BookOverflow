window.addEventListener('load', function () {
    let buttonsGenere = document.getElementsByClassName('button-genere');
    for (let i = 0; i < buttonsGenere.length; i++) {
        buttonsGenere[i].addEventListener('click', function () {
            toggleButton(buttonsGenere[i]);
        });
    }
});

function toggleButton(button) {
    var isAriaPressed = button.getAttribute('aria-pressed') === 'true';
    button.setAttribute('aria-pressed', isAriaPressed ? 'false' : 'true');
    button.classList.toggle('button-pressed');
    return isAriaPressed;
}

function resetButtons(){
    let buttonsGenere = document.getElementsByClassName('button-genere');
    for (let i = 0; i < buttonsGenere.length; i++) {
        buttonsGenere[i].setAttribute('aria-pressed', 'false');
        buttonsGenere[i].classList.remove('button-pressed');
    }
}
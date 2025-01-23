const MAX_TEXT_LENGTH = 700;

const descriptionText = document.getElementById('description-text');

const descrizioneCompleta = descriptionText.innerHTML;
let isCollapsed = false;

const buttonReadMore = document.createElement('button');
buttonReadMore.id = 'button-read-more';
buttonReadMore.classList.add('bold');
buttonReadMore.innerHTML = 'Leggi di più';

if(descriptionText.innerHTML.length > MAX_TEXT_LENGTH) {
    mostraDiMeno()
    descriptionText.insertAdjacentElement("afterend", buttonReadMore);
}

buttonReadMore.addEventListener('click', () => {
    isCollapsed? mostraDiPiu() : mostraDiMeno();
})

function mostraDiPiu() {
    isCollapsed = false;
    descriptionText.innerHTML = descrizioneCompleta;
    buttonReadMore.innerHTML = 'Leggi meno';
}

function mostraDiMeno() {
    isCollapsed = true;
    descriptionText.innerHTML = descriptionText.innerHTML.substring(0, MAX_TEXT_LENGTH) + '...';
    buttonReadMore.innerHTML = 'Leggi di più';
}
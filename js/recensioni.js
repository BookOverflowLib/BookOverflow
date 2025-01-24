document.addEventListener('DOMContentLoaded', () => {
    const recensioneDialog = document.getElementById('recensione-dialog');
    const openButtons = document.querySelectorAll('.button-recensione');
    const closeButton = document.getElementById('close-dialog');

    openButtons.forEach(button => {
        button.addEventListener('click', () => {
            const idScambio = button.parentNode.parentNode.id.split('-')[1];
            const datiUtente = button.parentNode.parentNode.querySelector('.dati-utente').children;
            const nome = datiUtente[0].textContent;
            const user = datiUtente[1].textContent.replace('@', '');
            document.querySelector('#recensione-dialog .nome-utente').textContent = nome;
            recensioneDialog.showModal();
        });
    });

    closeButton.addEventListener('click', () => {
        recensioneDialog.close();
    });
});
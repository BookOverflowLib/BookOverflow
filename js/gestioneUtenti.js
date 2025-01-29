const buttonsElimina = document.querySelectorAll('.elimina-utente');
const sureDialog = document.getElementById('sure-dialog');

let userClicked = '';
buttonsElimina.forEach(button => {
    button.addEventListener('click', () => {
        document.getElementById('form-username').value = button.dataset.username
        sureDialog.showModal()
    })
});

const confermaElimina = document.getElementById('conferma-elimina')
const closeButton = document.getElementById('close-dialog');
closeButton.addEventListener('click', () => {
    sureDialog.close();
});
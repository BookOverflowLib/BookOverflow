const buttonsElimina = document.querySelectorAll('.user-row .user-buttons .elimina-utente');
const sureDialog = document.getElementById('sure-dialog');

buttonsElimina.forEach(button => {
    button.addEventListener('click', () => {
        sureDialog.showModal()
    })
});

const closeButton = document.getElementById('close-dialog');
console.log(closeButton)
closeButton.addEventListener('click', () => {
    sureDialog.close();
});
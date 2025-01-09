let cerca = document.getElementById('cerca');

// let cercaButton = document.getElementById('cercaButton');

// cercaButton.addEventListener('click', function () {
//     fetch_books_API();
// });

// Suggerimenti di ricerca
var timeout;
cerca.onkeyup = function () {
    clearTimeout(timeout);
    timeout = setTimeout(fetch_books_API, 600);
};

let booksResults = [];
// sarebbe bello renderla più generica passando value come parametro ma poi non verrebbe applicato il delay da setTimeout; mettendo un parametro diventerebbe una chiamata diretta a fetch_books_API e quindi istantanea, infatti setTimeout richiede come parametro una funzione che verrà eseguita da lui
function fetch_books_API() {
    let url = 'https://www.googleapis.com/books/v1/volumes?q=' + cerca.value + '&maxResults=5';

    fetch(url)
        .then((response) => response.json())
        .then((data) => {
            let libri = data.items;
            let output = '';

            libri.forEach(function (libro) {
                let isbn = '';
                if (libro.volumeInfo.industryIdentifiers === undefined) return;
                if (libro.volumeInfo.industryIdentifiers[1] === undefined) {
                    if (libro.volumeInfo.industryIdentifiers[0] !== undefined) {
                        isbn = libro.volumeInfo.industryIdentifiers[0].identifier;
                    } else {
                        return;
                    }
                } else {
                    isbn = libro.volumeInfo.industryIdentifiers[1].identifier;
                }
                const titolo = libro.volumeInfo.title;
                const autore = libro.volumeInfo.authors;
                const immagine = libro.volumeInfo.imageLinks
                    ? libro.volumeInfo.imageLinks.thumbnail
                    : 'https://via.placeholder.com/128x200';
                const descrizione = libro.volumeInfo.description;
                const anno = libro.volumeInfo.publishedDate;
                const genere = libro.volumeInfo.categories;
                const lingua = libro.volumeInfo.language;
                const editore = libro.volumeInfo.publisher;
                output += `
                    <div class="search-results-row">
                        <input type="radio" name="search-result" value="" id="${isbn}" required/>
                        <label for="${isbn}">
                            <img src="${immagine}" width="50"/>
                            <div>
                                <p class="bold titolo">${titolo}</p>
                                <p class="italic autore">${autore}</p>
                            </div>
                        </label>
                    </div>
                `;
                booksResults.push({
                    isbn: isbn,
                    titolo: titolo,
                    autore: autore,
                    immagine: immagine,
                    descrizione: descrizione,
                    anno: anno,
                    genere: genere,
                    lingua: lingua,
                    editore: editore
                });
           });

            document.getElementById('book-results').innerHTML = output;
        });
}

// on select radio button
let selectedBook = {};
document.getElementById('book-results').addEventListener('change', function (e) {
    selectedBook = booksResults.find(book => book.isbn === e.target.id);
    document.getElementsByName('ISBN')[0].value = selectedBook.isbn; 
    document.getElementsByName('titolo')[0].value = selectedBook.titolo;
    document.getElementsByName('autore')[0].value = selectedBook.autore;
    document.getElementsByName('path_copertina')[0].value = selectedBook.immagine;
    document.getElementsByName('descrizione')[0].value = selectedBook.descrizione;
    document.getElementsByName('anno')[0].value = selectedBook.anno;
    document.getElementsByName('genere')[0].value = selectedBook.genere;
    document.getElementsByName('lingua')[0].value = selectedBook.lingua;
    document.getElementsByName('editore')[0].value = selectedBook.editore;

});


// ========= DIALOG =========
const dialog = document.getElementById('aggiungi-libro-dialog')
const showDialogButton = document.getElementById('aggiungi-libro-button')
showDialogButton.addEventListener('click', () => {
    dialog.showModal()
})

const closeDialog = document.getElementById('close-dialog')
closeDialog.addEventListener('click', () => {
    dialog.close()
})
// dialog.querySelector('button').addEventListener('click', () => {
// 	dialog.close();
// });
// // Modifica un libro
// const modificaButtons = document.querySelectorAll('.book-buttons button:first-child');
// modificaButtons.forEach(button => {
// 	button.addEventListener('click', () => {
// 		dialog.showModal();
// 	});
// });
// // Elimina un libro
// const eliminaButtons = document.querySelectorAll('.book-buttons button:last-child');
// eliminaButtons.forEach(button => {
// 	button.addEventListener('click', () => {
// 		// Elimina il libro
// 	});
// });
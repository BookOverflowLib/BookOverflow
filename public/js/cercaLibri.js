let cerca = document.getElementById('cerca');

// let cercaButton = document.getElementById('cercaButton');

// cercaButton.addEventListener('click', function () {
//     fetch_books_API();
// });


// Suggerimenti di ricerca
var timeout;
cerca.onkeyup = function () {
    clearTimeout(timeout);
    timeout = setTimeout(fetch_books_API, 1000);
};

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
                if(libro.volumeInfo.industryIdentifiers === undefined) return;
                if(libro.volumeInfo.industryIdentifiers[1] === undefined){
                    if(libro.volumeInfo.industryIdentifiers[0] !== undefined) {
                        isbn = libro.volumeInfo.industryIdentifiers[0].identifier;
                    }else{
                        return;
                    }
                }else{
                    isbn = libro.volumeInfo.industryIdentifiers[1].identifier;
                }
                const titolo = libro.volumeInfo.title;
                const autore = libro.volumeInfo.authors;
                const immagine = libro.volumeInfo.imageLinks
                    ? libro.volumeInfo.imageLinks.thumbnail
                    : 'https://via.placeholder.com/128x200';

                output += `
                    <div class="search-results-row">
                        <input type="radio" name="search-result" value="" id="${isbn}" />
                        <label for="${isbn}">
                            <img src="${immagine}" width="50"/>
                            <div>
                                <p class="bold titolo">${titolo}</p>
                                <p class="italic autore">${autore}</p>
                            </div>
                        </label>
                    </div>
                `;
            });

            document.getElementById('book-results').innerHTML = output;
        });
}

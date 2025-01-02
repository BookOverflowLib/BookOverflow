let cerca = document.getElementById("cerca");
let cercaButton = document.getElementById("cercaButton")

cercaButton.addEventListener('click', function () {
    fetch_books_API();
});

// Suggerimenti di ricerca
var timeout;
cerca.onkeyup = function () {
    clearTimeout(timeout);
    timeout = setTimeout(fetch_books_API, 1000);
}

// sarebbe bello renderla più generica passando value come parametro ma poi non verrebbe applicato il delay da setTimeout; mettendo un parametro diventerebbe una chiamata diretta a fetch_books_API e quindi istantanea, infatti setTimeout richiede come parametro una funzione che verrà eseguita da lui
function fetch_books_API() {
    let url = 'https://www.googleapis.com/books/v1/volumes?q=' + cerca.value;
    fetch(url)
        .then(response => response.json())
        .then(data => {
            let libri = data.items;
            let output = '';
            libri.forEach(function (libro) {
                if (libro.volumeInfo.imageLinks) {
                    output += `
                    <div class="libro">
                    <img src="${libro.volumeInfo.imageLinks ? libro.volumeInfo.imageLinks.thumbnail : 'https://via.placeholder.com/128x200'}">
                    <h4>${libro.volumeInfo.title}</h4>
                    <p>${libro.volumeInfo.authors}</p>
                    <p>${libro.volumeInfo.publishedDate}</p>
                    </div>
                    `;
                }
            });
            document.getElementById('results').innerHTML = output;
        })
}
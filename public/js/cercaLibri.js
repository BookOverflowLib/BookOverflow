let cerca = document.getElementById("cerca");
let cercaButton = document.getElementById("cercaButton")

cercaButton.addEventListener('click', function () {
    let value = cerca.value;
    let url = 'https://www.googleapis.com/books/v1/volumes?q=' + value;
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

});
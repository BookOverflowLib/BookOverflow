<?php
require_once '../src/model/dbAPI.php';

function getIsbnPopularBooksNYT($limit = 10): array
{
	$url = "https://api.nytimes.com/svc/books/v3/lists.json?list-name=hardcover-fiction";
	$key = "Mx5M5fEGnjdgXABoZ1rMXO9NQtorc0bk"; // SORRY FOR THAT :/
	$url = $url . "&api-key=" . $key;
	$ch2 = curl_init();
	curl_setopt($ch2, CURLOPT_URL, $url);
	curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
	$output = curl_exec($ch2);
	// Verifica errori
	if ($output === false) {
		echo "Errore nella richiesta: " . curl_error($ch2);
	}
	curl_close($ch2);
	$output = json_decode($output, true);
	$results = $output['results'];
	$isbn = array();
	for ($i = 0; $i < $limit; $i++) {
		if (isset($results[$i]['isbns'][0]['isbn13']))
			$isbn[$i] = $results[$i]['isbns'][0]['isbn13'];
	}
	return $isbn;
}

function getGoogleBooksInfo($isbn)
{
	$googleUrl = "https://www.googleapis.com/books/v1/volumes?q=isbn:";
	$bookUrl = $googleUrl . $isbn;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $bookUrl);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($ch);
	// Verifica errori
	if ($response === false) {
		echo "Errore nella richiesta: " . curl_error($ch);
	}
	curl_close($ch);
	$response = json_decode($response, true);
	$bookData = array();
	if (isset($response['items'][0]['volumeInfo']['title'])) {
		$bookData['isbn'] = $isbn;
		$bookData['titolo'] = $response['items'][0]['volumeInfo']['title'];
		$bookData['autore'] = $response['items'][0]['volumeInfo']['authors'][0]; //solo un autore
		if (isset($response['items'][0]['volumeInfo']['publisher'])) {
			$bookData['editore'] = $response['items'][0]['volumeInfo']['publisher'];
		} else {
			$bookData['editore'] = 'N/A';
		}
		$bookData['anno'] = $response['items'][0]['volumeInfo']['publishedDate'];
		if (str_contains($bookData['anno'], '-')) {
			$bookData['anno'] = explode('-', $bookData['anno'])[0];
		}
		$bookData['genere'] = $response['items'][0]['volumeInfo']['categories'][0]; //solo il primo genere
		$bookData['descrizione'] = $response['items'][0]['volumeInfo']['description'];
		$bookData['lingua'] = $response['items'][0]['volumeInfo']['language'];
		$bookData['path_copertina'] = $response['items'][0]['volumeInfo']['imageLinks']['thumbnail'];
	}
	return $bookData;
}

function showBooksInfo()
{
	$isbn = getIsbnPopularBooksNYT();
	$string = '';
	foreach ($isbn as $key => $value) {
		if ($value == null) {
			continue;
		} else {
			$bookData = getGoogleBooksInfo($value);
		}
		$string .= '<pre>' . print_r($bookData, true) . '</pre>';
	}
	return $string;
}

function insert_NYT_books()
{
	$isbn = getIsbnPopularBooksNYT();
	$db = new DBAccess();
	foreach ($isbn as $key => $value) {
		if ($value) {
			$bookData = getGoogleBooksInfo($value);
			$db->insert_new_book(isbn: $bookData['isbn'], titolo: $bookData['titolo'], autore: $bookData['autore'], editore: $bookData['editore'], anno: $bookData['anno'], genere: $bookData['genere'], descrizione: $bookData['descrizione'], lingua: $bookData['lingua'], path_copertina: $bookData['path_copertina']);
		}
	}
}

/*

	// GOOGLE PART
	$books = '<div class="carosello-libri">';
	$googleUrl = "https://www.googleapis.com/books/v1/volumes?q=";
	$ch2 = curl_init();
	foreach ($isbn as $key => $value) {
		$bookUrl = $googleUrl . $value;
		curl_setopt($ch2, CURLOPT_URL, $bookUrl);
		curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch2);
		// Verifica errori
		if ($response === false) {
			echo "Errore nella richiesta: " . curl_error($ch2);
		}
		$response = json_decode($response, true);
		if (isset($response['items'][0]['volumeInfo']['title'])) {
			$books .= '<div class="libro">';
			$imgBook = str_replace('&edge=curl', '', $response['items'][0]['volumeInfo']['imageLinks']['thumbnail']);
			$books .= '<img src="' . $imgBook . '" width="150" alt="" />';
			$books .= '<p class="titolo-libro">' . $response['items'][0]['volumeInfo']['title'] . '</p>';
			$books .= '<p class="autore-libro">' . $response['items'][0]['volumeInfo']['authors'][0] . '</p>';
			$books .= '</div>';
		}
	}
	curl_close($ch2);
	$books .= '</div>';

	return $books;
 *
 *
 *
 <div class="libro">
				<img
					alt=""
					src="../assets/imgs/artedicorrere.avif"
					width="150" />
				<p class="titolo-libro">L'arte di correre</p>
				<p class="autore-libro">Haruki Murakami</p>
			</div>
 */
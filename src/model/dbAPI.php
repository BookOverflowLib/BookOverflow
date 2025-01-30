<?php

require __DIR__ . '/exceptions.php';

use CustomExceptions\{
	EmailAlreadyExistsException,
	GenericCustomException,
	GenericRegistrationException,
	IncorrectCredentialsException,
	UsernameAlreadyExistsException,
	InvalidProvinciaException,
	InvalidComuneException
};

class DBAccess
{
	private $connection;

	public function __construct()
	{
		try {
			$this->load_env();
			$this->open_connection();
		} catch (Exception $e) {
			$_SESSION['error'] = "Errore durante la connessione al database";
		}
	}

	public function __destruct()
	{
		$this->close_connection();
	}

	private function load_env(): void
	{
		$env_path = __DIR__ . '/../../.env';
		if (!file_exists($env_path)) {
			throw new Exception('.env file not found');
		}

		$env = parse_ini_file($env_path);
		if ($env === false) {
			throw new Exception('Error parsing .env file');
		}

		foreach ($env as $key => $value) {
			if (!array_key_exists($key, $_ENV)) {
				$_ENV[$key] = $value;
			}
		}
	}

	public function open_connection(): bool
	{
		// turn on the errors | convert those errors into exceptions
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

		try {
			$this->connection = new mysqli(
				$_ENV['DB_HOST'],
				$_ENV['DB_USERNAME'],
				$_ENV['DB_PASSWORD'],
				$_ENV['DB_DATABASE']
			);
		} catch (mysqli_sql_exception $e) {
			throw new Exception("Connection error: " . $e->getMessage());
		}


		// errors check in production
		if (mysqli_connect_errno()) {
			return false;
		} else {
			return true;
		}
	}

	public function close_connection(): void
	{
		if ($this->connection) {
			mysqli_close($this->connection);
			$this->connection = null;
		}
	}

	public function ensure_connection(): void
	{
		if (!$this->connection) {
			$this->open_connection();
		}
	}

	/**
	 * Prepares an SQL statement for execution.
	 *
	 * This function ensures that a connection to the database is established,
	 * prepares the SQL query, and binds the parameters if provided.
	 *
	 * @param string $query The SQL query to be prepared.
	 * @param string|null $types A string that contains one or more characters which specify the types for the corresponding bind variables:
	 *                           'i' for integer, 'd' for double, 's' for string, and 'b' for blob. Default is null.
	 * @param array|null $params An array of variables to bind to the SQL statement. Default is null.
	 *
	 * @return mysqli_stmt The prepared statement.
	 *
	 * @throws Exception If the statement preparation or parameter binding fails.
	 */
	private function prepare_sql_statement($query, $types = null, $params = null): mysqli_stmt
	{
		$this->ensure_connection();

		$stmt = $this->connection->prepare($query);
		if (!$stmt) {
			throw new Exception("Prepare failed: " . $this->connection->error);
		}

		if ($types && $params) {
			// there must be the same number of types and parameters
			// e.g types = "iss", params = [1, "hello", 3.14]
			if (strlen($types) !== count($params)) {
				error_log("prepare_sql_statement: Number of types does not match number of parameters");
				throw new Exception("Error preparing query");
			}

			// Bind parameters dynamically
			if (!$stmt->bind_param($types, ...$params)) {
				error_log("prepare_sql_statement: Parameter binding failed: " . $stmt->error);
				throw new Exception("Error preparing query");
			}
		}

		return $stmt;
	}

	/**
	 * Executes an SQL query and returns the results as an associative array.
	 *
	 * @param string $query The SQL query to be executed.
	 * @param string|null $types Optional. A string that contains one or more characters which specify the types for the corresponding bind variables.
	 * @param array|null $params Optional. An array of variables to bind to the SQL statement.
	 *
	 * @return array|null The results of the query as an associative array, or null if the query fails.
	 *
	 * @throws Exception If the query execution fails.
	 */
	public function query_to_array($query, $types = null, $params = null): ?array
	{
		try {
			$stmt = $this->prepare_sql_statement($query, $types, $params);

			if (!$stmt->execute()) {
				throw new Exception("Query execution failed");
			}

			$results = $this->query_results_to_array($stmt->get_result());
			$stmt->close();
			return $results;
		} catch (Exception $e) {
			error_log("execute_select_query: " . $e->getMessage());
			error_log("execute_select_query: QUERY=" . $query . "; TYPES= " . $types . "; PARAMS=" .
				(is_array($params) ? implode(", ", $params) : (string) $params));
			throw $e;
		}
	}

	/**
	 * Executes an SQL query that does not return a result set.
	 *
	 * @param string $query The SQL query to be executed.
	 * @param string|null $types (Optional) A string that contains one or more characters which specify the types for the corresponding bind variables.
	 * @param array|null $params (Optional) An array of variables to bind to the query.
	 *
	 * @throws Exception If the query execution fails.
	 *
	 * This method prepares and executes a SQL statement. If the execution fails, it logs the error and rethrows the exception.
	 */
	public function void_query($query, $types = null, $params = null): void
	{
		try {
			$stmt = $this->prepare_sql_statement($query, $types, $params);
			if (!$stmt->execute()) {
				throw new Exception("Query execution failed");
			}
			$stmt->close();
		} catch (Exception $e) {
			error_log("void_query: " . $e->getMessage());
			error_log("void_query: QUERY=" . $query . "; TYPES= " . $types . "; PARAMS=" . implode(", ", $params));
			throw $e;
		}
	}


	/**
	 * Converts the result of a MySQL query to an associative array.
	 *
	 * @param mysqli_result $queryRes The result set from a MySQL query.
	 * @return array An associative array of the query results
	 */
	private function query_results_to_array($queryRes): ?array
	{
		$res = array();
		while ($row = mysqli_fetch_assoc($queryRes)) {
			array_push($res, $row);
		}
		$queryRes->free();
		return $res;
	}

	public function get_province(): ?array
	{
		$this->ensure_connection();
		$query = "SELECT p.id, p.nome, r.nome as regione FROM province p JOIN regioni r ON p.id_regione = r.id ORDER BY p.nome";
		return $this->query_to_array($query);
	}

	public function get_comune_by_provincia($idProvincia): ?array
	{
		$query = "SELECT * FROM comuni WHERE id_provincia = ?";
		try {
			return $this->query_to_array($query, "s", [$idProvincia]);
		} catch (Exception $e) {
			echo $e->getMessage();
		}
		return null;
	}

	private function check_exists_finalize($query, $identifier): bool
	{
		try {
			$stmt = $this->prepare_sql_statement($query, "s", [$identifier]);
			if (!$stmt->execute()) {
				throw new GenericRegistrationException();
			}
			return $stmt->get_result()->fetch_row()[0];
		} catch (Exception $e) {
			error_log("check_exists: " . $e->getMessage());
		} finally {
			if ($stmt)
				$stmt->close();
		}
		return false;
	}

	// https://stackoverflow.com/a/7171075
	// The 1 or * in the EXISTS is ignored
	public function check_username_exists($username): void
	{
		$query = "SELECT EXISTS ( SELECT * FROM Utente WHERE username = ? LIMIT 1)";
		if ($this->check_exists_finalize($query, $username)) {
			throw new UsernameAlreadyExistsException();
		}
	}

	public function check_email_exists($email): void
	{
		$query = "SELECT EXISTS ( SELECT * FROM Utente WHERE email = ? LIMIT 1)";
		if ($this->check_exists_finalize($query, $email)) {
			throw new EmailAlreadyExistsException();
		}
	}

	function check_provincia_exists($idProvincia): void
	{
		$query = "SELECT EXISTS ( SELECT * FROM province WHERE id = ? LIMIT 1)";
		if (!$this->check_exists_finalize($query, $idProvincia)) {
			throw new InvalidProvinciaException();
		}
	}

	function check_comune_exists($idComune): void
	{
		$query = "SELECT EXISTS ( SELECT * FROM comuni WHERE id = ? LIMIT 1)";
		if (!$this->check_exists_finalize($query, $idComune)) {
			throw new InvalidComuneException();
		}
	}

	public function register_user($nome, $cognome, $provincia, $comune, $email, $username, $password, $profileImg = null): void
	{
		try {
			$this->check_username_exists($username);
			$this->check_email_exists($email);
			$this->check_provincia_exists($provincia);
			$this->check_comune_exists($comune);
			$passwordHashed = password_hash($password, PASSWORD_BCRYPT);
			$query = "INSERT INTO Utente (email, password_hash, username, nome, cognome, provincia, comune, path_immagine) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

			$this->void_query($query, "ssssssss", [$email, $passwordHashed, $username, $nome, $cognome, $provincia, $comune, $profileImg]);
		} catch (Exception $e) {
			throw $e;
		}
	}

	public function login_user($identifier, $password): ?array
	{
		$query = "SELECT * FROM Utente WHERE email = ? OR username = ?";
		try {
			$res = $this->query_to_array($query, "ss", [$identifier, $identifier]);
			if ($res) {
				if (password_verify($password, $res[0]['password_hash'])) {
					return $res;
				} else {
					throw new IncorrectCredentialsException();
				}
			} else {
				throw new IncorrectCredentialsException();
			}
		} catch (Exception $e) {
			throw $e;
		}
	}

	public function delete_user($user): void
	{
		$query = "DELETE FROM Utente WHERE username = ?";
		try {
			$this->void_query($query, "s", [$user]);
		} catch (Exception $e) {
			error_log("delete_user: " . $e->getMessage());
			throw $e;
		}
	}

	public function get_user_rating_by_email($email): ?array
	{
		$query = 'SELECT R.emailRecensito, AVG(R.valutazione) AS media_valutazioni 
                    FROM Recensione R 
                    WHERE R.emailRecensito = ?
					GROUP BY R.emailRecensito';
		try {
			return $this->query_to_array($query, "s", [$email]);
		} catch (Exception $e) {
			error_log("get_user_rating_by_email: " . $e->getMessage());
		}
		return null;
	}

	public function get_user_by_identifier($identifier): ?array
	{
		$query = "SELECT * FROM Utente WHERE email = ? OR username = ?";
		try {
			return $this->query_to_array($query, "ss", [$identifier, $identifier]);
		} catch (Exception $e) {
			error_log("get_user_by_identifier: " . $e->getMessage());
		}
		return null;
	}

	public function get_comune_provincia_sigla_by_ids($idComune, $idProvincia): ?array
	{
		$queryProvincia = "SELECT nome, sigla FROM province WHERE id = ?";
		$queryComune = "SELECT nome FROM comuni WHERE id = ?";
		try {
			$prov = $this->query_to_array($queryProvincia, "i", [$idProvincia]);
			$comu = $this->query_to_array($queryComune, "i", [$idComune]);
			return array("provincia" => $prov[0]['nome'], "provincia_sigla" => $prov[0]['sigla'], "comune" => $comu[0]['nome']);
		} catch (Exception $e) {
			error_log("get_provincia_comune_by_ids: " . $e->getMessage());
		}
		return null;
	}

	public function insert_new_book($isbn, $titolo, $autore, $editore, $anno, $genere, $descrizione, $lingua, $path_copertina): void
	{
		if (empty(trim($isbn))) {
			throw new GenericCustomException("ISBN non valido");
		}
		$path_copertina = str_replace("&edge=curl", "", $path_copertina);
		$path_copertina = str_replace("http", "https", $path_copertina);
		$path_copertina .= "&fife=w328";

		$query = "INSERT IGNORE INTO Libro (ISBN, titolo, autore, editore, anno, genere, descrizione, lingua, path_copertina) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
		try {
			$this->void_query($query, "sssssssss", [$isbn, $titolo, $autore, $editore, $anno, $genere, $descrizione, $lingua, $path_copertina]);
		} catch (Exception $e) {
			throw $e;
		}
	}

	/**
	 * Aggiorna i generi preferiti dell'utente
	 * @param string $user username dell'utente
	 * @param string $generi generi preferiti dell'utente in formato JSON
	 *
	 * @return bool true se l'aggiornamento è andato a buon fine, false altrimenti
	 */
	public function update_user_generi($user, $generi): void
	{
		$query = "UPDATE Utente SET generi_preferiti = ? WHERE username = ?";
		try {
			$this->void_query($query, "ss", [$generi, $user]);
		} catch (Exception $e) {
			error_log("update_user_generi: " . $e->getMessage());
			throw $e;
		}
	}

	/**
	 * Ottiene i generi preferiti dell'utente
	 * @param string $user username dell'utente
	 * @return array|null
	 */
	public function get_generi_by_username($user): ?array
	{
		$query = "SELECT generi_preferiti FROM Utente WHERE username = ?";
		try {
			return $this->query_to_array($query, "s", [$user]);
		} catch (Exception $e) {
			error_log("get_generi_by_username: " . $e->getMessage());
			throw $e;
		}
	}

	private function get_user_email_by_username($username): ?string
	{
		$query = "SELECT email FROM Utente WHERE username = ?";
		try {
			$email = $this->query_to_array($query, "s", [$username]);
			return $email ? $email[0]['email'] : null;
		} catch (Exception $e) {
			error_log("get_user_email_by_username: " . $e->getMessage());
			throw $e;
		}
	}

	public function get_libri_offerti_by_username($user): ?array
	{
		try {
			$userEmail = $this->get_user_email_by_username($user);

			$query = <<<SQL
			SELECT L.ISBN, L.titolo, L.autore, L.editore, L.anno, L.genere, L.descrizione, L.lingua, L.path_copertina, C.condizioni, C.disponibile
			FROM Copia C JOIN Libro L ON C.ISBN = L.ISBN
			WHERE C.proprietario = ? 
			ORDER BY C.disponibile DESC;
			SQL;

			return $this->query_to_array($query, "s", [$userEmail]);
		} catch (Exception $e) {
			error_log("get_libri_offerti_by_username: " . $e->getMessage());
			throw $e;
		}
	}

	public function insert_libri_offerti_by_username($user, $isbn, $condizione): void
	{
		try {
			$userEmail = $this->get_user_email_by_username($user);
			$query = <<<SQL
			INSERT INTO Copia (ISBN, proprietario, condizioni)
			VALUES (?, ?, ?)
			SQL;

			$this->void_query($query, "sss", [$isbn, $userEmail, $condizione]);
		} catch (Exception $e) {
			error_log("insert_libri_offerti_by_username: " . $e->getMessage());
			throw $e;
		}
	}

	public function delete_libro_offerto($user, $isbn): void
	{
		try {
			$userEmail = $this->get_user_email_by_username($user);
			$query = <<<SQL
			DELETE FROM Copia
			WHERE ISBN = ? AND proprietario = ?
			SQL;

			$this->void_query($query, "ss", [$isbn, $userEmail]);
		} catch (Exception $e) {
			error_log("delete_libro_offerto: " . $e->getMessage());
			throw $e;
		}
	}

	public function get_libri_desiderati_by_username($user): ?array
	{
		try {
			$userEmail = $this->get_user_email_by_username($user);

			$query = <<<SQL
			SELECT L.ISBN, L.titolo, L.autore, L.editore, L.anno, L.genere, L.descrizione, L.lingua, L.path_copertina
			FROM Desiderio D JOIN Libro L ON D.ISBN = L.ISBN
			WHERE D.email = ?
			SQL;

			return $this->query_to_array($query, "s", [$userEmail]);
		} catch (Exception $e) {
			error_log("get_libri_desiderati_by_username: " . $e->getMessage());
			return null;
		}
	}

	public function insert_libri_desiderati_by_username($user, $isbn): void
	{
		try {
			$userEmail = $this->get_user_email_by_username($user);
			$query = <<<SQL
			INSERT INTO Desiderio (email, ISBN)
			VALUES (?, ?)
			SQL;

			$this->void_query($query, "ss", [$userEmail, $isbn]);
		} catch (Exception $e) {
			error_log("insert_libri_desiderati_by_username: " . $e->getMessage());
			throw $e;
		}
	}

	public function delete_libro_desiderato($user, $isbn): void
	{
		try {
			$userEmail = $this->get_user_email_by_username($user);
			$query = <<<SQL
			DELETE FROM Desiderio
			WHERE ISBN = ? AND email = ?
			SQL;

			$this->void_query($query, "ss", [$isbn, $userEmail]);
		} catch (Exception $e) {
			error_log("delete_libro_desiderato: " . $e->getMessage());
			throw $e;
		}
	}

	/**
	 * Ottiene i libri con quel ISBN
	 * @param string $ISBN isbn del libro
	 * @return array|null
	 */
	public function get_book_by_ISBN(string $ISBN): ?array
	{
		$query = "SELECT * FROM Libro WHERE ISBN = ?";
		try {
			return $this->query_to_array($query, "s", [$ISBN]);
		} catch (Exception $e) {
			error_log("get_book_by_ISBN: " . $e->getMessage());
			throw $e;
		}
	}

	public function get_users_interested_in_user_books($user): ?array
	{
		$query = <<<SQL
		SELECT DISTINCT U.email, U.nome, U.cognome, U.username, U.path_immagine, U.provincia, U.comune
		FROM Desiderio D JOIN Utente U ON D.email = U.email
		WHERE D.ISBN IN (
			SELECT C.ISBN
			FROM Copia C
			WHERE C.proprietario = ?
			AND C.disponibile = TRUE
		)
		SQL;

		try {
			return $this->query_to_array($query, "s", [$user]);
		} catch (Exception $e) {
			error_log("get_users_desirous_your_books_by_user: " . $e->getMessage());
			throw $e;
		}
	}

	// U è l'utente "destinazione" della richiesta
	// C è la sua copia, da scambiare con la nostra
	// D sono i desideri dell'utente destnatario, 
	// il sub-select controlla che tra i suoi desiderati ci siano libri miei
	public function get_users_with_that_book_and_interested_in_my_books($username, $isbnLibro): ?array
	{
		$query = <<<SQL
		SELECT DISTINCT U.email, U.nome, U.cognome, U.username, U.path_immagine, U.provincia, U.comune, C.ID AS id_copia
		FROM Utente U
		JOIN Copia C ON U.email = C.proprietario
		JOIN Desiderio D ON U.email = D.email
		WHERE C.ISBN = ? 
		AND C.disponibile = TRUE 
		AND C.proprietario != ?
		AND D.ISBN IN (
		    SELECT C2.ISBN
		    FROM Copia C2
		    WHERE C2.proprietario = ?
			AND C2.disponibile = TRUE
		);
		SQL;

		try {
			$user_email = $this->get_user_email_by_username($username);
			return $this->query_to_array($query, "sss", [$isbnLibro, $user_email, $user_email]);
		} catch (Exception $e) {
			error_log("get_users_with_book_and_interested_in_my_books: " . $e->getMessage());
			throw $e;
		}
	}

	public function get_desiderati_che_offro($userOfferente, $userDesiderante): ?array
	{
		$query = <<<SQL
		SELECT L.ISBN, L.titolo, L.autore, L.editore, L.anno, L.genere, L.descrizione, L.lingua, L.path_copertina, C.ID AS id_copia
		FROM Copia C JOIN Libro L ON C.ISBN = L.ISBN
		WHERE C.proprietario = ? 
		AND C.disponibile = TRUE
		AND C.ISBN IN (
			SELECT D.ISBN
			FROM Desiderio D
			WHERE D.email = ?
		)
		SQL;

		try {
			$user_email_off = $this->get_user_email_by_username($userOfferente);
			$user_email_des = $this->get_user_email_by_username($userDesiderante);
			return $this->query_to_array($query, "ss", [$user_email_off, $user_email_des]);
		} catch (Exception $e) {
			error_log("get_desiderati_che_offro: " . $e->getMessage());
			throw $e;
		}
	}

	public function get_id_copia_by_user_libro($user, $isbn): ?array
	{
		$query = <<<SQL
		SELECT C.ID
		FROM Copia C
		WHERE C.proprietario = ? AND C.ISBN = ?
		SQL;

		try {
			$user_email = $this->get_user_email_by_username($user);
			return $this->query_to_array($query, "ss", [$user_email, $isbn]);
		} catch (Exception $e) {
			error_log("get_id_copia_by_user_libro: " . $e->getMessage());
			throw $e;
		}
	}

	/* 
	 * Controlla che non ci sia un'altro scambio in attesa con gli stessi libri
	 */
	public function check_scambio_proposto($user_prop, $user_acc, $isbn_prop, $isbn_acc): bool
	{
		$query = <<<SQL
		SELECT * FROM Scambio S
		WHERE S.emailProponente = ? AND S.emailAccettatore = ? AND S.idCopiaProp = ? AND S.idCopiaAcc = ? AND S.stato = 'in attesa'
		SQL;

		try {
			$user_email_prop = $this->get_user_email_by_username($user_prop);
			$user_email_acc = $this->get_user_email_by_username($user_acc);
			$id_copia_prop = $this->get_id_copia_by_user_libro($user_prop, $isbn_prop)[0]['ID'];
			$id_copia_acc = $this->get_id_copia_by_user_libro($user_acc, $isbn_acc)[0]['ID'];

			$res = $this->query_to_array($query, "ssii", [$user_email_prop, $user_email_acc, $id_copia_prop, $id_copia_acc]);
			return count($res) > 0;
		} catch (Exception $e) {
			error_log("check_scambio_proposto: " . $e->getMessage());
			throw $e;
		}
	}

	/*
	 * Casi da escludere: 
	 * - Scambi con se stessi
	 * - Scambi già proposti -> stato: in attesa
	 */
	public function insert_scambio($user_prop, $user_acc, $isbn_prop, $isbn_acc): void
	{
		if ($user_prop === $user_acc) {
			throw new GenericCustomException("non è consentito proporre uno scambio con se stessi");
		}
		if ($this->check_scambio_proposto($user_prop, $user_acc, $isbn_prop, $isbn_acc)) {
			throw new GenericCustomException("scambio già proposto");
		}
		$query = "INSERT INTO Scambio (emailProponente, emailAccettatore, idCopiaProp, idCopiaAcc) VALUES (?, ?, ?, ?)";
		try {
			$user_email_prop = $this->get_user_email_by_username($user_prop);
			$user_email_acc = $this->get_user_email_by_username($user_acc);
			$id_copia_prop = $this->get_id_copia_by_user_libro($user_prop, $isbn_prop)[0]['ID'];
			$id_copia_acc = $this->get_id_copia_by_user_libro($user_acc, $isbn_acc)[0]['ID'];

			$this->void_query($query, "ssii", [$user_email_prop, $user_email_acc, $id_copia_prop, $id_copia_acc]);
		} catch (Exception $e) {
			error_log("insert_scambio: " . $e->getMessage());
			throw $e;
		}
	}

	public function get_scambi_by_user($user): ?array
	{
		$query = <<<SQL
		SELECT * FROM Scambio S
		WHERE S.emailProponente = ? OR S.emailAccettatore = ?
		ORDER BY S.ID DESC;
		SQL;

		try {
			$user_email = $this->get_user_email_by_username($user);
			return $this->query_to_array($query, "ss", [$user_email, $user_email]);
		} catch (Exception $e) {
			error_log("get_scambi_by_user: " . $e->getMessage());
			throw $e;
		}
	}

	public function get_copia_by_id($id): ?array
	{
		$query = "SELECT * FROM Copia c JOIN Libro l ON c.ISBN = l.ISBN WHERE c.ID = ?";
		try {
			return $this->query_to_array($query, "i", [$id]);
		} catch (Exception $e) {
			error_log("get_copia_by_id: " . $e->getMessage());
			throw $e;
		}
	}

	public function remove_scambio_by_id($id): void
	{
		$query = "DELETE FROM Scambio WHERE ID = ?";
		try {
			$this->void_query($query, "i", [$id]);
		} catch (Exception $e) {
			error_log("remove_scambio_by_id: " . $e->getMessage());
			throw $e;
		}
	}

	public function set_books_unavailable_by_idscambio($id): void
	{
		$query = <<<SQL
		UPDATE Copia C
		JOIN Scambio S ON C.ID = S.idCopiaProp OR C.ID = S.idCopiaAcc
		SET C.disponibile = FALSE
		WHERE S.ID = ?
		SQL;

		try {
			$this->void_query($query, "i", [$id]);
		} catch (Exception $e) {
			error_log("set_books_unavailable_by_idscambio: " . $e->getMessage());
			throw new GenericCustomException("Errore: scambio non accettato");
		}
	}

	public function accetta_scambio_by_id($id): void
	{
		$query = "UPDATE Scambio SET stato = 'accettato' WHERE ID = ?";
		try {
			$this->void_query($query, "i", [$id]);
			$this->set_books_unavailable_by_idscambio($id);
		} catch (Exception $e) {
			error_log("accetta_scambio_by_id: " . $e->getMessage());
			throw new GenericCustomException("Errore: scambio non accettato");
		}
	}

	public function rifiuta_scambio_by_id($id): void
	{
		$query = "UPDATE Scambio SET stato = 'rifiutato' WHERE ID = ?";
		try {
			$this->void_query($query, "i", [$id]);
		} catch (Exception $e) {
			error_log("rifiuta_scambio_by_id: " . $e->getMessage());
			throw $e;
		}
	}

	public function get_match_per_te_by_user($username): ?array
	{
		$query = <<<SQL
		SELECT DISTINCT L.ISBN, L.titolo, L.autore, L.editore, L.anno, L.genere, L.descrizione, L.lingua, L.path_copertina
		FROM Utente U
		JOIN Copia C ON U.email = C.proprietario
		JOIN Desiderio D ON U.email = D.email
		JOIN Libro L ON C.ISBN = L.ISBN
		WHERE C.disponibile = TRUE
		AND D.ISBN IN (
		    SELECT C2.ISBN
		    FROM Copia C2 
		    WHERE C2.proprietario = ? 
			AND C2.proprietario != U.email
			AND C2.disponibile = TRUE
		) AND C.ISBN IN (
		    SELECT D2.ISBN
		    FROM Desiderio D2
		    WHERE D2.email = ? 
		)
		SQL;

		try {
			$user_email = $this->get_user_email_by_username($username);
			return $this->query_to_array($query, "ss", [$user_email, $user_email]);
		} catch (Exception $e) {
			error_log("get_match_per_te_by_user: " . $e->getMessage());
			throw $e;
		}
	}

	public function get_potrebbe_piacerti_by_user($username): ?array
	{
		$query = <<<SQL
		SELECT DISTINCT L.ISBN, L.titolo, L.autore, L.editore, L.anno, L.genere, L.descrizione, L.lingua, L.path_copertina
		FROM Utente U
		JOIN Copia C ON U.email = C.proprietario
		JOIN Desiderio D ON U.email = D.email
		JOIN Libro L ON C.ISBN = L.ISBN
		JOIN Libro L2 ON D.ISBN = L2.ISBN
		WHERE C.disponibile = TRUE
		AND D.ISBN IN (
		    SELECT C2.ISBN
		    FROM Copia C2
		    WHERE C2.proprietario = ? 
			AND C2.proprietario != U.email
			AND C2.disponibile = TRUE
		) AND C.ISBN NOT IN (
		    SELECT D2.ISBN
		    FROM Desiderio D2
		    WHERE D2.email = ?
		)
		SQL;

		try {
			$user_email = $this->get_user_email_by_username($username);
			return $this->query_to_array($query, "ss", [$user_email, $user_email]);
		} catch (Exception $e) {
			error_log("get_match_per_te_by_user: " . $e->getMessage());
			throw $e;
		}
	}

	function get_piu_scambiati(): ?array
	{
		$query = <<<SQL
		SELECT L.ISBN, L.titolo, L.autore, L.path_copertina, COUNT(*) AS numero_scambi		
		FROM (		    
			SELECT l.ISBN, l.titolo, l.autore, l.path_copertina, s.ID
			FROM Scambio s 
			JOIN Copia c ON s.idCopiaProp = c.ID
			JOIN Libro l ON c.ISBN = l.ISBN
			WHERE s.stato = 'accettato'
			
			UNION ALL 
			
			SELECT l.ISBN, l.titolo, l.autore, l.path_copertina, s.ID
			FROM Scambio s
			JOIN Copia c ON s.idCopiaAcc = c.ID
			JOIN Libro l ON c.ISBN = l.ISBN
			WHERE s.stato = 'accettato'
		) AS L
		GROUP BY L.ISBN
		ORDER BY L.ID DESC
		SQL;

		try {
			return $this->query_to_array($query);
		} catch (Exception $e) {
			error_log("get_piu_scambiati: " . $e->getMessage());
			throw $e;
		}
	}

	public function get_book_title_by_ISBN($isbn): ?array
	{
		$query = "SELECT titolo FROM Libro WHERE ISBN = ?";
		try {
			return $this->query_to_array($query, "s", [$isbn]);
		} catch (Exception $e) {
			error_log("get_book_title_by_ISBN: " . $e->getMessage());
			throw $e;
		}
	}

	public function search_books($searchInput)
	{
		$query = <<<SQL
		SELECT * FROM Libro
		WHERE titolo LIKE ? OR autore LIKE ? OR genere LIKE ?
		SQL;

		$queryEmpty = <<<SQL
		SELECT * FROM Libro
		SQL;

		try {
			if (empty($searchInput)) {
				return $this->query_to_array($queryEmpty);
			} else {
				return $this->query_to_array($query, "sss", ["%$searchInput%", "%$searchInput%", "%$searchInput%"]);
			}
		} catch (Exception $e) {
			error_log("search_books: " . $e->getMessage());
			throw $e;
		}
	}

	public function get_books_by_preferences($user)
	{
		$query = <<<SQL
		SELECT UNIQUE(L.ISBN) AS ISBN, L.titolo AS titolo, L.autore AS autore, L.editore AS editore, L.anno AS anno, L.genere AS genere, L.descrizione AS descrizione, L.lingua AS lingua, L.path_copertina AS path_copertina
		FROM Libro L
		JOIN Copia C ON L.ISBN = C.ISBN
		WHERE genere IN (?) AND C.disponibile = TRUE
		SQL;

		try {
			$generi = $this->get_generi_by_username($user);
			$generiString = $generi[0]['generi_preferiti'];
			$generiString = str_replace(['[', ']'], '', $generiString);

			$query = str_replace("?", $generiString, $query);
			$stmt = $this->connection->prepare($query);
			$stmt->execute();

			$stmt->bind_result($ISBN, $titolo, $autore, $editore, $anno, $genere, $descrizione, $lingua, $path_copertina);

			$resArray = [];
			while ($stmt->fetch()) {
				$resArray[] = [
					"ISBN" => $ISBN,
					"titolo" => $titolo,
					"autore" => $autore,
					"editore" => $editore,
					"anno" => $anno,
					"genere" => $genere,
					"descrizione" => $descrizione,
					"lingua" => $lingua,
					"path_copertina" => $path_copertina
				];
			}

			return $resArray;
		} catch (Exception $e) {
			error_log("get_book_by_preferences: " . $e->getMessage());
			throw $e;
		}
	}

	public function get_review_by_user($user): ?array
	{
		$query = <<<SQL
		SELECT R.valutazione AS valutazione, R.contenuto AS contenuto, 
		R.dataPubblicazione AS dataPubblicazione, U.username AS recensito, 
		U2.username AS recensore, U2.path_immagine as immagine_recensore, 
		S.ID AS idScambio
		FROM Recensione R 
		JOIN Utente U ON R.emailRecensito = U.email
		JOIN Scambio S ON R.idScambio = S.ID
		JOIN Utente U2 ON (S.emailProponente = U2.email AND S.emailAccettatore = R.emailRecensito) OR (S.emailProponente = R.emailRecensito AND S.emailAccettatore = U2.email)
		WHERE U.username = ?
		SQL;

		try {
			return $this->query_to_array($query, "s", [$user]);
		} catch (Exception $e) {
			error_log("get_review_by_user: " . $e->getMessage());
			throw $e;
		}
	}

	/**
	 * Controlla se l'utente può aggiungere una recensione
	 * @param $userRecensito string utente recensito
	 * @param $userRecensore string utente recensore
	 * @param $id int id scambio
	 * @return bool true se l'utente può aggiungere una recensione, false altrimenti
	 * @throws Exception se si verifica un errore
	 */
	function check_if_user_can_add_review($userRecensito, $userRecensore, $id): bool
	{
		$query_scambio = "SELECT * FROM Scambio WHERE emailProponente = ? AND emailAccettatore = ?";
		$query_recensione = "SELECT * FROM Recensione WHERE emailRecensito = ? AND idScambio = ?";

		try {
			$emailRecensito = $this->get_user_email_by_username($userRecensito);
			$emailRecensore = $this->get_user_email_by_username($userRecensore);
			// controllo se c'è uno scambio tra i due utenti
			$res1 = $this->query_to_array($query_scambio, "ss", [$emailRecensito, $emailRecensore]);
			$res2 = $this->query_to_array($query_scambio, "ss", [$emailRecensore, $emailRecensito]);

			if (count($res1) > 0 || count($res2) > 0) {
				// controllo se l'utente ha già recensito l'altro
				$res3 = $this->query_to_array($query_recensione, "si", [$emailRecensito, $id]);
				return count($res3) === 0;
			} else {
				return false;
			}
		} catch (Exception $e) {
			error_log("check_if_user_can_add_review: " . $e->getMessage());
			throw $e;
		}
	}

	/**
	 * Inserisce una recensione
	 * @param $userRecensito string utente recensito
	 * @param $idScambio int id scambio
	 * @param $valutazione int valutazione
	 * @param $contenuto string contenuto
	 * @throws Exception se si verifica un errore
	 */
	function insert_review($userRecensito, $idScambio, $valutazione, $contenuto): void
	{
		$query = "INSERT INTO Recensione (emailRecensito, idScambio, valutazione, contenuto) VALUES (?, ?, ?, ?)";
		try {
			$emailRecensito = $this->get_user_email_by_username($userRecensito);
			$this->void_query($query, "siis", [$emailRecensito, $idScambio, $valutazione, $contenuto]);
		} catch (Exception $e) {
			error_log("insert_review: " . $e->getMessage());
			throw $e;
		}
	}

	public function check_user_has_libri_offerti($username): bool
	{
		$query = "SELECT * FROM Copia WHERE proprietario = ? LIMIT 1";
		try {
			$emailRec = $this->get_user_email_by_username($username);
			$val = $this->query_to_array($query, 's', [$emailRec]);
			return count($val) > 0;
		} catch (Exception $e) {
			error_log("check_user_has_libri_offerti: " . $e->getMessage());
			throw $e;
		}
	}

	public function check_user_has_libri_desiderati($username): bool
	{
		$query = "SELECT * FROM Desiderio WHERE email = ? LIMIT 1";
		try {
			$emailRec = $this->get_user_email_by_username($username);
			$val = $this->query_to_array($query, 's', [$emailRec]);
			return count($val) > 0;
		} catch (Exception $e) {
			error_log("check_user_has_libri_desiderati: " . $e->getMessage());
			throw $e;
		}
	}

	public function check_user_has_generi_preferiti($username): bool
	{
		$query = "SELECT * FROM Utente WHERE username = ? AND generi_preferiti IS NOT NULL LIMIT 1";
		try {
			$val = $this->query_to_array($query, 's', [$username]);
			return count($val) > 0;
		} catch (Exception $e) {
			error_log("check_user_has_generi_preferiti: " . $e->getMessage());
			throw $e;
		}
	}

	public function get_libri_offerti(): array
	{
		$query = <<<SQL
		SELECT DISTINCT L.ISBN, L.titolo, L.autore, L.editore, L.anno, L.genere, L.descrizione, L.lingua, L.path_copertina
		FROM Copia C JOIN Libro L ON C.ISBN = L.ISBN
		WHERE C.disponibile = TRUE
		ORDER BY C.ID DESC
		SQL;

		try {
			return $this->query_to_array($query);
		} catch (Exception $e) {
			error_log("get_libri_offerti: " . $e->getMessage());
			throw $e;
		}
	}

	public function get_users(): array
	{
		$query = <<<SQL
		SELECT email, username, nome, cognome, provincia, comune, path_immagine
		FROM Utente WHERE username <> 'admin'
		SQL;

		try {
			return $this->query_to_array($query);
		} catch (Exception $e) {
			error_log("get_users: " . $e->getMessage());
			throw $e;
		}
	}

	public function update_user_provincia($user, $nuovaProvincia)
	{
		$query = "UPDATE Utente SET provincia = ? WHERE username = ?";
		try {
			$this->void_query($query, "ss", [$nuovaProvincia, $user]);
		} catch (Exception $e) {
			error_log("update_user_provincia: " . $e->getMessage());
			throw $e;
		}
	}

	public function update_user_comune($user, $nuovoComune)
	{
		$query = "UPDATE Utente SET comune = ? WHERE username = ?";
		try {
			$this->void_query($query, "ss", [$nuovoComune, $user]);
		} catch (Exception $e) {
			error_log("update_user_comune: " . $e->getMessage());
			throw $e;
		}
	}

	public function update_user_nome($user, $nuovoNome)
	{
		$query = "UPDATE Utente SET nome = ? WHERE username = ?";
		try {
			$this->void_query($query, "ss", [$nuovoNome, $user]);
		} catch (Exception $e) {
			error_log("update_user_nome: " . $e->getMessage());
			throw $e;
		}
	}

	public function update_user_cognome($user, $nuovoCognome)
	{
		$query = "UPDATE Utente SET cognome = ? WHERE username = ?";
		try {
			$this->void_query($query, "ss", [$nuovoCognome, $user]);
		} catch (Exception $e) {
			error_log("update_user_cognome: " . $e->getMessage());
			throw $e;
		}
	}

	public function update_user_password($user, $nuovaPassword)
	{
		$query = "UPDATE Utente SET password_hash = ? WHERE username = ?";
		try {
			$passwordHashed = password_hash($nuovaPassword, PASSWORD_BCRYPT);
			$this->void_query($query, "ss", [$passwordHashed, $user]);
		} catch (Exception $e) {
			error_log("update_user_password: " . $e->getMessage());
			throw $e;
		}
	}
}

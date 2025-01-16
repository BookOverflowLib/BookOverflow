<?php

// TODO: maybe exception could be handled using best practices but i don´t think it's a requirement
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
		$query = "SELECT id, nome FROM province ORDER BY nome";
		$queryRes = mysqli_query($this->connection, $query);

		return $this->query_results_to_array($queryRes);
	}

	public function get_most_traded_with_cover($limit)
	{
		$query = "SELECT L.ISBN, L.titolo, L.autore, I.path, COUNT(*) AS numero_vendite
                    FROM Scambio AS S 
                    JOIN Copia AS CProp ON S.idCopiaProp = CProp.ID
                    JOIN Copia AS CAcc ON (S.idCopiaAcc = CAcc.ID AND CProp.ID != CAcc.ID) 
                    JOIN Libro AS L ON (CProp.ISBN = L.ISBN AND CAcc.ISBN = L.ISBN)
                    JOIN Immagine AS I ON L.ISBN = I.libro
                    WHERE I.isCopertina = TRUE
                    GROUP BY L.ISBN, L.titolo, L.autore, I.path
                    ORDER BY numero_vendite DESC
                    LIMIT ?";

		return $this->query_to_array($query, "i", [$limit]);
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

	public function register_user($nome, $cognome, $provincia, $comune, $email, $username, $password, $profileImg = null): void
	{
		$passwordHashed = password_hash($password, PASSWORD_BCRYPT);

		$query = "INSERT INTO Utente (email, password_hash, username, nome, cognome, provincia, comune, path_immagine) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
		try {
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
					throw new Exception("Invalid Credentials");
				}
			} else {
				throw new Exception("Invalid Credentials");
			}
		} catch (Exception $e) {
			throw $e;
		}
	}

	public function get_user_rating_by_email($email): ?array
	{
		$query = 'SELECT AVG(R.valutazione) AS media_valutazioni 
                    FROM Recensione R JOIN Scambio S ON R.idScambio = S.ID 
                    WHERE S.emailAccettatore = ? OR S.emailProponente = ?';
		try {
			return $this->query_to_array($query, "ss", [$email, $email]);
		} catch (Exception $e) {
			error_log("get_user_rating_by_email: " . $e->getMessage());
		}
		return null;
	}

	public function get_user_by_username($username): ?array
	{
		$query = "SELECT * FROM Utente WHERE username = ?";
		try {
			return $this->query_to_array($query, "s", [$username]);
		} catch (Exception $e) {
			error_log("get_user_by_username: " . $e->getMessage());
		}
		return null;
	}

	public function get_user_by_email($email): ?array
	{
		$query = "SELECT * FROM Utente WHERE email = ?";
		try {
			return $this->query_to_array($query, "s", [$email]);
		} catch (Exception $e) {
			error_log("get_user_by_email: " . $e->getMessage());
		}
		return null;
	}

	public function get_provincia_comune_by_ids($idProvincia, $idComune): ?array
	{
		$queryProvincia = "SELECT nome FROM province WHERE id = ?";
		$queryComune = "SELECT nome FROM comuni WHERE id = ?";
		try {
			$prov = $this->query_to_array($queryProvincia, "i", [$idProvincia]);
			$comu = $this->query_to_array($queryComune, "i", [$idComune]);
			return array("provincia" => $prov[0]['nome'], "comune" => $comu[0]['nome']);
		} catch (Exception $e) {
			error_log("get_provincia_comune_by_ids: " . $e->getMessage());
		}
		return null;
	}

	public function insert_new_book($isbn, $titolo, $autore, $editore, $anno, $genere, $descrizione, $lingua, $path_copertina): void
	{
		$path_copertina = str_replace("&edge=curl", "", $path_copertina);
		$query = "INSERT IGNORE INTO Libro (ISBN, titolo, autore, editore, anno, genere, descrizione, lingua, path_copertina) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
		try {
			$this->void_query($query, "sssssssss", [$isbn, $titolo, $autore, $editore, $anno, $genere, $descrizione, $lingua, $path_copertina]);
		} catch (Exception $e) {
			error_log("insert_new_book: " . $e->getMessage());
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
			SQL;

			return $this->query_to_array($query, "s", [$userEmail]);
		} catch (Exception $e) {
			error_log("get_libri_offerti_by_username: " . $e->getMessage());
			//throw $e;
		}
		return null;
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
			//throw $e;
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

	public function get_users_interested_in_user_books($user)
	{
		$query = <<<SQL
		SELECT DISTINCT U.email, U.nome, U.cognome, U.username, U.path_immagine, U.provincia, U.comune
		FROM Desiderio D JOIN Utente U ON D.email = U.email
		WHERE D.ISBN IN (
			SELECT C.ISBN
			FROM Copia C
			WHERE C.proprietario = ?
		)
		SQL;

		try {
			return $this->query_to_array($query, "s", [$user]);
		} catch (Exception $e) {
			error_log("get_users_desirous_your_books_by_user: " . $e->getMessage());
			throw $e;
		}
	}

	public function get_users_with_book_and_interested_in_my_books($username, $isbnLibro): ?array
	{
		$query = <<<SQL
		SELECT DISTINCT U.email, U.nome, U.cognome, U.username, U.path_immagine, U.provincia, U.comune
		FROM Utente U
		JOIN Copia C ON U.email = C.proprietario
		JOIN Desiderio D ON U.email = D.email
		WHERE C.ISBN = ? AND C.disponibile = TRUE
		AND D.ISBN IN (
		    SELECT C2.ISBN
		    FROM Copia C2
		    WHERE C2.proprietario = ?
		);
		SQL;

		try {
			$user_email = $this->get_user_email_by_username($username);
			return $this->query_to_array($query, "ss", [$isbnLibro, $user_email]);
		} catch (Exception $e) {
			error_log("get_users_with_book_and_interested_in_my_books: " . $e->getMessage());
			throw $e;
		}
	}

	public function get_desiderati_che_offro($userOfferente, $userDesiderante): ?array
	{
		$query = <<<SQL
		SELECT L.ISBN, L.titolo, L.autore, L.editore, L.anno, L.genere, L.descrizione, L.lingua, L.path_copertina	
		FROM Copia C JOIN Libro L ON C.ISBN = L.ISBN
		WHERE C.proprietario = ? AND C.ISBN IN (
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

	public function insert_scambio($user_prop, $user_acc, $isbn_prop, $isbn_acc): void
	{
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

}


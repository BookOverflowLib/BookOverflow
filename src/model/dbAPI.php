<?php
// TODO: maybe exception could be handled using best practices but i don´t think it's a requirement
class DBAccess
{
	private $connection;

	public function __construct()
	{
		$this->load_env();
		$this->open_connection();
	}

	public function __destruct()
	{
		$this->close_connection();
	}

	private function load_env()
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

	public function open_connection()
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

	public function close_connection()
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
	 * Forse è troppo cursata :(
	 * se è un SELECT ritorna
	 *  un array se la query ha successo
	 *  false se la query non ha successo
	 * se è un INSERT
	 *  true se la query ha successo
	 *  false se la query non ha successo
	 */
	public function prepare_and_execute_query($query, $types = null, $params = null): array|bool
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
				throw new Exception("Number of types does not match number of parameters");
			}

			// Bind parameters dynamically
			if (!$stmt->bind_param($types, ...$params)) {
				throw new Exception("Parameter binding failed: " . $stmt->error);
			}
		}

		if (!$stmt->execute()) {
			throw new Exception("Execute failed: " . $stmt->error);
		}

		$results = "";
		if (str_starts_with($query, "SELECT")) {
			try {
				$tmpResults = $this->query_results_to_array($stmt->get_result());
			} catch (Exception $e) {
				throw $e;
			}

			if ($tmpResults) {
				$results = $tmpResults;
			} else {
				$results = false;
			}
		} else {
			if (!$stmt->get_result() && !$stmt->errno) {
				$results = true;
			} else {
				$results = false;
			}
		}

		$stmt->close();

		return $results;
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
	 * Executes a SQL query and returns the results as an associative array.
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
	 * Executes a SQL query that does not return a result set.
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

		return $this->prepare_and_execute_query($query, "i", [$limit]);
	}

	public function get_comune_by_provincia($idProvincia): ?array
	{
		$query = "SELECT * FROM comuni WHERE id_provincia = ?";
		try {
			return $this->prepare_and_execute_query($query, "s", [$idProvincia]);
		} catch (Exception $e) {
			echo $e->getMessage();
		}
		return null;
	}

	public function register_user($nome, $cognome, $provincia, $comune, $email, $username, $password, $profileImg = null): bool
	{
		$passwordHashed = password_hash($password, PASSWORD_BCRYPT);

		$query = "INSERT INTO Utente (email, password_hash, username, nome, cognome, provincia, comune, path_immagine) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
		try {
			$res = $this->prepare_and_execute_query($query, "ssssssss", [$email, $passwordHashed, $username, $nome, $cognome, $provincia, $comune, $profileImg]);
			if ($res) {
				return true;
			}
		} catch (Exception $e) {
			throw $e;
		}
		return false;
	}

	public function login_user($email, $password): ?array
	{
		$query = "SELECT * FROM Utente WHERE email = ?";
		try {
			$res = $this->prepare_and_execute_query($query, "s", [$email]);
			if ($res) {
				if (password_verify($password, $res[0]['password_hash'])) {
					return $res;
				} else {
					throw new Exception("Wrong password");
				}
			} else {
				throw new Exception("User not registered");
			}
		} catch (Exception $e) {
			//echo $e->getMessage();
			//a meno che non si voglia una pagina completaemente bianca
			//questo non è l'approccio giusto
			throw $e;
		}
		return null;
	}

	public function get_user_rating_by_email($email): ?array
	{
		$query = 'SELECT AVG(R.valutazione) AS media_valutazioni 
                    FROM Recensione R JOIN Scambio S ON R.idScambio = S.ID 
                    WHERE S.emailAccettatore = ? OR S.emailProponente = ?';
		try {
			return $this->prepare_and_execute_query($query, "ss", [$email, $email]);
		} catch (Exception $e) {
			echo $e->getMessage();
		}
		return null;
	}

	public function get_user_by_username($username): ?array
	{
		$query = "SELECT * FROM Utente WHERE username = ?";
		try {
			return $this->prepare_and_execute_query($query, "s", [$username]);
		} catch (Exception $e) {
			echo $e->getMessage();
		}
		return null;
	}

	public function get_user_by_email($email): ?array
	{
		$query = "SELECT * FROM Utente WHERE email = ?";
		try {
			return $this->prepare_and_execute_query($query, "s", [$email]);
		} catch (Exception $e) {
			echo $e->getMessage();
		}
		return null;
	}

	public function get_provincia_comune_by_ids($idProvincia, $idComune): ?array
	{
		$queryProvincia = "SELECT nome FROM province WHERE id = ?";
		$queryComune = "SELECT nome FROM comuni WHERE id = ?";
		try {
			$prov = $this->prepare_and_execute_query($queryProvincia, "i", [$idProvincia]);
			$comu = $this->prepare_and_execute_query($queryComune, "i", [$idComune]);
			return array("provincia" => $prov[0]['nome'], "comune" => $comu[0]['nome']);
		} catch (Exception $e) {
			echo $e->getMessage();
		}
		return null;
	}

	public function insert_new_book($isbn, $titolo, $autore, $editore, $anno, $genere, $descrizione, $lingua, $path_copertina)
	{
		$query = "INSERT IGNORE INTO Libro (ISBN, titolo, autore, editore, anno, genere, descrizione, lingua, path_copertina) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
		try {
			$res = $this->prepare_and_execute_query($query, "sssssssss", [$isbn, $titolo, $autore, $editore, $anno, $genere, $descrizione, $lingua, $path_copertina]);
			if ($res) {
				return true;
			}
		} catch (Exception $e) {
			echo $e->getMessage();
		}
	}

	/**
	 * Aggiorna i generi preferiti dell'utente
	 * @param string $user username dell'utente
	 * @param string $generi generi preferiti dell'utente in formato JSON
	 * 
	 * @return bool true se l'aggiornamento è andato a buon fine, false altrimenti
	 */
	public function update_user_generi($user, $generi): bool
	{
		$query = "UPDATE Utente SET generi_preferiti = ? WHERE username = ?";
		try {
			$res = $this->prepare_and_execute_query($query, "ss", [$generi, $user]);
			if ($res) {
				return true;
			} else {
				return false;
			}
		} catch (Exception $e) {
			// echo $e->getMessage();
			return false;
		}
	}

	/**
	 * Ottiene i generi preferiti dell'utente
	 * @param string $user username dell'utente
	 * @return array|bool|null
	 */
	public function get_generi_by_username($user): ?array
	{
		$query = "SELECT generi_preferiti FROM Utente WHERE username = ?";
		try {
			return $this->prepare_and_execute_query($query, "s", [$user]);
		} catch (Exception $e) {
			// echo $e->getMessage();
			return null;
		}
	}

	private function get_user_email_by_username($username): ?string
	{
		$query = "SELECT email FROM Utente WHERE username = ?";
		try {
			$email = $this->prepare_and_execute_query($query, "s", [$username]);
			return $email ? $email[0]['email'] : null;
		} catch (Exception $e) {
			return null;
		}
	}

	public function get_libri_offerti_by_username($user): ?array
	{
		$userEmail = $this->get_user_email_by_username($user);

		$query = <<<SQL
		SELECT L.ISBN, L.titolo, L.autore, L.editore, L.anno, L.genere, L.descrizione, L.lingua, L.path_copertina, C.condizioni, C.disponibile
		FROM Copia C JOIN Libro L ON C.ISBN = L.ISBN
		WHERE C.proprietario = ? 
		SQL;

		try {
			$ris = $this->prepare_and_execute_query($query, "s", [$userEmail]);
			return $ris ? $ris : null;
		} catch (Exception $e) {
			// echo $e->getMessage();
			return null;
		}
	}

	public function insert_libri_offerti_by_username($user, $isbn, $condizione): bool|null
	{
		$userEmail = $this->get_user_email_by_username($user);
		$query = <<<SQL
		INSERT INTO Copia (ISBN, proprietario, condizioni)
		VALUES (?, ?, ?)
		SQL;

		try {
			return $this->prepare_and_execute_query($query, "sss", [$isbn, $userEmail, $condizione]);
		} catch (Exception $e) {
			// echo $e->getMessage();
			return false;
		}
	}

	public function delete_libro_offerto($user, $isbn): bool
	{
		$userEmail = $this->get_user_email_by_username($user);
		$query = <<<SQL
		DELETE FROM Copia
		WHERE ISBN = ? AND proprietario = ?
		SQL;

		try {
			return $this->prepare_and_execute_query($query, "ss", [$isbn, $userEmail]);
		} catch (Exception $e) {
			// echo $e->getMessage();
			return false;
		}
	}

	public function get_libri_desiderati_by_username($user): ?array
	{
		$userEmail = $this->get_user_email_by_username($user);

		$query = <<<SQL
		SELECT L.ISBN, L.titolo, L.autore, L.editore, L.anno, L.genere, L.descrizione, L.lingua, L.path_copertina
		FROM Desiderio D JOIN Libro L ON D.ISBN = L.ISBN
		WHERE D.email = ?
		SQL;

		try {
			$ris = $this->prepare_and_execute_query($query, "s", [$userEmail]);
			return $ris ? $ris : null;
		} catch (Exception $e) {
			// echo $e->getMessage();
			return null;
		}
	}

	public function insert_libri_desiderati_by_username($user, $isbn): bool
	{
		$userEmail = $this->get_user_email_by_username($user);
		$query = <<<SQL
		INSERT INTO Desiderio (email, ISBN)
		VALUES (?, ?)
		SQL;

		try {
			return $this->prepare_and_execute_query($query, "ss", [$userEmail, $isbn]);
		} catch (Exception $e) {
			return false;
		}
	}

	public function delete_libro_desiderato($user, $isbn): bool
	{
		$userEmail = $this->get_user_email_by_username($user);
		$query = <<<SQL
		DELETE FROM Desiderio
		WHERE ISBN = ? AND email = ?
		SQL;
		try {
			return $this->prepare_and_execute_query($query, "ss", [$isbn, $userEmail]);
		} catch (Exception $e) {
			return false;
		}
	}
}

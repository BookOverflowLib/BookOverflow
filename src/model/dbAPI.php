<?php

// TODO: maybe exception could be handled using best practices but i don´t think it's a requirement
class DBAccess
{
    private const HOST_DB = "db";
    private const DATABASE_NAME = "bookoverflow";
    private const USERNAME = "test";
    private const PASSWORD = "test";

    private $connection;

    public function __construct()
    {
        $this->open_connection();
    }

    public function __destruct()
    {
        $this->close_connection();
    }

    public function open_connection()
    {
        // turn on the errors | convert those errors into exceptions
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            $this->connection = new mysqli(
                DBAccess::HOST_DB,
                DBAccess::USERNAME,
                DBAccess::PASSWORD,
                DBAccess::DATABASE_NAME
            );
        } catch (mysqli_sql_exception $e) {
            throw new Exception("Connection error: " . $e->getMessage());
        }

        // errors check in debug; returns the error message from the last connection attempt
        // return mysqli_connect_error();

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
     * se è un SELECT ritorna un array,
     * se è un INSERT o qualsiasi altra cosa ritorna bool
     */
    // FIXME: null should not be a return type, remove result handling from this function
    public function prepare_and_execute_query($query, $types = null, $params = null): array|bool|null
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
            $results = $this->query_results_to_array($stmt->get_result());
        } else {
            if (!$stmt->get_result() && !$stmt->errno) {
                $results = true;
            } else {
                $results = false;
            }
        }

        $stmt->close();
        // maybe it's better to keep the connection open for browser session
        // $this->close_connection();

        return $results;
    }

    private function query_results_to_array($queryRes): ?array
    {
        if (mysqli_num_rows($queryRes) == 0) {
            return null;
        }
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
            echo $e->getMessage();
        }
        return false;
    }

    // ritorna un array con i dati dell'utente se il login è andato a buon fine
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
            echo $e->getMessage();
        }
        return null;
    }

    public function get_user_rating_by_email($email): ?array
    {
        $query = 'DECLARE @emailUtente AS VARCHAR(255) = ?;
                    SELECT AVG(R.valutazione) AS media_valutazioni 
                    FROM Recensione R JOIN Scambio S ON R.idScambio = S.ID 
                    WHERE S.emailAccettatore = @emailUtente OR S.emailProponente = @emailUtente';
        try {
            return $this->prepare_and_execute_query($query, "s", [$email]);
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
}

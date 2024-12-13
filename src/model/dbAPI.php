<?php
class DBAccess
{
    private const HOST_DB = "localhost";
    private const DATABASE_NAME = "lribon";
    private const USERNAME = "lribon";
    private const PASSWORD = "linai0aiMohgooPh";

    private $connection;

    private function handle_query_error($query)
    {
        if (!$query) {
            throw new Exception("Query failed: " . mysqli_error($this->connection));
        }
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
        //$this->connection = mysqli_connect(DBAccess::HOST_DB, DBAccess::USERNAME, DBAccess::PASSWORD, DBAccess::DATABASE_NAME);

        // errors check in debug
        return mysqli_connect_error();  // ???

        // errors check in production
        // if(mysqli_connect_errno()) {
        // 	return false;
        // } else {
        // 	return true;
        // }
    }

    public function close_connection()
    {
        // la connessione è già stata chiusa
        if (!$this->connection) {
            return;
        }
        mysqli_close($this->connection);
    }

    private function prepare_and_execute_query($query, $types = null, $params = null): ?array
    {
        if (!$this->connection) {
            $this->open_connection();
        }

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

        $result = $this->query_results_to_array($stmt->get_result());

        $stmt->close();
        $this->close_connection();
        // we might want to keep the connection open
        // constant opening and closing is slow

        return $result;
    }

    public function get_most_traded_with_cover($limit)
    {
        $query = "SELECT L.ISBN, L.titolo, L.autore, I.url, COUNT(*) AS numero_vendite
                    FROM Scambio AS S 
                    JOIN Copia AS CProp ON S.idCopiaProp = CProp.ID
                    JOIN Copia AS CAcc ON (S.idCopiaAcc = CAcc.ID AND CProp.ID != CAcc.ID) 
                    JOIN Libro AS L ON (CProp.ISBN = L.ISBN AND CAcc.ISBN = L.ISBN)
                    JOIN Immagine AS I ON L.ISBN = I.ISBN
                    WHERE I.isCopertina = TRUE
                    GROUP BY L.ISBN, L.titolo, L.autore, I.url
                    ORDER BY numero_vendite DESC
                    LIMIT ?";

        return $this->prepare_and_execute_query($query, "i", [$limit]);
    }

    function get_citta_by_provincia($provincia): ?array
    {
        $query = "SELECT * FROM citta WHERE provincia = ?";
        return $this->prepare_and_execute_query($query, "s", [$provincia]);
    }

    // No need for prepared statements? 
    function get_province(): ?array
    {
        $this->open_connection();

        $query = "SELECT id, nome FROM province";

        $queryRes = mysqli_query($this->connection, $query);
        $this->handle_query_error($queryRes);

        $this->close_connection();

        return $this->query_results_to_array($queryRes);
    }
}

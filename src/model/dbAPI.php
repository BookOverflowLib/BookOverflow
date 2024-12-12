<?php

// TODO: settare namespace che venga usato
use mysqli;

class DBAccess
{
    private const HOST_DB = "localhost";
    private const DATABASE_NAME = "lribon";
    private const USERNAME = "lribon";
    private const PASSWORD = "linai0aiMohgooPh";

    private $connection;

    public function open_connection()
    {
        // turn on the errors | convert those errors into exceptions
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            $this->connection = new mysqli(DBAccess::HOST_DB, DBAccess::USERNAME, DBAccess::PASSWORD, DBAccess::DATABASE_NAME);
        } catch (mysqli_sql_exception $e) {
            throw new Exception("Connection error: " . $e->getMessage());
        }
        $this->connection = mysqli_connect(DBAccess::HOST_DB, DBAccess::USERNAME, DBAccess::PASSWORD, DBAccess::DATABASE_NAME);

        // errors check in debug
        return mysqli_connect_error();

        // errors check in production
        // if(mysqli_connect_errno()) {
        // 	return false;
        // } else {
        // 	return true;
        // }
    }

    public function close_connection()
    {
        mysqli_close($this->connection);
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
                    LIMIT " . $limit;
        $queryRes = mysqli_query($this->connection, $query or die(mysqli_error($this->connection)));

        if (mysqli_num_rows($queryRes) == 0) {
            return null;
        } else {
            $res = array();
            while ($row = mysqli_fetch_assoc($queryRes)) {
                array_push($res, $row);
            }
            $queryRes->free();
            return $res;
        }
    }

    // public function insertNewElement()
    // {
    //     // TODO: usare prepare statement
    //     $queryIns = "";
    //     $queryRes = mysqli_query($this->connection, $queryIns or die(mysqli_error($this->connection)));

    //     if (mysqli_affected_rows($this->connection) > 0) {
    //         return true;
    //     } else {
    //         return false;
    //     }
    // }
}

<?php

namespace DB;

use mysqli;

class DBAccess
{
    private const HOST_DB = "localhost";
    private const DATABASE_NAME = "lribon";
    private const USERNAME = "lribon";
    private const PASSWORD = "linai0aiMohgooPh";

    private $connection;

    public function openDBConnection()
    {
        // turn on the errors | convert those errors into exceptions
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

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

    public function closeConnection()
    {
        mysqli_close($this->connection);
    }

    public function getList()
    {
        $query = "";
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

    public function insertNewElement()
    {
        $queryIns = "";
        $queryRes = mysqli_query($this->connection, $queryIns or die(mysqli_error($this->connection)));

        if (mysqli_affected_rows($this->connection) > 0) {
            return true;
        } else {
            return false;
        }
    }
}

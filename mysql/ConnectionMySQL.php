<?php

class ConnectionMySQL
{

    private $connection;
    private $host = "localhost";
    private $user = "emmanuel";
    private $pass = "desarrollo2020";
    private $db   = "tigo_test_SVEPI";  //tigo_test_SVEPI

    public function __construct()
    {
        $this->connection = new mysqli($this->host, $this->user, $this->pass, $this->db);
    }

    public function query($sql)
    {

        if ($this->connection->connect_errno) {
            printf("Connect failed: %s\n", $this->connection->connect_error);
            exit();
        }

        $result = $this->connection->query($sql);
        if (is_bool($result)) {
            if ($result) {
                return true;
            } else {
                printf("Errormessage: %s\n", $this->connection->error);
                printf($sql);
            }
        }
        return $result->fetch_all();
    }

    public function queryUpdateOrInsert($sql)
    {

        if ($this->connection->connect_errno) {
            printf("Connect failed: %s\n", $this->connection->connect_error);
            exit();
        }
        // print_r("---<br>");
        // print_r($sql);
        // print_r("---<br>");

        $result = $this->connection->query($sql);
        if (!$result) {
            printf("Errormessage: %s\n", $this->connection->error);
        }
        return $this->connection->insert_id;
    }
}

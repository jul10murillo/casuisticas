<?php

class ConnectionMySQL {

    private $connection;
    private $host = "localhost";
    private $user = "root";
    private $pass = "";
    private $db = "svepi_prod_test";

    public function __construct() {
        $this->connection = new mysqli($this->host, $this->user, $this->pass, $this->db);
    }

    public function query($sql) {
        
        if ($this->connection->connect_errno) {
            printf("Connect failed: %s\n", $this->connection->connect_error);
            exit();
        }
        $result = $this->connection->query($sql);

        if (!$result) {
            printf("Errormessage: %s\n", $this->connection->error);
        }
        return $result->fetch_all();
    }

}

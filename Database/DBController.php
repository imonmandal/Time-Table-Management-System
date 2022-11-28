<?php

class DBController
{
    // Database connection properties
    protected $host = 'localhost';
    protected $user = 'root';
    protected $password = '0110';
    protected $database = 'time_table';

    // connection property
    public $con = null;

    // call constructor
    public function __construct()
    {
        // Create connection
        $this->con = mysqli_connect($this->host, $this->user, $this->password, $this->database);

        // Check connection
        if ($this->con->connect_error) {
            die("Connection failed: " . $this->con->connect_error);
        }
    }

    // destructor
    public function __destruct()
    {
        $this->con->close();
    }

    // for closing mysqli connection
    protected function closeConnection()
    {
        if ($this->con != null) {
            $this->con->close();
            $this->con = null;
        }
    }
}

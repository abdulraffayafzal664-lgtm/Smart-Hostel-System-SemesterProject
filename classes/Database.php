<?php
// DESIGN PATTERN 1: SINGLETON
// Only one MySQL connection object exists for the whole request, however many times it is asked for.
class Database {
    private static ?Database $instance = null;
    private mysqli $conn;

    private function __construct() {                 // private: nobody else can call "new Database"
        $this->conn = new mysqli("localhost", "root", "", "smart_hostel");   // XAMPP defaults
        $this->conn->set_charset("utf8mb4");
    }
    private function __clone() {}                     // private: cannot be copied

    public static function getInstance(): Database {
        if (self::$instance === null) { self::$instance = new Database(); }
        return self::$instance;
    }
    public function connection(): mysqli { return $this->conn; }
}

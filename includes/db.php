<?php
// Database Connection Script
require_once __DIR__ . '/../config.php';

class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            // Check if it's a database not found error
            if ($exception->getCode() == 1049) {
                // Database doesn't exist, redirect to setup page
                if (!defined('SETUP_MODE') || !SETUP_MODE) {
                    header('Location: install.php');
                    exit();
                }
            }
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}

// Global database connection function
function getDB() {
    static $database = null;
    if ($database === null) {
        $database = new Database();
    }
    return $database->getConnection();
}

// Test database connection
try {
    $db = getDB();
    if ($db) {
        // Connection successful
    }
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>

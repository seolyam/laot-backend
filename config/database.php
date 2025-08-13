<?php
class DatabaseConnection {
    private $host;
    private $database;
    private $username;
    private $password;
    private $connection;

    public function __construct() {
        $this->loadConfig();
        $this->connect();
    }

    private function loadConfig() {
        $configFile = __DIR__ . '/database_config.php';
        
        if (file_exists($configFile)) {
            $config = require $configFile;
            $this->host = $config['host'];
            $this->database = $config['database'];
            $this->username = $config['username'];
            $this->password = $config['password'];
        } else {
            throw new Exception('Database configuration file not found');
        }
    }

    private function connect() {
        $this->connection = new mysqli(
            $this->host, 
            $this->username, 
            $this->password, 
            $this->database
        );

        if ($this->connection->connect_error) {
            error_log('Database connection failed: ' . $this->connection->connect_error);
            die('Database connection failed');
        }

        $this->connection->set_charset('utf8mb4');
    }

    public function getConnection() {
        return $this->connection;
    }
}

$db = new DatabaseConnection();
$mysqli = $db->getConnection();
?>

<?php
/**
 * Database Configuration File
 * 
 * This file handles the MySQLi database connection using OOP approach.
 * It provides a singleton pattern for efficient connection management.
 * 
 * @package IndustrialPortfolio
 * @version 1.0.0
 */

declare(strict_types=1);

class Database {
    private static ?Database $instance = null;
    private mysqli $connection;
    
    // Database configuration constants
    private const DB_HOST = 'localhost';
    private const DB_USER = 'root';
    private const DB_PASS = '';
    private const DB_NAME = 'industrial_portfolio';
    private const DB_CHARSET = 'utf8mb4';
    
    /**
     * Private constructor to prevent direct instantiation
     * Implements singleton pattern
     */
    private function __construct() {
        $this->connect();
    }
    
    /**
     * Prevent cloning of the instance
     */
    private function __clone() {}
    
    /**
     * Prevent unserializing of the instance
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
    
    /**
     * Get the single instance of the Database class
     * 
     * @return Database The database instance
     */
    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Establish database connection with error handling
     * 
     * @throws Exception If connection fails
     */
    private function connect(): void {
        try {
            $this->connection = new mysqli(
                self::DB_HOST,
                self::DB_USER,
                self::DB_PASS,
                self::DB_NAME
            );
            
            // Check for connection errors
            if ($this->connection->connect_error) {
                throw new Exception("Connection failed: " . $this->connection->connect_error);
            }
            
            // Set charset to utf8mb4 for full Unicode support
            if (!$this->connection->set_charset(self::DB_CHARSET)) {
                throw new Exception("Error loading character set: " . $this->connection->error);
            }
            
            // Set SQL mode for strict compliance
            $this->connection->query("SET sql_mode = 'STRICT_ALL_TABLES'");
            
            // Set timezone
            $this->connection->query("SET time_zone = '+00:00'");
            
        } catch (Exception $e) {
            // Log error and display user-friendly message
            error_log($e->getMessage());
            throw new Exception("Database connection failed. Please check configuration.");
        }
    }
    
    /**
     * Get the mysqli connection object
     * 
     * @return mysqli The database connection
     */
    public function getConnection(): mysqli {
        return $this->connection;
    }
    
    /**
     * Execute a prepared SELECT query
     * 
     * @param string $sql The SQL query with placeholders
     * @param string $types The parameter types (i, d, s, b)
     * @param array $params The parameters to bind
     * @return mysqli_result|false The result set or false on failure
     */
    public function select(string $sql, string $types = '', array $params = []): mysqli_result|false {
        try {
            $stmt = $this->connection->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->connection->error);
            }
            
            if (!empty($types) && !empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
            
            return $result;
            
        } catch (Exception $e) {
            error_log("SELECT error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Execute a prepared INSERT, UPDATE, or DELETE query
     * 
     * @param string $sql The SQL query with placeholders
     * @param string $types The parameter types (i, d, s, b)
     * @param array $params The parameters to bind
     * @return bool True on success, false on failure
     */
    public function execute(string $sql, string $types = '', array $params = []): bool {
        try {
            $stmt = $this->connection->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->connection->error);
            }
            
            if (!empty($types) && !empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            
            $success = $stmt->execute();
            $stmt->close();
            
            return $success;
            
        } catch (Exception $e) {
            error_log("EXECUTE error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get the last inserted ID
     * 
     * @return int The last insert ID
     */
    public function getLastInsertId(): int {
        return $this->connection->insert_id;
    }
    
    /**
     * Get the number of affected rows
     * 
     * @return int The number of affected rows
     */
    public function getAffectedRows(): int {
        return $this->connection->affected_rows;
    }
    
    /**
     * Escape special characters in a string for use in an SQL statement
     * 
     * @param string $string The string to escape
     * @return string The escaped string
     */
    public function escapeString(string $string): string {
        return $this->connection->real_escape_string($string);
    }
    
    /**
     * Close the database connection
     */
    public function close(): void {
        if (isset($this->connection)) {
            $this->connection->close();
        }
    }
    
    /**
     * Begin a transaction
     * 
     * @return bool True on success, false on failure
     */
    public function beginTransaction(): bool {
        return $this->connection->begin_transaction();
    }
    
    /**
     * Commit a transaction
     * 
     * @return bool True on success, false on failure
     */
    public function commit(): bool {
        return $this->connection->commit();
    }
    
    /**
     * Rollback a transaction
     * 
     * @return bool True on success, false on failure
     */
    public function rollback(): bool {
        return $this->connection->rollback();
    }
}

// Helper function to get database instance quickly
function db(): Database {
    return Database::getInstance();
}

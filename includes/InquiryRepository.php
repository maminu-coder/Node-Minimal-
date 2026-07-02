<?php
/**
 * Inquiry Repository
 * 
 * Handles all database operations related to contact form inquiries.
 * Uses prepared statements for SQL injection prevention.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

class InquiryRepository {
    private Database $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Create a new inquiry
     * 
     * @param array $data The inquiry data
     * @return int|false The new inquiry ID or false on failure
     */
    public function createInquiry(array $data) {
        $sql = "INSERT INTO inquiries (name, email, subject, message) VALUES (?, ?, ?, ?)";
        
        $success = $this->db->execute(
            $sql,
            'ssss',
            [
                sanitize($data['name'] ?? ''),
                sanitize($data['email'] ?? ''),
                sanitize($data['subject'] ?? ''),
                sanitize($data['message'] ?? '')
            ]
        );
        
        return $success ? $this->db->getLastInsertId() : false;
    }
    
    /**
     * Get all inquiries with optional filtering
     * 
     * @param bool $unreadOnly Only return unread inquiries
     * @param int|null $limit Maximum number of results
     * @param int $offset Offset for pagination
     * @return array Array of inquiry data
     */
    public function getInquiries(bool $unreadOnly = false, ?int $limit = null, int $offset = 0): array {
        $sql = "SELECT * FROM inquiries WHERE 1=1";
        
        $params = [];
        $types = '';
        
        if ($unreadOnly) {
            $sql .= " AND is_read = 0";
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        if ($limit !== null) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            $types .= 'ii';
        }
        
        $result = $this->db->select($sql, $types, $params);
        
        $inquiries = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $inquiries[] = $row;
            }
        }
        
        return $inquiries;
    }
    
    /**
     * Get a single inquiry by ID
     * 
     * @param int $id The inquiry ID
     * @return array|null The inquiry data or null if not found
     */
    public function getInquiryById(int $id): ?array {
        $sql = "SELECT * FROM inquiries WHERE id = ?";
        $result = $this->db->select($sql, 'i', [$id]);
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    /**
     * Mark an inquiry as read
     * 
     * @param int $id The inquiry ID
     * @return bool True on success, false on failure
     */
    public function markAsRead(int $id): bool {
        $sql = "UPDATE inquiries SET is_read = 1 WHERE id = ?";
        return $this->db->execute($sql, 'i', [$id]);
    }
    
    /**
     * Mark an inquiry as replied
     * 
     * @param int $id The inquiry ID
     * @return bool True on success, false on failure
     */
    public function markAsReplied(int $id): bool {
        $sql = "UPDATE inquiries SET is_read = 1, replied = 1 WHERE id = ?";
        return $this->db->execute($sql, 'i', [$id]);
    }
    
    /**
     * Delete an inquiry
     * 
     * @param int $id The inquiry ID
     * @return bool True on success, false on failure
     */
    public function deleteInquiry(int $id): bool {
        $sql = "DELETE FROM inquiries WHERE id = ?";
        return $this->db->execute($sql, 'i', [$id]);
    }
    
    /**
     * Get count of unread inquiries
     * 
     * @return int The count of unread inquiries
     */
    public function getUnreadCount(): int {
        $sql = "SELECT COUNT(*) as count FROM inquiries WHERE is_read = 0";
        $result = $this->db->select($sql);
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return (int)$row['count'];
        }
        
        return 0;
    }
    
    /**
     * Get total count of inquiries
     * 
     * @return int The total count
     */
    public function getTotalCount(): int {
        $sql = "SELECT COUNT(*) as count FROM inquiries";
        $result = $this->db->select($sql);
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return (int)$row['count'];
        }
        
        return 0;
    }
}

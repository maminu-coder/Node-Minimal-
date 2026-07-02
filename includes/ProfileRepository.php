<?php
/**
 * Profile Repository
 * 
 * Handles all database operations related to user profiles.
 * Uses prepared statements for SQL injection prevention.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

class ProfileRepository {
    private Database $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get the main profile
     * 
     * @return array|null The profile data or null if not found
     */
    public function getProfile(): ?array {
        $sql = "SELECT * FROM profile LIMIT 1";
        $result = $this->db->select($sql);
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    /**
     * Update profile information
     * 
     * @param array $data The profile data to update
     * @return bool True on success, false on failure
     */
    public function updateProfile(array $data): bool {
        $fields = [];
        $types = '';
        $params = [];
        
        $allowedFields = ['name', 'title', 'bio', 'photo_url', 'cv_url', 'email', 'phone', 'location', 'website', 'teaching_philosophy', 'social_links'];
        
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = ?";
                $types .= 's';
                $params[] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $sql = "UPDATE profile SET " . implode(', ', $fields) . " WHERE id = 1";
        $params[] = 1;
        $types .= 'i';
        
        return $this->db->execute($sql, $types, $params);
    }
    
    /**
     * Get teaching metrics
     * 
     * @return array|null The metrics data or null if not found
     */
    public function getTeachingMetrics(): ?array {
        $sql = "SELECT * FROM teaching_metrics LIMIT 1";
        $result = $this->db->select($sql);
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    /**
     * Update teaching metrics
     * 
     * @param array $data The metrics data to update
     * @return bool True on success, false on failure
     */
    public function updateTeachingMetrics(array $data): bool {
        $fields = [];
        $types = '';
        $params = [];
        
        $allowedFields = ['total_students_taught', 'total_courses_taught', 'mentees_published', 'section_load_details', 'students_per_section'];
        
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = ?";
                $types .= 'i';
                $params[] = (int)$data[$field];
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        // Handle string fields separately
        $stringFields = ['section_load_details', 'students_per_section'];
        foreach ($stringFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        if (count($params) === 0) {
            return false;
        }
        
        $sql = "UPDATE teaching_metrics SET " . implode(', ', $fields) . " WHERE id = 1";
        return $this->db->execute($sql, str_repeat('s', count($params)), $params);
    }
    
    /**
     * Get teaching pillars
     * 
     * @return array Array of pillar data
     */
    public function getTeachingPillars(): array {
        $sql = "SELECT * FROM teaching_pillars ORDER BY pillar_number ASC";
        $result = $this->db->select($sql);
        
        $pillars = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $pillars[] = $row;
            }
        }
        
        return $pillars;
    }
    
    /**
     * Insert or update a teaching pillar
     * 
     * @param array $data The pillar data
     * @return bool True on success, false on failure
     */
    public function saveTeachingPillar(array $data): bool {
        if (isset($data['id']) && !empty($data['id'])) {
            // Update existing
            $sql = "UPDATE teaching_pillars SET pillar_number = ?, title = ?, description = ? WHERE id = ?";
            return $this->db->execute($sql, 'issi', [$data['pillar_number'], $data['title'], $data['description'], $data['id']]);
        } else {
            // Insert new
            $sql = "INSERT INTO teaching_pillars (pillar_number, title, description) VALUES (?, ?, ?)";
            return $this->db->execute($sql, 'iss', [$data['pillar_number'], $data['title'], $data['description']]);
        }
    }
    
    /**
     * Delete a teaching pillar
     * 
     * @param int $id The pillar ID
     * @return bool True on success, false on failure
     */
    public function deleteTeachingPillar(int $id): bool {
        $sql = "DELETE FROM teaching_pillars WHERE id = ?";
        return $this->db->execute($sql, 'i', [$id]);
    }
}

<?php
/**
 * Skills Repository
 * 
 * Handles all database operations related to skills.
 * Uses prepared statements for SQL injection prevention.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

class SkillsRepository {
    private Database $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get all skills grouped by category
     * 
     * @return array Array of skills grouped by category
     */
    public function getSkillsGrouped(): array {
        $sql = "SELECT * FROM skills ORDER BY category ASC, proficiency_level DESC";
        $result = $this->db->select($sql);
        
        $grouped = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $category = $row['category'] ?? 'Other';
                $grouped[$category][] = $row;
            }
        }
        
        return $grouped;
    }
    
    /**
     * Get all skills
     * 
     * @return array Array of all skills
     */
    public function getAllSkills(): array {
        $sql = "SELECT * FROM skills ORDER BY proficiency_level DESC, name ASC";
        $result = $this->db->select($sql);
        
        $skills = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $skills[] = $row;
            }
        }
        
        return $skills;
    }
    
    /**
     * Get a single skill by ID
     * 
     * @param int $id The skill ID
     * @return array|null The skill data or null if not found
     */
    public function getSkillById(int $id): ?array {
        $sql = "SELECT * FROM skills WHERE id = ?";
        $result = $this->db->select($sql, 'i', [$id]);
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    /**
     * Create a new skill
     * 
     * @param array $data The skill data
     * @return int|false The new skill ID or false on failure
     */
    public function createSkill(array $data) {
        $sql = "INSERT INTO skills (name, category, proficiency_level, icon, years_experience) VALUES (?, ?, ?, ?, ?)";
        
        $success = $this->db->execute(
            $sql,
            'ssiis',
            [
                $data['name'] ?? '',
                $data['category'] ?? 'Other',
                (int)($data['proficiency_level'] ?? 5),
                $data['icon'] ?? null,
                (int)($data['years_experience'] ?? 0)
            ]
        );
        
        return $success ? $this->db->getLastInsertId() : false;
    }
    
    /**
     * Update an existing skill
     * 
     * @param int $id The skill ID
     * @param array $data The skill data to update
     * @return bool True on success, false on failure
     */
    public function updateSkill(int $id, array $data): bool {
        $sql = "UPDATE skills SET 
                name = ?, 
                category = ?, 
                proficiency_level = ?, 
                icon = ?, 
                years_experience = ? 
                WHERE id = ?";
        
        return $this->db->execute(
            $sql,
            'ssiisi',
            [
                $data['name'] ?? '',
                $data['category'] ?? 'Other',
                (int)($data['proficiency_level'] ?? 5),
                $data['icon'] ?? null,
                (int)($data['years_experience'] ?? 0),
                $id
            ]
        );
    }
    
    /**
     * Delete a skill
     * 
     * @param int $id The skill ID
     * @return bool True on success, false on failure
     */
    public function deleteSkill(int $id): bool {
        $sql = "DELETE FROM skills WHERE id = ?";
        return $this->db->execute($sql, 'i', [$id]);
    }
}

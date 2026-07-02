<?php
/**
 * Education & Experience Repository
 * 
 * Handles all database operations related to education and work experience.
 * Uses prepared statements for SQL injection prevention.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

class EducationRepository {
    private Database $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get all education records
     * 
     * @return array Array of education data
     */
    public function getEducation(): array {
        $sql = "SELECT * FROM education ORDER BY end_year DESC, start_year DESC";
        $result = $this->db->select($sql);
        
        $education = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $education[] = $row;
            }
        }
        
        return $education;
    }
    
    /**
     * Get a single education record by ID
     * 
     * @param int $id The education ID
     * @return array|null The education data or null if not found
     */
    public function getEducationById(int $id): ?array {
        $sql = "SELECT * FROM education WHERE id = ?";
        $result = $this->db->select($sql, 'i', [$id]);
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    /**
     * Create a new education record
     * 
     * @param array $data The education data
     * @return int|false The new education ID or false on failure
     */
    public function createEducation(array $data) {
        $sql = "INSERT INTO education (degree, institution, field, location, start_year, end_year, gpa, thesis_title, completion_date) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $success = $this->db->execute(
            $sql,
            'ssssissss',
            [
                $data['degree'] ?? '',
                $data['institution'] ?? '',
                $data['field'] ?? null,
                $data['location'] ?? null,
                (int)($data['start_year'] ?? 0),
                (int)($data['end_year'] ?? 0),
                $data['gpa'] ?? null,
                $data['thesis_title'] ?? null,
                $data['completion_date'] ?? null
            ]
        );
        
        return $success ? $this->db->getLastInsertId() : false;
    }
    
    /**
     * Update an existing education record
     * 
     * @param int $id The education ID
     * @param array $data The education data to update
     * @return bool True on success, false on failure
     */
    public function updateEducation(int $id, array $data): bool {
        $sql = "UPDATE education SET 
                degree = ?, 
                institution = ?, 
                field = ?, 
                location = ?, 
                start_year = ?, 
                end_year = ?, 
                gpa = ?, 
                thesis_title = ?, 
                completion_date = ? 
                WHERE id = ?";
        
        return $this->db->execute(
            $sql,
            'ssssissssi',
            [
                $data['degree'] ?? '',
                $data['institution'] ?? '',
                $data['field'] ?? null,
                $data['location'] ?? null,
                (int)($data['start_year'] ?? 0),
                (int)($data['end_year'] ?? 0),
                $data['gpa'] ?? null,
                $data['thesis_title'] ?? null,
                $data['completion_date'] ?? null,
                $id
            ]
        );
    }
    
    /**
     * Delete an education record
     * 
     * @param int $id The education ID
     * @return bool True on success, false on failure
     */
    public function deleteEducation(int $id): bool {
        $sql = "DELETE FROM education WHERE id = ?";
        return $this->db->execute($sql, 'i', [$id]);
    }
}


class ExperienceRepository {
    private Database $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get all work experiences
     * 
     * @return array Array of experience data
     */
    public function getExperiences(): array {
        $sql = "SELECT * FROM experiences ORDER BY start_date DESC";
        $result = $this->db->select($sql);
        
        $experiences = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Decode JSON responsibilities
                if (!empty($row['responsibilities'])) {
                    $row['responsibilities_array'] = json_decode($row['responsibilities'], true);
                } else {
                    $row['responsibilities_array'] = [];
                }
                $experiences[] = $row;
            }
        }
        
        return $experiences;
    }
    
    /**
     * Get a single experience by ID
     * 
     * @param int $id The experience ID
     * @return array|null The experience data or null if not found
     */
    public function getExperienceById(int $id): ?array {
        $sql = "SELECT * FROM experiences WHERE id = ?";
        $result = $this->db->select($sql, 'i', [$id]);
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (!empty($row['responsibilities'])) {
                $row['responsibilities_array'] = json_decode($row['responsibilities'], true);
            }
            return $row;
        }
        
        return null;
    }
    
    /**
     * Create a new experience record
     * 
     * @param array $data The experience data
     * @return int|false The new experience ID or false on failure
     */
    public function createExperience(array $data) {
        $sql = "INSERT INTO experiences (title, company, location, start_date, end_date, is_current, description, responsibilities) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $responsibilitiesJson = isset($data['responsibilities']) && is_array($data['responsibilities']) 
            ? json_encode($data['responsibilities']) 
            : null;
        
        $success = $this->db->execute(
            $sql,
            'sssssiss',
            [
                $data['title'] ?? '',
                $data['company'] ?? '',
                $data['location'] ?? null,
                $data['start_date'] ?? date('Y-m-d'),
                $data['end_date'] ?? null,
                (int)($data['is_current'] ?? 0),
                $data['description'] ?? null,
                $responsibilitiesJson
            ]
        );
        
        return $success ? $this->db->getLastInsertId() : false;
    }
    
    /**
     * Update an existing experience record
     * 
     * @param int $id The experience ID
     * @param array $data The experience data to update
     * @return bool True on success, false on failure
     */
    public function updateExperience(int $id, array $data): bool {
        $sql = "UPDATE experiences SET 
                title = ?, 
                company = ?, 
                location = ?, 
                start_date = ?, 
                end_date = ?, 
                is_current = ?, 
                description = ?, 
                responsibilities = ? 
                WHERE id = ?";
        
        $responsibilitiesJson = isset($data['responsibilities']) && is_array($data['responsibilities']) 
            ? json_encode($data['responsibilities']) 
            : null;
        
        return $this->db->execute(
            $sql,
            'sssssissi',
            [
                $data['title'] ?? '',
                $data['company'] ?? '',
                $data['location'] ?? null,
                $data['start_date'] ?? date('Y-m-d'),
                $data['end_date'] ?? null,
                (int)($data['is_current'] ?? 0),
                $data['description'] ?? null,
                $responsibilitiesJson,
                $id
            ]
        );
    }
    
    /**
     * Delete an experience record
     * 
     * @param int $id The experience ID
     * @return bool True on success, false on failure
     */
    public function deleteExperience(int $id): bool {
        $sql = "DELETE FROM experiences WHERE id = ?";
        return $this->db->execute($sql, 'i', [$id]);
    }
    
    /**
     * Get total count of experiences
     * 
     * @return int The total count
     */
    public function getTotalCount(): int {
        $sql = "SELECT COUNT(*) as count FROM experiences";
        $result = $this->db->select($sql);
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return (int)$row['count'];
        }
        
        return 0;
    }
}

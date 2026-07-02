<?php
/**
 * Project Repository
 * 
 * Handles all database operations related to portfolio projects.
 * Uses prepared statements for SQL injection prevention.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

class ProjectRepository {
    private Database $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get all projects with optional filtering
     * 
     * @param int|null $categoryId Filter by category
     * @param bool $featuredOnly Only return featured projects
     * @return array Array of project data
     */
    public function getProjects(?int $categoryId = null, bool $featuredOnly = false): array {
        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug 
                FROM projects p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE 1=1";
        
        $params = [];
        $types = '';
        
        if ($categoryId !== null) {
            $sql .= " AND p.category_id = ?";
            $params[] = $categoryId;
            $types .= 'i';
        }
        
        if ($featuredOnly) {
            $sql .= " AND p.featured = 1";
        }
        
        $sql .= " ORDER BY p.created_at DESC";
        
        $result = $this->db->select($sql, $types, $params);
        
        $projects = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Decode JSON fields
                if (!empty($row['tech_stack'])) {
                    $row['tech_stack_array'] = json_decode($row['tech_stack'], true);
                } else {
                    $row['tech_stack_array'] = [];
                }
                
                if (!empty($row['image_urls'])) {
                    $row['image_urls_array'] = json_decode($row['image_urls'], true);
                } else {
                    $row['image_urls_array'] = [];
                }
                
                $projects[] = $row;
            }
        }
        
        return $projects;
    }
    
    /**
     * Get a single project by ID
     * 
     * @param int $id The project ID
     * @return array|null The project data or null if not found
     */
    public function getProjectById(int $id): ?array {
        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug 
                FROM projects p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.id = ?";
        $result = $this->db->select($sql, 'i', [$id]);
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
            // Decode JSON fields
            if (!empty($row['tech_stack'])) {
                $row['tech_stack_array'] = json_decode($row['tech_stack'], true);
            } else {
                $row['tech_stack_array'] = [];
            }
            
            if (!empty($row['image_urls'])) {
                $row['image_urls_array'] = json_decode($row['image_urls'], true);
            } else {
                $row['image_urls_array'] = [];
            }
            
            return $row;
        }
        
        return null;
    }
    
    /**
     * Create a new project
     * 
     * @param array $data The project data
     * @return int|false The new project ID or false on failure
     */
    public function createProject(array $data) {
        $sql = "INSERT INTO projects (title, description, tech_stack, image_urls, live_url, repo_url, category_id, featured) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $techStackJson = isset($data['tech_stack']) && is_array($data['tech_stack']) 
            ? json_encode($data['tech_stack']) 
            : null;
        
        $imageUrlsJson = isset($data['image_urls']) && is_array($data['image_urls']) 
            ? json_encode($data['image_urls']) 
            : null;
        
        $success = $this->db->execute(
            $sql,
            'sssssiii',
            [
                $data['title'] ?? '',
                $data['description'] ?? '',
                $techStackJson,
                $imageUrlsJson,
                $data['live_url'] ?? null,
                $data['repo_url'] ?? null,
                $data['category_id'] ?? null,
                (int)($data['featured'] ?? 0)
            ]
        );
        
        return $success ? $this->db->getLastInsertId() : false;
    }
    
    /**
     * Update an existing project
     * 
     * @param int $id The project ID
     * @param array $data The project data to update
     * @return bool True on success, false on failure
     */
    public function updateProject(int $id, array $data): bool {
        $sql = "UPDATE projects SET 
                title = ?, 
                description = ?, 
                tech_stack = ?, 
                image_urls = ?, 
                live_url = ?, 
                repo_url = ?, 
                category_id = ?, 
                featured = ? 
                WHERE id = ?";
        
        $techStackJson = isset($data['tech_stack']) && is_array($data['tech_stack']) 
            ? json_encode($data['tech_stack']) 
            : null;
        
        $imageUrlsJson = isset($data['image_urls']) && is_array($data['image_urls']) 
            ? json_encode($data['image_urls']) 
            : null;
        
        return $this->db->execute(
            $sql,
            'sssssiiii',
            [
                $data['title'] ?? '',
                $data['description'] ?? '',
                $techStackJson,
                $imageUrlsJson,
                $data['live_url'] ?? null,
                $data['repo_url'] ?? null,
                $data['category_id'] ?? null,
                (int)($data['featured'] ?? 0),
                $id
            ]
        );
    }
    
    /**
     * Delete a project
     * 
     * @param int $id The project ID
     * @return bool True on success, false on failure
     */
    public function deleteProject(int $id): bool {
        $sql = "DELETE FROM projects WHERE id = ?";
        return $this->db->execute($sql, 'i', [$id]);
    }
    
    /**
     * Get total count of projects
     * 
     * @param bool $featuredOnly Only count featured projects
     * @return int The total count
     */
    public function getTotalCount(bool $featuredOnly = false): int {
        $sql = "SELECT COUNT(*) as count FROM projects";
        
        if ($featuredOnly) {
            $sql .= " WHERE featured = 1";
        }
        
        $result = $this->db->select($sql);
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return (int)$row['count'];
        }
        
        return 0;
    }
    
    /**
     * Get featured projects
     * 
     * @param int $limit Maximum number of projects
     * @return array Array of featured projects
     */
    public function getFeaturedProjects(int $limit = 6): array {
        return $this->getProjects(null, true);
    }
}

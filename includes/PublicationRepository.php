<?php
/**
 * Publication Repository
 * 
 * Handles all database operations related to academic publications.
 * Uses prepared statements for SQL injection prevention.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

class PublicationRepository {
    private Database $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get all publications with optional sorting
     * 
     * @param string $sortBy Field to sort by ('year', 'citation_count', 'created_at')
     * @param string $order Sort order ('ASC' or 'DESC')
     * @param int|null $limit Maximum number of results
     * @return array Array of publication data
     */
    public function getPublications(string $sortBy = 'year', string $order = 'DESC', ?int $limit = null): array {
        $allowedSorts = ['year', 'citation_count', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'year';
        }
        
        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';
        
        $sql = "SELECT * FROM publications ORDER BY {$sortBy} {$order}";
        if ($limit !== null) {
            $sql .= " LIMIT ?";
            $result = $this->db->select($sql, 'i', [$limit]);
        } else {
            $result = $this->db->select($sql);
        }
        
        $publications = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $publications[] = $row;
            }
        }
        
        return $publications;
    }
    
    /**
     * Get a single publication by ID
     * 
     * @param int $id The publication ID
     * @return array|null The publication data or null if not found
     */
    public function getPublicationById(int $id): ?array {
        $sql = "SELECT * FROM publications WHERE id = ?";
        $result = $this->db->select($sql, 'i', [$id]);
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    /**
     * Get publications by year range
     * 
     * @param int $startYear Start year
     * @param int $endYear End year
     * @return array Array of publication data
     */
    public function getPublicationsByYearRange(int $startYear, int $endYear): array {
        $sql = "SELECT * FROM publications WHERE year BETWEEN ? AND ? ORDER BY year DESC";
        $result = $this->db->select($sql, 'ii', [$startYear, $endYear]);
        
        $publications = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $publications[] = $row;
            }
        }
        
        return $publications;
    }
    
    /**
     * Search publications by keyword
     * 
     * @param string $keyword The search keyword
     * @return array Array of matching publications
     */
    public function searchPublications(string $keyword): array {
        $searchTerm = "%{$keyword}%";
        $sql = "SELECT * FROM publications WHERE title LIKE ? OR authors LIKE ? OR journal LIKE ? ORDER BY year DESC";
        $result = $this->db->select($sql, 'sss', [$searchTerm, $searchTerm, $searchTerm]);
        
        $publications = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $publications[] = $row;
            }
        }
        
        return $publications;
    }
    
    /**
     * Create a new publication
     * 
     * @param array $data The publication data
     * @return int|false The new publication ID or false on failure
     */
    public function createPublication(array $data) {
        $sql = "INSERT INTO publications (title, abstract, authors, journal, year, doi, citation_count, thumbnail_url, tags) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $success = $this->db->execute(
            $sql,
            'ssssisiss',
            [
                $data['title'] ?? '',
                $data['abstract'] ?? null,
                $data['authors'] ?? '',
                $data['journal'] ?? null,
                (int)($data['year'] ?? date('Y')),
                $data['doi'] ?? null,
                (int)($data['citation_count'] ?? 0),
                $data['thumbnail_url'] ?? null,
                isset($data['tags']) && is_array($data['tags']) ? json_encode($data['tags']) : null
            ]
        );
        
        return $success ? $this->db->getLastInsertId() : false;
    }
    
    /**
     * Update an existing publication
     * 
     * @param int $id The publication ID
     * @param array $data The publication data to update
     * @return bool True on success, false on failure
     */
    public function updatePublication(int $id, array $data): bool {
        $sql = "UPDATE publications SET 
                title = ?, 
                abstract = ?, 
                authors = ?, 
                journal = ?, 
                year = ?, 
                doi = ?, 
                citation_count = ?, 
                thumbnail_url = ?, 
                tags = ? 
                WHERE id = ?";
        
        return $this->db->execute(
            $sql,
            'ssssisissi',
            [
                $data['title'] ?? '',
                $data['abstract'] ?? null,
                $data['authors'] ?? '',
                $data['journal'] ?? null,
                (int)($data['year'] ?? date('Y')),
                $data['doi'] ?? null,
                (int)($data['citation_count'] ?? 0),
                $data['thumbnail_url'] ?? null,
                isset($data['tags']) && is_array($data['tags']) ? json_encode($data['tags']) : null,
                $id
            ]
        );
    }
    
    /**
     * Delete a publication
     * 
     * @param int $id The publication ID
     * @return bool True on success, false on failure
     */
    public function deletePublication(int $id): bool {
        $sql = "DELETE FROM publications WHERE id = ?";
        return $this->db->execute($sql, 'i', [$id]);
    }
    
    /**
     * Get total count of publications
     * 
     * @return int The total count
     */
    public function getTotalCount(): int {
        $sql = "SELECT COUNT(*) as count FROM publications";
        $result = $this->db->select($sql);
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return (int)$row['count'];
        }
        
        return 0;
    }
    
    /**
     * Get publications grouped by year
     * 
     * @return array Array of years with their publications
     */
    public function getPublicationsGroupedByYear(): array {
        $sql = "SELECT DISTINCT year FROM publications ORDER BY year DESC";
        $result = $this->db->select($sql);
        
        $grouped = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $year = $row['year'];
                $grouped[$year] = $this->getPublicationsByYearRange($year, $year);
            }
        }
        
        return $grouped;
    }
}

<?php
/**
 * Blog Repository
 * 
 * Handles all database operations related to blog posts.
 * Uses prepared statements for SQL injection prevention.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

class BlogRepository {
    private Database $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get all blog posts with optional filtering
     * 
     * @param string $status Filter by status ('published', 'draft', 'archived')
     * @param int|null $categoryId Filter by category
     * @param int|null $limit Maximum number of results
     * @param int $offset Offset for pagination
     * @return array Array of blog post data
     */
    public function getBlogs(string $status = 'published', ?int $categoryId = null, ?int $limit = null, int $offset = 0): array {
        $sql = "SELECT b.*, c.name as category_name, c.slug as category_slug 
                FROM blogs b 
                LEFT JOIN categories c ON b.category_id = c.id 
                WHERE b.status = ?";
        
        $params = [$status];
        $types = 's';
        
        if ($categoryId !== null) {
            $sql .= " AND b.category_id = ?";
            $params[] = $categoryId;
            $types .= 'i';
        }
        
        $sql .= " ORDER BY b.published_at DESC";
        
        if ($limit !== null) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            $types .= 'ii';
        }
        
        $result = $this->db->select($sql, $types, $params);
        
        $blogs = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $blogs[] = $row;
            }
        }
        
        return $blogs;
    }
    
    /**
     * Get a single blog post by ID
     * 
     * @param int $id The blog ID
     * @return array|null The blog data or null if not found
     */
    public function getBlogById(int $id): ?array {
        $sql = "SELECT b.*, c.name as category_name, c.slug as category_slug 
                FROM blogs b 
                LEFT JOIN categories c ON b.category_id = c.id 
                WHERE b.id = ?";
        $result = $this->db->select($sql, 'i', [$id]);
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    /**
     * Get a blog post by slug
     * 
     * @param string $slug The blog slug
     * @return array|null The blog data or null if not found
     */
    public function getBlogBySlug(string $slug): ?array {
        $sql = "SELECT b.*, c.name as category_name, c.slug as category_slug 
                FROM blogs b 
                LEFT JOIN categories c ON b.category_id = c.id 
                WHERE b.slug = ?";
        $result = $this->db->select($sql, 's', [$slug]);
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    /**
     * Create a new blog post
     * 
     * @param array $data The blog data
     * @return int|false The new blog ID or false on failure
     */
    public function createBlog(array $data) {
        $sql = "INSERT INTO blogs (title, slug, content, excerpt, featured_image, category_id, published_at, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $success = $this->db->execute(
            $sql,
            'ssssisis',
            [
                $data['title'] ?? '',
                $data['slug'] ?? generateSlug($data['title'] ?? ''),
                $data['content'] ?? '',
                $data['excerpt'] ?? null,
                $data['featured_image'] ?? null,
                $data['category_id'] ?? null,
                $data['published_at'] ?? date('Y-m-d H:i:s'),
                $data['status'] ?? 'draft'
            ]
        );
        
        return $success ? $this->db->getLastInsertId() : false;
    }
    
    /**
     * Update an existing blog post
     * 
     * @param int $id The blog ID
     * @param array $data The blog data to update
     * @return bool True on success, false on failure
     */
    public function updateBlog(int $id, array $data): bool {
        $sql = "UPDATE blogs SET 
                title = ?, 
                slug = ?, 
                content = ?, 
                excerpt = ?, 
                featured_image = ?, 
                category_id = ?, 
                published_at = ?, 
                status = ? 
                WHERE id = ?";
        
        return $this->db->execute(
            $sql,
            'ssssisissi',
            [
                $data['title'] ?? '',
                $data['slug'] ?? generateSlug($data['title'] ?? ''),
                $data['content'] ?? '',
                $data['excerpt'] ?? null,
                $data['featured_image'] ?? null,
                $data['category_id'] ?? null,
                $data['published_at'] ?? date('Y-m-d H:i:s'),
                $data['status'] ?? 'draft',
                $id
            ]
        );
    }
    
    /**
     * Delete a blog post
     * 
     * @param int $id The blog ID
     * @return bool True on success, false on failure
     */
    public function deleteBlog(int $id): bool {
        $sql = "DELETE FROM blogs WHERE id = ?";
        return $this->db->execute($sql, 'i', [$id]);
    }
    
    /**
     * Increment view count
     * 
     * @param int $id The blog ID
     * @return bool True on success, false on failure
     */
    public function incrementViews(int $id): bool {
        $sql = "UPDATE blogs SET views = views + 1 WHERE id = ?";
        return $this->db->execute($sql, 'i', [$id]);
    }
    
    /**
     * Get total count of blog posts
     * 
     * @param string $status Filter by status
     * @return int The total count
     */
    public function getTotalCount(string $status = 'published'): int {
        $sql = "SELECT COUNT(*) as count FROM blogs WHERE status = ?";
        $result = $this->db->select($sql, 's', [$status]);
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return (int)$row['count'];
        }
        
        return 0;
    }
    
    /**
     * Search blog posts by keyword
     * 
     * @param string $keyword The search keyword
     * @return array Array of matching blog posts
     */
    public function searchBlogs(string $keyword): array {
        $searchTerm = "%{$keyword}%";
        $sql = "SELECT b.*, c.name as category_name 
                FROM blogs b 
                LEFT JOIN categories c ON b.category_id = c.id 
                WHERE (b.title LIKE ? OR b.content LIKE ? OR b.excerpt LIKE ?) 
                AND b.status = 'published'
                ORDER BY b.published_at DESC";
        $result = $this->db->select($sql, 'sss', [$searchTerm, $searchTerm, $searchTerm]);
        
        $blogs = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $blogs[] = $row;
            }
        }
        
        return $blogs;
    }
    
    /**
     * Get recent blog posts
     * 
     * @param int $limit Maximum number of posts
     * @return array Array of recent blog posts
     */
    public function getRecentBlogs(int $limit = 5): array {
        return $this->getBlogs('published', null, $limit, 0);
    }
}

<?php
/**
 * Main Application Router
 * 
 * Handles all incoming requests and routes them to appropriate controllers.
 * Implements clean URL routing with regex pattern matching.
 */

declare(strict_types=1);

// Define application root
define('APP_ROOT', __DIR__);

// Load configuration
require_once __DIR__ . '/config/app.php';

// Simple router class
class Router {
    private array $routes = [];
    
    /**
     * Register a GET route
     */
    public function get(string $pattern, callable $handler): void {
        $this->routes['GET'][$pattern] = $handler;
    }
    
    /**
     * Register a POST route
     */
    public function post(string $pattern, callable $handler): void {
        $this->routes['POST'][$pattern] = $handler;
    }
    
    /**
     * Dispatch the request to the appropriate handler
     */
    public function dispatch(string $method, string $uri): void {
        foreach ($this->routes[$method] ?? [] as $pattern => $handler) {
            // Convert route pattern to regex
            $regex = '#^' . preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $pattern) . '$#';
            
            if (preg_match($regex, $uri, $matches)) {
                // Remove numeric keys from matches
                $params = array_filter($matches, ARRAY_FILTER_USE_KEY, fn($key) => is_string($key));
                
                // Call handler with parameters
                call_user_func_array($handler, $params);
                return;
            }
        }
        
        // No route found - 404
        http_response_code(404);
        require_once TEMPLATES_PATH . '/errors/404.php';
    }
}

// Initialize router
$router = new Router();

// ============================================
// PUBLIC ROUTES
// ============================================

// Home page
$router->get('/', function() {
    require_once INCLUDES_PATH . '/ProfileRepository.php';
    require_once INCLUDES_PATH . '/PublicationRepository.php';
    require_once INCLUDES_PATH . '/EducationExperienceRepository.php';
    require_once INCLUDES_PATH . '/ProjectRepository.php';
    
    $profileRepo = new ProfileRepository();
    $publicationRepo = new PublicationRepository();
    $experienceRepo = new ExperienceRepository();
    $educationRepo = new EducationRepository();
    $projectRepo = new ProjectRepository();
    
    $data = [
        'profile' => $profileRepo->getProfile(),
        'teachingMetrics' => $profileRepo->getTeachingMetrics(),
        'teachingPillars' => $profileRepo->getTeachingPillars(),
        'publications' => $publicationRepo->getPublications('year', 'DESC', 5),
        'experiences' => $experienceRepo->getExperiences(),
        'education' => $educationRepo->getEducation(),
        'projects' => $projectRepo->getFeaturedProjects(6),
        'pageTitle' => 'Home'
    ];
    
    require_once TEMPLATES_PATH . '/home.php';
});

// About page
$router->get('/about', function() {
    require_once INCLUDES_PATH . '/ProfileRepository.php';
    require_once INCLUDES_PATH . '/EducationExperienceRepository.php';
    require_once INCLUDES_PATH . '/PublicationRepository.php';
    require_once INCLUDES_PATH . '/SkillsRepository.php';
    
    $profileRepo = new ProfileRepository();
    $experienceRepo = new ExperienceRepository();
    $educationRepo = new EducationRepository();
    $publicationRepo = new PublicationRepository();
    
    $data = [
        'profile' => $profileRepo->getProfile(),
        'experiences' => $experienceRepo->getExperiences(),
        'education' => $educationRepo->getEducation(),
        'publicationsCount' => $publicationRepo->getTotalCount(),
        'pageTitle' => 'About'
    ];
    
    require_once TEMPLATES_PATH . '/about.php';
});

// Publications page
$router->get('/publications', function() {
    require_once INCLUDES_PATH . '/PublicationRepository.php';
    require_once INCLUDES_PATH . '/ProfileRepository.php';
    
    $publicationRepo = new PublicationRepository();
    $profileRepo = new ProfileRepository();
    
    $sortBy = $_GET['sort'] ?? 'year';
    $order = $_GET['order'] ?? 'DESC';
    
    $data = [
        'profile' => $profileRepo->getProfile(),
        'publications' => $publicationRepo->getPublications($sortBy, $order),
        'publicationsByYear' => $publicationRepo->getPublicationsGroupedByYear(),
        'pageTitle' => 'Publications'
    ];
    
    require_once TEMPLATES_PATH . '/publications.php';
});

// Blog listing
$router->get('/blog', function() {
    require_once INCLUDES_PATH . '/BlogRepository.php';
    require_once INCLUDES_PATH . '/ProfileRepository.php';
    
    $blogRepo = new BlogRepository();
    $profileRepo = new ProfileRepository();
    
    $page = max(1, (int)($_GET['page'] ?? 1));
    $offset = ($page - 1) * POSTS_PER_PAGE;
    
    $data = [
        'profile' => $profileRepo->getProfile(),
        'blogs' => $blogRepo->getBlogs('published', null, POSTS_PER_PAGE, $offset),
        'totalBlogs' => $blogRepo->getTotalCount('published'),
        'currentPage' => $page,
        'totalPages' => ceil($blogRepo->getTotalCount('published') / POSTS_PER_PAGE),
        'pageTitle' => 'Blog'
    ];
    
    require_once TEMPLATES_PATH . '/blog/index.php';
});

// Single blog post
$router->get('/blog/{slug}', function(string $slug) {
    require_once INCLUDES_PATH . '/BlogRepository.php';
    require_once INCLUDES_PATH . '/ProfileRepository.php';
    
    $blogRepo = new BlogRepository();
    $profileRepo = new ProfileRepository();
    
    $blog = $blogRepo->getBlogBySlug($slug);
    
    if (!$blog) {
        http_response_code(404);
        require_once TEMPLATES_PATH . '/errors/404.php';
        return;
    }
    
    // Increment view count
    $blogRepo->incrementViews((int)$blog['id']);
    
    $data = [
        'profile' => $profileRepo->getProfile(),
        'blog' => $blog,
        'pageTitle' => $blog['title']
    ];
    
    require_once TEMPLATES_PATH . '/blog/single.php';
});

// Projects page
$router->get('/projects', function() {
    require_once INCLUDES_PATH . '/ProjectRepository.php';
    require_once INCLUDES_PATH . '/ProfileRepository.php';
    
    $projectRepo = new ProjectRepository();
    $profileRepo = new ProfileRepository();
    
    $categoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;
    
    $data = [
        'profile' => $profileRepo->getProfile(),
        'projects' => $projectRepo->getProjects($categoryId),
        'pageTitle' => 'Projects'
    ];
    
    require_once TEMPLATES_PATH . '/projects.php';
});

// Contact page
$router->get('/contact', function() {
    require_once INCLUDES_PATH . '/ProfileRepository.php';
    
    $profileRepo = new ProfileRepository();
    
    $data = [
        'profile' => $profileRepo->getProfile(),
        'csrfToken' => generateCsrfToken(),
        'pageTitle' => 'Contact'
    ];
    
    require_once TEMPLATES_PATH . '/contact.php';
});

// ============================================
// API ROUTES
// ============================================

// Submit contact form
$router->post('/api/contact', function() {
    header('Content-Type: application/json');
    
    require_once INCLUDES_PATH . '/InquiryRepository.php';
    
    // Verify CSRF token
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!verifyCsrfToken($input['csrf_token'] ?? '')) {
        echo json_encode(['success' => false, 'message' => 'Invalid security token']);
        exit;
    }
    
    // Validate input
    $errors = [];
    
    if (empty($input['name'])) {
        $errors[] = 'Name is required';
    }
    
    if (empty($input['email']) || !isValidEmail($input['email'])) {
        $errors[] = 'Valid email is required';
    }
    
    if (empty($input['message'])) {
        $errors[] = 'Message is required';
    }
    
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit;
    }
    
    // Create inquiry
    $inquiryRepo = new InquiryRepository();
    $inquiryId = $inquiryRepo->createInquiry([
        'name' => $input['name'],
        'email' => $input['email'],
        'subject' => $input['subject'] ?? 'Contact Form Submission',
        'message' => $input['message']
    ]);
    
    if ($inquiryId) {
        logActivity('CONTACT_FORM', "New inquiry from {$input['email']}");
        echo json_encode(['success' => true, 'message' => 'Thank you! Your message has been sent.']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to send message. Please try again.']);
    }
});

// ============================================
// ADMIN ROUTES (redirect to admin directory)
// ============================================

$router->get('/admin', function() {
    redirect('/admin/index.php');
});

$router->get('/admin/login', function() {
    redirect('/admin/login.php');
});

// ============================================
// DISPATCH REQUEST
// ============================================

$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove trailing slash except for root
if ($requestUri !== '/' && substr($requestUri, -1) === '/') {
    $requestUri = rtrim($requestUri, '/');
}

$router->dispatch($requestMethod, $requestUri);

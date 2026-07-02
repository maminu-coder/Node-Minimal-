-- ============================================
-- Industrial Personal Website Database Schema
-- MySQL 8.x+ with MySQLi Compatibility
-- ============================================

-- Drop existing tables if they exist (in reverse order of dependencies)
DROP TABLE IF EXISTS inquiries;
DROP TABLE IF EXISTS skills;
DROP TABLE IF EXISTS awards;
DROP TABLE IF EXISTS projects;
DROP TABLE IF EXISTS lectures;
DROP TABLE IF EXISTS videos;
DROP TABLE IF EXISTS blogs;
DROP TABLE IF EXISTS experiences;
DROP TABLE IF EXISTS publications;
DROP TABLE IF EXISTS education;
DROP TABLE IF EXISTS profile;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS categories;

-- ============================================
-- 1. USERS TABLE - Admin authentication
-- ============================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(150) NOT NULL,
    role ENUM('admin', 'editor') DEFAULT 'admin',
    avatar_url VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 2. PROFILE TABLE - Main personal profile
-- ============================================
CREATE TABLE profile (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    title VARCHAR(255) NOT NULL,
    bio TEXT,
    photo_url VARCHAR(255) DEFAULT NULL,
    cv_url VARCHAR(255) DEFAULT NULL,
    email VARCHAR(255) DEFAULT NULL,
    phone VARCHAR(50) DEFAULT NULL,
    location VARCHAR(255) DEFAULT NULL,
    website VARCHAR(255) DEFAULT NULL,
    teaching_philosophy TEXT,
    social_links JSON DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 3. PUBLICATIONS TABLE - Academic publications
-- ============================================
CREATE TABLE publications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title TEXT NOT NULL,
    abstract TEXT DEFAULT NULL,
    authors TEXT NOT NULL,
    journal VARCHAR(255) DEFAULT NULL,
    year INT NOT NULL,
    doi VARCHAR(255) DEFAULT NULL,
    citation_count INT DEFAULT 0,
    thumbnail_url VARCHAR(255) DEFAULT NULL,
    tags JSON DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_year (year),
    INDEX idx_citations (citation_count)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 4. EXPERIENCES TABLE - Work experience timeline
-- ============================================
CREATE TABLE experiences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    company VARCHAR(255) NOT NULL,
    location VARCHAR(255) DEFAULT NULL,
    start_date DATE NOT NULL,
    end_date DATE DEFAULT NULL,
    is_current TINYINT(1) DEFAULT 0,
    description TEXT,
    responsibilities JSON DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_dates (start_date, end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 5. EDUCATION TABLE - Academic background
-- ============================================
CREATE TABLE education (
    id INT AUTO_INCREMENT PRIMARY KEY,
    degree VARCHAR(150) NOT NULL,
    institution VARCHAR(255) NOT NULL,
    field VARCHAR(255) DEFAULT NULL,
    location VARCHAR(255) DEFAULT NULL,
    start_year INT DEFAULT NULL,
    end_year INT DEFAULT NULL,
    gpa VARCHAR(50) DEFAULT NULL,
    thesis_title TEXT DEFAULT NULL,
    completion_date VARCHAR(50) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_years (start_year, end_year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 6. CATEGORIES TABLE - For blogs, videos, projects
-- ============================================
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    type ENUM('blog', 'video', 'project') NOT NULL,
    description TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type (type),
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 7. BLOGS TABLE - Blog posts / insights
-- ============================================
CREATE TABLE blogs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    content LONGTEXT NOT NULL,
    excerpt TEXT DEFAULT NULL,
    featured_image VARCHAR(255) DEFAULT NULL,
    category_id INT DEFAULT NULL,
    published_at DATETIME DEFAULT NULL,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_slug (slug),
    INDEX idx_published (published_at),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 8. VIDEOS TABLE - Video content
-- ============================================
CREATE TABLE videos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    url VARCHAR(500) NOT NULL,
    thumbnail VARCHAR(255) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    category_id INT DEFAULT NULL,
    duration VARCHAR(20) DEFAULT NULL,
    views INT DEFAULT 0,
    published_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_published (published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 9. LECTURES TABLE - Lectures and talks
-- ============================================
CREATE TABLE lectures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    event VARCHAR(255) DEFAULT NULL,
    date DATE DEFAULT NULL,
    location VARCHAR(255) DEFAULT NULL,
    slides_url VARCHAR(255) DEFAULT NULL,
    video_url VARCHAR(500) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_date (date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 10. PROJECTS TABLE - Portfolio projects
-- ============================================
CREATE TABLE projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    tech_stack JSON DEFAULT NULL,
    image_urls JSON DEFAULT NULL,
    live_url VARCHAR(255) DEFAULT NULL,
    repo_url VARCHAR(255) DEFAULT NULL,
    category_id INT DEFAULT NULL,
    featured TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_featured (featured)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 11. AWARDS TABLE - Awards and certifications
-- ============================================
CREATE TABLE awards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    issuer VARCHAR(255) DEFAULT NULL,
    date DATE DEFAULT NULL,
    credential_url VARCHAR(255) DEFAULT NULL,
    credential_id VARCHAR(100) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    type ENUM('award', 'certification') DEFAULT 'certification',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_date (date),
    INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 12. SKILLS TABLE - Technical skills
-- ============================================
CREATE TABLE skills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category VARCHAR(100) DEFAULT NULL,
    proficiency_level INT DEFAULT 5,
    icon VARCHAR(100) DEFAULT NULL,
    years_experience INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_proficiency (proficiency_level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 13. INQUIRIES TABLE - Contact form submissions
-- ============================================
CREATE TABLE inquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(255) NOT NULL,
    subject VARCHAR(255) DEFAULT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    replied TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_is_read (is_read),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 14. TEACHING_METRICS TABLE - Teaching statistics
-- ============================================
CREATE TABLE teaching_metrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    total_students_taught INT DEFAULT 0,
    total_courses_taught INT DEFAULT 0,
    mentees_published INT DEFAULT 0,
    section_load_details VARCHAR(255) DEFAULT NULL,
    students_per_section VARCHAR(100) DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 15. TEACHING_PILLARS TABLE - Teaching philosophy pillars
-- ============================================
CREATE TABLE teaching_pillars (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pillar_number INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_pillar_number (pillar_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 16. COURSES TABLE - Courses taught
-- ============================================
CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_code VARCHAR(30) NOT NULL,
    title VARCHAR(255) NOT NULL,
    level ENUM('Undergraduate', 'Graduate', 'Both') DEFAULT 'Undergraduate',
    description TEXT,
    technologies_tags JSON DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_level (level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SEED DATA - Profile Information
-- ============================================

-- Insert main profile (Muhammed Amin Abdullah, PhD)
INSERT INTO profile (name, title, bio, email, phone, location, website, teaching_philosophy, social_links) VALUES (
    'Muhammed Amin Abdullah, PhD',
    'Cybersecurity & Machine Learning Researcher | ML Engineer | Educator',
    'I am a dedicated researcher and educator specializing in cybersecurity, machine learning, and deep learning applications for IoT security. With a passion for turning confusion into clarity and then into creation, I mentor students and develop innovative solutions for real-world security challenges.',
    'drmaminu@gmail.com',
    '+1 (380) 270-5519',
    'Athens, OH 45701, USA',
    'https://drmaminu.com',
    'Confusion into Clarity. Then into Creation. A personal, evolving, and deeply intentional approach to education — built on active practice, honest feedback, and the belief that every student deserves a classroom where they truly belong.',
    '{"linkedin": "https://linkedin.com/in/drmaminu", "github": "https://github.com/drmaminu", "twitter": "https://twitter.com/drmaminu", "scholar": "https://scholar.google.com/citations?user=xxx"}'
);

-- ============================================
-- SEED DATA - Education History
-- ============================================

INSERT INTO education (degree, institution, field, location, start_year, end_year, gpa, thesis_title, completion_date) VALUES
('PhD', 'University of Electronic Science and Technology of China (UESTC)', 'Software Engineering', 'Chengdu, China', 2021, 2025, NULL, 'Application of Deep Learning for Malware Analysis in the Internet of Things (IoT)', 'Dec 2025'),
('M.Eng.', 'University of Electronic Science and Technology of China (UESTC)', 'Software Engineering', 'Chengdu, China', 2018, 2020, '3.84 / 4.0', 'An Effective Malware Classifier For IoT Devices Using Deep Learning', 'Jul 2020'),
('BSc', 'University for Development Studies', 'Computing with Accounting', 'Navrongo, Ghana', 2013, 2017, '4.10 / 5.0', 'An Online Facility Management System for UDS Navrongo Campus', 'Apr 2017');

-- ============================================
-- SEED DATA - Work Experience
-- ============================================

INSERT INTO experiences (title, company, location, start_date, end_date, is_current, description, responsibilities) VALUES
('Research Fellow — Cybersecurity & Machine Learning Lab', 'University of Electronic Science and Technology of China (UESTC)', 'Chengdu, China', '2021-08-01', '2024-11-30', 0, 'Conducted advanced research in cybersecurity and machine learning applications.', 
 '["Mentored 48 undergraduate and 5 graduate students.", "Developed novel deep learning architectures (CNN-GRU-FF, HCL-Classifier) achieving 99.89% accuracy.", "Authored 6+ Q1 journal publications.", "Evaluated cloud-based malware detection pipelines using AWS and Azure simulating 1 TB+ daily traffic."]'),
('Full Stack Web Developer (Freelance)', 'University of Technology and Applied Science (CKTUTAS)', 'Navrongo, Ghana', '2020-05-01', '2024-11-30', 0, 'Developed and maintained university web platforms.', 
 '["Developed and maintained the university''s main website and subsidiary platforms using PHP and WordPress.", "Ensured site-wide cross-platform optimization, security, and performance."]'),
('University Teaching Assistant', 'University for Development Studies', 'Navrongo, Ghana', '2017-09-01', '2018-07-31', 0, 'Delivered tutorials and hands-on instruction.', 
 '["Delivered tutorials and hands-on instruction in 7+ courses to 550+ undergraduate students.", "Designed assessments, graded assignments, and supervised final-year capstone projects."]');

-- ============================================
-- SEED DATA - Publications
-- ============================================

INSERT INTO publications (title, authors, journal, year, citation_count, tags, abstract) VALUES
('Deep Learning IoT Malware Analysis: Investigation and Understanding', 
 'MA Abdullah, Y Yu, J Cai, D Addo, EK Bankas, YH Gu, A Alqahtani, et al.', 
 'Neural Computing and Applications', 2025, 0, 
 '["Deep Learning", "IoT Security", "Malware Analysis"]',
 'A comprehensive investigation into deep learning techniques for malware analysis in IoT environments.'),
('CNN-GRU-FF: A Double-Layer Feature Fusion-Based Network Intrusion Detection System Using CNN and Gated Recurrent Units', 
 'Y Imrana, Y Xiang, L Ali, A Noor, K Sarpong, MA Abdullah', 
 'Complex & Intelligent Systems', 2024, 0, 
 '["CNN-GRU", "Intrusion Detection", "Feature Fusion"]',
 'Novel architecture combining CNN and GRU for enhanced network intrusion detection.'),
('HCL-Classifier: CNN and LSTM Based Hybrid Malware Classifier for Internet of Things (IoT)', 
 'MA Abdullah, Y Yu, K Adu, Y Imrana, X Wang, J Cai', 
 'Future Generation Computer Systems', 2023, 0, 
 '["Hybrid CNN-LSTM", "IoT Malware Classification"]',
 'A hybrid classifier leveraging CNN and LSTM for accurate IoT malware detection.');

-- ============================================
-- SEED DATA - Awards & Certifications
-- ============================================

INSERT INTO awards (title, issuer, date, credential_id, type, description) VALUES
('Convolutional Neural Networks in TensorFlow', 'Coursera', NULL, 'WUPJFKDT5FYR', 'certification', 'Professional certification in CNN implementation using TensorFlow'),
('DDoS Attacks and Defenses', 'Coursera', '2021-12-01', '2EPWWLHLJ4FQ', 'certification', 'Specialization in distributed denial of service attack mitigation'),
('Custom Models, Layers & Loss Functions — TensorFlow', 'Coursera', '2021-08-31', 'BZSERJZZ5L5L', 'certification', 'Advanced TensorFlow customization and model development');

-- ============================================
-- SEED DATA - Teaching Metrics
-- ============================================

INSERT INTO teaching_metrics (total_students_taught, total_courses_taught, mentees_published, section_load_details, students_per_section) VALUES
(500, 10, 3, '4× Section Load (Predictable weekly cadence)', '20–90 students per section');

-- ============================================
-- SEED DATA - Teaching Pillars
-- ============================================

INSERT INTO teaching_pillars (pillar_number, title, description) VALUES
(1, 'Clarity First', 'Every session opens with a plain-language objective. Students always know the destination before starting the journey.'),
(2, 'Active by Default', 'Lectures are brief launchers, not main events. Class time is for doing, testing, predicting, and adjusting.'),
(3, 'Continuous Feedback', 'Low-stakes, auto-graded checks surface misconceptions early. Pulse surveys at mid-semester drive visible changes within one week.'),
(4, 'Portable Methods', 'Pedagogy that works in-person, hybrid, and fully online. Certified in online teaching so every learner gets the same clarity and momentum.'),
(5, 'Living Portfolio', 'Syllabi, module maps, rubrics, and "what we changed" notes align with ABET/HLC outcomes so improvements persist across every offering.'),
(6, 'Beyond the Classroom', 'Supervising honors projects from proposal to presentation. Mentoring students into internships, research, and early professional roles.');

-- ============================================
-- SEED DATA - Courses
-- ============================================

INSERT INTO courses (course_code, title, level, description, technologies_tags) VALUES
('CS 4XX / 6XX', 'Machine Learning & Deep Learning', 'Both', 
 'Comprehensive coverage of supervised and unsupervised learning, neural networks, CNNs, RNNs, GANs, and state-of-the-art architectures. Hands-on projects with TensorFlow, PyTorch, and real-world datasets.',
 '["TensorFlow", "PyTorch", "Scikit-learn", "Neural Networks", "GANs", "Model Optimization"]'),
('CS 3XX / 4XX', 'Cybersecurity & Network Security', 'Undergraduate', 
 'Threat modeling, cryptographic protocols, intrusion detection systems, malware analysis, and secure software development. Practical labs on penetration testing and defensive security strategies.',
 '["IDS/IPS", "Cryptography", "Penetration Testing"]'),
('CS 2XX', 'CS50's Introduction to Computer Science', 'Undergraduate', 
 'Transform curiosity into capability by guiding students through the intellectual enterprises of computer science and the art of programming.',
 '["Python", "Data Structures", "C", "SQL", "Algorithms", "Flask", "Scratch"]'),
('CS 2XX', 'Programming with Python', 'Undergraduate', 
 'From fundamentals to advanced topics: data structures, object-oriented programming, file I/O, libraries (NumPy, Pandas), web scraping, APIs, and building real-world applications.',
 '["Python 3", "Data Structures", "OOP"]'),
('CS 3XX', 'Data Structures & Algorithms', 'Undergraduate', 
 'Foundational data structures (arrays, trees, graphs, hash tables) and algorithm design techniques (divide-and-conquer, dynamic programming, greedy algorithms).',
 '["Big-O", "Trees & Graphs", "Dynamic Programming"]'),
('CS 3XX', 'Software Engineering', 'Undergraduate', 
 'Full software development lifecycle: requirements engineering, design patterns, testing strategies, version control (Git), CI/CD pipelines, agile methodologies.',
 '["Git", "GitHub", "Agile", "Testing"]'),
('CS 2XX / 3XX', 'Web Development (Full Stack)', 'Undergraduate', 
 'Frontend (HTML, CSS, JavaScript, React) and backend (Node.js, Express, databases) development. Building responsive, scalable web applications.',
 '["HTML", "CSS", "JavaScript", "PHP", "React", "Node.js", "REST APIs"]'),
('CS 3XX', 'Database Systems', 'Undergraduate', 
 'Relational database design, SQL, normalization, transactions, indexing, query optimization, NoSQL databases (MongoDB, Redis), and cloud database services.',
 '["SQL", "MongoDB", "PostgreSQL", "Database Design"]'),
('CS 3XX', 'Computer Networks', 'Undergraduate', 
 'Network architectures, protocols (TCP/IP, HTTP, DNS), routing algorithms, network security, wireless networks, and IoT connectivity.',
 '["TCP/IP", "Routing", "Wireshark"]'),
('CS 4XX', 'Cloud Computing & DevOps', 'Undergraduate', 
 'Cloud service models (IaaS, PaaS, SaaS), AWS/Azure fundamentals, containerization (Docker), orchestration (Kubernetes), CI/CD pipelines.',
 '["AWS", "Docker", "Kubernetes"]'),
('CS 5XX / 6XX', 'Internet of Things (IoT) Security', 'Graduate', 
 'IoT architectures, embedded systems, sensor networks, communication protocols (MQTT, CoAP), security challenges, malware detection in IoT environments.',
 '["Arduino", "MQTT", "Embedded Security"]');

-- ============================================
-- SEED DATA - Skills
-- ============================================

INSERT INTO skills (name, category, proficiency_level, years_experience, icon) VALUES
('Python', 'Programming Languages', 10, 7, 'python'),
('JavaScript', 'Programming Languages', 9, 6, 'javascript'),
('PHP', 'Programming Languages', 9, 6, 'php'),
('TensorFlow', 'ML Frameworks', 9, 5, 'tensorflow'),
('PyTorch', 'ML Frameworks', 8, 4, 'pytorch'),
('React', 'Frontend', 8, 5, 'react'),
('Node.js', 'Backend', 8, 5, 'nodejs'),
('MySQL', 'Databases', 9, 6, 'mysql'),
('MongoDB', 'Databases', 7, 4, 'mongodb'),
('Docker', 'DevOps', 8, 4, 'docker'),
('AWS', 'Cloud', 8, 4, 'aws'),
('Cybersecurity', 'Security', 9, 6, 'security'),
('Machine Learning', 'AI/ML', 10, 6, 'ml'),
('Deep Learning', 'AI/ML', 9, 5, 'deep-learning'),
('IoT Security', 'Security', 9, 5, 'iot');

-- ============================================
-- SEED DATA - Categories
-- ============================================

INSERT INTO categories (name, slug, type, description) VALUES
('Technology', 'technology', 'blog', 'Posts about technology and programming'),
('Research', 'research', 'blog', 'Research findings and insights'),
('Teaching', 'teaching', 'blog', 'Educational content and pedagogy'),
('Tutorials', 'tutorials', 'blog', 'Step-by-step guides and tutorials'),
('Machine Learning', 'machine-learning', 'video', 'ML-related video content'),
('Cybersecurity', 'cybersecurity', 'video', 'Security-focused videos'),
('Web Development', 'web-development', 'project', 'Web development projects'),
('Research Projects', 'research-projects', 'project', 'Academic and research projects');

-- ============================================
-- SEED DATA - Sample Admin User
-- Password: admin123 (hashed with password_hash PASSWORD_DEFAULT)
-- ============================================

INSERT INTO users (email, password_hash, name, role) VALUES
('admin@drmaminu.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin User', 'admin');

-- ============================================
-- END OF SCHEMA
-- ============================================

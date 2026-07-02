# Industrial Personal Website with Admin Panel (PHP/MySQLi)

A comprehensive, production-ready personal portfolio website and custom CMS admin panel built with clean, secure object-oriented PHP and MySQLi.

## Features

### Public Portfolio Frontend
- Modern Industrial Design - Deep Navy (#1e3a5f), Accent Gold (#f59e0b), Background (#f7f5f0)
- Responsive Layout using Tailwind CSS
- Dynamic Content Modules:
  - Hero Section with animated typography
  - About & CV section with download links
  - Academic Publications Gallery (sortable by year/citations)
  - Interactive Work Experience & Education timelines
  - Projects Portfolio filterable by tech stack
  - Blog/Insights with SEO-friendly URL slugs
  - Video & Lecture grids with YouTube embed support
- Contact Form with async submission and validation

### Secure Admin Panel & CMS
- Authentication System with password_hash/password_verify
- Session Management with regenerated session IDs
- Analytics Dashboard with summary statistics
- Complete CRUD Management for all content types
- Media Upload Handler with MIME validation

## Tech Stack

| Component | Technology |
|-----------|------------|
| Backend | PHP 8.x+ (OOP, MVC-style) |
| Database | MySQL 8.x+ (MySQLi only) |
| Frontend | HTML5, Tailwind CSS, Vanilla JS |
| Storage | Local filesystem (S3 ready) |

## Installation

1. Create database and import schema:
```bash
mysql -u root -p -e "CREATE DATABASE industrial_portfolio CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p industrial_portfolio < config/database.sql
```

2. Edit config/db.php with your database credentials

3. Set permissions:
```bash
chmod -R 755 assets/uploads
chmod -R 777 logs
```

## Default Credentials

- Email: admin@drmaminu.com
- Password: admin123

Change these immediately after first login!

## Security Features

- SQL Injection Prevention via prepared statements
- XSS Mitigation with htmlspecialchars()
- CSRF Protection with token-based forms
- Secure Sessions with HTTPOnly cookies
- Password Hashing with bcrypt
- File Upload Validation with MIME checking

## License

MIT License

# Skillsync AI - Intelligent Resume Builder

**Skillsync AI** is an advanced AI-powered resume building platform that helps job seekers create professional resumes with intelligent skill suggestions, job recommendations, and comprehensive resume analysis.

## Features

### Core Features
- **AI-Powered Skill Suggestions** - Get personalized skill recommendations based on your profile and industry trends
- **Smart Resume Builder** - Create professional resumes with multiple templates
- **Resume Analysis** - Receive actionable insights to improve your resume
- **Job & Internship Recommendations** - Discover curated opportunities matching your profile
- **AI Chatbot Assistant** - Get career advice and resume help through intelligent conversation
- **User Profiles** - Manage your professional information and track your progress

### Technical Features
- User authentication and session management
- Profile completion workflow
- Integration with Perplexity AI for intelligent suggestions
- Responsive design for all devices
- Secure password storage
- Database-driven architecture

## Quick Start

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- XAMPP, WAMP, or MAMP (for local development)
- Composer (for dependency management)

### Installation

1. **Clone or Download the Repository**
   ```bash
   cd /Applications/XAMPP/xamppfiles/htdocs/
   # Place the Skillsync folder here
   ```

2. **Install Dependencies**
   ```bash
   composer install
   ```

3. **Configure Database**
   
   Create a new database in MySQL:
   ```sql
   CREATE DATABASE skillsync;
   ```

   Import the database schema:
   ```bash
   mysql -u root -p skillsync < database/jobs.sql
   ```

4. **Configure Environment Variables**
   
   Create/Edit `app/config/.env`:
   ```env
   DB_HOST=localhost
   DB_NAME=skillsync
   DB_USER=root
   DB_PASS=your_password
   
   PERPLEXITY_API_KEY=your_api_key_here
   ```

5. **Update Database Connection**
   
   Edit `app/config/database.php` with your database credentials:
   ```php
   $host = 'localhost';
   $db   = 'skillsync';
   $user = 'root';
   $pass = 'your_password';
   ```

6. **Start Your Server**
   ```bash
   # If using XAMPP, start Apache and MySQL
   # Then navigate to:
   http://localhost/Skillsync/
   ```

## Project Structure

```
Skillsync/
├── public/                      # Publicly accessible files
│   ├── css/
│   │   └── style.css           # Main stylesheet
│   ├── js/
│   │   └── main.js             # JavaScript functionality
│   ├── images/
│   │   └── favicon.svg         # App favicon
│   ├── create.php              # Resume creation page
│   ├── chatbot.php             # AI chatbot interface
│   ├── jobs.php                # Job listings
│   ├── job_post.php            # Job posting page
│   └── logout.php              # Logout handler
│
├── app/                         # Application core
│   ├── config/
│   │   ├── database.php        # Database connection
│   │   └── .env                # Environment variables
│   ├── controllers/
│   │   ├── ProfileController.php    # Profile management
│   │   ├── ChatController.php       # Chatbot backend
│   │   └── PerplexityController.php # AI integration
│   ├── models/                 # Data models
│   └── views/
│       ├── form.php            # Profile completion form
│       ├── dashboard.php       # User dashboard
│       ├── profile.php         # User profile page
│       ├── resume.php          # Resume viewer
│       ├── resume-builder.php  # Resume builder interface
│       ├── about.php           # About page
│       └── developers.php      # Developers page
│
├── includes/                    # Reusable components
│   ├── auth/
│   │   └── Auth.php            # Authentication logic
│   └── partials/
│       ├── navbar.php          # Navigation bar
│       └── modals.php          # Modal components
│
├── database/                    # Database files
│   └── jobs.sql                # Database schema
│
├── storage/                     # Storage directories
│   ├── logs/                   # Application logs
│   └── uploads/                # User uploads
│
├── vendor/                      # Composer dependencies
│
├── index.php                    # Application entry point
├── composer.json                # Composer configuration
├── .htaccess                    # Apache configuration
├── .gitignore                   # Git ignore rules
└── README.md                    # This file
```

## Configuration

### Database Schema

The application requires the following tables:
- `users` - User accounts and authentication
- `profiles` - User profile information
- `resumes` - Resume data
- `jobs` - Job listings
- `skills` - Skills database

### Environment Variables

Required environment variables in `.env`:
```
DB_HOST=localhost
DB_NAME=skillsync
DB_USER=root
DB_PASS=

PERPLEXITY_API_KEY=your_key_here
APP_ENV=development
APP_DEBUG=true
```

## Usage

### Creating an Account

1. Visit `http://localhost/Skillsync/`
2. Click "Sign Up" in the navigation
3. Enter your email and password
4. Complete your profile in the form
5. Access your dashboard

### Building a Resume

1. Log in to your account
2. Navigate to "Resume Builder"
3. Fill in your information
4. Choose a template
5. Generate your resume

### Using AI Features

- **Skill Suggestions**: The AI analyzes your profile and suggests relevant skills
- **Job Matching**: Get personalized job recommendations
- **Resume Analysis**: Upload your resume for detailed feedback
- **Chatbot**: Ask questions about career development and resume tips

## Technologies Used

### Backend
- **PHP 7.4+** - Server-side logic
- **MySQL** - Database management
- **PDO** - Database abstraction layer
- **Composer** - Dependency management

### Frontend
- **HTML5** - Structure
- **CSS3** - Styling (with custom properties)
- **JavaScript (ES6+)** - Interactivity
- **Google Fonts (Poppins)** - Typography

### Integrations
- **Perplexity AI** - AI-powered suggestions and analysis

## Security Features

- Session-based authentication
- SQL injection prevention using PDO prepared statements
- XSS protection
- Secure password handling

## Development

### Adding New Features

1. **Controllers**: Add to `app/controllers/`
2. **Views**: Add to `app/views/`
3. **Models**: Add to `app/models/`
4. **Routes**: Update `index.php` or create a routing file

### Code Style

- Follow PSR-12 coding standards
- Use meaningful variable and function names
- Comment complex logic
- Keep functions focused and single-purpose

## Troubleshooting

### Common Issues

**Database Connection Errors**
- Check your database credentials in `app/config/database.php`
- Ensure MySQL server is running
- Verify database exists

**Session Errors**
- Check PHP session configuration
- Ensure write permissions on session directory
- Clear browser cookies

**File Upload Issues**
- Check `storage/uploads/` permissions (755 or 777)
- Verify PHP upload settings in `php.ini`
- Check file size limits

## Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Authors

- **Developers Team** - [Developers Page](developers.php)

## Acknowledgments

- Perplexity AI for intelligent suggestions
- Google Fonts for typography
- XAMPP for local development environment

## Support

For support, please visit the [About Us](about.php) page or contact the development team.

---

## Design Philosophy

### Core Principles

#### Modular Component Architecture
We follow a modular approach where reusable components (like navbar, footer) are separated into their own files and included where needed. This ensures:
- **DRY (Don't Repeat Yourself)**: Write once, use everywhere
- **Easy Maintenance**: Update one file, changes reflect everywhere
- **Scalability**: Add new pages without rewriting common components

#### Modern Dark Theme Aesthetic
```css
Color Palette:
- Primary: #6366f1 (Indigo) - Main brand color
- Secondary: #ec4899 (Pink) - Accent highlights
- Accent: #14b8a6 (Teal) - Tertiary accents
- Background: #0f172a (Dark Navy) - Main background
- Cards: #1e293b (Slate) - Card backgrounds
- Text: #f1f5f9 (Light Gray) - Primary text
- Muted: #94a3b8 (Medium Gray) - Secondary text
```

#### Smooth Animations & Micro-interactions
Every user interaction has feedback:
- Hover effects with sliding backgrounds
- Icon bounce animations
- Card lift effects on hover
- Smooth transitions (0.3s cubic-bezier)
- Loading animations (fadeInUp)

#### Responsive First
Mobile → Tablet → Desktop progression:
- **Mobile (< 768px)**: Icon-only navigation, single column
- **Tablet (768px - 1024px)**: Compact layout
- **Desktop (> 1024px)**: Full experience

#### Typography Hierarchy
```
Headings: 'Sora' (bold, modern, distinctive)
Body: 'Inter' (clean, readable, professional)
Sizing: clamp() for fluid typography
```

## Component System

### Navbar Component

**File Location**: `/includes/partials/navbar.php`

#### How It Works:
```php
// Navbar detects its location automatically
$base_path = '';
if (strpos($_SERVER['PHP_SELF'], '/app/views/') !== false) {
    $base_path = '../../';  // From app/views/ → root
} elseif (strpos($_SERVER['PHP_SELF'], '/public/') !== false) {
    $base_path = '../';      // From public/ → root
}

// Uses base_path for all links
<a href="<?= $base_path ?>app/views/dashboard.php">Dashboard</a>

// Auto-detects active page
$current_page = basename($_SERVER['PHP_SELF']);
class="<?= $current_page == 'dashboard.php' ? 'active' : '' ?>"
```

#### Include in Pages:
```php
// From /app/views/dashboard.php
<?php include __DIR__ . '/../../includes/partials/navbar.php'; ?>

// From /public/jobs.php
<?php include __DIR__ . '/../includes/partials/navbar.php'; ?>
```

## Path Resolution Guide

### Understanding Relative Paths

```
Current File: /app/views/dashboard.php

To reach:
├── Root (index.php)           → ../../index.php
├── Config (database.php)      → ../config/database.php
├── Navbar                     → ../../includes/partials/navbar.php
├── Public files               → ../../public/jobs.php
└── Images                     → ../../public/images/favicon.svg
```

```
Current File: /public/jobs.php

To reach:
├── Root (index.php)           → ../index.php
├── Config (database.php)      → ../app/config/database.php
├── Navbar                     → ../includes/partials/navbar.php
├── Views                      → ../app/views/dashboard.php
└── Images                     → images/favicon.svg (same folder)
```

## Design System

### CSS Variables
All components use CSS variables for consistency:
```css
:root {
  /* Colors */
  --primary: #6366f1;
  --secondary: #ec4899;
  --accent: #14b8a6;
  
  /* Backgrounds */
  --bg-dark: #0f172a;
  --bg-card: #1e293b;
  
  /* Text */
  --text-light: #f1f5f9;
  --text-gray: #94a3b8;
  
  /* Borders & Shadows */
  --border: #334155;
  --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.12);
  
  /* Gradients */
  --gradient-1: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  --gradient-2: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}
```

### Animation Timing
```css
/* Fast interactions */
transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);

/* Card hovers */
transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);

/* Loading animations */
animation: fadeInUp 0.6s ease-out;
```

## Responsive Breakpoints

```css
/* Mobile First Approach */

/* Small Mobile */
@media (max-width: 480px) {
  .navbar .logo { font-size: 1.2rem; }
  .nav-links a svg { width: 16px; height: 16px; }
}

/* Mobile */
@media (max-width: 768px) {
  .nav-links a span { display: none; }  /* Icons only */
  .dashboard-cards { grid-template-columns: 1fr; }
}

/* Tablet */
@media (max-width: 1024px) {
  .nav-links { gap: 0.3rem; }
  .nav-links a { padding: 0.6rem 1rem; }
}

/* Desktop */
@media (min-width: 1025px) {
  /* Full experience */
}
```

## Best Practices Implemented

### Security
```php
// Session management
session_start();

// Authentication checks
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit;
}

// SQL injection prevention
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);

// XSS prevention
echo htmlspecialchars($username);
```

### Performance
- CSS variables for instant theme changes
- Lazy loading animations (staggered delays)
- Minimal JavaScript (pure CSS animations)
- Optimized SVG icons (inline, no requests)

### Maintainability
- Single source of truth for styles (CSS variables)
- Reusable components (navbar, footer)
- Clear file organization
- Commented code sections

### Accessibility
- Semantic HTML5 elements
- Proper heading hierarchy (h1 → h3)
- High contrast text (WCAG AA compliant)
- Keyboard navigation support

## Component Reusability

### How to Add Navbar to Any Page

**Step 1**: Create your page in appropriate directory
```php
// /app/views/newpage.php or /public/newpage.php
```

**Step 2**: Include navbar
```php
<!DOCTYPE html>
<html>
<head>
  <!-- Your head content -->
  <style>
    /* Include CSS variables */
    :root {
      --primary: #6366f1;
      /* ...other variables... */
    }
  </style>
</head>
<body>
  <!-- Include navbar -->
  <?php include __DIR__ . '/../../includes/partials/navbar.php'; ?>
  
  <!-- Your page content -->
</body>
</html>
```

**Step 3**: No additional configuration needed
- Navbar auto-detects location
- Paths resolve automatically
- Active state works immediately

## Key Innovations

### Self-Aware Components
Navbar knows where it is and adjusts paths automatically:
```php
// No manual configuration needed
if (strpos($_SERVER['PHP_SELF'], '/app/views/') !== false) {
    $base_path = '../../';
}
```

### Gradient System
Four distinct gradients for visual hierarchy:
```css
--gradient-1: Purple/Violet (Primary features)
--gradient-2: Pink/Red (Jobs/Opportunities)
--gradient-3: Blue/Cyan (Building/Creating)
--gradient-4: Pink/Yellow (Analysis/AI)
```

### Microanimations
Every interaction is delightful:
- Icon bounce on hover
- Sliding background reveals
- Card lift with shadow
- Pulse effect on AI button

## Files Overview

| File | Purpose | Includes |
|------|---------|----------|
| `navbar.php` | Navigation component | Standalone, auto-configuring |
| `dashboard.php` | Main user dashboard | navbar.php |
| `index.php` | Landing page | Own navigation (public) |
| `database.php` | PDO connection | None (config) |

## Extension Guide

### To Add a New Page:

1. **Create file** in `/app/views/` or `/public/`
2. **Include navbar**: `<?php include __DIR__ . '/../../includes/partials/navbar.php'; ?>`
3. **Add CSS variables** in `<style>` tag
4. **Write content** with existing classes
5. **Done** - Navbar automatically updates

### To Modify Design:

1. **Colors**: Update CSS variables in `:root`
2. **Animations**: Adjust transition timings
3. **Spacing**: Modify padding/gap values
4. **Breakpoints**: Adjust media queries

---

**Design System**: Modern, Dark, Gradient-heavy, Animated
**Architecture**: Modular, Component-based, Self-configuring
**Philosophy**: Beautiful, Functional, Maintainable
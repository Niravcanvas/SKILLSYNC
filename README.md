# Skillsync AI — Intelligent Career Platform

<div align="center">

![Skillsync AI](https://img.shields.io/badge/Skillsync-AI-6366f1?style=for-the-badge&logo=sparkles)
![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql)
![Groq AI](https://img.shields.io/badge/Groq-AI-F55036?style=for-the-badge)
![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?style=for-the-badge&logo=docker)

**An AI-powered career platform that helps job seekers build resumes, track applications, generate cover letters, and close skill gaps — all in one place.**

</div>

---

## Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Tech Stack](#tech-stack)
- [Project Structure](#project-structure)
- [Quick Start (Local / XAMPP)](#quick-start-local--xampp)
- [Docker Deployment](#docker-deployment)
- [Environment Variables](#environment-variables)
- [Database Schema](#database-schema)
- [AI Integration](#ai-integration)
- [Design System](#design-system)
- [File Placement Guide](#file-placement-guide)
- [Troubleshooting](#troubleshooting)
- [Contributing](#contributing)

---

## Overview

Skillsync AI is a full-stack PHP web application that combines a traditional resume-building workflow with modern AI capabilities powered by [Groq](https://console.groq.com/). Users sign up, complete their career profile, and gain access to a suite of tools designed to accelerate their job search.

---

## Features

### Core Pages
| Page | Path | Description |
|------|------|-------------|
| Landing | `/index.php` | Public homepage with login / signup modals |
| Dashboard | `/app/views/dashboard.php` | User home with profile summary and quick stats |
| Profile | `/app/views/profile.php` | Manage education, experience, skills, certifications, projects |
| Resume Builder | `/app/views/resume-builder.php` | Build a printable resume with AI-generated career objective |
| Resume Preview | `/app/views/resume.php` | View and download the generated resume |
| Jobs | `/public/jobs.php` | Browse and post job listings |
| AI Assistant | `/public/chatbot.php` | Conversational AI chatbot with full user context |
| Applications Tracker | `/app/views/applications.php` | Track job applications by status |
| Cover Letter Generator | `/app/views/cover-letter.php` | AI-generated tailored cover letters |
| Skill Gap Analysis | `/app/views/skill-gap.php` | Compare your skills against a job description |
| Profile Form | `/app/views/form.php` | Onboarding form after signup |

### AI-Powered Features
- **AI Chatbot** — Conversational assistant with full awareness of the user's profile (name, skills, experience, education, projects, certifications)
- **Resume Career Objective** — Groq generates a personalized 2–3 sentence objective based on your profile
- **Cover Letter Generator** — Paste a job description, choose a tone (Professional / Enthusiastic / Formal / Creative), and get a tailored letter in seconds
- **Skill Gap Analysis** — AI extracts required skills from any job description and shows which you have vs. which you need to learn

### Platform Features
- Secure session-based authentication
- Profile completion workflow with onboarding redirect
- Mobile-responsive navbar with hamburger menu
- Application status tracking (Applied → Interview → Offer / Rejected)
- Salary, location, and notes per application

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Language | PHP 8.2 |
| Database | MySQL 8.0 / MariaDB 10.4 |
| AI API | Groq (`llama-3.3-70b-versatile`) |
| Web Server | Apache 2.4 (XAMPP locally, Apache in Docker) |
| Frontend | Vanilla HTML, CSS3, JavaScript (ES6+) |
| Fonts | Sora (headings), Inter (body), Crimson Pro (resume) |
| Icons | Inline SVG |
| Local Dev | XAMPP on macOS / Windows |
| Deployment | Docker + Docker Compose |

---

## Project Structure

```
Skillsync/
├── app/
│   ├── config/
│   │   ├── database.php              # PDO connection singleton
│   │   └── .env                      # Environment variables (not committed)
│   ├── controllers/
│   │   ├── chat_backend.php          # AI chatbot handler (Groq)
│   │   ├── cover_letter_backend.php  # Cover letter generation (Groq)
│   │   ├── skill_gap_backend.php     # Skill extraction + comparison (Groq)
│   │   ├── groq_resume.php           # Resume career objective (Groq)
│   │   ├── application_form.php      # Add/update job application (CRUD)
│   │   └── ProfileController.php     # Profile management
│   └── views/
│       ├── dashboard.php             # User dashboard
│       ├── profile.php               # Full profile management
│       ├── form.php                  # Onboarding profile form
│       ├── resume.php                # Resume viewer / print
│       ├── resume-builder.php        # Resume builder interface
│       ├── applications.php          # Applications tracker
│       ├── cover-letter.php          # Cover letter generator
│       └── skill-gap.php             # Skill gap analysis
│
├── includes/
│   ├── auth/
│   │   └── Auth.php                  # Authentication helpers
│   └── partials/
│       └── navbar.php                # Shared mobile-responsive navbar
│
├── public/
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   └── main.js
│   ├── images/
│   │   └── favicon.svg
│   ├── chatbot.php                   # AI Assistant page (public entry)
│   ├── jobs.php                      # Job listings
│   ├── job_post.php                  # Post a job
│   └── logout.php                    # Session logout
│
├── database/
│   └── dump.sql                      # Full schema + seed data
│
├── storage/
│   ├── logs/
│   └── uploads/
│
├── uploads/
│   └── profiles/                     # User profile pictures
│
├── index.php                         # App entry point / landing page
├── docker-compose.yml                # Docker orchestration
├── Dockerfile                        # PHP + Apache container
├── .env.example                      # Environment template
├── .htaccess                         # Apache rewrite rules
├── composer.json
└── README.md
```

---

## Quick Start (Local / XAMPP)

### Prerequisites
- [XAMPP](https://www.apachefriends.org/) with PHP 8.2+ and MySQL
- A free [Groq API key](https://console.groq.com/keys)

### Steps

**1. Place the project**
```bash
# macOS
cp -r Skillsync /Applications/XAMPP/xamppfiles/htdocs/

# Windows
# Copy to C:\xampp\htdocs\Skillsync
```

**2. Create the database**

Open phpMyAdmin (`http://localhost/phpmyadmin`) and run:
```sql
CREATE DATABASE skillsync CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```
Then import `database/dump.sql`.

**3. Configure environment**
```bash
cp .env.example app/config/.env
```
Edit `app/config/.env`:
```env
DB_HOST=localhost
DB_NAME=skillsync
DB_USER=root
DB_PASS=

GROQ_API_KEY=gsk_your_key_here

APP_ENV=development
APP_DEBUG=true
```

**4. Start XAMPP**

Start Apache and MySQL from the XAMPP control panel.

**5. Open the app**
```
http://localhost/Skillsync/
```

---

## Docker Deployment

> Use this for staging or production. No XAMPP required.

### Prerequisites
- [Docker](https://docs.docker.com/get-docker/) and [Docker Compose](https://docs.docker.com/compose/install/) installed
- A Groq API key

### Steps

**1. Clone / place the project**
```bash
git clone <your-repo> skillsync
cd skillsync
```

**2. Set environment variables**
```bash
cp .env.example .env
```
Edit `.env` with your values (see [Environment Variables](#environment-variables)).

**3. Build and run**
```bash
docker compose up -d --build
```

**4. Import the database**
```bash
docker compose exec db mysql -u skillsync_user -p skillsync < database/dump.sql
# Enter the password from your .env when prompted
```

**5. Open the app**
```
http://localhost:8080
```

### Useful Docker Commands

```bash
# View logs
docker compose logs -f

# View just the app logs
docker compose logs -f app

# Stop everything
docker compose down

# Stop and remove volumes (wipe DB)
docker compose down -v

# Rebuild after code changes
docker compose up -d --build

# Access PHP container shell
docker compose exec app bash

# Access MySQL shell
docker compose exec db mysql -u skillsync_user -pskillsync_pass skillsync
```

---

## Environment Variables

Copy `.env.example` to `app/config/.env` (local) or `.env` (Docker root).

| Variable | Description | Example |
|----------|-------------|---------|
| `DB_HOST` | Database hostname | `localhost` / `db` (Docker) |
| `DB_NAME` | Database name | `skillsync` |
| `DB_USER` | Database username | `root` |
| `DB_PASS` | Database password | *(empty for XAMPP root)* |
| `GROQ_API_KEY` | Your Groq API key | `gsk_...` |
| `APP_ENV` | Environment mode | `development` / `production` |
| `APP_DEBUG` | Show PHP errors | `true` / `false` |

---

## Database Schema

### Core Tables

**`users`** — Authentication and basic profile
```
id, full_name, email, password_hash, headline, location, phone, bio,
profile_picture, linkedin_url, github_url, portfolio_url,
profile_completed, created_at, updated_at
```

**`education`** — User education history
```
id, user_id, institution, degree, field_of_study, start_year, end_year, description
```

**`experience`** — Work experience
```
id, user_id, company, position, start_date, end_date, description, is_current
```

**`skills`** — User skills
```
id, user_id, skill_name, created_at
```

**`certifications`** — Certifications and credentials
```
id, user_id, title, issuer, cert_date, url
```

**`projects`** — Portfolio projects
```
id, user_id, project_name, description, technologies, project_url
```

**`jobs`** — Job board listings
```
id, title, company, description, location, type, salary, posted_by, created_at
```

**`applications`** — Job application tracker
```sql
CREATE TABLE applications (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    job_title   VARCHAR(255) NOT NULL,
    company     VARCHAR(255) NOT NULL,
    status      ENUM('Applied','Interview','Offer','Rejected') DEFAULT 'Applied',
    date_applied DATE NOT NULL,
    salary      VARCHAR(100),
    location    VARCHAR(255),
    job_url     TEXT,
    notes       TEXT,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_date (date_applied)
);
```

> Run the migration by visiting `http://localhost/Skillsync/create_applications_table.php` once.

---

## AI Integration

All AI features use **Groq's** free API tier with the `llama-3.3-70b-versatile` model.

### Chatbot (`chat_backend.php`)
- Fetches the full user profile from the database on each request
- Injects structured context (name, education, experience, skills, certifications, projects) into the system prompt
- Maintains conversation history in `$_SESSION['chat_history']`
- Returns chat history as JSON; the frontend renders the full list

### Cover Letter Generator (`cover_letter_backend.php`)
- Accepts job title, company name, description, and tone via POST
- Pulls user profile data automatically
- Sends a tailored prompt to Groq
- Returns a 250–300 word professional cover letter

### Skill Gap Analysis (`skill_gap_backend.php`)
- Accepts a raw job description via POST
- Groq extracts a JSON array of required skills (temperature: 0.3 for precision)
- PHP compares extracted skills against the user's `skills` table (case-insensitive)
- Returns `matched` and `missing` arrays

### Resume Objective (`groq_resume.php`)
- Accepts form data (name, education, skills, experience) via POST
- Generates a concise 2–3 sentence career objective
- Returns plain text for the resume builder textarea

### Rate Limits (Free Tier)
| Metric | Limit |
|--------|-------|
| Requests per minute | 30 |
| Tokens per minute | 14,400 |
| Tokens per day | 500,000 |

---

## Design System

Skillsync AI uses a strict design system documented in `Design.md`.

### Color Palette
| Token | Value | Use |
|-------|-------|-----|
| `--bg` | `#080e1a` | Page background |
| `--bg-card` | `#111827` | Card surfaces |
| `--bg-lift` | `#161f31` | Hover / focused inputs |
| `--border` | `#1f2d45` | Default borders |
| `--border-lit` | `#2e3f5e` | Hover borders |
| `--primary` | `#6366f1` | Indigo — brand, AI features |
| `--secondary` | `#ec4899` | Pink — jobs, opportunities |
| `--accent` | `#14b8a6` | Teal — building, creating |
| `--text` | `#f1f5f9` | Primary text |
| `--muted` | `#64748b` | Secondary text |

### Gradients
| Variable | Colors | Used for |
|----------|--------|----------|
| `--g1` | Purple → Violet | Primary actions, AI |
| `--g2` | Pink → Rose | Jobs, opportunities |
| `--g3` | Teal → Cyan | Resume, building |
| `--g4` | Amber → Orange | Stats, progress |

### Typography
- **Sora** — Headings only (`font-family: 'Sora'`)
- **Inter** — All UI text, labels, buttons
- **Crimson Pro** — Resume document only (print context)
- `clamp()` for fluid heading sizes

### Animation
- Entrances: `fadeUp` (18px + opacity, 0.5s)
- Stagger: 0.1s increments
- Hover lifts: `translateY(-5px)` + border illuminate + accent strip reveal
- Easing: `cubic-bezier(0.4, 0, 0.2, 1)`

---

## File Placement Guide

```
Skillsync/
├── index.php                                   ← Landing page
├── app/
│   ├── config/
│   │   ├── database.php
│   │   └── .env                                ← Your secrets
│   ├── controllers/
│   │   ├── chat_backend.php                    ← Chatbot AI
│   │   ├── cover_letter_backend.php            ← Cover letter AI
│   │   ├── skill_gap_backend.php               ← Skill gap AI
│   │   ├── groq_resume.php                     ← Resume objective AI
│   │   ├── application_form.php                ← Applications CRUD
│   │   └── ProfileController.php
│   └── views/
│       ├── dashboard.php
│       ├── profile.php
│       ├── form.php
│       ├── resume.php
│       ├── resume-builder.php
│       ├── applications.php                    ← NEW: App tracker
│       ├── cover-letter.php                    ← NEW: Cover letters
│       └── skill-gap.php                       ← NEW: Skill gap
├── includes/
│   └── partials/
│       └── navbar.php                          ← Shared navbar (updated)
└── public/
    ├── chatbot.php
    ├── jobs.php
    ├── job_post.php
    └── logout.php
```

### Path Rules
Pages in `/app/views/` use paths like:
- Config: `../config/database.php`
- Navbar: `../../includes/partials/navbar.php`
- Public: `../../public/images/favicon.svg`

Pages in `/public/` use paths like:
- Config: `../app/config/database.php`
- Navbar: `../includes/partials/navbar.php`

---

## Troubleshooting

| Problem | Cause | Fix |
|---------|-------|-----|
| Blank white page / HTTP 500 | PHP error, wrong include path | Enable `display_errors` or check Apache logs |
| "Groq API key not configured" | `.env` missing or wrong key | Add `GROQ_API_KEY=gsk_...` to `app/config/.env` |
| AI responses not personalized | DB queries failing | Run debug script; check column names match schema |
| "Table applications doesn't exist" | Migration not run | Visit `create_applications_table.php` in browser |
| Hamburger menu not showing | Viewport width threshold | Test at < 900px width; clear cache |
| Docker: DB connection refused | Container not ready | Wait 10s after `docker compose up`; run health check |
| Docker: Permission denied on uploads | File ownership | `docker compose exec app chmod -R 777 /var/www/html/uploads` |
| Session not persisting | Cookie / session config | Check `session_start()` is first line; no output before headers |

---

## Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/my-feature`
3. Follow the existing design system (`Design.md`)
4. Commit: `git commit -m "feat: add my feature"`
5. Push: `git push origin feature/my-feature`
6. Open a Pull Request

### Code Conventions
- PHP: PSR-12 coding standard
- SQL: Use PDO prepared statements always — no raw interpolation
- CSS: Follow the existing variable system; no hardcoded hex values
- JS: Vanilla ES6+; no jQuery or external libraries

---

## License

MIT License — see `LICENSE` for details.

---

## Acknowledgements

- [Groq](https://groq.com/) — ultra-fast LLM inference (free tier)
- [Google Fonts](https://fonts.google.com/) — Sora, Inter, Crimson Pro
- [XAMPP](https://www.apachefriends.org/) — local development environment
- Icons — all inline SVG, zero external requests

---
<div align="center">
</div>
<?php
// migrate.php — run once in browser to create all tables and seed data
// DELETE THIS FILE after running!

require_once __DIR__ . '/app/config/database.php';

$errors = [];
$success = [];

$queries = [

'users' => "CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(255) DEFAULT NULL,
  `headline` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `profile_complete` tinyint(1) NOT NULL DEFAULT 0,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",

'education' => "CREATE TABLE IF NOT EXISTS `education` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `institution` varchar(255) DEFAULT NULL,
  `degree` varchar(255) DEFAULT NULL,
  `field_of_study` varchar(255) DEFAULT NULL,
  `start_year` varchar(10) DEFAULT NULL,
  `end_year` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",

'experience' => "CREATE TABLE IF NOT EXISTS `experience` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `company` varchar(255) DEFAULT NULL,
  `position` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",

'skills' => "CREATE TABLE IF NOT EXISTS `skills` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `skill_name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",

'certifications' => "CREATE TABLE IF NOT EXISTS `certifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `issuer` varchar(255) DEFAULT NULL,
  `cert_date` date DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",

'projects' => "CREATE TABLE IF NOT EXISTS `projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `project_name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `technologies` varchar(255) DEFAULT NULL,
  `project_url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",

'jobs' => "CREATE TABLE IF NOT EXISTS `jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `company` varchar(255) NOT NULL,
  `position` varchar(255) NOT NULL,
  `working_hours` varchar(50) NOT NULL,
  `contact` varchar(20) NOT NULL,
  `description` text NOT NULL,
  `vacancies` int(5) NOT NULL,
  `location` varchar(255) DEFAULT 'Not Specified',
  `type` varchar(100) DEFAULT 'Full-time',
  `salary` varchar(100) DEFAULT 'Negotiable',
  `posted_on` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",

'applications' => "CREATE TABLE IF NOT EXISTS `applications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `job_title` varchar(255) NOT NULL,
  `company` varchar(255) NOT NULL,
  `status` enum('Applied','Interview','Offer','Rejected') DEFAULT 'Applied',
  `date_applied` date NOT NULL,
  `salary` varchar(100) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `job_url` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_date_applied` (`date_applied`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

];

// Create tables (users first, others depend on it)
foreach ($queries as $table => $sql) {
    try {
        $pdo->exec($sql);
        $success[] = "✅ Table <strong>$table</strong> — created/exists";
    } catch (PDOException $e) {
        $errors[] = "❌ Table <strong>$table</strong> — " . $e->getMessage();
    }
}

// Add foreign keys (ignore if already exist)
$fks = [
    "ALTER TABLE `applications` ADD CONSTRAINT `fk_app_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE",
    "ALTER TABLE `certifications` ADD CONSTRAINT `fk_cert_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE",
    "ALTER TABLE `education` ADD CONSTRAINT `fk_edu_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE",
    "ALTER TABLE `experience` ADD CONSTRAINT `fk_exp_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE",
    "ALTER TABLE `projects` ADD CONSTRAINT `fk_proj_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE",
    "ALTER TABLE `skills` ADD CONSTRAINT `fk_skill_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE",
];
foreach ($fks as $sql) {
    try { $pdo->exec($sql); } catch (PDOException $e) { /* already exists, ignore */ }
}

// Seed only if users table is empty
$count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
if ($count == 0) {
    $seeds = [];

    $seeds['users'] = "INSERT INTO `users` (`id`,`full_name`,`headline`,`location`,`email`,`phone`,`bio`,`profile_complete`,`password`,`created_at`) VALUES
    (1,'Aryan Mehta','Full Stack Developer','Bangalore, India','aryan.mehta@gmail.com','9876543210','Full stack developer with 3 years of experience building scalable web apps.',1,'Aryan@2005','2025-06-12 08:30:00'),
    (2,'Priya Sharma','UI/UX Designer & Frontend Developer','Pune, India','priya.sharma@outlook.com','8765432109','Creative UI/UX designer with a strong frontend background.',1,'Priya@2005','2025-07-04 10:15:00'),
    (3,'Rohan Desai','Data Analyst | Python & SQL','Mumbai, India','rohan.desai@yahoo.com','9123456780','Data analyst with hands-on experience in Python, SQL, and Power BI.',1,'Rohan@2005','2025-08-20 13:45:00'),
    (4,'Sneha Kulkarni','Backend Engineer | Java & Spring Boot','Hyderabad, India','sneha.kulkarni@gmail.com','9988776655','Backend engineer specialising in Java microservices and Spring Boot.',1,'Sneha@2005','2025-09-01 09:00:00'),
    (5,'Karan Patel','DevOps & Cloud Engineer','Ahmedabad, India','karan.patel@protonmail.com','9001122334','DevOps engineer with deep expertise in Docker, Kubernetes, and CI/CD.',1,'Karan@2005','2025-10-10 11:30:00'),
    (6,'Nirav Thakur','Full Stack Developer & AI Enthusiast','Mumbai, India','niravthakur2005@gmail.com','9653472213','Full stack developer passionate about building AI-powered web applications.',1,'nirav','2026-02-17 09:10:11'),
    (7,'Lawrance Johnwilson Nadar','Backend Developer','Mumbai, India','lawrencejohnwilson28624@gmail.com','9653472942','A motivated and adaptable individual seeking to begin my professional journey.',1,'Lawrence','2025-09-09 03:36:48')";

    $seeds['education'] = "INSERT INTO `education` (`id`,`user_id`,`institution`,`degree`,`field_of_study`,`start_year`,`end_year`) VALUES
    (1,1,'RV College of Engineering','Bachelor of Engineering','Computer Science','2020','2024'),
    (2,2,'Symbiosis Institute of Technology','Bachelor of Technology','Information Technology','2020','2024'),
    (3,3,'VJTI Mumbai','Bachelor of Engineering','Electronics & CS','2019','2023'),
    (4,4,'BITS Pilani Hyderabad','Bachelor of Engineering','Computer Science','2020','2024'),
    (5,5,'Nirma University','Bachelor of Technology','Information Technology','2019','2023'),
    (7,7,'Pillai HOC College of Arts Commerce and Science','Bachelor of Science','Data Science','2021','2025'),
    (8,6,'University of Mumbai','Bachelor of Science','Information Technology','2023','2026')";

    $seeds['experience'] = "INSERT INTO `experience` (`id`,`user_id`,`company`,`position`,`start_date`,`end_date`,`description`) VALUES
    (1,1,'Infosys','Software Engineer Intern','2023-06-01','2023-08-31','Built internal dashboard features using React and Node.js.'),
    (2,2,'Zeta Suite','UI/UX Design Intern','2023-05-01','2023-07-31','Redesigned the onboarding flow for a B2B SaaS product.'),
    (3,3,'Razorpay','Data Analyst Intern','2022-12-01','2023-03-31','Analysed 5M+ transaction records using Python and SQL.'),
    (4,4,'Freshworks','Backend Developer Intern','2023-07-01','2023-12-31','Developed and maintained Spring Boot microservices.'),
    (5,5,'Jio Platforms','DevOps Intern','2022-10-01','2023-03-31','Managed CI/CD pipelines for 8 internal services.'),
    (7,7,'QUASCO LTD','Backend Intern','2024-06-01','2024-08-31','Worked on hardware networking infrastructure and backend systems.'),
    (8,6,'Skillsync AI','Full Stack Developer','2025-09-01',NULL,'Building Skillsync AI from the ground up.')";

    $seeds['skills'] = "INSERT INTO `skills` (`id`,`user_id`,`skill_name`) VALUES
    (1,1,'React'),(2,1,'Node.js'),(3,1,'TypeScript'),(4,1,'PostgreSQL'),(5,1,'Docker'),
    (9,2,'Figma'),(10,2,'Vue.js'),(11,2,'Tailwind CSS'),(14,2,'JavaScript'),
    (17,3,'Python'),(18,3,'SQL'),(19,3,'Power BI'),(20,3,'Pandas'),
    (25,4,'Java'),(26,4,'Spring Boot'),(27,4,'Kafka'),(28,4,'MySQL'),
    (33,5,'Docker'),(34,5,'Kubernetes'),(35,5,'Terraform'),(36,5,'AWS'),
    (57,6,'React'),(58,6,'PHP'),(59,6,'MySQL'),(60,6,'JavaScript'),(61,6,'Tailwind CSS'),(62,6,'Node.js'),(63,6,'Python'),(64,6,'Git'),
    (49,7,'Python'),(50,7,'Django'),(51,7,'MySQL'),(52,7,'REST APIs'),(54,7,'Java'),(55,7,'Git')";

    $seeds['certifications'] = "INSERT INTO `certifications` (`id`,`user_id`,`title`,`issuer`,`cert_date`,`url`) VALUES
    (1,1,'Meta Frontend Developer Certificate','Meta / Coursera','2024-01-15','https://coursera.org/verify/meta-frontend'),
    (8,6,'Responsive Web Design','freeCodeCamp','2024-08-01','https://freecodecamp.org/certification/nirav/responsive-web-design')";

    $seeds['projects'] = "INSERT INTO `projects` (`id`,`user_id`,`project_name`,`description`,`technologies`,`project_url`) VALUES
    (1,1,'DevCollab','Real-time collaborative code editor.','React, Node.js, Socket.io','https://github.com/aryanmehta/devcollab'),
    (8,6,'Skillsync AI','An AI-powered resume builder and career platform.','PHP, MySQL, JavaScript, Tailwind CSS','https://github.com/niravthakur/skillsync')";

    $seeds['jobs'] = "INSERT INTO `jobs` (`id`,`user_id`,`title`,`company`,`position`,`working_hours`,`contact`,`description`,`vacancies`,`location`,`type`,`salary`,`posted_on`) VALUES
    (1,1,'Frontend Developer','TechSoft Solutions','Software Engineer','9AM-6PM','9876543210','Build responsive SPAs using React and TypeScript.',2,'Bangalore','Full-time','6L-9L','2026-01-10'),
    (2,2,'UI/UX Designer','Zeta Suite','Product Designer','10AM-7PM','8765432109','Own the end-to-end design process.',1,'Pune','Full-time','5L-8L','2026-01-14'),
    (3,3,'Data Analyst','Razorpay','Business Analyst','9AM-6PM','9123456780','Analyse large payment datasets.',2,'Mumbai','Full-time','7L-11L','2026-01-18')";

    foreach ($seeds as $table => $sql) {
        try {
            $pdo->exec($sql);
            $success[] = "✅ Seeded <strong>$table</strong>";
        } catch (PDOException $e) {
            $errors[] = "❌ Seed <strong>$table</strong> — " . $e->getMessage();
        }
    }
} else {
    $success[] = "ℹ️ Users already exist ($count rows) — skipped seeding";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Skillsync Migration</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: monospace; background: #080e1a; color: #f1f5f9; padding: 2rem; }
  h1 { color: #6366f1; margin-bottom: 1.5rem; font-size: 1.4rem; }
  p { margin: .35rem 0; font-size: .9rem; }
  .ok { color: #10b981; }
  .err { color: #ef4444; }
  .done { color: #10b981; font-size: 1.3rem; margin-top: 1.5rem; font-weight: bold; }
  .warn { color: #fbbf24; margin-top: 2rem; padding: 1rem 1.5rem; border: 1px solid #f59e0b; border-radius: .5rem; line-height: 1.6; }
  code { background: #1f2d45; padding: .1rem .4rem; border-radius: .25rem; }
</style>
</head>
<body>
<h1>⚡ Skillsync DB Migration</h1>

<?php foreach ($success as $msg): ?>
  <p class="ok"><?= $msg ?></p>
<?php endforeach; ?>
<?php foreach ($errors as $msg): ?>
  <p class="err"><?= $msg ?></p>
<?php endforeach; ?>

<?php if (empty($errors)): ?>
  <p class="done">✅ All done! Database is ready.</p>
<?php else: ?>
  <p class="err" style="font-size:1.1rem;margin-top:1rem;">⚠️ Some errors above — check them.</p>
<?php endif; ?>

<div class="warn">
  ⚠️ <strong>Delete this file now!</strong><br>
  Remove <code>migrate.php</code> from your repo and redeploy. Leaving it up exposes your DB structure.
</div>
</body>
</html>
<?php
require __DIR__ . '/Templates/dbcon.php';

try {
    // Start transaction
    $pdo->beginTransaction();

    // 1️⃣ Insert new user with plain password
    $stmt = $pdo->prepare("INSERT INTO users (full_name, headline, location, email, phone, bio, profile_picture, profile_complete, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        'Lawrance Johnwilson Nadar', // full_name
        'Aspiring Web Developer',    // headline
        'Mumbai, India',             // location
        'lawrencejohnwilson28624@gmail.com', // email
        '+91 9876543210',            // phone
        'Passionate about building interactive web applications.', // bio
        null,                        // profile_picture
        1,                           // profile_complete
        'Lawarnce'                   // password (plain)
    ]);

    $user_id = $pdo->lastInsertId();

    // 2️⃣ Insert Education
    $educations = [
        ['ABC University', 'B.Tech', 'Computer Engineering', '2019', '2023'],
        ['XYZ High School', 'High School Diploma', 'Science', '2017', '2019']
    ];

    $stmt = $pdo->prepare("INSERT INTO education (user_id, institution, degree, field_of_study, start_year, end_year) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($educations as $edu) {
        $stmt->execute([$user_id, $edu[0], $edu[1], $edu[2], $edu[3], $edu[4]]);
    }

    // 3️⃣ Insert Experience (YYYY-MM-DD format)
    $experiences = [
        ['Tech Solutions Pvt Ltd', 'Intern', '2024-06-01', '2024-08-31', 'Worked on front-end web development projects.'],
        ['Freelance', 'Web Developer', '2023-05-01', '2024-05-31', 'Built multiple portfolio websites for clients.']
    ];

    $stmt = $pdo->prepare("INSERT INTO experience (user_id, company, position, start_date, end_date, description) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($experiences as $exp) {
        $stmt->execute([$user_id, $exp[0], $exp[1], $exp[2], $exp[3], $exp[4]]);
    }

    // 4️⃣ Insert Skills
    $skills = ['HTML', 'CSS', 'JavaScript', 'PHP', 'MySQL'];
    $stmt = $pdo->prepare("INSERT INTO skills (user_id, skill_name) VALUES (?, ?)");
    foreach ($skills as $skill) {
        $stmt->execute([$user_id, $skill]);
    }

    // 5️⃣ Insert Certifications
    $certifications = [
        ['Frontend Web Development', 'Coursera', '2023-08-15', 'https://www.coursera.org/certificate/frontend'],
        ['PHP & MySQL Basics', 'Udemy', '2022-12-20', 'https://www.udemy.com/certificate/php-mysql']
    ];

    $stmt = $pdo->prepare("INSERT INTO certifications (user_id, title, issuer, cert_date, url) VALUES (?, ?, ?, ?, ?)");
    foreach ($certifications as $cert) {
        $stmt->execute([$user_id, $cert[0], $cert[1], $cert[2], $cert[3]]);
    }

    // 6️⃣ Insert Projects
    $projects = [
        ['Portfolio Website', 'Created a personal portfolio website using HTML, CSS, JS.', 'HTML, CSS, JavaScript', 'https://lawrance-portfolio.com'],
        ['Task Manager App', 'Developed a task management web app using PHP & MySQL.', 'PHP, MySQL, JS', 'https://lawrance-taskapp.com']
    ];

    $stmt = $pdo->prepare("INSERT INTO projects (user_id, project_name, description, technologies, project_url) VALUES (?, ?, ?, ?, ?)");
    foreach ($projects as $proj) {
        $stmt->execute([$user_id, $proj[0], $proj[1], $proj[2], $proj[3]]);
    }

    $pdo->commit();
    echo "Profile created successfully for Lawrance Johnwilson Nadar with password 'Lawarnce'!";
} catch (PDOException $e) {
    $pdo->rollBack();
    echo "Error creating profile: " . $e->getMessage();
}
?>
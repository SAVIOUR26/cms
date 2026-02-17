<?php
/**
 * KandaNews CMS — Database Schema & Utilities
 */
if (!defined('KANDA_CMS')) exit;

function initializeDatabase() {
    $db = getDatabase();

    $tables = [
        "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(255) UNIQUE,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(100),
            role ENUM('admin','editor','viewer') DEFAULT 'viewer',
            status ENUM('active','inactive','banned') DEFAULT 'active',
            last_login DATETIME,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS editions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            edition_date DATE NOT NULL,
            country_code VARCHAR(5) DEFAULT 'UG',
            theme VARCHAR(100),
            cover_image VARCHAR(500),
            file_path VARCHAR(500),
            page_count INT DEFAULT 16,
            is_premium TINYINT(1) DEFAULT 0,
            is_special TINYINT(1) DEFAULT 0,
            status ENUM('draft','published','archived') DEFAULT 'draft',
            published_at DATETIME,
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS activity_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            action VARCHAR(50) NOT NULL,
            entity_type VARCHAR(50),
            entity_id VARCHAR(50),
            description TEXT,
            ip_address VARCHAR(45),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            INDEX idx_action (action),
            INDEX idx_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    ];

    foreach ($tables as $sql) {
        try {
            $db->exec($sql);
        } catch (PDOException $e) {
            error_log("Table creation failed: " . $e->getMessage());
        }
    }
}

function getEditions($limit = 30, $country = null, $status = 'published') {
    $db = getDatabase();
    $sql = "SELECT * FROM editions WHERE status = ?";
    $params = [$status];

    if ($country) {
        $sql .= " AND country_code = ?";
        $params[] = strtoupper($country);
    }

    $sql .= " ORDER BY edition_date DESC LIMIT ?";
    $params[] = (int)$limit;

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getEditionById($id) {
    $db = getDatabase();
    $stmt = $db->prepare("SELECT * FROM editions WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

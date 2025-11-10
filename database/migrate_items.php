<?php
/**
 * Projects table migration
 * Creates the projects table for user-created projects
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Create projects table
    $conn->exec("
        CREATE TABLE IF NOT EXISTS projects (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");
    
    // Create indexes for better performance
    $conn->exec("CREATE INDEX IF NOT EXISTS idx_projects_user ON projects(user_id)");
    $conn->exec("CREATE INDEX IF NOT EXISTS idx_projects_created ON projects(created_at)");
    
    echo "✅ Projects table created successfully!\n";
    
} catch (PDOException $e) {
    echo "❌ Projects migration failed: " . $e->getMessage() . "\n";
    exit(1);
}


<?php
/**
 * MIDI Files table migration
 * Creates the midi_files table for storing generated MIDI files
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Create midi_files table
    $conn->exec("
        CREATE TABLE IF NOT EXISTS midi_files (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            project_id INTEGER NOT NULL,
            file_type VARCHAR(50) NOT NULL,
            file_path VARCHAR(255) NOT NULL,
            parameters TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
        )
    ");
    
    // Create indexes for better performance
    $conn->exec("CREATE INDEX IF NOT EXISTS idx_midi_files_project ON midi_files(project_id)");
    $conn->exec("CREATE INDEX IF NOT EXISTS idx_midi_files_type ON midi_files(file_type)");
    $conn->exec("CREATE INDEX IF NOT EXISTS idx_midi_files_created ON midi_files(created_at)");
    
    echo "✅ MIDI files table created successfully!\n";
    
} catch (PDOException $e) {
    echo "❌ MIDI files migration failed: " . $e->getMessage() . "\n";
    exit(1);
}


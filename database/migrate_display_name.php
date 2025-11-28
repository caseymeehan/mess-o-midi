<?php
/**
 * Display Name column migration
 * Adds display_name column to midi_files table for custom file naming
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Check if column already exists
    $result = $conn->query("PRAGMA table_info(midi_files)");
    $columns = $result->fetchAll(PDO::FETCH_ASSOC);
    $hasDisplayName = false;
    
    foreach ($columns as $column) {
        if ($column['name'] === 'display_name') {
            $hasDisplayName = true;
            break;
        }
    }
    
    if (!$hasDisplayName) {
        // Add display_name column (nullable, max 50 chars)
        $conn->exec("ALTER TABLE midi_files ADD COLUMN display_name VARCHAR(50) DEFAULT NULL");
        echo "✅ display_name column added to midi_files table!\n";
    } else {
        echo "ℹ️ display_name column already exists.\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Display name migration failed: " . $e->getMessage() . "\n";
    exit(1);
}


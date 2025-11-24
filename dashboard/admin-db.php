<?php
/**
 * Database Backup/Restore Admin Tool
 * Simple utility for managing database backups
 * 
 * SECURITY: Password-protected (no database dependency)
 * Set ADMIN_DB_PASSWORD environment variable in Railway
 */

require_once __DIR__ . '/../config.php';

// Password authentication (independent of database)
session_start();

$adminPassword = getenv('ADMIN_DB_PASSWORD') ?: 'CHANGE_ME_IMMEDIATELY';
$isAuthenticated = isset($_SESSION['admin_db_authenticated']) && $_SESSION['admin_db_authenticated'] === true;

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password']) && !$isAuthenticated) {
    if ($_POST['password'] === $adminPassword) {
        $_SESSION['admin_db_authenticated'] = true;
        $isAuthenticated = true;
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $loginError = 'Invalid password';
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    unset($_SESSION['admin_db_authenticated']);
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Require authentication
if (!$isAuthenticated) {
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Admin Login - <?php echo SITE_NAME; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }
        h1 { font-size: 24px; margin-bottom: 10px; color: #333; }
        p { color: #666; margin-bottom: 30px; font-size: 14px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 500; color: #333; }
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
        }
        .btn {
            width: 100%;
            padding: 12px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn:hover { background: #5568d3; }
        .error {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #fcc;
        }
        .warning {
            background: #fffbea;
            color: #c87619;
            padding: 12px;
            border-radius: 6px;
            margin-top: 20px;
            font-size: 13px;
            border: 1px solid #ffeaa7;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>🔐 Database Admin</h1>
        <p>Enter admin password to manage database backups</p>
        
        <?php if (isset($loginError)): ?>
            <div class="error"><?php echo htmlspecialchars($loginError); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="password">Admin Password</label>
                <input type="password" id="password" name="password" required autofocus>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
        
        <?php if ($adminPassword === 'CHANGE_ME_IMMEDIATELY'): ?>
            <div class="warning">
                ⚠️ <strong>Default password detected!</strong><br>
                Set <code>ADMIN_DB_PASSWORD</code> environment variable in Railway for security.
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
    <?php
    exit;
}

$message = '';
$error = '';

// Handle database download
if (isset($_GET['action']) && $_GET['action'] === 'download') {
    $dbPath = DB_PATH;
    if (file_exists($dbPath)) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="saas-backup-' . date('Y-m-d-His') . '.db"');
        header('Content-Length: ' . filesize($dbPath));
        readfile($dbPath);
        exit;
    } else {
        $error = 'Database file not found';
    }
}

// Handle database upload/restore
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['database'])) {
    $uploadedFile = $_FILES['database'];
    
    if ($uploadedFile['error'] !== UPLOAD_ERR_OK) {
        $error = 'Upload failed with error code: ' . $uploadedFile['error'];
    } else {
        $dbPath = DB_PATH;
        $backupPath = dirname($dbPath) . '/saas-backup-' . date('Y-m-d-His') . '.db';
        
        // Backup existing database
        if (file_exists($dbPath)) {
            copy($dbPath, $backupPath);
            $message .= 'Existing database backed up to: ' . basename($backupPath) . '<br>';
        }
        
        // Move uploaded file to database location
        if (move_uploaded_file($uploadedFile['tmp_name'], $dbPath)) {
            // Verify it's a valid SQLite database
            try {
                $testDb = new SQLite3($dbPath);
                $testDb->close();
                $message .= 'Database restored successfully!<br>';
                $message .= 'Size: ' . number_format(filesize($dbPath)) . ' bytes';
            } catch (Exception $e) {
                // Restore backup if verification failed
                if (file_exists($backupPath)) {
                    copy($backupPath, $dbPath);
                }
                $error = 'Invalid SQLite database file. Original database restored.';
            }
        } else {
            $error = 'Failed to move uploaded file';
        }
    }
}

// Get current database info
$dbPath = DB_PATH;
$dbExists = file_exists($dbPath);
$dbSize = $dbExists ? filesize($dbPath) : 0;
$dbModified = $dbExists ? date('Y-m-d H:i:s', filemtime($dbPath)) : 'N/A';

// Get table count
$tableCount = 0;
$userCount = 0;
$projectCount = 0;

if ($dbExists) {
    try {
        $db = new SQLite3($dbPath);
        $result = $db->query("SELECT COUNT(*) as count FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
        $row = $result->fetchArray();
        $tableCount = $row['count'];
        
        $result = $db->query("SELECT COUNT(*) as count FROM users");
        $row = $result->fetchArray();
        $userCount = $row['count'];
        
        $result = $db->query("SELECT COUNT(*) as count FROM projects");
        $row = $result->fetchArray();
        $projectCount = $row['count'];
        
        $db->close();
    } catch (Exception $e) {
        $error = 'Error reading database: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Admin - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .admin-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
        }
        .db-info {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .db-info-item {
            margin: 10px 0;
            display: flex;
            justify-content: space-between;
        }
        .db-info-label {
            font-weight: bold;
        }
        .action-section {
            background: white;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .btn {
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover {
            background: #0056b3;
        }
        .btn-danger {
            background: #dc3545;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .btn-success {
            background: #28a745;
        }
        .btn-success:hover {
            background: #218838;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h1>🗄️ Database Administration</h1>
            <a href="?logout=1" class="btn" style="width: auto; padding: 8px 16px; font-size: 14px; background: #6c757d;">Logout</a>
        </div>
        <p><a href="/dashboard/">← Back to Dashboard</a> | <a href="/">← Home</a></p>
        
        <?php if ($adminPassword === 'CHANGE_ME_IMMEDIATELY'): ?>
            <div class="error">
                <strong>⚠️ SECURITY WARNING:</strong> You're using the default password!<br>
                Set <code>ADMIN_DB_PASSWORD</code> environment variable in Railway immediately.
            </div>
        <?php endif; ?>
        
        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="db-info">
            <h2>Current Database Status</h2>
            <div class="db-info-item">
                <span class="db-info-label">Database Path:</span>
                <span><?php echo htmlspecialchars($dbPath); ?></span>
            </div>
            <div class="db-info-item">
                <span class="db-info-label">Status:</span>
                <span><?php echo $dbExists ? '✅ Exists' : '❌ Not Found'; ?></span>
            </div>
            <div class="db-info-item">
                <span class="db-info-label">Size:</span>
                <span><?php echo number_format($dbSize); ?> bytes</span>
            </div>
            <div class="db-info-item">
                <span class="db-info-label">Last Modified:</span>
                <span><?php echo $dbModified; ?></span>
            </div>
            <div class="db-info-item">
                <span class="db-info-label">Tables:</span>
                <span><?php echo $tableCount; ?></span>
            </div>
            <div class="db-info-item">
                <span class="db-info-label">Users:</span>
                <span><?php echo $userCount; ?></span>
            </div>
            <div class="db-info-item">
                <span class="db-info-label">Projects:</span>
                <span><?php echo $projectCount; ?></span>
            </div>
        </div>
        
        <div class="action-section">
            <h2>📥 Download Database Backup</h2>
            <p>Download the current database file for safekeeping.</p>
            <a href="?action=download" class="btn btn-success">Download Backup</a>
        </div>
        
        <div class="action-section">
            <h2>📤 Restore Database from Backup</h2>
            <p><strong>⚠️ Warning:</strong> This will replace the current database. A backup of the existing database will be created automatically.</p>
            <form method="POST" enctype="multipart/form-data" onsubmit="return confirm('Are you sure you want to replace the current database?');">
                <input type="file" name="database" accept=".db,.sqlite,.sqlite3" required style="margin-bottom: 10px;">
                <br>
                <button type="submit" class="btn btn-danger">Restore Database</button>
            </form>
        </div>
    </div>
</body>
</html>


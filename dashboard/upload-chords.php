<?php
/**
 * Upload Chords Endpoint
 * Handles file uploads for user chord MIDI files
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/Projects.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed'
    ]);
    exit;
}

// Require authentication
$auth = new Auth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
if (!$user) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Unauthorized'
    ]);
    exit;
}

try {
    // Validate project ID
    $projectId = isset($_POST['project_id']) ? (int)$_POST['project_id'] : 0;
    
    if (!$projectId) {
        throw new Exception('Missing required parameter: project_id');
    }
    
    // Verify user owns the project
    $projectsManager = new Projects();
    $project = $projectsManager->getProject($projectId, $user['id']);
    
    if (!$project) {
        throw new Exception('Project not found or you do not have permission to access it');
    }
    
    // Validate file upload
    if (!isset($_FILES['chord_file']) || $_FILES['chord_file']['error'] !== UPLOAD_ERR_OK) {
        $errorMsg = 'No file uploaded';
        if (isset($_FILES['chord_file']['error'])) {
            switch ($_FILES['chord_file']['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $errorMsg = 'File is too large';
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $errorMsg = 'No file was uploaded';
                    break;
                default:
                    $errorMsg = 'File upload failed';
            }
        }
        throw new Exception($errorMsg);
    }
    
    $uploadedFile = $_FILES['chord_file'];
    
    // Validate file extension
    $fileExt = strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION));
    if (!in_array($fileExt, ['mid', 'midi'])) {
        throw new Exception('Invalid file type. Please upload a .mid or .midi file');
    }
    
    // Validate file size (50KB max)
    $maxSize = 50 * 1024; // 50KB in bytes
    if ($uploadedFile['size'] > $maxSize) {
        throw new Exception('File is too large. Maximum size is 50KB');
    }
    
    // Validate MIDI header (first 4 bytes should be "MThd")
    $fileHandle = fopen($uploadedFile['tmp_name'], 'rb');
    if ($fileHandle) {
        $header = fread($fileHandle, 4);
        fclose($fileHandle);
        
        if ($header !== 'MThd') {
            throw new Exception('Invalid MIDI file format');
        }
    }
    
    // Get next increment number for uploaded_chords
    $existingChords = $projectsManager->getProjectMidiFiles($projectId, $user['id']);
    $maxNumber = 0;
    
    foreach ($existingChords as $file) {
        if ($file['file_type'] === 'uploaded_chords') {
            // Extract number from filename pattern: {userId}_{projectId}_uploaded_chords_{number}.mid
            if (preg_match('/_uploaded_chords_(\d+)\.mid$/', $file['file_path'], $matches)) {
                $number = (int)$matches[1];
                if ($number > $maxNumber) {
                    $maxNumber = $number;
                }
            }
        }
    }
    
    $nextNumber = $maxNumber + 1;
    
    // Generate filename
    $timestamp = time();
    $filename = "{$user['id']}_{$projectId}_uploaded_chords_{$nextNumber}.mid";
    $uploadDir = __DIR__ . '/../uploads/midi/';
    
    // Ensure upload directory exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $filepath = $uploadDir . $filename;
    
    // Move uploaded file
    if (!move_uploaded_file($uploadedFile['tmp_name'], $filepath)) {
        throw new Exception('Failed to save uploaded file');
    }
    
    // Set proper file permissions (0644 = readable by everyone, writable by owner)
    chmod($filepath, 0644);
    
    // Add to database
    $parameters = json_encode([
        'original_filename' => $uploadedFile['name'],
        'uploaded_at' => date('Y-m-d H:i:s')
    ]);
    
    $fileId = $projectsManager->addMidiFile(
        $projectId,
        'uploaded_chords',
        $filepath,
        $parameters
    );
    
    if (!$fileId) {
        // Clean up uploaded file if DB insert fails
        unlink($filepath);
        throw new Exception('Failed to save file metadata to database');
    }
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Chord file uploaded successfully',
        'file_id' => $fileId,
        'filename' => $filename,
        'chord_number' => $nextNumber
    ]);
    
} catch (Exception $e) {
    error_log('Upload chords error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}


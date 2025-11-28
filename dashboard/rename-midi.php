<?php
/**
 * Rename MIDI File Endpoint
 * Handles renaming of MIDI file display names
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
    // Get request body
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        throw new Exception('Invalid JSON input');
    }
    
    // Validate required parameters
    $fileId = isset($data['file_id']) ? (int)$data['file_id'] : 0;
    $displayName = isset($data['display_name']) ? trim($data['display_name']) : '';
    
    if (!$fileId) {
        throw new Exception('Missing required parameter: file_id');
    }
    
    // Validate display name
    $validation = Projects::validateDisplayName($displayName);
    if (!$validation['valid']) {
        throw new Exception($validation['error']);
    }
    
    // Update display name
    $projectsManager = new Projects();
    $success = $projectsManager->updateMidiDisplayName($fileId, $user['id'], $displayName);
    
    if (!$success) {
        throw new Exception('Failed to rename file. You may not have permission to modify this file.');
    }
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'File renamed successfully',
        'display_name' => $displayName ?: null
    ]);
    
} catch (Exception $e) {
    error_log('Rename MIDI error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}


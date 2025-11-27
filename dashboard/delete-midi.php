<?php
/**
 * Delete MIDI File Endpoint
 * Handles deletion of MIDI files (both generated and uploaded)
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
    $midiFileId = isset($data['file_id']) ? (int)$data['file_id'] : 0;
    
    if (!$midiFileId) {
        throw new Exception('Missing required parameter: file_id');
    }
    
    // Initialize Projects manager
    $projectsManager = new Projects();
    
    // Get MIDI file info
    $midiFile = $projectsManager->getMidiFile($midiFileId);
    
    if (!$midiFile) {
        throw new Exception('MIDI file not found');
    }
    
    // Get project to verify ownership
    $project = $projectsManager->getProject($midiFile['project_id'], $user['id']);
    
    if (!$project) {
        throw new Exception('You do not have permission to delete this file');
    }
    
    // Delete the file using the Projects method
    $success = $projectsManager->deleteMidiFile($midiFileId, $user['id']);
    
    if (!$success) {
        throw new Exception('Failed to delete MIDI file');
    }
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'MIDI file deleted successfully'
    ]);
    
} catch (Exception $e) {
    error_log('Delete MIDI error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}


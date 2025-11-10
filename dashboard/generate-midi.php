<?php
/**
 * Generate MIDI Endpoint
 * Handles AJAX requests to generate MIDI files for projects
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/Projects.php';
require_once __DIR__ . '/../includes/MidiGenerator.php';

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
    $projectId = isset($data['project_id']) ? (int)$data['project_id'] : 0;
    $type = isset($data['type']) ? trim($data['type']) : '';
    
    if (!$projectId || !$type) {
        throw new Exception('Missing required parameters: project_id and type');
    }
    
    // Verify user owns the project
    $projectsManager = new Projects();
    $project = $projectsManager->getProject($projectId, $user['id']);
    
    if (!$project) {
        throw new Exception('Project not found or you do not have permission to access it');
    }
    
    // Initialize MIDI generator
    $midiGenerator = new MidiGenerator();
    
    // Check if Python service is available
    if (!$midiGenerator->isServiceAvailable()) {
        throw new Exception('MIDI generation service is not available. Please make sure the Python service is running.');
    }
    
    // Generate MIDI file based on type
    $result = null;
    
    switch ($type) {
        case 'bass':
        case 'bassline':
            $options = [];
            
            // Add optional parameters if provided
            if (isset($data['scale'])) {
                $options['scale'] = $data['scale'];
            }
            if (isset($data['rhythm'])) {
                $options['rhythm'] = $data['rhythm'];
            }
            
            $result = $midiGenerator->generateBasslineForProject(
                $user['id'],
                $projectId,
                $options
            );
            break;
            
        default:
            throw new Exception('Unsupported MIDI type: ' . $type);
    }
    
    // Check result
    if (!$result || !$result['success']) {
        throw new Exception($result['error'] ?? 'Failed to generate MIDI file');
    }
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'MIDI file generated successfully',
        'file_id' => $result['file_id'],
        'filename' => $result['filename']
    ]);
    
} catch (Exception $e) {
    error_log('Generate MIDI error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}


<?php
/**
 * Download MIDI File
 * Handles downloading of generated MIDI files
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/Projects.php';

// Require authentication
$auth = new Auth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
if (!$user) {
    flashMessage('error', 'Unauthorized access.');
    redirect('/dashboard/');
    exit;
}

try {
    // Get MIDI file ID from URL
    $fileId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if (!$fileId) {
        throw new Exception('Invalid file ID');
    }
    
    // Get MIDI file from database
    $projectsManager = new Projects();
    $midiFile = $projectsManager->getMidiFile($fileId);
    
    if (!$midiFile) {
        throw new Exception('MIDI file not found');
    }
    
    // Verify user owns the project this MIDI file belongs to
    $project = $projectsManager->getProject($midiFile['project_id'], $user['id']);
    
    if (!$project) {
        throw new Exception('You do not have permission to access this file');
    }
    
    // Check if file exists
    $filepath = $midiFile['file_path'];
    
    if (!file_exists($filepath)) {
        throw new Exception('MIDI file not found on disk');
    }
    
    // Prepare filename for download
    $filename = basename($filepath);
    
    // Format file type for download name
    $fileTypeDisplay = $midiFile['file_type'];
    if ($midiFile['file_type'] === 'uploaded_chords') {
        // Extract number from filename for uploaded chords
        if (preg_match('/_uploaded_chords_(\d+)\.mid$/', $midiFile['file_path'], $matches)) {
            $fileTypeDisplay = 'uploaded_chords_' . $matches[1];
        }
    }
    
    $downloadName = $project['title'] . '_' . $fileTypeDisplay . '.mid';
    // Clean filename
    $downloadName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $downloadName);
    
    // Set headers for file download
    header('Content-Type: audio/midi');
    header('Content-Disposition: attachment; filename="' . $downloadName . '"');
    header('Content-Length: ' . filesize($filepath));
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: public');
    
    // Clear output buffer
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    // Output file
    readfile($filepath);
    exit;
    
} catch (Exception $e) {
    error_log('Download MIDI error: ' . $e->getMessage());
    flashMessage('error', 'Failed to download MIDI file: ' . $e->getMessage());
    
    // Redirect back to dashboard or project edit page
    if (isset($midiFile) && isset($midiFile['project_id'])) {
        redirect('/dashboard/project-edit.php?id=' . $midiFile['project_id']);
    } else {
        redirect('/dashboard/');
    }
    exit;
}


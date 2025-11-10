<?php
/**
 * Project Actions Handler
 * Handles delete and duplicate actions for projects
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/Projects.php';

$auth = new Auth();
$auth->requireAuth();

$user = $auth->getCurrentUser();
if (!$user) {
    flashMessage('error', 'Unable to load user data.');
    redirect('../auth/logout.php');
}

$projectsManager = new Projects($user['id']);

// Get action and project ID from URL
$action = $_GET['action'] ?? '';
$projectId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$projectId || !$action) {
    flashMessage('error', 'Invalid request.');
    redirect('/dashboard/');
}

// Handle actions
switch ($action) {
    case 'delete':
        $success = $projectsManager->deleteProject($projectId, $user['id']);
        if ($success) {
            flashMessage('success', 'Project deleted successfully!');
        } else {
            flashMessage('error', 'Project not found or you do not have permission to delete it.');
        }
        break;
        
    case 'duplicate':
        // Check usage limits before duplicating
        $usage = $projectsManager->getUserUsage($user['id']);
        if (!$usage['can_create']) {
            flashMessage('error', 'You have reached your project limit. Please upgrade your plan to create more projects.');
            redirect('/pricing.php');
        }
        
        $newProjectId = $projectsManager->duplicateProject($projectId, $user['id']);
        if ($newProjectId) {
            flashMessage('success', 'Project duplicated successfully!');
        } else {
            flashMessage('error', 'Project not found or you do not have permission to duplicate it.');
        }
        break;
        
    default:
        flashMessage('error', 'Invalid action.');
        break;
}

redirect('/dashboard/');


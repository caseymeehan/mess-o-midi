<?php
/**
 * Edit Project Page
 * Form to edit an existing project and generate MIDI files
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

$projectsManager = new Projects();

// Get project ID from URL
$projectId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$projectId) {
    flashMessage('error', 'Invalid project ID.');
    redirect('/dashboard/');
}

// Get the project (ensures user owns it)
$project = $projectsManager->getProject($projectId, $user['id']);

if (!$project) {
    flashMessage('error', 'Project not found or you do not have permission to edit it.');
    redirect('/dashboard/');
}

// Get MIDI files for this project
$midiFiles = $projectsManager->getProjectMidiFiles($projectId, $user['id']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    if (empty($title)) {
        flashMessage('error', 'Title is required.');
    } else {
        $success = $projectsManager->updateProject($projectId, $user['id'], $title, $description);
        
        if ($success) {
            flashMessage('success', 'Project updated successfully!');
            // Reload project data
            $project = $projectsManager->getProject($projectId, $user['id']);
        } else {
            flashMessage('error', 'Failed to update project. Please try again.');
        }
    }
}

$pageTitle = 'Edit Project';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle . ' - ' . SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo url('assets/css/style.css'); ?>">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f9fafb;
            color: #1f2937;
            min-height: 100vh;
        }

        .top-header {
            background: white;
            border-bottom: 1px solid #e5e7eb;
            padding: 1rem 2rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        }

        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .site-logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: #6366f1;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .back-link {
            color: #6366f1;
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .form-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .form-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .form-input, .form-textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 1rem;
            font-family: inherit;
            transition: border-color 0.2s;
        }

        .form-input:focus, .form-textarea:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .form-textarea {
            min-height: 120px;
            resize: vertical;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: #6366f1;
            color: white;
        }

        .btn-primary:hover {
            background: #4f46e5;
        }

        .btn-secondary {
            background: white;
            color: #6b7280;
            border: 1px solid #d1d5db;
        }

        .btn-secondary:hover {
            background: #f9fafb;
        }

        .btn-success {
            background: #10b981;
            color: white;
        }

        .btn-success:hover {
            background: #059669;
        }

        .btn-success:disabled {
            background: #9ca3af;
            cursor: not-allowed;
        }

        /* MIDI Tracks Section */
        .midi-section {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
        }

        .midi-files-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .midi-file-item {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .midi-file-info {
            flex: 1;
        }

        .midi-file-type {
            font-weight: 600;
            color: #1f2937;
            text-transform: capitalize;
        }

        .midi-file-date {
            font-size: 0.875rem;
            color: #6b7280;
            margin-top: 0.25rem;
        }

        .midi-file-actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn-small {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #6b7280;
        }

        .empty-state-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .flash-messages {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            max-width: 400px;
        }

        .flash-message {
            padding: 1rem 1.5rem;
            padding-right: 3rem;
            margin-bottom: 0.5rem;
            border-radius: 8px;
            background: white;
            border-left: 4px solid;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            animation: slideIn 0.3s ease-out;
            position: relative;
        }

        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        .flash-message.success {
            border-left-color: #10b981;
            color: #065f46;
        }

        .flash-message.error {
            border-left-color: #ef4444;
            color: #991b1b;
        }
        
        .flash-close {
            position: absolute;
            top: 0.75rem;
            right: 0.75rem;
            background: transparent;
            border: none;
            color: currentColor;
            opacity: 0.5;
            cursor: pointer;
            font-size: 1.25rem;
            line-height: 1;
            padding: 0.25rem;
            transition: opacity 0.2s;
        }
        
        .flash-close:hover {
            opacity: 1;
        }

        .spinner {
            display: inline-block;
            width: 1rem;
            height: 1rem;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        @media (max-width: 768px) {
            .main-container {
                padding: 1rem;
            }

            .form-card, .midi-section {
                padding: 1.5rem;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                text-align: center;
            }

            .section-header {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }

            .midi-file-item {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }

            .midi-file-actions {
                width: 100%;
            }

            .btn-small {
                flex: 1;
            }
        }

        /* Chord Style Modal */
        .chord-modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.2s ease-out;
        }

        .chord-modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .chord-modal-content {
            background-color: white;
            margin: auto;
            padding: 0;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            width: 90%;
            max-width: 500px;
            animation: slideUp 0.3s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from { 
                transform: translateY(30px);
                opacity: 0;
            }
            to { 
                transform: translateY(0);
                opacity: 1;
            }
        }

        .chord-modal-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .chord-modal-header h3 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
        }

        .chord-modal-close {
            background: none;
            border: none;
            font-size: 2rem;
            color: #6b7280;
            cursor: pointer;
            padding: 0;
            width: 2rem;
            height: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            transition: all 0.2s;
        }

        .chord-modal-close:hover {
            background: #f3f4f6;
            color: #1f2937;
        }

        .chord-modal-body {
            padding: 2rem;
        }

        .chord-modal-description {
            color: #4b5563;
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .chord-info-icon {
            display: inline-block;
            margin-left: 0.5rem;
            cursor: help;
            font-size: 1rem;
            color: #6366f1;
            opacity: 0.7;
            transition: opacity 0.2s;
        }

        .chord-info-icon:hover {
            opacity: 1;
        }

        .chord-button-group {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .chord-button-group .btn {
            flex: 1;
            padding: 1rem 1.5rem;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .btn-chord-simple {
            background: #8b5cf6;
            color: white;
        }

        .btn-chord-simple:hover {
            background: #7c3aed;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
        }

        .btn-chord-complex {
            background: #10b981;
            color: white;
        }

        .btn-chord-complex:hover {
            background: #059669;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        @media (max-width: 768px) {
            .chord-modal-content {
                width: 95%;
                margin: 1rem;
            }

            .chord-button-group {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <header class="top-header">
        <div class="header-content">
            <a href="<?php echo url('/dashboard/'); ?>" class="site-logo">
                <img src="/assets/images/logo.png" alt="Mess o Midi" style="width: 32px; height: 32px; vertical-align: middle;">
                <span><?php echo SITE_NAME; ?></span>
            </a>
            <a href="<?php echo url('/dashboard/'); ?>" class="back-link">
                ← Back to Projects
            </a>
        </div>
    </header>

    <?php if (hasFlashMessages()): ?>
        <div class="flash-messages">
            <?php foreach (getFlashMessages() as $flash): ?>
                <div class="flash-message <?php echo escape($flash['type']); ?>">
                    <?php echo escape($flash['message']); ?>
                    <button class="flash-close" onclick="dismissFlash(this)" aria-label="Close">&times;</button>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <main class="main-container">
        <!-- Project Details Form -->
        <div class="form-card">
            <h1 class="form-title">Edit Project</h1>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="title" class="form-label">Title *</label>
                    <input 
                        type="text" 
                        id="title" 
                        name="title" 
                        class="form-input" 
                        placeholder="Enter project title..."
                        value="<?php echo escape($project['title']); ?>"
                        required
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label for="description" class="form-label">Description</label>
                    <textarea 
                        id="description" 
                        name="description" 
                        class="form-textarea" 
                        placeholder="Add a description (optional)..."
                    ><?php echo escape($project['description']); ?></textarea>
                </div>

                <div class="form-actions">
                    <a href="<?php echo url('/dashboard/'); ?>" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>

        <!-- MIDI Tracks Section -->
        <div class="midi-section">
            <div class="section-header">
                <h2 class="section-title">MIDI Tracks</h2>
                <div style="display: flex; gap: 0.75rem;">
                    <button type="button" class="btn btn-success" onclick="generateMidi('bass')" id="generateBassBtn">
                        🎸 Generate Bassline
                    </button>
                    <button type="button" class="btn btn-success" onclick="openChordStyleModal()" id="generateChordsBtn">
                        🎹 Generate Chords
                    </button>
                </div>
            </div>

            <div class="midi-files-list" id="midiFilesList">
                <?php if (empty($midiFiles)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">🎹</div>
                        <p>No MIDI files generated yet.</p>
                        <p style="font-size: 0.875rem;">Click "Generate Bassline" or "Generate Chords" to create your first MIDI track.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($midiFiles as $midiFile): ?>
                        <div class="midi-file-item">
                            <div class="midi-file-info">
                                <div class="midi-file-type"><?php echo escape($midiFile['file_type']); ?></div>
                                <div class="midi-file-date">
                                    Created: <?php echo date('M j, Y g:i A', strtotime($midiFile['created_at'])); ?>
                                </div>
                            </div>
                            <div class="midi-file-actions">
                                <a href="<?php echo url('/dashboard/download-midi.php?id=' . $midiFile['id']); ?>" 
                                   class="btn btn-secondary btn-small">
                                    Download
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Chord Style Selection Modal -->
    <div id="chordStyleModal" class="chord-modal">
        <div class="chord-modal-content">
            <div class="chord-modal-header">
                <h3>Choose Chord Style</h3>
                <button class="chord-modal-close" onclick="closeChordStyleModal()">&times;</button>
            </div>
            <div class="chord-modal-body">
                <p class="chord-modal-description">
                    <strong>Simple:</strong> Traditional chord progressions • <strong>Complex:</strong> Jazz-like, experimental harmonies
                    <span class="chord-info-icon" title="Simple chords use clean triads (root, third, fifth, octave). Complex chords use dense, layered harmonies with random intervals for a more experimental sound.">ℹ️</span>
                </p>
                <div class="chord-button-group">
                    <button type="button" class="btn btn-chord-simple" onclick="selectChordStyle('simple-chords')">
                        🎵 Simple Chords
                    </button>
                    <button type="button" class="btn btn-chord-complex" onclick="selectChordStyle('complex-chords')">
                        🎹 Complex Chords
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function dismissFlash(button) {
            const flashMessage = button.closest('.flash-message');
            flashMessage.classList.add('dismissing');
            setTimeout(() => {
                flashMessage.remove();
            }, 300);
        }

        // Modal functions
        function openChordStyleModal() {
            const modal = document.getElementById('chordStyleModal');
            modal.classList.add('active');
        }

        function closeChordStyleModal() {
            const modal = document.getElementById('chordStyleModal');
            modal.classList.remove('active');
        }

        function selectChordStyle(chordType) {
            closeChordStyleModal();
            generateMidi(chordType);
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('chordStyleModal');
            if (event.target === modal) {
                closeChordStyleModal();
            }
        }

        // Close modal with ESC key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeChordStyleModal();
            }
        });

        async function generateMidi(type) {
            // Determine which button was clicked and friendly name
            let btnId, typeName;
            
            if (type === 'bass') {
                btnId = 'generateBassBtn';
                typeName = 'bassline';
            } else if (type === 'simple-chords') {
                btnId = 'generateChordsBtn';
                typeName = 'simple chords';
            } else if (type === 'complex-chords') {
                btnId = 'generateChordsBtn';
                typeName = 'complex chords';
            }
            
            const btn = document.getElementById(btnId);
            const originalText = btn.innerHTML;
            
            // Disable button and show loading
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner"></span> Generating...';
            
            try {
                const response = await fetch('<?php echo url("/dashboard/generate-midi.php"); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        project_id: <?php echo $projectId; ?>,
                        type: type
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Reload page to show new MIDI file
                    window.location.reload();
                } else {
                    alert('Error: ' + (data.error || `Failed to generate ${typeName}`));
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            } catch (error) {
                console.error('Error:', error);
                alert(`Failed to generate ${typeName}. Please try again.`);
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }

        // Auto-dismiss flash messages
        document.addEventListener('DOMContentLoaded', function() {
            const flashMessages = document.querySelectorAll('.flash-message');
            flashMessages.forEach(function(message) {
                setTimeout(function() {
                    if (message.parentElement) {
                        dismissFlash(message.querySelector('.flash-close'));
                    }
                }, 5000);
            });
        });
    </script>
</body>
</html>


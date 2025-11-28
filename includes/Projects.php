<?php
/**
 * Projects Class
 * Handles CRUD operations for user projects
 */

require_once __DIR__ . '/../database/Database.php';
require_once __DIR__ . '/Subscription.php';

class Projects {
    private $db;
    private $subscription;
    
    public function __construct($userId = null) {
        $this->db = Database::getInstance();
        if ($userId) {
            $this->subscription = new Subscription($userId);
        }
    }
    
    /**
     * Get all projects for a user
     * @param int $userId
     * @return array
     */
    public function getUserProjects($userId) {
        $query = "SELECT * FROM projects WHERE user_id = :user_id ORDER BY created_at DESC";
        return $this->db->fetchAll($query, ['user_id' => $userId]);
    }
    
    /**
     * Get a single project by ID
     * @param int $projectId
     * @param int $userId (to ensure user owns the project)
     * @return array|null
     */
    public function getProject($projectId, $userId) {
        $query = "SELECT * FROM projects WHERE id = :id AND user_id = :user_id";
        return $this->db->fetchOne($query, [
            'id' => $projectId,
            'user_id' => $userId
        ]);
    }
    
    /**
     * Create a new project
     * @param int $userId
     * @param string $title
     * @param string $description
     * @return int (new project ID)
     */
    public function createProject($userId, $title, $description = '') {
        return $this->db->insert('projects', [
            'user_id' => $userId,
            'title' => $title,
            'description' => $description
        ]);
    }
    
    /**
     * Update a project
     * @param int $projectId
     * @param int $userId (to ensure user owns the project)
     * @param string $title
     * @param string $description
     * @return bool
     */
    public function updateProject($projectId, $userId, $title, $description = '') {
        // First verify the project belongs to the user
        $project = $this->getProject($projectId, $userId);
        if (!$project) {
            return false;
        }
        
        return $this->db->update('projects', 
            [
                'title' => $title,
                'description' => $description,
                'updated_at' => date('Y-m-d H:i:s')
            ],
            'id = :id AND user_id = :user_id',
            [
                'id' => $projectId,
                'user_id' => $userId
            ]
        );
    }
    
    /**
     * Delete a project
     * @param int $projectId
     * @param int $userId (to ensure user owns the project)
     * @return bool
     */
    public function deleteProject($projectId, $userId) {
        // First verify the project belongs to the user
        $project = $this->getProject($projectId, $userId);
        if (!$project) {
            return false;
        }
        
        return $this->db->delete('projects', 
            'id = :id AND user_id = :user_id',
            [
                'id' => $projectId,
                'user_id' => $userId
            ]
        );
    }
    
    /**
     * Duplicate a project
     * @param int $projectId
     * @param int $userId (to ensure user owns the project)
     * @return int|false (new project ID or false)
     */
    public function duplicateProject($projectId, $userId) {
        // Get the original project
        $project = $this->getProject($projectId, $userId);
        if (!$project) {
            return false;
        }
        
        // Create a copy with "Copy of" prefix
        return $this->createProject(
            $userId,
            'Copy of ' . $project['title'],
            $project['description']
        );
    }
    
    /**
     * Get project count for a user
     * @param int $userId
     * @return int
     */
    public function getProjectCount($userId) {
        $query = "SELECT COUNT(*) as count FROM projects WHERE user_id = :user_id";
        $result = $this->db->fetchOne($query, ['user_id' => $userId]);
        return $result ? (int)$result['count'] : 0;
    }
    
    /**
     * Get usage information for a user (projects count with limits)
     * @param int $userId
     * @return array ['current' => int, 'limit' => int|null, 'plan' => string, 'can_create' => bool, 'percentage' => float]
     */
    public function getUserUsage($userId) {
        if (!$this->subscription) {
            $this->subscription = new Subscription($userId);
        }
        
        $usageInfo = $this->subscription->canCreateProject();
        
        // Calculate percentage for progress bar
        $percentage = 0;
        if ($usageInfo['limit'] !== null && $usageInfo['limit'] > 0) {
            $percentage = ($usageInfo['current'] / $usageInfo['limit']) * 100;
        }
        
        return array_merge($usageInfo, ['percentage' => $percentage]);
    }
    
    /**
     * Check if user can create more projects
     * @param int $userId
     * @return bool
     */
    public function canCreateProject($userId) {
        if (!$this->subscription) {
            $this->subscription = new Subscription($userId);
        }
        
        $usageInfo = $this->subscription->canCreateProject();
        return $usageInfo['can_create'];
    }
    
    /**
     * Get MIDI files for a project
     * @param int $projectId
     * @param int $userId (to ensure user owns the project)
     * @return array
     */
    public function getProjectMidiFiles($projectId, $userId) {
        // First verify the project belongs to the user
        $project = $this->getProject($projectId, $userId);
        if (!$project) {
            return [];
        }
        
        $query = "SELECT * FROM midi_files WHERE project_id = :project_id ORDER BY created_at DESC";
        return $this->db->fetchAll($query, ['project_id' => $projectId]);
    }
    
    /**
     * Add a MIDI file to a project
     * @param int $projectId
     * @param string $fileType
     * @param string $filePath
     * @param string $parameters (JSON)
     * @return int (new MIDI file ID)
     */
    public function addMidiFile($projectId, $fileType, $filePath, $parameters = null) {
        return $this->db->insert('midi_files', [
            'project_id' => $projectId,
            'file_type' => $fileType,
            'file_path' => $filePath,
            'parameters' => $parameters
        ]);
    }
    
    /**
     * Get a single MIDI file by ID
     * @param int $midiFileId
     * @return array|null
     */
    public function getMidiFile($midiFileId) {
        $query = "SELECT * FROM midi_files WHERE id = :id";
        return $this->db->fetchOne($query, ['id' => $midiFileId]);
    }
    
    /**
     * Delete a MIDI file
     * @param int $midiFileId
     * @param int $userId (to ensure user owns the project)
     * @return bool
     */
    public function deleteMidiFile($midiFileId, $userId) {
        // First get the MIDI file and verify the project belongs to the user
        $midiFile = $this->getMidiFile($midiFileId);
        if (!$midiFile) {
            return false;
        }
        
        $project = $this->getProject($midiFile['project_id'], $userId);
        if (!$project) {
            return false;
        }
        
        // Delete the physical file if it exists
        if (file_exists($midiFile['file_path'])) {
            unlink($midiFile['file_path']);
        }
        
        return $this->db->delete('midi_files', 
            'id = :id',
            ['id' => $midiFileId]
        );
    }
    
    /**
     * Get the next sequential number for a file type in a project
     * @param int $projectId
     * @param string $fileType
     * @return int
     */
    public function getNextFileNumber($projectId, $fileType) {
        $query = "SELECT file_path FROM midi_files WHERE project_id = :project_id AND file_type = :file_type";
        $files = $this->db->fetchAll($query, [
            'project_id' => $projectId,
            'file_type' => $fileType
        ]);
        
        $maxNumber = 0;
        
        // Pattern to extract number from filename: {userId}_{projectId}_{type}_{number}.mid
        $pattern = '/_(\\d+)\\.mid$/';
        
        foreach ($files as $file) {
            if (preg_match($pattern, $file['file_path'], $matches)) {
                $number = (int)$matches[1];
                if ($number > $maxNumber) {
                    $maxNumber = $number;
                }
            }
        }
        
        return $maxNumber + 1;
    }
    
    /**
     * Update the display name of a MIDI file
     * @param int $midiFileId
     * @param int $userId (to ensure user owns the project)
     * @param string $displayName (max 50 chars, sanitized)
     * @return bool
     */
    public function updateMidiDisplayName($midiFileId, $userId, $displayName) {
        // First get the MIDI file and verify the project belongs to the user
        $midiFile = $this->getMidiFile($midiFileId);
        if (!$midiFile) {
            return false;
        }
        
        $project = $this->getProject($midiFile['project_id'], $userId);
        if (!$project) {
            return false;
        }
        
        // Validate display name
        $displayName = trim($displayName);
        
        // Check length (max 50 characters)
        if (strlen($displayName) > 50) {
            return false;
        }
        
        // Block dangerous characters: /\:*?"<>|
        if (preg_match('/[\/\\\\:*?"<>|]/', $displayName)) {
            return false;
        }
        
        // Allow empty string to clear the display name (reset to default)
        if ($displayName === '') {
            $displayName = null;
        }
        
        return $this->db->update('midi_files', 
            ['display_name' => $displayName],
            'id = :id',
            ['id' => $midiFileId]
        );
    }
    
    /**
     * Validate a display name for MIDI files
     * @param string $displayName
     * @return array ['valid' => bool, 'error' => string|null]
     */
    public static function validateDisplayName($displayName) {
        $displayName = trim($displayName);
        
        // Check length
        if (strlen($displayName) > 50) {
            return ['valid' => false, 'error' => 'Name must be 50 characters or less'];
        }
        
        // Check for blocked characters
        if (preg_match('/[\/\\\\:*?"<>|]/', $displayName)) {
            return ['valid' => false, 'error' => 'Name cannot contain / \\ : * ? " < > |'];
        }
        
        return ['valid' => true, 'error' => null];
    }
}


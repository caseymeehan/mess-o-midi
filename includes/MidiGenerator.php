<?php
/**
 * MIDI Generator Class
 * Handles communication with the Python MIDI generation service
 */

class MidiGenerator {
    private $serviceUrl;
    
    public function __construct() {
        $this->serviceUrl = PYTHON_SERVICE_URL;
    }
    
    /**
     * Check if the Python service is available
     * 
     * @return bool
     */
    public function isServiceAvailable() {
        try {
            $ch = curl_init($this->serviceUrl . '/health');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            return $httpCode === 200 && $response !== false;
        } catch (Exception $e) {
            error_log('Python service health check failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate a bassline MIDI file
     * 
     * @param string $filename Filename for the MIDI file
     * @param array $options Optional parameters (scale, rhythm)
     * @return array ['success' => bool, 'filepath' => string, 'filename' => string, 'error' => string]
     */
    public function generateBassline($filename, $options = []) {
        try {
            // Prepare request data
            $data = [
                'filename' => $filename
            ];
            
            // Add optional parameters if provided
            if (isset($options['scale'])) {
                $data['scale'] = $options['scale'];
            }
            if (isset($options['rhythm'])) {
                $data['rhythm'] = $options['rhythm'];
            }
            
            // Make POST request to Python service
            $ch = curl_init($this->serviceUrl . '/api/generate/bass');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json'
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            // Check for cURL errors
            if ($response === false) {
                throw new Exception('cURL error: ' . $curlError);
            }
            
            // Check HTTP status
            if ($httpCode !== 200) {
                throw new Exception('Python service returned HTTP ' . $httpCode);
            }
            
            // Decode response
            $result = json_decode($response, true);
            
            if (!$result || !isset($result['success'])) {
                throw new Exception('Invalid response from Python service');
            }
            
            if (!$result['success']) {
                throw new Exception($result['error'] ?? 'Unknown error from Python service');
            }
            
            return [
                'success' => true,
                'filepath' => $result['filepath'],
                'filename' => $result['filename']
            ];
            
        } catch (Exception $e) {
            error_log('MIDI generation error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Generate a complex chord progression MIDI file
     * 
     * @param string $filename Filename for the MIDI file
     * @param array $options Optional parameters (scale, rhythm)
     * @return array ['success' => bool, 'filepath' => string, 'filename' => string, 'error' => string]
     */
    public function generateComplexChords($filename, $options = []) {
        try {
            // Prepare request data
            $data = [
                'filename' => $filename
            ];
            
            // Add optional parameters if provided
            if (isset($options['scale'])) {
                $data['scale'] = $options['scale'];
            }
            if (isset($options['rhythm'])) {
                $data['rhythm'] = $options['rhythm'];
            }
            
        // Make POST request to Python service
        $ch = curl_init($this->serviceUrl . '/api/generate/complex-chords');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json'
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            // Check for cURL errors
            if ($response === false) {
                throw new Exception('cURL error: ' . $curlError);
            }
            
            // Check HTTP status
            if ($httpCode !== 200) {
                throw new Exception('Python service returned HTTP ' . $httpCode);
            }
            
            // Decode response
            $result = json_decode($response, true);
            
            if (!$result || !isset($result['success'])) {
                throw new Exception('Invalid response from Python service');
            }
            
            if (!$result['success']) {
                throw new Exception($result['error'] ?? 'Unknown error from Python service');
            }
            
            return [
                'success' => true,
                'filepath' => $result['filepath'],
                'filename' => $result['filename']
            ];
            
    } catch (Exception $e) {
        error_log('Complex chord generation error: ' . $e->getMessage());
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Generate a bassline for a specific project
     * 
     * @param int $userId User ID
     * @param int $projectId Project ID
     * @param array $options Optional parameters
     * @return array ['success' => bool, 'file_id' => int, 'error' => string]
     */
    public function generateBasslineForProject($userId, $projectId, $options = []) {
        try {
            // Store MIDI file metadata in database
            require_once __DIR__ . '/Projects.php';
            $projectsManager = new Projects();
            
            // Get next sequential number for this file type
            $nextNumber = $projectsManager->getNextFileNumber($projectId, 'bass');
            
            // Generate filename based on user, project, and sequential number
            $filename = "{$userId}_{$projectId}_bass_{$nextNumber}.mid";
            
            // Generate the MIDI file
            $result = $this->generateBassline($filename, $options);
            
            if (!$result['success']) {
                return $result;
            }
            
            // Prepare parameters JSON
            $parameters = json_encode([
                'scale' => $options['scale'] ?? 'C_MAJOR',
                'generated_at' => date('Y-m-d H:i:s')
            ]);
            
            // Add MIDI file to project
            $fileId = $projectsManager->addMidiFile(
                $projectId,
                'bass',
                $result['filepath'],
                $parameters
            );
            
            if (!$fileId) {
                throw new Exception('Failed to save MIDI file metadata to database');
            }
            
            return [
                'success' => true,
                'file_id' => $fileId,
                'filepath' => $result['filepath'],
                'filename' => $result['filename'],
                'file_number' => $nextNumber
            ];
            
        } catch (Exception $e) {
            error_log('Generate bassline for project error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Generate complex chords for a specific project
     * 
     * @param int $userId User ID
     * @param int $projectId Project ID
     * @param array $options Optional parameters
     * @return array ['success' => bool, 'file_id' => int, 'error' => string]
     */
    public function generateComplexChordsForProject($userId, $projectId, $options = []) {
        try {
            // Store MIDI file metadata in database
            require_once __DIR__ . '/Projects.php';
            $projectsManager = new Projects();
            
            // Get next sequential number for this file type
            $nextNumber = $projectsManager->getNextFileNumber($projectId, 'complex_chords');
            
            // Generate filename based on user, project, and sequential number
            $filename = "{$userId}_{$projectId}_complex_chords_{$nextNumber}.mid";
            
            // Generate the MIDI file
            $result = $this->generateComplexChords($filename, $options);
            
            if (!$result['success']) {
                return $result;
            }
            
            // Prepare parameters JSON
            $parameters = json_encode([
                'scale' => $options['scale'] ?? 'C_MAJOR',
                'chord_type' => 'complex',
                'generated_at' => date('Y-m-d H:i:s')
            ]);
            
            // Add MIDI file to project
            $fileId = $projectsManager->addMidiFile(
                $projectId,
                'complex_chords',
                $result['filepath'],
                $parameters
            );
            
            if (!$fileId) {
                throw new Exception('Failed to save MIDI file metadata to database');
            }
            
            return [
                'success' => true,
                'file_id' => $fileId,
                'filepath' => $result['filepath'],
                'filename' => $result['filename'],
                'file_number' => $nextNumber
            ];
            
        } catch (Exception $e) {
            error_log('Generate complex chords for project error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Generate simple chords for a specific project
     * 
     * @param int $userId User ID
     * @param int $projectId Project ID
     * @param array $options Optional parameters
     * @return array ['success' => bool, 'file_id' => int, 'error' => string]
     */
    public function generateSimpleChordsForProject($userId, $projectId, $options = []) {
        try {
            // Store MIDI file metadata in database
            require_once __DIR__ . '/Projects.php';
            $projectsManager = new Projects();
            
            // Get next sequential number for this file type
            $nextNumber = $projectsManager->getNextFileNumber($projectId, 'simple_chords');
            
            // Generate filename based on user, project, and sequential number
            $filename = "{$userId}_{$projectId}_simple_chords_{$nextNumber}.mid";
            
            // Generate the MIDI file
            $result = $this->generateSimpleChords($filename, $options);
            
            if (!$result['success']) {
                return $result;
            }
            
            // Prepare parameters JSON
            $parameters = json_encode([
                'scale' => $options['scale'] ?? 'C_MAJOR',
                'chord_type' => 'simple',
                'generated_at' => date('Y-m-d H:i:s')
            ]);
            
            // Add MIDI file to project
            $fileId = $projectsManager->addMidiFile(
                $projectId,
                'simple_chords',
                $result['filepath'],
                $parameters
            );
            
            if (!$fileId) {
                throw new Exception('Failed to save MIDI file metadata to database');
            }
            
            return [
                'success' => true,
                'file_id' => $fileId,
                'filepath' => $result['filepath'],
                'filename' => $result['filename'],
                'file_number' => $nextNumber
            ];
            
        } catch (Exception $e) {
            error_log('Generate simple chords for project error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Generate a simple chord progression MIDI file
     * 
     * @param string $filename Filename for the MIDI file
     * @param array $options Optional parameters (scale, rhythm)
     * @return array ['success' => bool, 'filepath' => string, 'filename' => string, 'error' => string]
     */
    public function generateSimpleChords($filename, $options = []) {
        try {
            // Prepare request data
            $data = [
                'filename' => $filename
            ];
            
            // Add optional parameters if provided
            if (isset($options['scale'])) {
                $data['scale'] = $options['scale'];
            }
            if (isset($options['rhythm'])) {
                $data['rhythm'] = $options['rhythm'];
            }
            
            // Make POST request to Python service
            $ch = curl_init($this->serviceUrl . '/api/generate/simple-chords');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json'
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            // Check for cURL errors
            if ($response === false) {
                throw new Exception('cURL error: ' . $curlError);
            }
            
            // Check HTTP status
            if ($httpCode !== 200) {
                throw new Exception('Python service returned HTTP ' . $httpCode);
            }
            
            // Decode response
            $result = json_decode($response, true);
            
            if (!$result || !isset($result['success'])) {
                throw new Exception('Invalid response from Python service');
            }
            
            if (!$result['success']) {
                throw new Exception($result['error'] ?? 'Unknown error from Python service');
            }
            
            return [
                'success' => true,
                'filepath' => $result['filepath'],
                'filename' => $result['filename']
            ];
            
        } catch (Exception $e) {
            error_log('Simple chord generation error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Test connection to Python service
     * 
     * @return array Status information
     */
    public function testConnection() {
        $available = $this->isServiceAvailable();
        
        return [
            'available' => $available,
            'service_url' => $this->serviceUrl,
            'message' => $available 
                ? 'Python service is available' 
                : 'Python service is not available. Make sure it is running on ' . $this->serviceUrl
        ];
    }
}


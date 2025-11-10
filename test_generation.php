<?php
/**
 * Test MIDI Generation
 * Quick test to verify the Python service integration works
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/MidiGenerator.php';

echo "🎵 Mess o Midi - Generation Test\n";
echo "================================\n\n";

// Test 1: Service availability
echo "1. Testing Python service connection...\n";
$generator = new MidiGenerator();
$status = $generator->testConnection();

if ($status['available']) {
    echo "   ✅ Python service is available at {$status['service_url']}\n\n";
} else {
    echo "   ❌ {$status['message']}\n\n";
    exit(1);
}

// Test 2: Generate a test bassline
echo "2. Generating test bassline...\n";
$result = $generator->generateBassline('test_bassline_' . time() . '.mid');

if ($result['success']) {
    echo "   ✅ Bassline generated successfully!\n";
    echo "   📁 File: {$result['filepath']}\n";
    echo "   📄 Filename: {$result['filename']}\n";
    
    // Check if file exists
    if (file_exists($result['filepath'])) {
        $size = filesize($result['filepath']);
        echo "   📊 File size: " . number_format($size) . " bytes\n";
    }
} else {
    echo "   ❌ Generation failed: {$result['error']}\n";
    exit(1);
}

echo "\n✅ All tests passed! Mess o Midi is ready to use.\n";
echo "\n🌐 Open your browser to: http://localhost:9000\n";


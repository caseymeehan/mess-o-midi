<?php
/**
 * Test OAuth Configuration
 * Verify Google OAuth credentials are set up correctly
 */

require_once __DIR__ . '/config.php';

echo "🔐 OAuth Configuration Test\n";
echo "============================\n\n";

// Test 1: Check if credentials are defined
echo "1. Checking credentials...\n";

if (!defined('GOOGLE_CLIENT_ID')) {
    echo "   ❌ GOOGLE_CLIENT_ID not defined\n";
    exit(1);
}

if (!defined('GOOGLE_CLIENT_SECRET')) {
    echo "   ❌ GOOGLE_CLIENT_SECRET not defined\n";
    exit(1);
}

echo "   ✅ GOOGLE_CLIENT_ID: " . substr(GOOGLE_CLIENT_ID, 0, 30) . "...\n";
echo "   ✅ GOOGLE_CLIENT_SECRET: " . substr(GOOGLE_CLIENT_SECRET, 0, 15) . "...\n";
echo "   ✅ Redirect URI: " . GOOGLE_REDIRECT_URI . "\n\n";

// Test 2: Check if Client ID format is correct
echo "2. Validating Client ID format...\n";
if (strpos(GOOGLE_CLIENT_ID, '.apps.googleusercontent.com') !== false) {
    echo "   ✅ Client ID format is correct\n\n";
} else {
    echo "   ⚠️  Warning: Client ID doesn't end with .apps.googleusercontent.com\n\n";
}

// Test 3: Check if Client Secret format is correct
echo "3. Validating Client Secret format...\n";
if (strpos(GOOGLE_CLIENT_SECRET, 'GOCSPX-') === 0) {
    echo "   ✅ Client Secret format is correct\n\n";
} else {
    echo "   ⚠️  Warning: Client Secret doesn't start with GOCSPX-\n\n";
}

// Test 4: Try to initialize Google Client
echo "4. Testing Google Client initialization...\n";
try {
    $client = new Google_Client();
    $client->setClientId(GOOGLE_CLIENT_ID);
    $client->setClientSecret(GOOGLE_CLIENT_SECRET);
    $client->setRedirectUri(GOOGLE_REDIRECT_URI);
    $client->addScope('email');
    $client->addScope('profile');
    
    echo "   ✅ Google Client initialized successfully\n\n";
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 5: Generate auth URL
echo "5. Generating authentication URL...\n";
try {
    $authUrl = $client->createAuthUrl();
    echo "   ✅ Auth URL generated successfully\n";
    echo "   🔗 URL: " . substr($authUrl, 0, 80) . "...\n\n";
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n\n";
    exit(1);
}

echo "✅ All OAuth tests passed!\n\n";
echo "📋 Next Steps:\n";
echo "   1. Make sure both services are running (./start-services.sh)\n";
echo "   2. Open: http://localhost:9000\n";
echo "   3. Click 'Sign in with Google'\n";
echo "   4. You should see Google's login page\n";
echo "   5. After signing in, you'll be redirected to the dashboard\n\n";
echo "🎵 Ready to start creating music!\n";


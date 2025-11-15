<?php
/**
 * Configuration file for PHP SaaS Template
 */

// Autoload Composer dependencies
require_once __DIR__ . '/vendor/autoload.php';

// Database configuration
// NOTE: Database file is in /data (mounted volume) separate from migration scripts in /database
define('DB_PATH', __DIR__ . '/data/saas.db');

// Auto-initialize database if it doesn't exist (Railway deployment support)
if (!file_exists(DB_PATH) || filesize(DB_PATH) === 0) {
    error_log('Database not found or empty, auto-initializing...');
    
    // Create database directory if it doesn't exist
    $dbDir = dirname(DB_PATH);
    if (!is_dir($dbDir)) {
        mkdir($dbDir, 0755, true);
    }
    
    // Suppress output and run initialization scripts
    ob_start();
    try {
        // Initialize base tables
        require __DIR__ . '/database/init.php';
        
        // Run migrations
        require __DIR__ . '/database/migrate_google_oauth.php';
        require __DIR__ . '/database/migrate_items.php';
        require __DIR__ . '/database/migrate_stripe.php';
        require __DIR__ . '/database/migrate_midi_files.php';
        
        error_log('Database auto-initialization completed successfully');
    } catch (Exception $e) {
        error_log('Database auto-initialization failed: ' . $e->getMessage());
    }
    ob_end_clean();
}

// Load local configuration overrides FIRST (gitignored - for actual credentials)
// This allows config.local.php to override SITE_URL for local development
if (file_exists(__DIR__ . '/config.local.php')) {
    require_once __DIR__ . '/config.local.php';
}

// Site configuration
define('SITE_NAME', 'Mess o Midi');
// Auto-detect Railway URL or use localhost (can be overridden by config.local.php)
if (!defined('SITE_URL')) {
    $railwayUrl = getenv('RAILWAY_PUBLIC_DOMAIN');
    define('SITE_URL', $railwayUrl ? 'https://' . $railwayUrl : 'http://localhost:9000');
}
define('SITE_EMAIL', 'hello@yoursaas.com');

// Google OAuth Configuration
// These will be overridden by config.local.php if it exists
if (!defined('GOOGLE_CLIENT_ID')) {
    define('GOOGLE_CLIENT_ID', getenv('GOOGLE_CLIENT_ID') ?: 'YOUR_GOOGLE_CLIENT_ID');
}
if (!defined('GOOGLE_CLIENT_SECRET')) {
    define('GOOGLE_CLIENT_SECRET', getenv('GOOGLE_CLIENT_SECRET') ?: 'YOUR_GOOGLE_CLIENT_SECRET');
}
define('GOOGLE_REDIRECT_URI', SITE_URL . '/auth/google-callback.php');

// Stripe Configuration
// These should be overridden in config.local.php with your actual keys
if (!defined('STRIPE_PUBLISHABLE_KEY')) {
    define('STRIPE_PUBLISHABLE_KEY', getenv('STRIPE_PUBLISHABLE_KEY') ?: 'pk_test_YOUR_KEY');
}
if (!defined('STRIPE_SECRET_KEY')) {
    define('STRIPE_SECRET_KEY', getenv('STRIPE_SECRET_KEY') ?: 'sk_test_YOUR_KEY');
}
if (!defined('STRIPE_WEBHOOK_SECRET')) {
    define('STRIPE_WEBHOOK_SECRET', getenv('STRIPE_WEBHOOK_SECRET') ?: 'whsec_YOUR_WEBHOOK_SECRET');
}
define('STRIPE_WEBHOOK_URL', SITE_URL . '/webhooks/stripe.php');

// Security
define('SESSION_LIFETIME', 86400); // 24 hours
define('PASSWORD_MIN_LENGTH', 8);

// Features
define('ENABLE_REGISTRATION', true);
define('REQUIRE_EMAIL_VERIFICATION', false);

// Pricing
define('FREE_TIER_ENABLED', true);

// Pricing Plans
define('PRICING_PLANS', [
    'free' => [
        'name' => 'Free',
        'price' => 0,
        'currency' => 'USD',
        'billing_cycle' => 'month',
        'stripe_price_id' => null,
        'project_limit' => 5
    ],
    'pro' => [
        'name' => 'Pro',
        'price' => 29,
        'currency' => 'USD',
        'billing_cycle' => 'month',
        'stripe_price_id' => 'price_YOUR_PRO_PRICE_ID_HERE',
        'project_limit' => 50
    ],
    'enterprise' => [
        'name' => 'Enterprise',
        'price' => 99,
        'currency' => 'USD',
        'billing_cycle' => 'month',
        'stripe_price_id' => 'price_YOUR_ENTERPRISE_PRICE_ID_HERE',
        'project_limit' => null // unlimited
    ]
]);

// Python Service Configuration
if (!defined('PYTHON_SERVICE_URL')) {
    // In production (Railway), both services run on same host
    // Use 127.0.0.1 instead of localhost for better compatibility
    define('PYTHON_SERVICE_URL', getenv('PYTHON_SERVICE_URL') ?: 'http://127.0.0.1:5001');
}

// Timezone
date_default_timezone_set('UTC');

// Detect production environment (Railway provides RAILWAY_PUBLIC_DOMAIN or RAILWAY_ENVIRONMENT_NAME)
$isProduction = getenv('RAILWAY_PUBLIC_DOMAIN') !== false || getenv('RAILWAY_ENVIRONMENT_NAME') !== false;

// Error reporting (disable display in production)
error_reporting(E_ALL);
if ($isProduction) {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
} else {
    ini_set('display_errors', '1');
}

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', $isProduction ? 1 : 0);
ini_set('session.use_strict_mode', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


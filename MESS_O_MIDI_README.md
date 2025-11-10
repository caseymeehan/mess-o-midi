# Mess o Midi - MIDI-Based Generative Music Tool

## Overview

Mess o Midi is a web-based MIDI generation tool for electronic music producers. It transforms a PHP SaaS template into a powerful music creation platform where users can create projects and generate MIDI files for basslines, chords, kicks, and more.

## Features

- 🎹 **MIDI Generation**: Generate basslines, chords, and drum patterns
- 📁 **Project Management**: Organize your MIDI files into projects
- 💾 **Export & Download**: Download MIDI files for use in your DAW
- 🔒 **User Authentication**: Google OAuth integration
- 💳 **Subscription Management**: Stripe payment integration with tiered plans
- 🎵 **Multiple Tracks**: Support for multiple MIDI files per project

## Architecture

### Tech Stack

**Backend:**
- PHP 7.4+ (Web Application)
- SQLite (Database)
- Python 3.7+ (MIDI Generation Service)

**Frontend:**
- Vanilla JavaScript
- Modern CSS

**Libraries:**
- Flask (Python web framework)
- py_midicsv (MIDI file generation)
- Stripe PHP SDK (Payments)
- Google API Client (OAuth)

## Installation & Setup

### Prerequisites

- PHP 7.4 or higher
- Python 3.7 or higher
- Composer (PHP package manager)
- Git

### Step 1: Clone and Setup PHP Application

```bash
# Install PHP dependencies
composer install

# Copy configuration template
cp config.local.example.php config.local.php

# Edit config.local.php with your credentials
# - Google OAuth credentials
# - Stripe API keys (optional for testing)
```

### Step 2: Initialize Database

```bash
# Run database migrations
php database/init.php
php database/migrate_google_oauth.php
php database/migrate_items.php
php database/migrate_stripe.php
php database/migrate_midi_files.php
```

### Step 3: Setup Python MIDI Service

```bash
cd python_service

# Create virtual environment
python3 -m venv venv

# Activate virtual environment
# On macOS/Linux:
source venv/bin/activate
# On Windows:
# venv\Scripts\activate

# Install Python dependencies
pip install -r requirements.txt

# Return to project root
cd ..
```

### Step 4: Start Services

You can start both services manually or use the provided script:

#### Option A: Use Start Script (Recommended)

```bash
./start-services.sh
```

This will start both the PHP web server and Python API service.

#### Option B: Manual Start

**Terminal 1 - Python Service:**
```bash
cd python_service
source venv/bin/activate
python app.py
# Runs on http://localhost:5000
```

**Terminal 2 - PHP Web Server:**
```bash
php -S localhost:9000
# Runs on http://localhost:9000
```

### Step 5: Access Application

Open your browser and navigate to:
- **Web Application**: http://localhost:9000
- **Python API Health**: http://localhost:5000/health

## Configuration

### Environment Variables

Create a `config.local.php` file with your settings:

```php
<?php
// Site URL
define('SITE_URL', 'http://localhost:9000');

// Google OAuth
define('GOOGLE_CLIENT_ID', 'your-client-id');
define('GOOGLE_CLIENT_SECRET', 'your-client-secret');

// Stripe (Optional)
define('STRIPE_PUBLISHABLE_KEY', 'pk_test_...');
define('STRIPE_SECRET_KEY', 'sk_test_...');
define('STRIPE_WEBHOOK_SECRET', 'whsec_...');

// Python Service
define('PYTHON_SERVICE_URL', 'http://localhost:5000');
```

### Pricing Plans

Edit `config.php` to adjust subscription limits:

```php
define('PRICING_PLANS', [
    'free' => [
        'name' => 'Free',
        'price' => 0,
        'project_limit' => 5
    ],
    'pro' => [
        'name' => 'Pro',
        'price' => 29,
        'project_limit' => 50
    ],
    'enterprise' => [
        'name' => 'Enterprise',
        'price' => 99,
        'project_limit' => null // unlimited
    ]
]);
```

## Project Structure

```
Mess-o-Midi/
├── assets/              # Static assets (CSS, JS, images)
├── auth/                # Authentication handlers
├── checkout/            # Stripe checkout handlers
├── config.php           # Main configuration
├── config.local.php     # Local overrides (gitignored)
├── dashboard/           # User dashboard pages
│   ├── index.php        # Projects list
│   ├── project-new.php  # Create project
│   ├── project-edit.php # Edit project & generate MIDI
│   ├── project-actions.php # Project actions
│   ├── generate-midi.php # MIDI generation endpoint
│   └── download-midi.php # MIDI download handler
├── database/            # Database and migrations
│   ├── Database.php     # Database wrapper
│   ├── init.php         # Initialize database
│   ├── migrate_*.php    # Migration scripts
├── includes/            # PHP classes
│   ├── Auth.php         # Authentication
│   ├── Projects.php     # Project management
│   ├── MidiGenerator.php # Python service client
│   └── Subscription.php # Stripe subscriptions
├── python_service/      # Python MIDI generation service
│   ├── app.py           # Flask application
│   ├── config.py        # Python configuration
│   ├── generators/      # MIDI generators
│   │   ├── bass.py      # Bassline generator
│   │   ├── midi_tools.py # MIDI utilities
│   │   └── __init__.py
│   └── requirements.txt # Python dependencies
├── uploads/             # User uploads
│   └── midi/            # Generated MIDI files
├── index.php            # Landing page
├── pricing.php          # Pricing page
└── start-services.sh    # Service startup script
```

## Usage

### Creating a Project

1. Sign in with Google OAuth
2. Click "New" to create a project
3. Enter project title and description
4. Click "Create Project"

### Generating MIDI Files

1. Open a project
2. Click "Generate Bassline" button
3. Wait for generation (usually 1-2 seconds)
4. Download the generated MIDI file
5. Import into your DAW (Ableton, FL Studio, Logic, etc.)

### Adding More Generators

To add new MIDI generators (chords, drums, etc.):

1. Create generator in `python_service/generators/`
2. Add endpoint in `python_service/app.py`
3. Add method in `includes/MidiGenerator.php`
4. Add UI button in `dashboard/project-edit.php`
5. Handle in `dashboard/generate-midi.php`

## API Documentation

### Python Service Endpoints

#### Health Check
```
GET /health
Response: {"status": "healthy", "service": "Mess o Midi - Python MIDI Service"}
```

#### Generate Bassline
```
POST /api/generate/bass
Content-Type: application/json

Request:
{
  "filename": "my_bass.mid",
  "scale": [40, 41, 43, 45, 47, 48, 50],  // optional
  "rhythm": [0, 384, 768, ...]  // optional
}

Response:
{
  "success": true,
  "filepath": "/path/to/file.mid",
  "filename": "my_bass.mid"
}
```

## Development

### Adding New MIDI Generators

1. **Create Python Generator** (`python_service/generators/yourgen.py`):
```python
from .midi_tools import data_to_midi, create_file

def generate_chords(output_path, ...):
    # Your generation logic
    midi_notes = data_to_midi(pitch_data, note_on, note_off)
    return create_file(midi_notes, output_path)
```

2. **Add Flask Endpoint** (`python_service/app.py`):
```python
@app.route('/api/generate/chords', methods=['POST'])
def generate_chords_endpoint():
    # Handle request and call generator
    pass
```

3. **Update PHP Client** (`includes/MidiGenerator.php`):
```php
public function generateChords($filename, $options = []) {
    // Call Python service
}
```

4. **Add UI Button** (`dashboard/project-edit.php`)

## Troubleshooting

### Python Service Not Available

**Error**: "MIDI generation service is not available"

**Solutions**:
- Make sure Python service is running on port 5000
- Check `PYTHON_SERVICE_URL` in config
- Verify no firewall is blocking localhost:5000
- Check Python service logs for errors

### MIDI Files Not Generating

**Check**:
- Python service is running
- `uploads/midi/` directory exists and is writable
- Python dependencies are installed (`pip install -r requirements.txt`)
- Database table `midi_files` exists

### Database Issues

**Reset Database**:
```bash
rm database/saas.db
php database/init.php
php database/migrate_google_oauth.php
php database/migrate_items.php
php database/migrate_stripe.php
php database/migrate_midi_files.php
```

## Future Enhancements

- [ ] Chord progression generator
- [ ] Drum pattern generator
- [ ] Melody generator
- [ ] Real-time MIDI preview
- [ ] Custom scale selection UI
- [ ] Tempo/BPM control
- [ ] Export multiple tracks as one file
- [ ] Share projects with other users
- [ ] AI-powered generation options

## Credits

- Original Python MIDI code by Casey Meehan
- SaaS template base structure
- py_midicsv library for MIDI file handling

## License

See LICENSE file for details.

## Support

For issues or questions:
- Check the troubleshooting section above
- Review Python service logs
- Check PHP error logs
- Verify all services are running

---

**Mess o Midi** - Create music, one MIDI file at a time 🎵


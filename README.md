# Mess o Midi 🎵

Web-based MIDI generation tool for electronic music production. Create projects, generate MIDI files (basslines, chords, drums), and export for use in any DAW.

**Private commercial SaaS** - see [AI_CONTEXT.md](AI_CONTEXT.md) for complete project state and roadmap.

---

## Quick Start

### Prerequisites
- PHP 8.1+ with Composer
- Python 3.7+
- Google OAuth credentials

### Setup (First Time)

```bash
# 1. Install PHP dependencies
composer install

# 2. Install Python dependencies
cd python_service
python3 -m venv venv
source venv/bin/activate  # Windows: venv\Scripts\activate
pip install -r requirements.txt
cd ..

# 3. Configure
cp config.local.example.php config.local.php
# Edit config.local.php with your Google OAuth credentials

# 4. Initialize database
php database/init.php
php database/migrate_google_oauth.php
php database/migrate_items.php
php database/migrate_stripe.php
php database/migrate_midi_files.php
```

### Daily Use

```bash
# Start both services
./start-services.sh

# Or manually in separate terminals:
# Terminal 1: cd python_service && source venv/bin/activate && python app.py
# Terminal 2: php -S localhost:9000
```

**Access:** http://localhost:9000  
**Python API:** http://localhost:5001

---

## Usage

1. **Sign in** with Google
2. **Create project** (Dashboard → "+ New")
3. **Generate MIDI** (Open project → "Generate Bassline")
4. **Download** and import into your DAW

---

## Project Structure

```
Mess-o-Midi/
├── dashboard/              # User interface
│   ├── index.php          # Projects list
│   ├── project-edit.php   # MIDI generation UI
│   ├── generate-midi.php  # Generation endpoint
│   └── download-midi.php  # Download handler
│
├── python_service/        # MIDI generation service (port 5001)
│   ├── app.py            # Flask API
│   ├── generators/
│   │   ├── bass.py       # Bassline generator
│   │   └── midi_tools.py # MIDI utilities
│   └── venv/             # Virtual environment
│
├── database/
│   ├── saas.db           # SQLite database
│   └── migrate_*.php     # Migration scripts
│
├── includes/             # PHP classes
│   ├── Projects.php      # Project CRUD
│   └── MidiGenerator.php # Python service client
│
└── uploads/midi/         # Generated MIDI files
```

---

## Common Commands

### Services
```bash
# Start services
./start-services.sh

# Check health
curl http://localhost:5001/health  # Python service
curl http://localhost:9000         # PHP app

# Stop services
Ctrl+C
```

### Testing
```bash
# Test MIDI generation without login
php test_generation.php

# Test OAuth setup
php test_oauth.php
```

### Database
```bash
# Reset database
rm database/saas.db
php database/init.php
php database/migrate_google_oauth.php
php database/migrate_items.php
php database/migrate_stripe.php
php database/migrate_midi_files.php
```

---

## Adding New MIDI Generators

You have existing Python generators ready to integrate from:
`/Users/caseymeehan/Documents/base/work/other/code/Programming_Music_New/`

**Quick integration** (see [AI_CONTEXT.md](AI_CONTEXT.md) for detailed steps):

1. Copy generator to `python_service/generators/`
2. Add Flask endpoint in `app.py`
3. Add method in `includes/MidiGenerator.php`
4. Add button in `dashboard/project-edit.php`
5. Handle in `dashboard/generate-midi.php`

**Ready to add:**
- `chords.py` - Chord progressions
- `kick_pattern.py` - Drum patterns
- `motif.py` - Melodies
- `fit_to_chords.py` - Melodic fitting
- And more...

---

## Configuration

### Required
Edit `config.local.php`:
```php
// Google OAuth (required for authentication)
define('GOOGLE_CLIENT_ID', 'your-client-id');
define('GOOGLE_CLIENT_SECRET', 'your-client-secret');
```

Get credentials: https://console.cloud.google.com/apis/credentials
- Create OAuth client → Web application
- Add redirect: `http://localhost:9000/auth/google-callback.php`

### Optional
```php
// Stripe (only if using subscriptions)
define('STRIPE_PUBLISHABLE_KEY', 'pk_test_...');
define('STRIPE_SECRET_KEY', 'sk_test_...');
```

### Pricing Plans
Edit `config.php`:
```php
define('PRICING_PLANS', [
    'free' => ['price' => 0, 'project_limit' => 5],
    'pro' => ['price' => 29, 'project_limit' => 50],
    'enterprise' => ['price' => 99, 'project_limit' => null]
]);
```

---

## Troubleshooting

**"redirect_uri_mismatch"**  
→ Verify Google Console redirect URI: `http://localhost:9000/auth/google-callback.php`

**"MIDI generation service is not available"**  
→ Check Python service: `curl http://localhost:5001/health`  
→ Restart: `cd python_service && source venv/bin/activate && python app.py`

**Port 5001 conflict**  
→ Change in `python_service/config.py` and `config.php`

**MIDI files not generating**  
→ Check `uploads/midi/` exists and is writable  
→ Check Python service logs

**Database errors**  
→ Reset database (see commands above)

---

## Tech Stack

- **PHP 8.1+** - Web application
- **Python 3.7+** - MIDI generation service (Flask)
- **SQLite** - Database
- **Google OAuth** - Authentication
- **Stripe** - Optional subscriptions
- **py_midicsv** - MIDI file generation

---

## Files Generated

MIDI files saved to: `uploads/midi/`  
Format: `{userId}_{projectId}_{type}_{timestamp}.mid`  
Example: `1_5_bass_1762794384.mid`

---

## Next Steps

See [AI_CONTEXT.md](AI_CONTEXT.md) for:
- Complete architecture documentation
- Detailed roadmap
- How to add generators (step-by-step)
- API reference
- Known issues

---

**Mess o Midi** - Create music, one MIDI file at a time 🎵

# AI Context - Mess o Midi

**Purpose:** This document provides AI assistants with complete context about the project's current state, architecture, and roadmap.

**Last Updated:** November 11, 2025 (Chord Generator Integrated)

---

## Project Overview

**Mess o Midi** is a web-based MIDI generation tool for electronic music production. It allows creating projects and generating MIDI files (basslines, chords, drums) that can be exported and used in any DAW.

**Key Point:** This is a **private commercial SaaS project**. The codebase is not public/open source. Documentation is focused on development efficiency and AI assistant context, not external contributor onboarding.

---

## Current State

### ✅ Fully Implemented

**Database (SQLite)**
- `projects` table (renamed from `items`)
- `midi_files` table for generated MIDI metadata
- `users`, `sessions`, `subscriptions`, `invoices`, `payment_methods` tables
- All migrations complete and working

**Backend (PHP 8.1+)**
- `Projects.php` - Full CRUD operations for projects
- `MidiGenerator.php` - Client for Python service communication
- `Auth.php` - Google OAuth authentication
- `Subscription.php` - Stripe subscription management with project limits
- `generate-midi.php` - Endpoint for MIDI generation requests
- `download-midi.php` - Handler for downloading generated files

**Python Service (Flask on port 5001)**
- `app.py` - Flask API server
- `generators/bass.py` - Bassline generator (ported from existing code)
- `generators/chords.py` - Complex chord progression generator (ported from complex_chords.py)
- `generators/midi_tools.py` - MIDI utilities (data_to_midi, create_file)
- Health check endpoint: `/health`
- Bassline generation endpoint: `/api/generate/bass`
- Chord generation endpoint: `/api/generate/chords`
- Note: Changed from port 5000 to 5001 to avoid macOS AirPlay conflict

**Frontend**
- Dashboard with projects list (`dashboard/index.php`)
- Project creation page (`dashboard/project-new.php`)
- Project edit page with MIDI tracks section (`dashboard/project-edit.php`)
- "🎸 Generate Bassline" and "🎹 Generate Chords" buttons with AJAX
- Download functionality for generated MIDI files
- Full branding as "Mess o Midi" 🎵

**Configuration**
- `config.php` - Main config with Python service URL, pricing plans
- `config.local.php` - Local overrides for credentials (gitignored)
- Pricing plans use `project_limit` (Free: 5, Pro: 50, Enterprise: unlimited)

**Services**
- PHP web server: http://localhost:9000
- Python MIDI API: http://localhost:5001
- Start script: `./start-services.sh`

### Test Results
```
✅ Python service connection: PASS
✅ Bassline generation: PASS (244 bytes typical)
✅ Chord generation: PASS (804 bytes typical)
✅ File saved to disk: PASS
✅ Web server response: PASS
✅ End-to-end flow: PASS
```

---

## Architecture & Tech Decisions

### Tech Stack

**Backend:**
- PHP 8.1+ (web application, user management)
- SQLite (database - perfect for personal project)
- Composer (dependency management)

**MIDI Generation Service:**
- Python 3.7+ with Flask
- py_midicsv library for MIDI file generation
- Runs as separate service for modularity

**Frontend:**
- Vanilla JavaScript (no framework needed)
- Modern CSS
- AJAX for MIDI generation

**External Services:**
- Google OAuth (authentication)
- Stripe (optional subscription management)

### Key Architecture Decisions

1. **Separate Python Service**: MIDI generation runs as independent Flask service
   - Keeps PHP codebase simple
   - Easy to add new generators
   - Can scale independently if needed

2. **Port 5001**: Changed from 5000 due to macOS AirPlay conflict
   - Updated in `python_service/config.py`
   - Updated in PHP `config.php` (PYTHON_SERVICE_URL)

3. **SQLite Database**: Sufficient for personal use
   - Simple setup, no separate database server
   - Could migrate to PostgreSQL/MySQL if needed later

4. **Projects (not Items)**: Renamed for music production context
   - Each project contains multiple MIDI files
   - Better semantic fit for the use case

---

## Project Structure

```
Mess-o-Midi/
├── dashboard/              # User interface
│   ├── index.php          # Projects list
│   ├── project-new.php    # Create project form
│   ├── project-edit.php   # Edit project + MIDI generation UI
│   ├── project-actions.php # CRUD handlers
│   ├── generate-midi.php  # MIDI generation endpoint (calls Python)
│   └── download-midi.php  # Download handler
│
├── python_service/        # MIDI generation service (port 5001)
│   ├── app.py            # Flask application
│   ├── config.py         # Port and config
│   ├── generators/
│   │   ├── __init__.py
│   │   ├── bass.py       # ✅ Bassline generator (implemented)
│   │   └── midi_tools.py # MIDI utilities (data_to_midi, create_file)
│   ├── requirements.txt
│   └── venv/             # Virtual environment
│
├── database/
│   ├── saas.db           # SQLite database
│   ├── Database.php      # Database wrapper class
│   ├── init.php          # Initialize database
│   └── migrate_*.php     # Migration scripts
│
├── includes/             # PHP classes
│   ├── Auth.php          # Authentication (Google OAuth)
│   ├── Projects.php      # Project CRUD operations
│   ├── MidiGenerator.php # Python service client
│   ├── Subscription.php  # Stripe subscriptions
│   └── helpers.php       # Utility functions
│
├── uploads/midi/         # Generated MIDI files
│   └── {userId}_{projectId}_{type}_{timestamp}.mid
│
├── auth/                 # OAuth handlers
├── checkout/             # Stripe checkout (optional)
├── webhooks/             # Stripe webhooks (optional)
├── assets/               # CSS, JS, images
├── config.php            # Main configuration
├── config.local.php      # Local overrides (gitignored)
├── index.php             # Landing page
└── start-services.sh     # Start both services
```

---

## How to Add New MIDI Generators

You have existing Python generators ready to integrate:
- **Location:** `/Users/caseymeehan/Documents/base/work/other/code/Programming_Music_New/`
- **Available:** `chords.py`, `kick_pattern.py`, `motif.py`, `multiple_kick_patterns.py`, `complex_chords.py`, etc.

### Step-by-Step Process

#### 1. Create Python Generator
**File:** `python_service/generators/your_generator.py`

```python
from .midi_tools import data_to_midi, create_file

def generate_your_feature(output_path, scale=None, rhythm=None, **kwargs):
    """
    Generate MIDI file
    
    Args:
        output_path: Full path where to save .mid file
        scale: List of MIDI note numbers (optional)
        rhythm: List of timing values (optional)
        **kwargs: Additional parameters
    
    Returns:
        str: Path to generated file
    """
    # Your generation logic here
    pitch_data = [...]  # Your pitch generation
    note_on = [...]     # Note on timestamps
    note_off = [...]    # Note off timestamps
    
    # Convert to MIDI format
    midi_notes = data_to_midi(pitch_data, note_on, note_off)
    
    # Create file
    return create_file(midi_notes, output_path)
```

#### 2. Add Flask Endpoint
**File:** `python_service/app.py`

```python
@app.route('/api/generate/your_feature', methods=['POST'])
def generate_your_feature_endpoint():
    try:
        data = request.get_json()
        filename = data.get('filename', 'output.mid')
        output_path = os.path.join(config.UPLOAD_DIR, filename)
        
        # Call your generator
        result_path = your_generator.generate_your_feature(
            output_path,
            scale=data.get('scale'),
            rhythm=data.get('rhythm')
        )
        
        return jsonify({
            'success': True,
            'filepath': result_path,
            'filename': filename
        })
    except Exception as e:
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500
```

#### 3. Add PHP Client Method
**File:** `includes/MidiGenerator.php`

```php
public function generateYourFeature($filename, $options = []) {
    $data = [
        'filename' => $filename,
        'scale' => $options['scale'] ?? null,
        'rhythm' => $options['rhythm'] ?? null
    ];
    
    return $this->callPythonService('/api/generate/your_feature', $data);
}
```

#### 4. Add Dashboard Endpoint Handler
**File:** `dashboard/generate-midi.php`

```php
case 'your_feature':
    $result = $midiGenerator->generateYourFeature($filename, [
        'scale' => $_POST['scale'] ?? null,
        'rhythm' => $_POST['rhythm'] ?? null
    ]);
    break;
```

#### 5. Add UI Button
**File:** `dashboard/project-edit.php`

```html
<button onclick="generateMidi('your_feature')" class="btn btn-secondary">
    Generate Your Feature
</button>
```

That's it! The existing AJAX code in `project-edit.php` will handle the request/response.

---

## API Reference

### Python Service Endpoints

**Base URL:** `http://localhost:5001`

#### Health Check
```
GET /health

Response:
{
  "status": "healthy",
  "service": "Mess o Midi - Python MIDI Service"
}
```

#### Generate Bassline
```
POST /api/generate/bass
Content-Type: application/json

Request:
{
  "filename": "my_bass.mid",
  "scale": [40, 41, 43, 45, 47, 48, 50],  // optional, MIDI note numbers
  "rhythm": [0, 384, 768, ...]             // optional, timing in ticks
}

Response:
{
  "success": true,
  "filepath": "/full/path/to/file.mid",
  "filename": "my_bass.mid"
}
```

#### Generate Chords
```
POST /api/generate/chords
Content-Type: application/json

Request:
{
  "filename": "my_chords.mid",
  "scale": [40, 41, 43, 45, 47, 48, 50],  // optional, MIDI note numbers for bass notes
  "rhythm": [0, 384, 768, ...]             // optional, timing in ticks
}

Response:
{
  "success": true,
  "filepath": "/full/path/to/file.mid",
  "filename": "my_chords.mid"
}

Notes:
- Generates 5-voice complex chord progressions
- Bass, root, harmony1, harmony2, harmony3
- Random intervals (2-4 semitones) with musical intelligence
- All notes fit to C major scale
```

### MIDI File Format
- Files saved to: `uploads/midi/`
- Naming: `{userId}_{projectId}_{type}_{timestamp}.mid`
- Example: `1_5_bass_1762794384.mid`
- Typical size: 200-300 bytes

---

## Roadmap

### High Priority - Ready to Add

These generators already exist in `/Programming_Music_New/` and just need integration:

1. ✅ **Chord Progression Generator** - COMPLETED
   - Source: `complex_chords.py`
   - Status: Integrated and working
   - Impact: High (key feature for music production)

2. **Drum/Kick Pattern Generator**
   - Source: `kick_pattern.py` or `multiple_kick_patterns.py`
   - Complexity: Easy
   - Impact: High (rhythm is essential)

3. **Melody/Motif Generator**
   - Source: `motif.py` or `multiple-motifs.py`
   - Complexity: Easy
   - Impact: Medium-High

4. **Fit to Chords Generator**
   - Source: `fit_to_chords.py`
   - Complexity: Medium (requires chord context)
   - Impact: High (advanced feature)

### Medium Priority - Enhancement Features

5. **UI for Scale Selection**
   - Currently hardcoded in generators
   - Add dropdown in project edit page
   - Pass to Python service as parameter

6. **BPM/Tempo Control**
   - Add BPM field to projects
   - Use in MIDI generation timing

7. **MIDI Preview**
   - Play MIDI in browser before download
   - Use Web MIDI API or MIDI.js

8. **Multi-Track Export**
   - Combine multiple MIDI files into one
   - Useful for exporting full project

### Low Priority - Nice to Have

9. **Project Templates**
   - Pre-configured scale/BPM combinations
   - Genre-specific starting points

10. **Randomization Controls**
    - Seed values for reproducibility
    - Variation sliders

11. **Pattern Libraries**
    - Save/load custom rhythm patterns
    - Share patterns between projects

---

## Known Issues & Technical Debt

### Current Issues
- None critical

### Technical Debt
1. **Error Handling**: Python service could use more robust error handling
2. **Validation**: Input validation could be stricter (scale ranges, etc.)
3. **Logging**: Could add better logging for debugging
4. **Tests**: No automated tests yet (manual testing works fine for personal use)

### Port Conflict Resolution
- ✅ Fixed: Changed from port 5000 to 5001
- Reason: macOS AirPlay uses port 5000
- Updated in: `python_service/config.py` and PHP `config.php`

---

## Common Commands

### Starting Services
```bash
./start-services.sh
```

Or manually:
```bash
# Terminal 1 - Python service
cd python_service
source venv/bin/activate
python app.py

# Terminal 2 - PHP server
php -S localhost:9000
```

### Testing
```bash
# Test generation without login
php test_generation.php

# Check Python service
curl http://localhost:5001/health

# Check PHP service
curl http://localhost:9000
```

### Database Reset
```bash
rm database/saas.db
php database/init.php
php database/migrate_google_oauth.php
php database/migrate_items.php
php database/migrate_stripe.php
php database/migrate_midi_files.php
```

### Troubleshooting
- **MIDI service unavailable**: Check Python service is running on 5001
- **Port in use**: Kill process or change port in configs
- **Generation fails**: Check Python logs, verify `uploads/midi/` is writable
- **OAuth fails**: Verify credentials in `config.local.php`

---

## Configuration Notes

### Required for Basic Use
- Google OAuth credentials (for authentication)
- Python service must be running

### Optional
- Stripe credentials (only if using paid subscriptions)
- Custom pricing limits (defaults work fine)

### Google OAuth Setup
1. Go to https://console.cloud.google.com/apis/credentials
2. Create OAuth client ID → Web application
3. Add redirect: `http://localhost:9000/auth/google-callback.php`
4. Copy Client ID and Secret to `config.local.php`

---

## Notes for AI Assistants

### When User Asks to Add Generator

1. Ask which existing Python file to use (from `/Programming_Music_New/`)
2. Copy file to `python_service/generators/`
3. Follow the 5-step integration process above
4. Test with `php test_generation.php` or through UI

### Code Style Preferences
- PHP: Follow existing patterns in `includes/` classes
- Python: PEP 8, docstrings for functions
- Keep it simple - this is a personal project, not production SaaS

### Key Files to Know
- `includes/MidiGenerator.php` - PHP client for Python service
- `python_service/generators/midi_tools.py` - Core MIDI utilities
- `dashboard/project-edit.php` - Main UI for generation
- `dashboard/generate-midi.php` - Backend handler

---

**This is a living document. Update as project evolves.**


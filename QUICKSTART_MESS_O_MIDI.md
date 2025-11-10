# Mess o Midi - Quick Start Guide

## 🎉 Your Application is Ready!

Both services are now running and tested:
- ✅ PHP Web Server: http://localhost:9000
- ✅ Python MIDI Service: http://localhost:5001
- ✅ Database initialized with all tables
- ✅ MIDI generation tested and working

## 🚀 Getting Started

### 1. Open the Application

Open your browser and go to: **http://localhost:9000**

### 2. Sign In (Development Mode)

For local development without Google OAuth setup:
1. You can manually create a test user in the database, or
2. Set up Google OAuth credentials (see SETUP.md)

### 3. Create Your First Project

1. Click "Sign in with Google" (if OAuth is set up)
2. Go to Dashboard
3. Click "+ New" to create a project
4. Give it a name like "My First Beat"
5. Click "Create Project"

### 4. Generate a Bassline

1. Open your project by clicking on it
2. Click the green "Generate Bassline" button
3. Wait 1-2 seconds
4. Your MIDI file will appear in the tracks list
5. Click "Download" to get the .mid file

### 5. Use in Your DAW

Import the downloaded MIDI file into:
- Ableton Live
- FL Studio
- Logic Pro
- Any DAW that supports MIDI

## 🎹 What You Can Do Now

### Current Features
- ✅ Create unlimited projects (on free tier: 5 projects)
- ✅ Generate bassline MIDI files
- ✅ Download MIDI files for your DAW
- ✅ Manage multiple projects
- ✅ Track-based organization

### Coming Soon (Easy to Add)
You already have the Python code for these in your other repo:
- 🎼 Chord progressions (`chords.py`)
- 🥁 Kick patterns (`kick_pattern.py`)
- 🎶 Melodies/motifs (`motif.py`)

## 📝 Quick Testing Without Login

If you want to test generation without setting up auth:

```bash
cd /Users/caseymeehan/Documents/base/work/other/code/Mess-o-Midi
php test_generation.php
```

This will generate a test MIDI file and show you it works!

## 🛠️ Adding More Generators

To add chord or drum generators from your existing Python code:

### Step 1: Copy Generator Files
```bash
cp /Users/caseymeehan/Documents/base/work/other/code/Programming_Music_New/chords.py python_service/generators/
cp /Users/caseymeehan/Documents/base/work/other/code/Programming_Music_New/kick_pattern.py python_service/generators/
```

### Step 2: Update Python Service
Edit `python_service/app.py` and add new endpoints:

```python
@app.route('/api/generate/chords', methods=['POST'])
def generate_chords():
    # Similar to generate_bass() but calls chord generator
    pass
```

### Step 3: Update PHP Client
Edit `includes/MidiGenerator.php` and add new methods:

```php
public function generateChords($filename, $options = []) {
    // Similar to generateBassline()
}
```

### Step 4: Add UI Button
Edit `dashboard/project-edit.php` and add a "Generate Chords" button next to "Generate Bassline"

### Step 5: Handle in Generation Endpoint
Update `dashboard/generate-midi.php` to handle the 'chords' type

## 🎵 File Structure

```
Your generated MIDI files are saved in:
/Users/caseymeehan/Documents/base/work/other/code/Mess-o-Midi/uploads/midi/

Format: {userId}_{projectId}_{type}_{timestamp}.mid
Example: 1_5_bass_1762794384.mid
```

## 🔧 Managing Services

### Stop Services
Press `Ctrl+C` in the terminal where services are running

### Restart Services
```bash
./start-services.sh
```

### Check Service Status
```bash
# Python Service
curl http://localhost:5001/health

# PHP Service
curl http://localhost:9000
```

## 🐛 Troubleshooting

### "MIDI generation service is not available"
- Make sure Python service is running on port 5001
- Check: `curl http://localhost:5001/health`
- Restart with: `cd python_service && ./venv/bin/python app.py`

### Port 5001 Already in Use
Edit `python_service/config.py` and change the port number

### Database Issues
Reset the database:
```bash
rm database/saas.db
php database/init.php
php database/migrate_google_oauth.php
php database/migrate_items.php
php database/migrate_stripe.php
php database/migrate_midi_files.php
```

## 📚 Documentation

- **Full Setup**: See `MESS_O_MIDI_README.md`
- **Original Template**: See `README.md`
- **Local Setup**: See `LOCAL_SETUP.md`

## 🎨 Customization Tips

### Change Scale
Edit `python_service/generators/bass.py`:
```python
# Change C_MAJOR to any scale you want
C_MAJOR = [40, 41, 43, 45, 47, 48, 50]  # C major
A_MINOR = [45, 47, 48, 50, 52, 53, 55]  # A minor
```

### Change Rhythm Pattern
Edit the `RHYTHM_DATA` array in `bass.py` to create different note timings

### Add Custom Generators
Use your existing Python code from `/Programming_Music_New/` as templates

## 🎉 You're All Set!

Your Mess o Midi application is fully functional and ready to generate music!

**Next Steps:**
1. Open http://localhost:9000 in your browser
2. Create a project
3. Generate some basslines
4. Download and use them in your music production!

---

**Happy Music Making! 🎵🎹🎶**


"""
Python MIDI Generation Service
Flask API for generating MIDI files
"""
from flask import Flask, request, jsonify, send_file
from flask_cors import CORS
import os
import time
import traceback
from config import PORT, HOST, DEBUG, OUTPUT_DIR
from generators import generate_bassline, generate_complex_chords, generate_simple_chords

app = Flask(__name__)
CORS(app)  # Enable CORS for PHP frontend


@app.route('/health', methods=['GET'])
def health_check():
    """Health check endpoint"""
    return jsonify({
        'status': 'healthy',
        'service': 'Mess o Midi - Python MIDI Service',
        'version': '1.0.0'
    })


@app.route('/api/generate/bass', methods=['POST'])
def generate_bass():
    """
    Generate a bassline MIDI file
    
    Request JSON:
    {
        "filename": "optional_filename.mid",
        "scale": [40, 41, 43, 45, 47, 48, 50],  // optional
        "rhythm": [0, 384, 768, ...]  // optional
    }
    
    Response JSON:
    {
        "success": true,
        "filepath": "path/to/file.mid",
        "filename": "filename.mid"
    }
    """
    try:
        data = request.get_json() or {}
        
        # Generate unique filename if not provided
        filename = data.get('filename')
        if not filename:
            timestamp = int(time.time())
            filename = f"bass_{timestamp}.mid"
        
        # Ensure .mid extension
        if not filename.endswith('.mid'):
            filename += '.mid'
        
        # Full path for the file
        filepath = os.path.join(OUTPUT_DIR, filename)
        
        # Get optional parameters
        scale = data.get('scale')
        rhythm = data.get('rhythm')
        
        # Generate the bassline
        result_path = generate_bassline(filepath, scale=scale, rhythm=rhythm)
        
        return jsonify({
            'success': True,
            'filepath': result_path,
            'filename': filename
        })
    
    except Exception as e:
        print(f"Error generating bassline: {str(e)}")
        traceback.print_exc()
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500


@app.route('/api/generate/complex-chords', methods=['POST'])
def generate_complex_chords_endpoint():
    """
    Generate a complex chord progression MIDI file
    
    Request JSON:
    {
        "filename": "optional_filename.mid",
        "scale": [40, 41, 43, 45, 47, 48, 50],  // optional
        "rhythm": [0, 384, 768, ...]  // optional
    }
    
    Response JSON:
    {
        "success": true,
        "filepath": "path/to/file.mid",
        "filename": "filename.mid"
    }
    """
    try:
        data = request.get_json() or {}
        
        # Generate unique filename if not provided
        filename = data.get('filename')
        if not filename:
            timestamp = int(time.time())
            filename = f"complex_chords_{timestamp}.mid"
        
        # Ensure .mid extension
        if not filename.endswith('.mid'):
            filename += '.mid'
        
        # Full path for the file
        filepath = os.path.join(OUTPUT_DIR, filename)
        
        # Get optional parameters
        scale = data.get('scale')
        rhythm = data.get('rhythm')
        
        # Generate the complex chords
        result_path = generate_complex_chords(filepath, scale=scale, rhythm=rhythm)
        
        return jsonify({
            'success': True,
            'filepath': result_path,
            'filename': filename
        })
    
    except Exception as e:
        print(f"Error generating complex chords: {str(e)}")
        traceback.print_exc()
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500


@app.route('/api/generate/simple-chords', methods=['POST'])
def generate_simple_chords_endpoint():
    """
    Generate a simple triad chord progression MIDI file
    
    Request JSON:
    {
        "filename": "optional_filename.mid",
        "scale": [40, 41, 43, 45, 47, 48, 50],  // optional
        "rhythm": [0, 384, 768, ...]  // optional
    }
    
    Response JSON:
    {
        "success": true,
        "filepath": "path/to/file.mid",
        "filename": "filename.mid"
    }
    """
    try:
        data = request.get_json() or {}
        
        # Generate unique filename if not provided
        filename = data.get('filename')
        if not filename:
            timestamp = int(time.time())
            filename = f"simple_chords_{timestamp}.mid"
        
        # Ensure .mid extension
        if not filename.endswith('.mid'):
            filename += '.mid'
        
        # Full path for the file
        filepath = os.path.join(OUTPUT_DIR, filename)
        
        # Get optional parameters
        scale = data.get('scale')
        rhythm = data.get('rhythm')
        
        # Generate the simple chords
        result_path = generate_simple_chords(filepath, scale=scale, rhythm=rhythm)
        
        return jsonify({
            'success': True,
            'filepath': result_path,
            'filename': filename
        })
    
    except Exception as e:
        print(f"Error generating simple chords: {str(e)}")
        traceback.print_exc()
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500


@app.route('/api/download/<filename>', methods=['GET'])
def download_file(filename):
    """
    Download a generated MIDI file
    """
    try:
        filepath = os.path.join(OUTPUT_DIR, filename)
        
        if not os.path.exists(filepath):
            return jsonify({
                'success': False,
                'error': 'File not found'
            }), 404
        
        return send_file(
            filepath,
            mimetype='audio/midi',
            as_attachment=True,
            download_name=filename
        )
    
    except Exception as e:
        print(f"Error downloading file: {str(e)}")
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500


@app.errorhandler(404)
def not_found(error):
    """404 error handler"""
    return jsonify({
        'success': False,
        'error': 'Endpoint not found'
    }), 404


@app.errorhandler(500)
def internal_error(error):
    """500 error handler"""
    return jsonify({
        'success': False,
        'error': 'Internal server error'
    }), 500


if __name__ == '__main__':
    print(f"Starting Mess o Midi Python Service on {HOST}:{PORT}")
    print(f"Output directory: {OUTPUT_DIR}")
    app.run(host=HOST, port=PORT, debug=DEBUG)


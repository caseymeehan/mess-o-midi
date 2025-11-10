"""
Configuration for Python MIDI Service
"""
import os

# Service configuration
PORT = int(os.getenv('PORT', 5001))
HOST = os.getenv('HOST', '0.0.0.0')
DEBUG = os.getenv('DEBUG', 'False').lower() == 'true'

# Output directory for generated MIDI files
OUTPUT_DIR = os.path.abspath(os.path.join(os.path.dirname(__file__), '..', 'uploads', 'midi'))

# Ensure output directory exists
os.makedirs(OUTPUT_DIR, exist_ok=True)

# MIDI generation defaults
DEFAULT_SCALE = 'C_MAJOR'
DEFAULT_BPM = 120


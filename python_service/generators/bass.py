"""
Bassline Generator
Generates random basslines in C major scale
"""
import random
from .midi_tools import data_to_midi, create_file, rhythm_to_on_off

# MIDI numbers of the possible bass notes (c major scale, E1-D2)
C_MAJOR = [40, 41, 43, 45, 47, 48, 50]

# Timing of the notes on and off (12 whole notes, 6 half notes, 4 quarter notes)
RHYTHM_DATA = [0, 384, 768, 1152, 1536, 1920, 2304, 2688, 3072, 3456, 3840, 4224, 4608, 4800, 4992, 5184, 5376, 5568, 5760, 5856, 5952, 6048, 6144, 6240]


def generate_bassline(output_path, scale=None, rhythm=None):
    """
    Generate a random bassline MIDI file
    
    Args:
        output_path (str): Full path where to save the MIDI file
        scale (list, optional): List of MIDI note numbers to use. Defaults to C_MAJOR.
        rhythm (list, optional): List of timing values. Defaults to RHYTHM_DATA.
    
    Returns:
        str: Path to the generated MIDI file
    """
    if scale is None:
        scale = C_MAJOR
    if rhythm is None:
        rhythm = RHYTHM_DATA
    
    # Grab random notes from bass octave of c-major scale and arrange them randomly
    pitch_data = random.choices(scale, k=22)
    
    # Create note off data when one note starts signals the end of the previous note
    note_on_timing, note_off_timing = rhythm_to_on_off(rhythm)
    
    # Turn pitch and rhythm data to midi data
    bass_midi_notes = data_to_midi(pitch_data, note_on_timing, note_off_timing)
    
    # Add meta midi data (beginning and ending data) and write a new file
    return create_file(bass_midi_notes, output_path)


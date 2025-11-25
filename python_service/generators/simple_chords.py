"""
Simple Chord Generator
Generates traditional triad chord progressions in C major
"""
import random
import copy
from .midi_tools import data_to_midi, create_file, fit_to_c_major, rhythm_to_on_off


def generate_simple_chords(output_path, scale=None, rhythm=None):
    """
    Generate simple triad chord progression
    
    Creates traditional chord progressions using root, third, fifth, and octave.
    Perfect for clean, classic harmonies.
    
    Args:
        output_path: Full path where to save .mid file
        scale: Optional list of MIDI note numbers for bass notes (defaults to C major)
        rhythm: Optional list of timing values (defaults to varied rhythm pattern)
    
    Returns:
        str: Path to generated file
    """
    # Default to C major scale (E1-D2) if not provided
    if scale is None:
        scale = [40, 41, 43, 45, 47, 48, 50]
    
    # Default rhythm: 12 whole notes, 6 half notes, 4 quarter notes
    if rhythm is None:
        rhythm = [0, 384, 768, 1152, 1536, 1920, 2304, 2688, 3072, 3456, 3840, 4224, 
                 4608, 4800, 4992, 5184, 5376, 5568, 5760, 5856, 5952, 6048, 6144, 6240]
    
    # Generate random bassline from scale
    num_notes = len(rhythm) - 1
    bass = random.choices(scale, k=num_notes)
    
    # Create root notes (bass transposed up an octave)
    roots_raw = copy.deepcopy(bass)
    roots = [x + 12 for x in roots_raw]
    
    # Create thirds (root + 3 semitones, fit to C major)
    harmony1_raw = copy.deepcopy(roots)
    harmony1_raw = [x + 3 for x in harmony1_raw]
    harmony1 = fit_to_c_major(harmony1_raw)
    
    # Create fifths (third + 3 semitones, fit to C major)
    harmony2_raw = copy.deepcopy(harmony1)
    harmony2_raw = [x + 3 for x in harmony2_raw]
    harmony2 = fit_to_c_major(harmony2_raw)
    
    # Create octave (root + 12 semitones)
    roots_octave_raw = copy.deepcopy(roots)
    roots_octave = [x + 12 for x in roots_octave_raw]
    
    # Create note on/off timing
    note_on_timing, note_off_timing = rhythm_to_on_off(rhythm)
    
    # Convert pitch and rhythm data to MIDI
    roots_midi_notes = data_to_midi(roots, note_on_timing, note_off_timing)
    harmony1_midi_notes = data_to_midi(harmony1, note_on_timing, note_off_timing)
    harmony2_midi_notes = data_to_midi(harmony2, note_on_timing, note_off_timing)
    roots_octave_midi_notes = data_to_midi(roots_octave, note_on_timing, note_off_timing)
    
    # Combine all voices into one MIDI sequence
    chords = map(list, zip(roots_midi_notes, harmony1_midi_notes, 
                          harmony2_midi_notes, roots_octave_midi_notes))
    chords = [item for lst in chords for item in lst]
    
    # Create MIDI file
    return create_file(chords, output_path)


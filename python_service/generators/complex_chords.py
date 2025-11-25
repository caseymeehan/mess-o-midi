"""
Complex Chord Progression Generator
Generates complex, randomized chord progressions in C major with jazz-like harmonies
"""
import random
import copy
from .midi_tools import data_to_midi, create_file, fit_to_c_major, rhythm_to_on_off


def generate_complex_chords(output_path, scale=None, rhythm=None):
    """
    Generate complex chord progression with experimental harmonies
    
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
    num_notes = len(rhythm) - 1  # One less than rhythm points
    bass = random.choices(scale, k=num_notes)
    
    def random_harmony(pitch_data):
        """
        Create harmony by transposing up 2-4 semitones randomly and fitting to scale
        """
        harmony_raw = copy.deepcopy(pitch_data)
        harmony_raw = [x + random.choice([2, 3, 4]) for x in harmony_raw]
        harmony = fit_to_c_major(harmony_raw)
        return harmony
    
    # Create root notes (bass transposed up an octave)
    roots_raw = copy.deepcopy(bass)
    roots = [x + 12 for x in roots_raw]
    
    # Generate 3 harmony layers with random intervals
    harmony1 = random_harmony(roots)
    harmony2 = random_harmony(harmony1)
    harmony3 = random_harmony(harmony2)
    
    # Create note on/off timing
    note_on_timing, note_off_timing = rhythm_to_on_off(rhythm)
    
    # Convert pitch and rhythm data to MIDI
    bass_midi_notes = data_to_midi(bass, note_on_timing, note_off_timing)
    roots_midi_notes = data_to_midi(roots, note_on_timing, note_off_timing)
    harmony1_midi_notes = data_to_midi(harmony1, note_on_timing, note_off_timing)
    harmony2_midi_notes = data_to_midi(harmony2, note_on_timing, note_off_timing)
    harmony3_midi_notes = data_to_midi(harmony3, note_on_timing, note_off_timing)
    
    # Combine all voices into one MIDI sequence
    chords = map(list, zip(bass_midi_notes, roots_midi_notes, harmony1_midi_notes, 
                          harmony2_midi_notes, harmony3_midi_notes))
    chords = [item for lst in chords for item in lst]
    
    # Create MIDI file
    return create_file(chords, output_path)


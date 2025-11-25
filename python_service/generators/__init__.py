"""
MIDI Generators Package
"""
from .bass import generate_bassline
from .complex_chords import generate_complex_chords
from .simple_chords import generate_simple_chords
from .midi_tools import (
    data_to_midi,
    create_file,
    rhythm_to_on_off,
    fit_to_c_major,
    normal_choice
)

__all__ = [
    'generate_bassline',
    'generate_complex_chords',
    'generate_simple_chords',
    'data_to_midi',
    'create_file',
    'rhythm_to_on_off',
    'fit_to_c_major',
    'normal_choice'
]


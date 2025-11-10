"""
MIDI Generators Package
"""
from .bass import generate_bassline
from .midi_tools import (
    data_to_midi,
    create_file,
    rhythm_to_on_off,
    fit_to_c_major,
    normal_choice
)

__all__ = [
    'generate_bassline',
    'data_to_midi',
    'create_file',
    'rhythm_to_on_off',
    'fit_to_c_major',
    'normal_choice'
]


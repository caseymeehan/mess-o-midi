"""
MIDI Tools - Utilities for MIDI file generation
Based on original midi_tools.py
"""
import py_midicsv
import copy
from random import normalvariate, randrange

# These integers correspond to the black and white keys of the piano
black_keys = [1, 3, 6, 8, 10, 13, 15, 18, 20, 22, 25, 27, 30, 32, 34, 37, 39, 42, 44, 46, 49, 51, 54, 56, 58, 61, 63, 66, 68, 70, 73, 75, 78, 80, 82, 85, 87, 90, 92, 94, 97, 99, 102, 104, 106, 109, 111, 114, 116, 118, 121, 123, 126]
white_keys = [0, 2, 4, 5, 7, 9, 11, 12, 14, 16, 17, 19, 21, 23, 24, 26, 28, 29, 31, 33, 35, 36, 38, 40, 41, 43, 45, 47, 48, 50, 52, 53, 55, 57, 59, 60, 62, 64, 65, 67, 69, 71, 72, 74, 76, 77, 79, 81, 83, 84, 86, 88, 89, 91, 93, 95, 96, 98, 100, 101, 103, 105, 107, 108, 110, 112, 113, 115, 117, 119, 120, 122, 124, 125, 127]

NOTE_ON_WRAPPER = ['1', 0, 'Note_on_c', '0', 60, '100'] 
NOTE_OFF_WRAPPER = ['1', 96, 'Note_off_c', '0', 60, '64']


def replace_value(replace_item, notes_list=None, timing_list=None):
    """Used for manipulating pitch and rhythm data"""
    if not notes_list:
        return None
    else: 
        notes_list_copy = copy.deepcopy(notes_list)

    if not timing_list:
        return None
    else: 
        timing_list_copy = copy.deepcopy(timing_list)

    for index, item in enumerate(notes_list_copy):
        item[replace_item] = timing_list_copy[index]

    return notes_list_copy


def switch_string(switch_index, switch_index_2, notes=None, str_int_switch=str):
    """Used for switching from int to str and back"""
    if not notes:
        return None
    else: 
        new_notes = copy.deepcopy(notes)

    for index, item in enumerate(new_notes):
        item[switch_index] = str_int_switch(item[switch_index])
        item[switch_index_2] = str_int_switch(item[switch_index_2])

    return new_notes


def fit_to_c_major(notes_list):
    """Used for fitting pitch data to correspond with c-major scale / white keys list"""
    new_list = []
    for i in notes_list:  # changes note data to the next highest number in scale
        if i not in white_keys:
            i = i + 1
        new_list.append(i)
    return new_list


def midi_to_data(midi):
    """Used for midi files into pitch and rhythm data"""
    midicsv = py_midicsv.midi_to_csv(midi)
    melody = midicsv[5:-2]

    new_melody = [i.split(",") for i in melody]

    for index, item in enumerate(new_melody):
        item[1] = int(item[1])
        item[4] = int(item[4])

    all_melody = switch_string(1, 4, notes=new_melody, str_int_switch=int)

    note_on = [i for i in all_melody if i[2] == ' Note_on_c']

    note_off = [i for i in all_melody if i[2] == ' Note_off_c']

    pitch_data = [i[4] for i in note_on]
    note_on_timing = [i[1] for i in note_on]
    note_off_timing = [i[1] for i in note_off]
    return pitch_data, note_on_timing, note_off_timing


def on_off_to_rhythm(note_on_timing, note_off_timing):
    """Convert note on/off timing to rhythm data"""
    rhythm_data = note_on_timing + note_off_timing[-1:]
    return rhythm_data


def rhythm_to_on_off(rhythm_data):
    """Used to create note off data when one note starts signals the end of the previous note"""
    # Splitting rhythm data into two lists, one to turn notes on and one to turn notes off 
    note_on_timing, note_off_timing = rhythm_data[:-1], rhythm_data[1:]
    return note_on_timing, note_off_timing


def data_to_midi(pitch_data, note_on_timing, note_off_timing):
    """Used for pitch and note on off data into midi notes"""
    # This is how the midi data needs to be formatted for each note
    NOTE_ON_WRAPPER = ['1', 0, 'Note_on_c', '0', 60, '100'] 
    NOTE_OFF_WRAPPER = ['1', 96, 'Note_off_c', '0', 60, '64']

    # Creating a wrapper for each time a note turns on
    notes_on = [NOTE_ON_WRAPPER[:] for i in range(len(pitch_data))]
    # Creating a wrapper for each time a note turns off
    notes_off = [NOTE_OFF_WRAPPER[:] for i in range(len(pitch_data))]

    # Replacing index 1 of dummy wrapper data new timing / rhythm data
    notes_on, notes_off = replace_value(1, notes_list=notes_on, timing_list=note_on_timing), replace_value(1, notes_list=notes_off, timing_list=note_off_timing)
    # Replacing index 4 of dummy wrapper data with pitch data
    notes_on, notes_off = replace_value(4, notes_list=notes_on, timing_list=pitch_data), replace_value(4, notes_off, timing_list=pitch_data)

    # Switching the new timing and pitch data into a string
    notes_on_str = switch_string(1, 4, notes=notes_on)
    notes_off_str = switch_string(1, 4, notes=notes_off)

    # Combining notes on and notes off data in the proper order
    midi_notes = map(list, zip(notes_on_str, notes_off_str))
    midi_notes = [item for lst in midi_notes for item in lst]

    # Joining this into one list
    midi_notes_join = [','.join(i) for i in midi_notes]

    return midi_notes_join


def normal_choice(lst, mean=None, stddev=None):
    """Choose an item from a list using normal distribution"""
    if mean is None:
        # if mean is not specified, use center of list
        mean = (len(lst) - 1) / 2
 
    if stddev is None:
        # if stddev is not specified, let list be -3 .. +3 standard deviations
        stddev = len(lst) / 6
 
    while True:
        index = int(normalvariate(mean, stddev) + 0.5)
        if 0 <= index < len(lst):
            return lst[index]


def create_file(midi_notes, filepath):
    """
    Used for packaging up midi notes into actual midi files
    Adding the proper midi meta data so that music software can read this file
    """
    TOP_META = ['0, 0, Header, 0, 1, 96\n', '1, 0, Start_track\n', '1, 0, Title_t, "\\000"\n', '1, 0, Time_signature, 4, 2, 36, 8\n', '1, 0, Time_signature, 4, 2, 36, 8\n']
    END_META = ['1, 384, End_track\n', '0, 0, End_of_file']
    ready_for_midi = TOP_META + midi_notes + END_META

    # Creating the actual midi file
    midi_object = py_midicsv.csv_to_midi(ready_for_midi)

    with open(filepath, "wb") as output_file:
        midi_writer = py_midicsv.FileWriter(output_file)
        midi_writer.write(midi_object)
    
    return filepath


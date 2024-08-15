import os

PROJECT_ROOT = os.path.dirname(os.path.abspath(__file__))


def path_from_root(*varargs):
    return os.path.join(PROJECT_ROOT, *varargs)

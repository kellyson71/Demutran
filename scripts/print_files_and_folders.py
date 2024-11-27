
import os

def load_gitignore(path):
    gitignore_path = os.path.join(path, '.gitignore')
    ignored_paths = [
        './descomentador.py',
        'sandbox/',
        'Defesa/midia/',
        'fpdf',
        'PCD/midia/',
        'env/config.php',
        'sandbox/',
        'utils/vendor/'
    ]
    if os.path.exists(gitignore_path):
        with open(gitignore_path, 'r') as file:
            ignored_paths.extend([line.strip() for line in file if line.strip() and not line.startswith('#')])
    return ignored_paths

def is_ignored(path, ignored_paths):
    for ignored in ignored_paths:
        if ignored.endswith('/'):
            if path.startswith(ignored):
                return True
        else:
            if path == ignored:
                return True
    return False

def print_files_and_folders(path, ignored_paths, level=0, max_level=1):
    if level > max_level:
        return
    try:
        for entry in os.listdir(path):
            entry_path = os.path.join(path, entry)
            relative_path = os.path.relpath(entry_path, start=path)
            if is_ignored(relative_path, ignored_paths):
                continue
            print('  ' * level + '- ' + entry)
            if os.path.isdir(entry_path) and level < max_level:
                print_files_and_folders(entry_path, ignored_paths, level + 1, max_level)
    except PermissionError:
        print('  ' * level + '- [Permission Denied]')

if __name__ == "__main__":
    project_path = os.path.dirname(os.path.abspath(__file__))
    ignored_paths = load_gitignore(project_path)
    print_files_and_folders(project_path, ignored_paths)
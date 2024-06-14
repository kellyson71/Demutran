import os
import re
import fnmatch

def remove_comments(code):
    # Remove comments from the code
    code = re.sub(r'/\*[\s\S]*?\*/', '', code)  # Remove /* ... */ comments
    code = re.sub(r'<!--[\s\S]*?-->', '', code)  # Remove <!-- ... --> comments
    code = re.sub(r'^\s*//.*$', '', code, flags=re.MULTILINE)  # Remove // comments
    return code

def process_file(filepath):
    with open(filepath, 'r+', encoding='utf-8') as file:
        codigo = file.read()
        file.seek(0)
        codigo_sem_comentarios = remove_comments(codigo)
        file.write(codigo_sem_comentarios)
        file.truncate()

def process_directory(directory):
    for root, _, filenames in os.walk(directory):
        for pattern in ['*.js', '*.html', '*.css']:
            for filename in fnmatch.filter(filenames, pattern):
                filepath = os.path.join(root, filename)
                process_file(filepath)
root_directory = '../demutram'
process_directory(root_directory)
print('Comentários removidos com sucesso!')
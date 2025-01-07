import os

def contar_arquivos_e_pastas(diretorio, excluir_subpasta=None):
    total_arquivos = 0
    total_pastas = 0
    extensoes = {}

    for root, dirs, files in os.walk(diretorio):
        # Exclui a subpasta, se especificada
        if excluir_subpasta and excluir_subpasta in dirs:
            dirs.remove(excluir_subpasta)

        # Conta pastas
        total_pastas += len(dirs)
        
        # Conta arquivos
        total_arquivos += len(files)
        for file in files:
            ext = os.path.splitext(file)[1]
            if ext not in extensoes:
                extensoes[ext] = 0
            extensoes[ext] += 1

    return total_pastas, total_arquivos, extensoes

diretorio = r"C:\wamp64\www\Demutran"
subpasta_excluida = "lib"

pastas, arquivos, tipos = contar_arquivos_e_pastas(diretorio, subpasta_excluida)

print(f"Total de pastas (excluindo '{subpasta_excluida}'): {pastas}")
print(f"Total de arquivos (excluindo '{subpasta_excluida}'): {arquivos}")
print("Quantidade de arquivos por tipo:")
for ext, count in tipos.items():
    print(f"  {ext or 'Sem extensão'}: {count}")

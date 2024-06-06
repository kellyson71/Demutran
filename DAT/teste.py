import cv2

# Função para ativar a câmera
def ativar_camera():
    cap = cv2.VideoCapture(0)  # Inicializa a captura de vídeo com a câmera padrão (0)

    while True:
        ret, frame = cap.read()  # Lê o próximo quadro da câmera

        cv2.imshow('Camera', frame)  # Mostra o quadro na janela 'Camera'

        if cv2.waitKey(1) & 0xFF == ord('q'):  # Aguarda pressionamento da tecla 'q' para sair
            break

    cap.release()  # Libera a captura de vídeo
    cv2.destroyAllWindows()  # Fecha todas as janelas

# Chamada da função para ativar a câmera
ativar_camera()


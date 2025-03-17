-- Script SQL para criação da estrutura do banco de dados de formulários DAT
-- Este script cria todas as tabelas necessárias para o funcionamento completo do sistema

-- Criar a tabela principal de controle de formulários
CREATE TABLE IF NOT EXISTS formularios_dat_central (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token VARCHAR(64) NOT NULL UNIQUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultima_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    preenchimento_status ENUM('Não Iniciado', 'Em Andamento', 'Concluído') DEFAULT 'Não Iniciado',
    usuario_id INT NULL,  -- ID do usuário que criou o formulário, se aplicável
    INDEX (token),
    INDEX (preenchimento_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Criar a tabela DAT1 - Informações iniciais e do acidente
CREATE TABLE IF NOT EXISTS DAT1 (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token VARCHAR(64) NOT NULL,
    relacao_com_veiculo VARCHAR(50) NOT NULL,
    estrangeiro TINYINT(1) DEFAULT 0,
    tipo_documento VARCHAR(30) NULL,
    numero_documento VARCHAR(50) NULL,
    pais VARCHAR(50) NULL,
    nome VARCHAR(100) NOT NULL,
    cpf VARCHAR(14) NULL,
    profissao VARCHAR(100) NULL,
    sexo CHAR(1) NULL,
    data_nascimento DATE NULL,
    email VARCHAR(100) NULL,
    celular VARCHAR(20) NULL,
    cep VARCHAR(10) NULL,
    logradouro VARCHAR(100) NULL,
    numero VARCHAR(20) NULL,
    complemento VARCHAR(100) NULL,
    bairro_localidade VARCHAR(100) NULL,
    cidade VARCHAR(100) NULL,
    uf CHAR(2) NULL,
    
    -- Informações do acidente
    data DATE NULL,
    horario TIME NULL,
    cidade_acidente VARCHAR(100) NULL,
    uf_acidente CHAR(2) NULL,
    cep_acidente VARCHAR(10) NULL,
    logradouro_acidente VARCHAR(100) NULL,
    numero_acidente VARCHAR(20) NULL,
    complemento_acidente VARCHAR(100) NULL,
    bairro_localidade_acidente VARCHAR(100) NULL,
    ponto_referencia_acidente VARCHAR(200) NULL,
    
    -- Condições do acidente
    condicoes_via VARCHAR(50) NULL,
    sinalizacao_horizontal_vertical VARCHAR(50) NULL,
    tracado_via VARCHAR(50) NULL,
    condicoes_meteorologicas VARCHAR(50) NULL,
    tipo_acidente VARCHAR(50) NULL,
    
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultima_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (token) REFERENCES formularios_dat_central(token) ON DELETE CASCADE,
    INDEX (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Criar a tabela DAT2 - Informações do veículo e condutor
CREATE TABLE IF NOT EXISTS DAT2 (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token VARCHAR(64) NOT NULL,
    
    -- Informações do veículo
    situacao_veiculo VARCHAR(50) NULL,
    placa VARCHAR(10) NULL,
    renavam VARCHAR(20) NULL,
    tipo_veiculo VARCHAR(50) NULL,
    chassi VARCHAR(30) NULL,
    uf_veiculo CHAR(2) NULL,
    cor_veiculo VARCHAR(30) NULL,
    marca_modelo VARCHAR(100) NULL,
    ano_modelo INT NULL,
    ano_fabricacao INT NULL,
    categoria VARCHAR(50) NULL,
    segurado VARCHAR(3) NULL,
    seguradora VARCHAR(100) NULL,
    veiculo_articulado VARCHAR(3) NULL,
    manobra_acidente VARCHAR(100) NULL,
    
    -- Informações do condutor
    nao_habilitado TINYINT(1) DEFAULT 0,
    numero_registro VARCHAR(30) NULL,
    uf_cnh CHAR(2) NULL,
    categoria_cnh VARCHAR(5) NULL,
    data_1habilitacao DATE NULL,
    validade_cnh DATE NULL,
    estrangeiro_condutor TINYINT(1) DEFAULT 0,
    tipo_documento_condutor VARCHAR(30) NULL,
    numero_documento_condutor VARCHAR(50) NULL,
    pais_documento_condutor VARCHAR(50) NULL,
    nome_condutor VARCHAR(100) NULL,
    cpf_condutor VARCHAR(14) NULL,
    sexo_condutor CHAR(1) NULL,
    nascimento_condutor DATE NULL,
    email_condutor VARCHAR(100) NULL,
    celular_condutor VARCHAR(20) NULL,
    cep_condutor VARCHAR(10) NULL,
    logradouro_condutor VARCHAR(100) NULL,
    numero_condutor VARCHAR(20) NULL,
    complemento_condutor VARCHAR(100) NULL,
    bairro_condutor VARCHAR(100) NULL,
    cidade_condutor VARCHAR(100) NULL,
    uf_condutor CHAR(2) NULL,
    
    -- Informações de danos
    danos_sistema_seguranca TINYINT(1) DEFAULT 0,
    partes_danificadas TEXT NULL,
    
    -- Informações da carga
    danos_carga TINYINT(1) DEFAULT 0,
    numero_notas VARCHAR(50) NULL,
    tipo_mercadoria VARCHAR(100) NULL,
    valor_mercadoria VARCHAR(20) NULL,
    extensao_danos VARCHAR(100) NULL,
    tem_seguro_carga TINYINT(1) DEFAULT 0,
    seguradora_carga VARCHAR(100) NULL,
    
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultima_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (token) REFERENCES formularios_dat_central(token) ON DELETE CASCADE,
    INDEX (token),
    INDEX (placa),
    INDEX (renavam)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Criar a tabela DAT3 - Informações de testemunhas do acidente
CREATE TABLE IF NOT EXISTS DAT3 (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token VARCHAR(64) NOT NULL,
    nome_testemunha VARCHAR(100) NOT NULL,
    cpf_testemunha VARCHAR(14) NULL,
    telefone_testemunha VARCHAR(20) NULL,
    email_testemunha VARCHAR(100) NULL,
    cep_testemunha VARCHAR(10) NULL,
    logradouro_testemunha VARCHAR(100) NULL,
    numero_testemunha VARCHAR(20) NULL,
    complemento_testemunha VARCHAR(100) NULL,
    bairro_testemunha VARCHAR(100) NULL,
    cidade_testemunha VARCHAR(100) NULL,
    uf_testemunha CHAR(2) NULL,
    
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultima_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (token) REFERENCES formularios_dat_central(token) ON DELETE CASCADE,
    INDEX (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Criar a tabela DAT4 - Informações de outros veículos envolvidos
CREATE TABLE IF NOT EXISTS DAT4 (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token VARCHAR(64) NOT NULL,
    tipo_veiculo VARCHAR(50) NULL,
    placa VARCHAR(10) NULL,
    uf_veiculo CHAR(2) NULL,
    marca_modelo VARCHAR(100) NULL,
    cor_veiculo VARCHAR(30) NULL,
    nome_proprietario VARCHAR(100) NULL,
    telefone_proprietario VARCHAR(20) NULL,
    nome_condutor VARCHAR(100) NULL,
    telefone_condutor VARCHAR(20) NULL,
    seguradora VARCHAR(100) NULL,
    
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultima_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (token) REFERENCES formularios_dat_central(token) ON DELETE CASCADE,
    INDEX (token),
    INDEX (placa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Criar a tabela DAT5 - Informações de vítimas e feridos
CREATE TABLE IF NOT EXISTS DAT5 (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token VARCHAR(64) NOT NULL,
    nome_vitima VARCHAR(100) NOT NULL,
    cpf_vitima VARCHAR(14) NULL,
    sexo_vitima CHAR(1) NULL,
    data_nascimento_vitima DATE NULL,
    condicao_vitima ENUM('Ferido Leve', 'Ferido Grave', 'Fatal') NOT NULL,
    hospital_encaminhado VARCHAR(100) NULL,
    tipo_envolvido ENUM('Condutor', 'Passageiro', 'Pedestre', 'Outro') NOT NULL,
    observacoes TEXT NULL,
    
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultima_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (token) REFERENCES formularios_dat_central(token) ON DELETE CASCADE,
    INDEX (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Criar tabela para anexos/uploads de documentos
CREATE TABLE IF NOT EXISTS DAT_anexos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token VARCHAR(64) NOT NULL,
    tipo_documento ENUM('CNH', 'CRLV', 'Boletim de Ocorrência', 'Foto do Acidente', 'Outro') NOT NULL,
    nome_arquivo VARCHAR(255) NOT NULL,
    caminho_arquivo VARCHAR(255) NOT NULL,
    tamanho_arquivo INT NOT NULL,
    data_upload TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (token) REFERENCES formularios_dat_central(token) ON DELETE CASCADE,
    INDEX (token),
    INDEX (tipo_documento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Criar tabela para histórico de alterações
CREATE TABLE IF NOT EXISTS DAT_historico (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token VARCHAR(64) NOT NULL,
    usuario_id INT NULL,
    acao VARCHAR(50) NOT NULL,
    tabela_afetada VARCHAR(50) NOT NULL,
    data_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    detalhes TEXT NULL,
    
    FOREIGN KEY (token) REFERENCES formularios_dat_central(token) ON DELETE CASCADE,
    INDEX (token),
    INDEX (data_hora)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Criar índices adicionais para otimização de consultas
ALTER TABLE DAT1 ADD INDEX (cpf);
ALTER TABLE DAT2 ADD INDEX (cpf_condutor);

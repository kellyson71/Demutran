-- Script de inserção de dados de exemplo para teste do banco de dados

-- Inserir um registro de controle
INSERT INTO formularios_dat_central (token, preenchimento_status) 
VALUES ('abc123xyz456', 'Não Iniciado');

-- Inserir dados básicos no DAT1
INSERT INTO DAT1 (
    token, relacao_com_veiculo, estrangeiro, tipo_documento, numero_documento,
    pais, nome, cpf, profissao, sexo, data_nascimento, email, celular,
    cep, logradouro, numero, complemento, bairro_localidade, cidade, uf,
    data, horario, cidade_acidente, uf_acidente, cep_acidente, logradouro_acidente,
    numero_acidente, complemento_acidente, bairro_localidade_acidente,
    ponto_referencia_acidente, condicoes_via, sinalizacao_horizontal_vertical,
    tracado_via, condicoes_meteorologicas, tipo_acidente
) VALUES (
    'abc123xyz456', 'Proprietário', 0, 'RG', '12345678',
    'Brasil', 'João Silva', '123.456.789-00', 'Engenheiro', 'M', '1980-05-15',
    'joao@email.com', '(11)98765-4321', '01234-567', 'Rua das Flores', '123',
    'Apto 45', 'Jardim das Árvores', 'São Paulo', 'SP',
    '2023-06-10', '14:30:00', 'São Paulo', 'SP', '04567-890', 'Av. Principal',
    '1000', '', 'Centro', 'Próximo ao Shopping Center', 'Asfalto em bom estado',
    'Sinalização adequada', 'Reta', 'Céu limpo', 'Colisão traseira'
);

-- Inserir dados do veículo e condutor no DAT2
INSERT INTO DAT2 (
    token, situacao_veiculo, placa, renavam, tipo_veiculo, chassi,
    uf_veiculo, cor_veiculo, marca_modelo, ano_modelo, ano_fabricacao,
    categoria, segurado, seguradora, veiculo_articulado, manobra_acidente,
    nao_habilitado, numero_registro, uf_cnh, categoria_cnh, data_1habilitacao,
    validade_cnh, estrangeiro_condutor, tipo_documento_condutor,
    numero_documento_condutor, pais_documento_condutor, nome_condutor,
    cpf_condutor, sexo_condutor, nascimento_condutor, email_condutor,
    celular_condutor, cep_condutor, logradouro_condutor, numero_condutor,
    complemento_condutor, bairro_condutor, cidade_condutor, uf_condutor,
    danos_sistema_seguranca, partes_danificadas, danos_carga, numero_notas,
    tipo_mercadoria, valor_mercadoria, extensao_danos, tem_seguro_carga,
    seguradora_carga
) VALUES (
    'abc123xyz456', 'Particular', 'ABC1234', '12345678901', 'Automóvel', '9BWZZZ377VT004251',
    'SP', 'Preto', 'Volkswagen Gol', 2020, 2019,
    'Particular', 'Sim', 'Seguradora XYZ', 'Não', 'Seguindo em frente',
    0, '12345678901', 'SP', 'B', '2010-01-15',
    '2030-01-15', 0, 'CNH', '12345678901',
    'Brasil', 'João Silva', '123.456.789-00', 'M', '1980-05-15',
    'joao@email.com', '(11)98765-4321', '01234-567', 'Rua das Flores', '123',
    'Apto 45', 'Jardim das Árvores', 'São Paulo', 'SP',
    1, 'Para-choque dianteiro, capô', 0, '', 
    '', '', '', 0, ''
);

-- Inserir uma testemunha no DAT3
INSERT INTO DAT3 (
    token, nome_testemunha, cpf_testemunha, telefone_testemunha, 
    email_testemunha, cep_testemunha, logradouro_testemunha,
    numero_testemunha, complemento_testemunha, bairro_testemunha,
    cidade_testemunha, uf_testemunha
) VALUES (
    'abc123xyz456', 'Maria Oliveira', '987.654.321-00', '(11)91234-5678',
    'maria@email.com', '04567-890', 'Rua dos Lírios',
    '456', '', 'Vila Nova', 'São Paulo', 'SP'
);

-- Inserir outro veículo envolvido no DAT4
INSERT INTO DAT4 (
    token, tipo_veiculo, placa, uf_veiculo, marca_modelo,
    cor_veiculo, nome_proprietario, telefone_proprietario,
    nome_condutor, telefone_condutor, seguradora
) VALUES (
    'abc123xyz456', 'Automóvel', 'DEF5678', 'SP', 'Fiat Palio',
    'Branco', 'Carlos Pereira', '(11)99876-5432',
    'Carlos Pereira', '(11)99876-5432', 'Seguradora ABC'
);

-- Inserir uma vítima no DAT5
INSERT INTO DAT5 (
    token, nome_vitima, cpf_vitima, sexo_vitima, 
    data_nascimento_vitima, condicao_vitima,
    hospital_encaminhado, tipo_envolvido, observacoes
) VALUES (
    'abc123xyz456', 'Ana Santos', '456.789.123-00', 'F',
    '1990-03-25', 'Ferido Leve',
    'Hospital Municipal', 'Passageiro', 'Ferimentos leves no braço direito'
);

-- Inserir um anexo no DAT_anexos
INSERT INTO DAT_anexos (
    token, tipo_documento, nome_arquivo, caminho_arquivo,
    tamanho_arquivo
) VALUES (
    'abc123xyz456', 'Foto do Acidente', 'foto_acidente.jpg',
    '/uploads/abc123xyz456/foto_acidente.jpg', 2500000
);

-- Inserir registro no histórico
INSERT INTO DAT_historico (
    token, acao, tabela_afetada, detalhes
) VALUES (
    'abc123xyz456', 'Inserção', 'DAT1', 'Criação inicial do formulário'
);

-- Atualizar o status do formulário
UPDATE formularios_dat_central 
SET preenchimento_status = 'Em Andamento' 
WHERE token = 'abc123xyz456';

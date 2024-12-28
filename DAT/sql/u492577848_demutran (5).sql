-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 28/12/2024 às 02:23
-- Versão do servidor: 10.11.10-MariaDB
-- Versão do PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `u492577848_demutran`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `danos_veiculo`
--

CREATE TABLE `danos_veiculo` (
  `id` int(11) NOT NULL,
  `veiculo_id` int(11) NOT NULL,
  `tipo_dano` varchar(50) DEFAULT NULL,
  `descricao` text DEFAULT NULL,
  `valor_estimado` decimal(10,2) DEFAULT NULL,
  `tem_seguro` tinyint(1) DEFAULT 0,
  `seguradora` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `DAT1`
--

CREATE TABLE `DAT1` (
  `token` varchar(90) NOT NULL,
  `relacao_com_veiculo` varchar(255) NOT NULL,
  `estrangeiro` tinyint(1) DEFAULT NULL,
  `tipo_documento` varchar(50) NOT NULL,
  `numero_documento` varchar(100) NOT NULL,
  `pais` varchar(100) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `cpf` varchar(14) NOT NULL,
  `profissao` varchar(255) DEFAULT NULL,
  `sexo` char(1) DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `celular` varchar(20) DEFAULT NULL,
  `cep` varchar(10) NOT NULL,
  `logradouro` varchar(255) NOT NULL,
  `numero` varchar(10) NOT NULL,
  `complemento` varchar(255) DEFAULT NULL,
  `bairro_localidade` varchar(255) NOT NULL,
  `cidade` varchar(255) NOT NULL,
  `uf` char(2) NOT NULL,
  `data` date NOT NULL,
  `horario` time NOT NULL,
  `cidade_acidente` varchar(255) DEFAULT 'Pau dos Ferros',
  `uf_acidente` char(2) DEFAULT 'RN',
  `cep_acidente` varchar(10) DEFAULT NULL,
  `logradouro_acidente` varchar(255) DEFAULT NULL,
  `numero_acidente` varchar(10) DEFAULT NULL,
  `complemento_acidente` varchar(255) DEFAULT NULL,
  `bairro_localidade_acidente` varchar(255) DEFAULT NULL,
  `ponto_referencia_acidente` varchar(255) DEFAULT NULL,
  `condicoes_via` varchar(255) NOT NULL,
  `sinalizacao_horizontal_vertical` varchar(255) NOT NULL,
  `tracado_via` varchar(255) NOT NULL,
  `condicoes_meteorologicas` varchar(255) NOT NULL,
  `tipo_acidente` varchar(255) NOT NULL,
  `id` int(11) NOT NULL,
  `data_submissao` timestamp NULL DEFAULT current_timestamp(),
  `formulario_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `DAT2`
--

CREATE TABLE `DAT2` (
  `token` varchar(90) NOT NULL,
  `situacao_veiculo` varchar(255) DEFAULT NULL,
  `placa` varchar(10) DEFAULT NULL,
  `renavam` varchar(20) DEFAULT NULL,
  `tipo_veiculo` varchar(255) DEFAULT NULL,
  `chassi` varchar(20) DEFAULT NULL,
  `uf_veiculo` varchar(2) DEFAULT NULL,
  `cor_veiculo` varchar(50) DEFAULT NULL,
  `marca_modelo` varchar(255) DEFAULT NULL,
  `ano_modelo` int(11) DEFAULT NULL,
  `ano_fabricacao` int(11) DEFAULT NULL,
  `categoria` varchar(255) DEFAULT NULL,
  `segurado` varchar(255) DEFAULT NULL,
  `seguradora` varchar(255) DEFAULT NULL,
  `veiculo_articulado` varchar(255) DEFAULT NULL,
  `manobra_acidente` varchar(255) DEFAULT NULL,
  `nao_habilitado` tinyint(1) DEFAULT NULL,
  `numero_registro` varchar(20) DEFAULT NULL,
  `uf_cnh` varchar(2) DEFAULT NULL,
  `categoria_cnh` varchar(50) DEFAULT NULL,
  `data_1habilitacao` date DEFAULT NULL,
  `validade_cnh` date DEFAULT NULL,
  `estrangeiro_condutor` tinyint(1) DEFAULT NULL,
  `tipo_documento_condutor` varchar(255) DEFAULT NULL,
  `numero_documento_condutor` varchar(50) DEFAULT NULL,
  `pais_documento_condutor` varchar(100) DEFAULT NULL,
  `nome_condutor` varchar(255) DEFAULT NULL,
  `cpf_condutor` varchar(14) DEFAULT NULL,
  `sexo_condutor` varchar(10) DEFAULT NULL,
  `nascimento_condutor` date DEFAULT NULL,
  `email_condutor` varchar(100) DEFAULT NULL,
  `celular_condutor` varchar(15) DEFAULT NULL,
  `cep_condutor` varchar(10) DEFAULT NULL,
  `logradouro_condutor` varchar(255) DEFAULT NULL,
  `numero_condutor` varchar(10) DEFAULT NULL,
  `complemento_condutor` varchar(50) DEFAULT NULL,
  `bairro_condutor` varchar(100) DEFAULT NULL,
  `cidade_condutor` varchar(100) DEFAULT NULL,
  `uf_condutor` varchar(2) DEFAULT NULL,
  `danos_sistema_seguranca` tinyint(1) DEFAULT NULL,
  `partes_danificadas` varchar(255) DEFAULT NULL,
  `danos_carga` tinyint(1) DEFAULT NULL,
  `numero_notas` varchar(50) DEFAULT NULL,
  `tipo_mercadoria` varchar(255) DEFAULT NULL,
  `valor_mercadoria` decimal(10,2) DEFAULT NULL,
  `extensao_danos` varchar(255) DEFAULT NULL,
  `tem_seguro_carga` tinyint(1) DEFAULT NULL,
  `seguradora_carga` varchar(255) DEFAULT NULL,
  `id` int(11) NOT NULL,
  `formulario_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `DAT4`
--

CREATE TABLE `DAT4` (
  `id` int(11) NOT NULL,
  `token` varchar(90) NOT NULL,
  `patrimonio_text` text DEFAULT NULL,
  `meio_ambiente_text` text DEFAULT NULL,
  `informacoes_complementares_text` text DEFAULT NULL,
  `data_submissao` timestamp NULL DEFAULT current_timestamp(),
  `situacao` varchar(50) DEFAULT 'Pendente',
  `is_read` tinyint(1) DEFAULT 0,
  `formulario_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `formularios_dat_central`
--

CREATE TABLE `formularios_dat_central` (
  `id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `status` varchar(50) DEFAULT 'Pendente',
  `preenchimento_status` varchar(50) DEFAULT 'Incompleto',
  `tipo` enum('DAT') DEFAULT 'DAT',
  `data_criacao` timestamp NULL DEFAULT current_timestamp(),
  `ultima_atualizacao` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `email_usuario` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `leituras_registros`
--

CREATE TABLE `leituras_registros` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `tabela_origem` varchar(50) NOT NULL,
  `registro_id` int(11) NOT NULL,
  `data_leitura` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `noticias`
--

CREATE TABLE `noticias` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `resumo` text NOT NULL,
  `conteudo` mediumtext DEFAULT NULL,
  `imagem_url` varchar(255) DEFAULT NULL,
  `data_publicacao` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `notificacoes`
--

CREATE TABLE `notificacoes` (
  `id` int(11) NOT NULL,
  `mensagem` varchar(255) NOT NULL,
  `lida` tinyint(1) DEFAULT 0,
  `data_criacao` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `Parecer`
--

CREATE TABLE `Parecer` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `telefone` varchar(50) NOT NULL,
  `cpf_cnpj` varchar(20) NOT NULL,
  `local` varchar(255) NOT NULL,
  `evento` varchar(255) NOT NULL,
  `ponto_referencia` varchar(255) NOT NULL,
  `data_horario` varchar(255) NOT NULL,
  `protocolo` varchar(10) DEFAULT NULL,
  `declaracao` tinyint(1) DEFAULT 0,
  `signed_form_path` varchar(255) DEFAULT NULL,
  `data_submissao` timestamp NULL DEFAULT current_timestamp(),
  `email` varchar(255) NOT NULL,
  `documento_identificacao` varchar(255) DEFAULT NULL,
  `comprovante_residencia` varchar(255) DEFAULT NULL,
  `situacao` varchar(50) DEFAULT 'Pendente',
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `sac`
--

CREATE TABLE `sac` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `telefone` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `assunto` varchar(255) NOT NULL,
  `mensagem` text DEFAULT NULL,
  `data_submissao` timestamp NULL DEFAULT current_timestamp(),
  `tipo_contato` varchar(20) DEFAULT 'solicitacao',
  `situacao` varchar(50) DEFAULT 'Pendente',
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `solicitacao_cartao`
--

CREATE TABLE `solicitacao_cartao` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `n_cartao` varchar(10) DEFAULT NULL,
  `residente` tinyint(1) NOT NULL,
  `tipo_solicitacao` varchar(50) NOT NULL,
  `emissao_cartao` varchar(50) NOT NULL,
  `solicitante` varchar(50) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `data_nascimento` date NOT NULL,
  `cpf` varchar(20) NOT NULL,
  `endereco` varchar(200) NOT NULL,
  `telefone` varchar(20) NOT NULL,
  `doc_identidade_num` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `doc_identidade_url` varchar(255) DEFAULT NULL,
  `comprovante_residencia_url` varchar(255) DEFAULT NULL,
  `laudo_medico_url` varchar(255) DEFAULT NULL,
  `representante_legal` tinyint(1) NOT NULL DEFAULT 0,
  `nome_representante` varchar(100) DEFAULT NULL,
  `cpf_representante` varchar(20) DEFAULT NULL,
  `endereco_representante` varchar(200) DEFAULT NULL,
  `telefone_representante` varchar(20) DEFAULT NULL,
  `email_representante` varchar(100) DEFAULT NULL,
  `doc_identidade_representante_url` varchar(255) DEFAULT NULL,
  `proc_comprovante_url` varchar(255) DEFAULT NULL,
  `data_submissao` timestamp NULL DEFAULT current_timestamp(),
  `situacao` varchar(50) DEFAULT 'Pendente',
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `solicitacao_demutran`
--

CREATE TABLE `solicitacao_demutran` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tipo_solicitacao` varchar(255) DEFAULT NULL,
  `nome` varchar(255) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `assunto` varchar(255) DEFAULT NULL,
  `descricao` text DEFAULT NULL,
  `doc_requerimento_url` varchar(255) DEFAULT NULL,
  `cnh_url` varchar(255) DEFAULT NULL,
  `cnh_condutor_url` varchar(255) DEFAULT NULL,
  `notif_demutran_url` varchar(255) DEFAULT NULL,
  `crlv_url` varchar(255) DEFAULT NULL,
  `comprovante_residencia_url` varchar(255) DEFAULT NULL,
  `doc_complementares_urls` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `data_submissao` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `solicitacoes_demutran`
--

CREATE TABLE `solicitacoes_demutran` (
  `id` int(11) NOT NULL,
  `tipo_solicitacao` varchar(50) NOT NULL,
  `nome` varchar(255) DEFAULT NULL,
  `telefone` varchar(50) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `assunto` varchar(255) DEFAULT NULL,
  `descricao` text DEFAULT NULL,
  `doc_requerimento_url` varchar(255) DEFAULT NULL,
  `cnh_url` varchar(255) DEFAULT NULL,
  `cnh_condutor_url` varchar(255) DEFAULT NULL,
  `notif_DEMUTRAN_url` varchar(255) DEFAULT NULL,
  `crlv_url` varchar(255) DEFAULT NULL,
  `comprovante_residencia_url` varchar(255) DEFAULT NULL,
  `doc_complementares_urls` text DEFAULT NULL,
  `data_submissao` timestamp NULL DEFAULT current_timestamp(),
  `cpf` varchar(255) DEFAULT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `numero` varchar(50) DEFAULT NULL,
  `complemento` varchar(255) DEFAULT NULL,
  `bairro` varchar(255) DEFAULT NULL,
  `cep` varchar(50) DEFAULT NULL,
  `municipio` varchar(255) DEFAULT NULL,
  `placa` varchar(50) DEFAULT NULL,
  `marcaModelo` varchar(255) DEFAULT NULL,
  `cor` varchar(50) DEFAULT NULL,
  `especie` varchar(50) DEFAULT NULL,
  `categoria` varchar(50) DEFAULT NULL,
  `ano` varchar(50) DEFAULT NULL,
  `autoInfracao` varchar(255) DEFAULT NULL,
  `dataInfracao` date DEFAULT NULL,
  `horaInfracao` time DEFAULT NULL,
  `localInfracao` varchar(255) DEFAULT NULL,
  `enquadramento` varchar(255) DEFAULT NULL,
  `defesa` text DEFAULT NULL,
  `signed_document_url` varchar(255) DEFAULT NULL,
  `gmail` varchar(255) DEFAULT NULL,
  `identidade` varchar(20) DEFAULT NULL,
  `assinatura_condutor_url` varchar(255) DEFAULT NULL,
  `assinatura_proprietario_url` varchar(255) DEFAULT NULL,
  `registro_cnh_infrator` varchar(20) DEFAULT NULL,
  `tipo_contato` varchar(20) DEFAULT 'solicitacao',
  `situacao` varchar(50) DEFAULT 'Pendente',
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tokens`
--

CREATE TABLE `tokens` (
  `id` int(11) NOT NULL,
  `token` varchar(100) NOT NULL,
  `user_email` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `user_vehicles`
--

CREATE TABLE `user_vehicles` (
  `id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `total_vehicles` int(11) NOT NULL DEFAULT 1,
  `data_submissao` timestamp NULL DEFAULT current_timestamp(),
  `formulario_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `data_registro` timestamp NULL DEFAULT current_timestamp(),
  `avatar_url` varchar(255) DEFAULT NULL,
  `is_admin` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios_pendentes`
--

CREATE TABLE `usuarios_pendentes` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `data_registro` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `vehicles_incidents`
--

CREATE TABLE `vehicles_incidents` (
  `id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `index_vehicle` int(11) NOT NULL,
  `damage_system` varchar(3) NOT NULL,
  `load_damage` varchar(3) NOT NULL,
  `nota_fiscal` varchar(255) DEFAULT NULL,
  `tipo_mercadoria` varchar(255) DEFAULT NULL,
  `valor_total` decimal(10,2) DEFAULT NULL,
  `estimativa_danos` decimal(10,2) DEFAULT NULL,
  `has_insurance` varchar(3) DEFAULT NULL,
  `seguradora` varchar(255) DEFAULT NULL,
  `damaged_parts` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `vehicles_involved`
--

CREATE TABLE `vehicles_involved` (
  `id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `placa` varchar(20) DEFAULT NULL,
  `renavam` varchar(50) DEFAULT NULL,
  `damage_system` tinyint(1) DEFAULT 0,
  `dianteira_direita` tinyint(1) DEFAULT 0,
  `dianteira_esquerda` tinyint(1) DEFAULT 0,
  `lateral_direita` tinyint(1) DEFAULT 0,
  `lateral_esquerda` tinyint(1) DEFAULT 0,
  `traseira_direita` tinyint(1) DEFAULT 0,
  `traseira_esquerda` tinyint(1) DEFAULT 0,
  `load_damage` tinyint(1) DEFAULT 0,
  `nota_fiscal` varchar(100) DEFAULT NULL,
  `tipo_mercadoria` varchar(255) DEFAULT NULL,
  `valor_total` decimal(10,2) DEFAULT NULL,
  `estimativa_danos` decimal(10,2) DEFAULT NULL,
  `has_insurance` tinyint(1) DEFAULT 0,
  `seguradora` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `vehicle_damages`
--

CREATE TABLE `vehicle_damages` (
  `id` int(11) NOT NULL,
  `user_vehicles_id` int(11) NOT NULL,
  `vehicle_index` int(11) NOT NULL,
  `dianteira_direita` tinyint(1) DEFAULT 0,
  `dianteira_esquerda` tinyint(1) DEFAULT 0,
  `lateral_direita` tinyint(1) DEFAULT 0,
  `lateral_esquerda` tinyint(1) DEFAULT 0,
  `traseira_direita` tinyint(1) DEFAULT 0,
  `traseira_esquerda` tinyint(1) DEFAULT 0,
  `has_load_damage` tinyint(1) DEFAULT 0,
  `nota_fiscal` varchar(255) DEFAULT NULL,
  `tipo_mercadoria` varchar(255) DEFAULT NULL,
  `valor_total` decimal(10,2) DEFAULT NULL,
  `estimativa_danos` decimal(10,2) DEFAULT NULL,
  `has_insurance` tinyint(1) DEFAULT 0,
  `seguradora` varchar(255) DEFAULT NULL,
  `data_submissao` timestamp NULL DEFAULT current_timestamp(),
  `formulario_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `veiculos_dat`
--

CREATE TABLE `veiculos_dat` (
  `id` int(11) NOT NULL,
  `formulario_id` int(11) NOT NULL,
  `indice_veiculo` int(11) NOT NULL DEFAULT 1,
  `placa` varchar(10) DEFAULT NULL,
  `renavam` varchar(50) DEFAULT NULL,
  `tipo_veiculo` varchar(255) DEFAULT NULL,
  `chassi` varchar(20) DEFAULT NULL,
  `uf_veiculo` varchar(2) DEFAULT NULL,
  `cor_veiculo` varchar(50) DEFAULT NULL,
  `marca_modelo` varchar(255) DEFAULT NULL,
  `ano_modelo` int(11) DEFAULT NULL,
  `ano_fabricacao` int(11) DEFAULT NULL,
  `dianteira_direita` tinyint(1) DEFAULT 0,
  `dianteira_esquerda` tinyint(1) DEFAULT 0,
  `lateral_direita` tinyint(1) DEFAULT 0,
  `lateral_esquerda` tinyint(1) DEFAULT 0,
  `traseira_direita` tinyint(1) DEFAULT 0,
  `traseira_esquerda` tinyint(1) DEFAULT 0,
  `danos_carga` tinyint(1) DEFAULT 0,
  `nota_fiscal` varchar(255) DEFAULT NULL,
  `tipo_mercadoria` varchar(255) DEFAULT NULL,
  `valor_total` decimal(10,2) DEFAULT NULL,
  `estimativa_danos` decimal(10,2) DEFAULT NULL,
  `tem_seguro` tinyint(1) DEFAULT 0,
  `seguradora` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `veiculos_registro`
--

CREATE TABLE `veiculos_registro` (
  `id` int(11) NOT NULL,
  `formulario_id` int(11) NOT NULL,
  `indice_veiculo` int(11) NOT NULL DEFAULT 1,
  `placa` varchar(10) DEFAULT NULL,
  `marca` varchar(50) DEFAULT NULL,
  `modelo` varchar(50) DEFAULT NULL,
  `ano` varchar(4) DEFAULT NULL,
  `cor` varchar(30) DEFAULT NULL,
  `chassi` varchar(50) DEFAULT NULL,
  `renavam` varchar(50) DEFAULT NULL,
  `proprietario` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `danos_veiculo`
--
ALTER TABLE `danos_veiculo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `veiculo_id` (`veiculo_id`);

--
-- Índices de tabela `DAT1`
--
ALTER TABLE `DAT1`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD UNIQUE KEY `token_2` (`token`),
  ADD KEY `DAT1_fk_formulario` (`formulario_id`);

--
-- Índices de tabela `DAT2`
--
ALTER TABLE `DAT2`
  ADD KEY `DAT2_fk_formulario` (`formulario_id`);

--
-- Índices de tabela `DAT4`
--
ALTER TABLE `DAT4`
  ADD PRIMARY KEY (`id`),
  ADD KEY `DAT4_fk_formulario` (`formulario_id`);

--
-- Índices de tabela `formularios_dat_central`
--
ALTER TABLE `formularios_dat_central`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`);

--
-- Índices de tabela `leituras_registros`
--
ALTER TABLE `leituras_registros`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_leitura` (`usuario_id`,`tabela_origem`,`registro_id`),
  ADD KEY `idx_tabela_registro` (`tabela_origem`,`registro_id`);

--
-- Índices de tabela `noticias`
--
ALTER TABLE `noticias`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `notificacoes`
--
ALTER TABLE `notificacoes`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `Parecer`
--
ALTER TABLE `Parecer`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `sac`
--
ALTER TABLE `sac`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `solicitacao_cartao`
--
ALTER TABLE `solicitacao_cartao`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `solicitacao_demutran`
--
ALTER TABLE `solicitacao_demutran`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `solicitacoes_demutran`
--
ALTER TABLE `solicitacoes_demutran`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `tokens`
--
ALTER TABLE `tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`);

--
-- Índices de tabela `user_vehicles`
--
ALTER TABLE `user_vehicles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `formulario_id` (`formulario_id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Índices de tabela `usuarios_pendentes`
--
ALTER TABLE `usuarios_pendentes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Índices de tabela `vehicles_incidents`
--
ALTER TABLE `vehicles_incidents`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `vehicles_involved`
--
ALTER TABLE `vehicles_involved`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_vehicles_dat1` (`token`);

--
-- Índices de tabela `vehicle_damages`
--
ALTER TABLE `vehicle_damages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_vehicle` (`user_vehicles_id`,`vehicle_index`),
  ADD KEY `formulario_id` (`formulario_id`);

--
-- Índices de tabela `veiculos_dat`
--
ALTER TABLE `veiculos_dat`
  ADD PRIMARY KEY (`id`),
  ADD KEY `formulario_id` (`formulario_id`);

--
-- Índices de tabela `veiculos_registro`
--
ALTER TABLE `veiculos_registro`
  ADD PRIMARY KEY (`id`),
  ADD KEY `formulario_id` (`formulario_id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `danos_veiculo`
--
ALTER TABLE `danos_veiculo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `DAT1`
--
ALTER TABLE `DAT1`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `DAT4`
--
ALTER TABLE `DAT4`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `formularios_dat_central`
--
ALTER TABLE `formularios_dat_central`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `leituras_registros`
--
ALTER TABLE `leituras_registros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `noticias`
--
ALTER TABLE `noticias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `notificacoes`
--
ALTER TABLE `notificacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `Parecer`
--
ALTER TABLE `Parecer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `sac`
--
ALTER TABLE `sac`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `solicitacao_cartao`
--
ALTER TABLE `solicitacao_cartao`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `solicitacao_demutran`
--
ALTER TABLE `solicitacao_demutran`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `solicitacoes_demutran`
--
ALTER TABLE `solicitacoes_demutran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tokens`
--
ALTER TABLE `tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `user_vehicles`
--
ALTER TABLE `user_vehicles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `usuarios_pendentes`
--
ALTER TABLE `usuarios_pendentes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `vehicles_incidents`
--
ALTER TABLE `vehicles_incidents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `vehicles_involved`
--
ALTER TABLE `vehicles_involved`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `vehicle_damages`
--
ALTER TABLE `vehicle_damages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `veiculos_dat`
--
ALTER TABLE `veiculos_dat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `veiculos_registro`
--
ALTER TABLE `veiculos_registro`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `danos_veiculo`
--
ALTER TABLE `danos_veiculo`
  ADD CONSTRAINT `danos_veiculo_ibfk_1` FOREIGN KEY (`veiculo_id`) REFERENCES `veiculos_registro` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `DAT1`
--
ALTER TABLE `DAT1`
  ADD CONSTRAINT `DAT1_fk_formulario` FOREIGN KEY (`formulario_id`) REFERENCES `formularios_dat_central` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `DAT2`
--
ALTER TABLE `DAT2`
  ADD CONSTRAINT `DAT2_fk_formulario` FOREIGN KEY (`formulario_id`) REFERENCES `formularios_dat_central` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `DAT4`
--
ALTER TABLE `DAT4`
  ADD CONSTRAINT `DAT4_fk_formulario` FOREIGN KEY (`formulario_id`) REFERENCES `formularios_dat_central` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `leituras_registros`
--
ALTER TABLE `leituras_registros`
  ADD CONSTRAINT `leituras_registros_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `user_vehicles`
--
ALTER TABLE `user_vehicles`
  ADD CONSTRAINT `user_vehicles_ibfk_1` FOREIGN KEY (`formulario_id`) REFERENCES `formularios_dat_central` (`id`);

--
-- Restrições para tabelas `vehicles_involved`
--
ALTER TABLE `vehicles_involved`
  ADD CONSTRAINT `fk_vehicles_dat1` FOREIGN KEY (`token`) REFERENCES `DAT1` (`token`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `vehicle_damages`
--
ALTER TABLE `vehicle_damages`
  ADD CONSTRAINT `vehicle_damages_ibfk_1` FOREIGN KEY (`user_vehicles_id`) REFERENCES `user_vehicles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vehicle_damages_ibfk_2` FOREIGN KEY (`formulario_id`) REFERENCES `formularios_dat_central` (`id`);

--
-- Restrições para tabelas `veiculos_dat`
--
ALTER TABLE `veiculos_dat`
  ADD CONSTRAINT `fk_formulario_veiculos` FOREIGN KEY (`formulario_id`) REFERENCES `formularios_dat_central` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 09/12/2024 às 23:11
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
  `data_submissao` timestamp NULL DEFAULT current_timestamp()
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
  `id` int(11) NOT NULL
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
  `is_read` TINYINT(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `DAT4`
--

INSERT INTO `DAT4` (`id`, `token`, `patrimonio_text`, `meio_ambiente_text`, `informacoes_complementares_text`, `data_submissao`, `situacao`) VALUES
(1, 'não informado', 'teste', 'teste', 'teste', '2024-10-29 10:48:34', 'Pendente'),
(2, 'não informado', 'teste', 'teste', 'teste', '2024-10-29 10:48:34', 'Pendente'),
(3, '9537e5c3714ba482ce2b040ad96bbc54', 'não informado', 'não informado', 'não informado', '2024-10-29 10:48:34', 'Pendente'),
(4, '19263a165465ed1c31d89972d06729d2', 'não informado', 'não informado', 'não informado', '2024-10-29 10:48:34', 'Pendente'),
(5, '19263a165465ed1c31d89972d06729d2', 'teste', 'teste', 'teste', '2024-10-29 10:48:34', 'Pendente'),
(6, '19263a165465ed1c31d89972d06729d2', 'não informado', 'não informado', 'não informado', '2024-10-29 10:48:34', 'Pendente'),
(7, '19263a165465ed1c31d89972d06729d2', 'não informado', 'não informado', 'não informado', '2024-10-29 10:48:34', 'Pendente'),
(8, '19263a165465ed1c31d89972d06729d2', 'não informado', 'não informado', 'não informado', '2024-10-29 10:48:34', 'Pendente'),
(9, '19263a165465ed1c31d89972d06729d2', 'não informado', 'não informado', 'não informado', '2024-10-29 10:48:34', 'Pendente'),
(10, 'b311de0004acd8b38e82a21f46d901a8', 's', 's', 's', '2024-10-29 10:48:34', 'Pendente'),
(11, '2729c83f5ea4066ad22e2333c9108380', 'não informado', 'não informado', 'não informado', '2024-10-29 10:48:34', 'Pendente'),
(12, '9ad31eb0395133c28804ccc95dcf837e', 'não informado', 'não informado', 'não informado', '2024-10-29 12:36:01', 'Pendente'),
(13, '2d0d4f080749e44e033b13bb09c178e3', 'não informado', 'não informado', 'não informado', '2024-11-28 10:50:20', 'Pendente'),
(14, '0afc9b8e862c3fcea25e1aef2bb46c7a', 'Vel eum ullam deserunt deleniti possimus veritatis repellendus Sequi asperiores et in accusamus fuga Odit enim repudiandae', 'Veniam excepturi nesciunt nesciunt lorem do rem quaerat omnis', 'não informado', '2024-12-04 23:30:49', 'Pendente'),
(15, 'ac7bfd82a881157e118ceea66703fbdb', 'não informado', 'não informado', 'Reprehenderit consectetur sunt asperiores aut aut adipisicing', '2024-12-09 03:19:19', 'Pendente'),
(17, 'ac7bfd82a881157e118ceea66703fbdb', 'Vel consectetur dignissimos velit non quis magni amet deleniti ullamco', 'Distinctio Perferendis ad ipsam dolore id dolores aut aliqua Aute consequat Molestiae ut inventore et ut', 'não informado', '2024-12-09 03:26:59', 'Pendente'),
(18, 'ac7bfd82a881157e118ceea66703fbdb', 'Architecto hic qui velit pariatur Enim expedita adipisicing animi do delectus maxime sequi qui possimus est', 'Doloribus quia pariatur Doloribus anim laboris consectetur natus aliquid voluptates eligendi necessitatibus tempora suscipit aspernatur et architecto', 'Hic quos sint eveniet odio distinctio Commodo ut quas consequatur Quibusdam voluptates totam asperiores ut cillum inventore nisi consequatur eos', '2024-12-09 03:30:56', 'Pendente'),
(19, 'ac7bfd82a881157e118ceea66703fbdb', 'não informado', 'não informado', 'não informado', '2024-12-09 03:32:48', 'Pendente'),
(20, 'ac7bfd82a881157e118ceea66703fbdb', 'não informado', 'não informado', 'não informado', '2024-12-09 03:39:48', 'Pendente');

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

-

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
  `is_read` TINYINT(1) DEFAULT 0
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
  `is_read` TINYINT(1) DEFAULT 0
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
  `is_read` TINYINT(1) DEFAULT 0
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
  `is_read` TINYINT(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `solicitacoes_demutran`
--
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
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `data_registro` timestamp NULL DEFAULT current_timestamp(),
  `avatar_url` varchar(255) DEFAULT NULL
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
-- Estrutura para tabela `vehicles`
--

CREATE TABLE `vehicles` (
  `id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `damage_system` tinyint(1) NOT NULL,
  `damaged_parts` text DEFAULT NULL,
  `load_damage` tinyint(1) NOT NULL,
  `nota_fiscal` varchar(255) DEFAULT NULL,
  `tipo_mercadoria` varchar(255) DEFAULT NULL,
  `valor_total` decimal(10,2) DEFAULT NULL,
  `estimativa_danos` decimal(10,2) DEFAULT NULL,
  `has_insurance` tinyint(1) DEFAULT NULL,
  `seguradora` varchar(255) DEFAULT NULL,
  `data_submissao` timestamp NULL DEFAULT current_timestamp()
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
-- Estrutura para tabela `leituras_registros`
--

CREATE TABLE `leituras_registros` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `tabela_origem` varchar(50) NOT NULL,
  `registro_id` int(11) NOT NULL,
  `data_leitura` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_leitura` (`usuario_id`, `tabela_origem`, `registro_id`),
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
  KEY `idx_tabela_registro` (`tabela_origem`, `registro_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `DAT1`
--
ALTER TABLE `DAT1`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `DAT2`
--
ALTER TABLE `DAT2`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `DAT4`
--
ALTER TABLE `DAT4`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `Formularios_DAT3`
--
ALTER TABLE `Formularios_DAT3`
  ADD PRIMARY KEY (`id`);

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
-- Índices de tabela `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `vehicles_incidents`
--
ALTER TABLE `vehicles_incidents`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `DAT1`
--
ALTER TABLE `DAT1`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de tabela `DAT2`
--
ALTER TABLE `DAT2`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de tabela `DAT4`
--
ALTER TABLE `DAT4`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de tabela `Formularios_DAT3`
--
ALTER TABLE `Formularios_DAT3`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `noticias`
--
ALTER TABLE `noticias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de tabela `notificacoes`
--
ALTER TABLE `notificacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=156;

--
-- AUTO_INCREMENT de tabela `Parecer`
--
ALTER TABLE `Parecer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT de tabela `sac`
--
ALTER TABLE `sac`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `solicitacao_cartao`
--
ALTER TABLE `solicitacao_cartao`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=239;

--
-- AUTO_INCREMENT de tabela `solicitacao_demutran`
--
ALTER TABLE `solicitacao_demutran`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `solicitacoes_demutran`
--
ALTER TABLE `solicitacoes_demutran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=133;

--
-- AUTO_INCREMENT de tabela `tokens`
--
ALTER TABLE `tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `usuarios_pendentes`
--
ALTER TABLE `usuarios_pendentes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de tabela `vehicles_incidents`
--
ALTER TABLE `vehicles_incidents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

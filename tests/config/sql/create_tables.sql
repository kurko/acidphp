--
-- Banco de Dados: `acid_tests`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `admins`
--

CREATE TABLE IF NOT EXISTS `admins` (
  `id` int(11) NOT NULL auto_increment,
  `nome` varchar(120) collate utf8_unicode_ci default NULL,
  `login` varchar(40) collate utf8_unicode_ci default NULL,
  `senha` varchar(40) collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Extraindo dados da tabela `admins`
--

INSERT INTO `admins` (`nome`, `login`, `senha`)
VALUES
('Alexandre de Oliveira', 'kurko', '123');

-- --------------------------------------------------------

--
-- Estrutura da tabela `textos`
--

CREATE TABLE IF NOT EXISTS `textos` (
  `id` int(11) NOT NULL auto_increment,
  `titulo` text collate utf8_unicode_ci COMMENT 'O título do texto que será mostrado para humanos.',
  `titulo_encoded` text collate utf8_unicode_ci COMMENT 'Título tratado para ser mostrado na barra de endereços.',
  `subtitulo` text collate utf8_unicode_ci,
  `resumo` text collate utf8_unicode_ci,
  `texto` text collate utf8_unicode_ci,
  `visitantes` int(11) default '0',
  `restrito` varchar(120) collate utf8_unicode_ci default NULL,
  `publico` varchar(120) collate utf8_unicode_ci default NULL,
  `aprovado` int(11) default NULL,
  `created_on` datetime default NULL,
  `updated_on` datetime default NULL,
  `autor` int(11) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=36 ;

--
-- Extraindo dados da tabela `textos`
--

INSERT INTO `textos`
(`titulo`, `titulo_encoded`, `texto`, created_on, updated_on, `autor`)
VALUES
( 'Notícia2', 'noticia2', '<p>aergarg</p>', '2010-02-12 21:13:03', '2010-03-11 21:13:03', 1),
( 'Notícia de teste123456789', 'noticia_de_teste123456789', '<p>aergarg2</p>', '2010-02-12 19:30:42', '2010-03-11 21:13:03', 1);
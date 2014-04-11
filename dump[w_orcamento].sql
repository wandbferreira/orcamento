-- phpMyAdmin SQL Dump
-- version 4.0.4
-- http://www.phpmyadmin.net
--
-- Máquina: localhost
-- Data de Criação: 11-Abr-2014 às 02:58
-- Versão do servidor: 5.6.12-log
-- versão do PHP: 5.4.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de Dados: `w_orcamento_db`
--
CREATE DATABASE IF NOT EXISTS `w_orcamento_db` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `w_orcamento_db`;

-- --------------------------------------------------------

--
-- Estrutura da tabela `pagamentos`
--

CREATE TABLE IF NOT EXISTS `pagamentos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tipo` smallint(5) unsigned DEFAULT NULL,
  `nome` varchar(45) DEFAULT NULL,
  `calculo` smallint(5) unsigned DEFAULT NULL,
  `valor` double(7,2) NOT NULL DEFAULT '0.00',
  `data` date DEFAULT NULL,
  `transferencia` smallint(6) DEFAULT '0' COMMENT '1- indica que é saque ou deposito',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Extraindo dados da tabela `pagamentos`
--

INSERT INTO `pagamentos` (`id`, `tipo`, `nome`, `calculo`, `valor`, `data`, `transferencia`) VALUES
(2, 0, 'Positive Gold', 1, 45.90, '2014-04-11', 0);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

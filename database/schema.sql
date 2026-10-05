-- Schema do Diário de Cultivo
-- Importe este arquivo no phpMyAdmin (aba Importar) ou via: mysql -u root < database/schema.sql

CREATE DATABASE IF NOT EXISTS `diario_cultivo` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `diario_cultivo`;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `strain` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `caracteristicas` text DEFAULT NULL,
  `floracao_semanas` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `planta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `strain_id` int(11) DEFAULT NULL,
  `tipo_cultivo` text NOT NULL,
  `germinacao` date DEFAULT NULL,
  `plantinha` date DEFAULT NULL,
  `vegetativo` date DEFAULT NULL,
  `floracao` date DEFAULT NULL,
  `colheita` date DEFAULT NULL,
  `rendimento` float DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `strain_id` (`strain_id`),
  CONSTRAINT `planta_ibfk_1` FOREIGN KEY (`strain_id`) REFERENCES `strain` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `manejo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `planta_id` int(11) DEFAULT NULL,
  `data` date DEFAULT NULL,
  `tipo` varchar(255) DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `planta_id` (`planta_id`),
  CONSTRAINT `manejo_ibfk_1` FOREIGN KEY (`planta_id`) REFERENCES `planta` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `fotos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `planta_id` int(11) DEFAULT NULL,
  `data` date DEFAULT NULL,
  `imagem` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `planta_id` (`planta_id`),
  CONSTRAINT `fotos_ibfk_1` FOREIGN KEY (`planta_id`) REFERENCES `planta` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

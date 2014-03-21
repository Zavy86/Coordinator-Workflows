-- phpMyAdmin SQL Dump
-- version 3.4.11.1deb2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generato il: Mar 21, 2014 alle 17:03
-- Versione del server: 5.5.35
-- Versione PHP: 5.4.4-14+deb7u8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `intranet`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `workflows_actions`
--

CREATE TABLE IF NOT EXISTS `workflows_actions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `idFlow` int(11) unsigned NOT NULL,
  `typology` tinyint(1) unsigned NOT NULL COMMENT '1 ticket, 2 external ticket, 3 authorization',
  `idAction` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'id of the action required',
  `conditioned` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 no, 1 yes',
  `idField` int(11) unsigned DEFAULT NULL COMMENT 'id of the condition field',
  `value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'value of the condition field',
  `subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `note` text COLLATE utf8_unicode_ci,
  `idGroup` int(11) unsigned NOT NULL,
  `idAssigned` int(11) unsigned DEFAULT NULL,
  `mail` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `difficulty` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1 low, 2 medium, 3 high',
  `priority` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1 highest, 2 high, 3 medium, 4 low, 5 lowest',
  `slaAssignment` int(5) unsigned NOT NULL DEFAULT '0',
  `slaClosure` int(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idWorkflow` (`idFlow`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Struttura della tabella `workflows_categories`
--

CREATE TABLE IF NOT EXISTS `workflows_categories` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `idCategory` int(11) unsigned NOT NULL DEFAULT '0',
  `name` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `idGroup` int(11) unsigned NOT NULL DEFAULT '0',
  `addDate` datetime NOT NULL,
  `addIdAccount` int(11) unsigned NOT NULL,
  `updDate` datetime DEFAULT NULL,
  `updIdAccount` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idCategory` (`idCategory`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=14 ;

-- --------------------------------------------------------

--
-- Struttura della tabella `workflows_fields`
--

CREATE TABLE IF NOT EXISTS `workflows_fields` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `idFlow` int(11) unsigned NOT NULL DEFAULT '0',
  `typology` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `class` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `placeholder` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `options` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `required` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `position` int(2) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Struttura della tabella `workflows_flows`
--

CREATE TABLE IF NOT EXISTS `workflows_flows` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `idCategory` int(11) unsigned NOT NULL,
  `typology` tinyint(1) unsigned NOT NULL COMMENT '1 request, 2 incident',
  `pinned` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `advice` text COLLATE utf8_unicode_ci,
  `priority` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1 highest, 2 high, 3 medium, 4 low, 5 lowest',
  `sla` int(5) unsigned NOT NULL DEFAULT '0',
  `procedure` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `addDate` datetime NOT NULL,
  `addIdAccount` int(11) unsigned NOT NULL,
  `updDate` datetime DEFAULT NULL,
  `updIdAccount` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idCategory` (`idCategory`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Struttura della tabella `workflows_tickets`
--

CREATE TABLE IF NOT EXISTS `workflows_tickets` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `idWorkflow` int(11) unsigned NOT NULL,
  `idCategory` int(11) unsigned NOT NULL DEFAULT '0',
  `idTicket` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'id of the ticket required',
  `idAction` int(11) unsigned DEFAULT '0' COMMENT 'id of the action required',
  `typology` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1 ticket, 2 external ticket, 3 authorization',
  `hash` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mail` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `note` text COLLATE utf8_unicode_ci,
  `idGroup` int(11) unsigned NOT NULL,
  `idAssigned` int(11) unsigned DEFAULT NULL,
  `difficulty` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1 low, 2 medium, 3 high',
  `priority` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1 highest, 2 high, 3 medium, 4 low, 5 lowest',
  `slaAssignment` int(5) unsigned NOT NULL DEFAULT '0',
  `slaClosure` int(5) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1 open, 2 assigned, 3 standby, 4 closed, 5 locked',
  `solved` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 unexecuted, 1 executed, 2 unnecessary',
  `approved` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 no, 1 yes',
  `hostname` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `addDate` datetime NOT NULL,
  `addIdAccount` int(11) unsigned NOT NULL,
  `updDate` datetime DEFAULT NULL,
  `assDate` datetime DEFAULT NULL,
  `endDate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Struttura della tabella `workflows_workflows`
--

CREATE TABLE IF NOT EXISTS `workflows_workflows` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `idCategory` int(11) unsigned NOT NULL,
  `idFlow` int(11) unsigned NOT NULL DEFAULT '0',
  `typology` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1 request, 2 incident',
  `subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `note` text COLLATE utf8_unicode_ci,
  `priority` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1 highest, 2 high, 3 medium, 4 low, 5 lowest',
  `sla` int(5) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1 open, 2 assigned, 3 standby, 4 closed',
  `addDate` datetime NOT NULL,
  `addIdAccount` int(11) unsigned NOT NULL,
  `endDate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idCategory` (`idCategory`),
  KEY `idWorkflow` (`idFlow`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4 ;

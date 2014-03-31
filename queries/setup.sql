--
-- Setup module workflows
--

-- --------------------------------------------------------

--
-- Struttura della tabella `workflows_actions`
--

CREATE TABLE IF NOT EXISTS `workflows_actions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `idFlow` int(11) unsigned NOT NULL,
  `typology` tinyint(1) unsigned NOT NULL COMMENT '1 ticket, 2 external ticket, 3 authorization',
  `requiredAction` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'id of the action required',
  `conditionedField` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'id of the condition field',
  `conditionedValue` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'value of the condition field',
  `subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `note` text COLLATE utf8_unicode_ci,
  `idGroup` int(11) unsigned NOT NULL DEFAULT '0',
  `idAssigned` int(11) unsigned NOT NULL DEFAULT '0',
  `mail` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `difficulty` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1 low, 2 medium, 3 high',
  `priority` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1 highest, 2 high, 3 medium, 4 low, 5 lowest',
  `slaAssignment` int(5) unsigned NOT NULL DEFAULT '0',
  `slaClosure` int(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idWorkflow` (`idFlow`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `workflows_attachments`
--

CREATE TABLE IF NOT EXISTS `workflows_attachments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `size` int(11) NOT NULL,
  `hash` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `file` longblob NOT NULL,
  `label` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tags` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'comma separated tag',
  `txtContent` text COLLATE utf8_unicode_ci COMMENT 'Textual file content for search queries',
  `addDate` datetime NOT NULL,
  `addIdAccount` int(11) NOT NULL DEFAULT '0',
  `updDate` datetime DEFAULT NULL,
  `updIdAccount` int(11) unsigned DEFAULT NULL,
  `del` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `hash` (`hash`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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

--
-- Dump dei dati per la tabella `workflows_categories`
--

INSERT IGNORE INTO `workflows_categories` (`id`, `idCategory`, `name`, `description`, `idGroup`, `addDate`, `addIdAccount`, `updDate`, `updIdAccount`) VALUES
(1, 0, 'Support', 'Reporting problems and support requests', 1, now(), 1, NULL, NULL),
(2, 1, 'Hardware', 'Support for hardware issues', 1, now(), 1, NULL, NULL),
(3, 1, 'Software', 'Support for software issues', 1, now(), 1, NULL, NULL);

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
  `options_method` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `options_values` text COLLATE utf8_unicode_ci,
  `options_query` text COLLATE utf8_unicode_ci,
  `required` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `position` int(2) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `workflows_tickets`
--

CREATE TABLE IF NOT EXISTS `workflows_tickets` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `idWorkflow` int(11) unsigned NOT NULL,
  `idCategory` int(11) unsigned NOT NULL DEFAULT '0',
  `requiredTicket` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'id of the required ticket',
  `requiredAction` int(11) unsigned DEFAULT '0' COMMENT 'id of the required flow action',
  `typology` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1 ticket, 2 external ticket, 3 authorization',
  `hash` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mail` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `idGroup` int(11) unsigned NOT NULL DEFAULT '0',
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `workflows_tickets_notes`
--

CREATE TABLE IF NOT EXISTS `workflows_tickets_notes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `idTicket` int(11) unsigned NOT NULL,
  `note` text COLLATE utf8_unicode_ci NOT NULL,
  `addDate` datetime DEFAULT NULL,
  `addIdAccount` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idFeasibility` (`idTicket`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Dati della tabelle `settings_permissions`
--

INSERT IGNORE INTO `settings_permissions` (`id`,`module`,`action`,`description`,`locked`) VALUES
(NULL,'workflows','workflows_view','View workflows','0'),
(NULL,'workflows','workflows_add','Open a workflow','0'),
(NULL,'workflows','workflows_','Process a workflow','0'),
(NULL,'workflows','workflows_admin','Administer workflows and categories','0');

-- --------------------------------------------------------
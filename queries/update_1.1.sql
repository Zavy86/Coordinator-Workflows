--
-- Update module workflows
--
-- From 1.1 to 1.2
--

-- --------------------------------------------------------

--
-- Alter table `workflows_tickets`
--

ALTER TABLE `workflows_tickets` ADD `urged` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0 false, 1 true' AFTER `approved`

-- --------------------------------------------------------
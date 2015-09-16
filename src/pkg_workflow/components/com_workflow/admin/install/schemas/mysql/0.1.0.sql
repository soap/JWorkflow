CREATE TABLE IF NOT EXISTS `#__wf_transition_permissions` (
  `transition_id` int(11) NOT NULL,
  `permission_context` varchar(50) NOT NULL,
  `item_id` int(11) NOT NULL,
  UNIQUE KEY `transition_id_unique` (`transition_id`,`permission_context`,`item_id`),
  KEY `transition_id_idx` (`transition_id`,`permission_context`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='transition permission allowed for group, user or role';
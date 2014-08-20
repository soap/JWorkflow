
CREATE TABLE IF NOT EXISTS `#__wf_waiting_items` (
  `id` int(11) NOT NULL,
  `item_type` varchar(200) NOT NULL COMMENT 'in com_xxx.entity format',
  `item_id` int(11) NOT NULL COMMENT 'PK of working item',
  `role_type` varchar(200) NOT NULL DEFAULT 'joomla.user' COMMENT 'joomla.user, joomla.group, workflow.role',
  `role_id` int(11) NOT NULL,
  `params` text NOT NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL,
  `checked_out` int(11) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- <?php defined('_JEXEC') or die ?>;
-- Generation Time: Aug 02, 2013 at 09:23 AM
-- Server version: 5.5.24-log
-- PHP Version: 5.2.9-2

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- --------------------------------------------------------

--
-- Table structure for table `#__wf_guards`
--
-- Creation: Jul 30, 2013 at 07:32 AM
-- Last update: Aug 01, 2013 at 04:13 PM
--

CREATE TABLE IF NOT EXISTS `#__wf_triggers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin_id` int(11) NOT NULL,
  `transition_id` int(11) NOT NULL DEFAULT '0',
  `title` varchar(250) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `trigger_config` text,
  `published` tinyint(4) NOT NULL DEFAULT '1',
  `created_by` int(11) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `checked_out` int(11) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `note` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `workflow_transition_id` (`transition_id`),
  KEY `namespace` (`namespace`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Stores trigger instance (w config) for each transition' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__wf_plugins`
--
-- Creation: Jul 30, 2013 at 08:25 AM
-- Last update: Jul 30, 2013 at 08:42 AM
--

CREATE TABLE IF NOT EXISTS `#__wf_plugins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `namespace` varchar(200) NOT NULL,
  `alias` varchar(200) NOT NULL,
  `group` varchar(200) NOT NULL COMMENT 'folder in component plugins folder',
  `name` varchar(200) NOT NULL,
  `checked_out` int(11) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `published` tinyint(3) NOT NULL DEFAULT '1',
  `ordering` int(11) NOT NULL,
  `access` tinyint(3) NOT NULL DEFAULT '0',
  `language` varchar(7) NOT NULL DEFAULT '*',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='stores component plugin, e.g. transition guard plugins' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__wf_states`
--
-- Creation: Aug 01, 2013 at 02:50 PM
-- Last update: Aug 01, 2013 at 03:13 PM
--

CREATE TABLE IF NOT EXISTS `#__wf_states` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `workflow_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `alias` varchar(100) NOT NULL,
  `start_state` tinyint(2) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `checked_out` int(11) NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `access` int(11) NOT NULL DEFAULT '0',
  `language` varchar(100) NOT NULL DEFAULT '*',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__wf_state_transitions`
--
-- Creation: Jul 19, 2013 at 10:02 AM
-- Last update: Aug 01, 2013 at 08:49 AM
--

CREATE TABLE IF NOT EXISTS `#__wf_state_transitions` (
  `state_id` int(11) NOT NULL COMMENT 'from state',
  `transition_id` int(11) NOT NULL,
  UNIQUE KEY `state_id` (`state_id`,`transition_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__wf_transitions`
--
-- Creation: Jul 08, 2013 at 06:45 AM
-- Last update: Aug 01, 2013 at 04:13 PM
--

CREATE TABLE IF NOT EXISTS `#__wf_transitions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `workflow_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `alias` varchar(100) NOT NULL,
  `target_state_id` int(11) NOT NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) NOT NULL DEFAULT '0',
  `checked_out` int(11) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `access` int(11) NOT NULL DEFAULT '0',
  `language` varchar(100) NOT NULL DEFAULT '*',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__wf_transition_logs`
--
-- Creation: Jul 19, 2013 at 09:09 AM
-- Last update: Jul 20, 2013 at 06:27 PM
--

CREATE TABLE IF NOT EXISTS `#__wf_transition_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `context` varchar(100) NOT NULL,
  `item_id` int(11) NOT NULL,
  `title` varchar(200) DEFAULT NULL,
  `comment` text NOT NULL,
  `from_state_id` int(11) NOT NULL,
  `transition_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) NOT NULL DEFAULT '0',
  `checked_out` int(11) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__wf_typemaps`
--
-- Creation: Aug 01, 2013 at 02:28 PM
-- Last update: Aug 01, 2013 at 02:43 PM
--

CREATE TABLE IF NOT EXISTS `#__wf_typemaps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `context` varchar(50) NOT NULL,
  `workflow_id` int(11) NOT NULL,
  `params` text NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) NOT NULL DEFAULT '0',
  `checked_out` int(11) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `published` tinyint(3) NOT NULL DEFAULT '1',
  `note` varchar(200) DEFAULT NULL,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `access` int(11) NOT NULL DEFAULT '0',
  `language` varchar(100) NOT NULL DEFAULT '*',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__wf_workflows`
--
-- Creation: Jul 08, 2013 at 07:52 AM
-- Last update: Jul 08, 2013 at 05:05 PM
--

CREATE TABLE IF NOT EXISTS `#__wf_workflows` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `alias` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `category_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `created_by` int(11) NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL,
  `modified_by` int(11) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL,
  `checked_out` int(11) NOT NULL DEFAULT '0',
  `published` tinyint(4) NOT NULL DEFAULT '1',
  `ordering` int(11) NOT NULL,
  `access` int(11) NOT NULL DEFAULT '0',
  `language` varchar(50) NOT NULL DEFAULT '*',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__wf_user_roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `alias` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `method` varchar(100) NOT NULL DEFAULT 'query',
  `method_params` text NOT NULL,
  `params` text NOT NULL,
  `created_by` int(11) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `checked_out` int(11) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `published` tinyint(4) NOT NULL DEFAULT '1',
  `language` varchar(50) NOT NULL DEFAULT '*',
  `access` int(11) NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `note` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

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

CREATE TABLE IF NOT EXISTS `#__wf_instances` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `workflow_id` int(11) NOT NULL,
  `workflow_state_id` int(11) NOT NULL,
  `context` varchar(50) NOT NULL,
  `item_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `context` (`context`,`item_id`),
  KEY `workflow_id` (`workflow_id`,`workflow_state_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
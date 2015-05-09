-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Apr 24, 2015 at 02:48 PM
-- Server version: 5.6.17
-- PHP Version: 5.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `joomla3x_dev`
--

-- --------------------------------------------------------

--
-- Table structure for table `xkboa_wf_bindings`
--

CREATE TABLE IF NOT EXISTS `xkboa_wf_bindings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `context` varchar(50) NOT NULL,
  `workflow_id` int(11) NOT NULL,
  `params` text NOT NULL,
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
  PRIMARY KEY (`id`),
  KEY `context` (`context`,`workflow_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Truncate table before insert `xkboa_wf_bindings`
--

TRUNCATE TABLE `xkboa_wf_bindings`;
--
-- Dumping data for table `xkboa_wf_bindings`
--

INSERT INTO `xkboa_wf_bindings` (`id`, `context`, `workflow_id`, `params`, `created`, `created_by`, `modified`, `modified_by`, `checked_out`, `checked_out_time`, `published`, `note`, `ordering`, `access`, `language`) VALUES
(1, 'com_content.article', 1, '{"table_path":"","table_prefix":"JTable","table_name":"Content","other_mappings":""}', '2014-08-23 08:39:39', 223, '2015-04-20 13:21:34', 614, 0, '0000-00-00 00:00:00', 1, '', 1, 1, '*'),
(2, 'com_jongman.reservation', 2, '{"table_path":"components\\/com_jongman\\/tables","table_prefix":"JongmanTable","table_name":"Reservation","other_mappings":""}', '2015-04-20 13:21:21', 614, '2015-04-23 17:28:35', 614, 614, '2015-04-24 07:01:53', 1, '', 1, 1, '*');

-- --------------------------------------------------------

--
-- Table structure for table `xkboa_wf_instances`
--

CREATE TABLE IF NOT EXISTS `xkboa_wf_instances` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `workflow_id` int(11) NOT NULL,
  `workflow_state_id` int(11) NOT NULL,
  `context` varchar(50) NOT NULL,
  `item_id` int(11) NOT NULL,
  `binding_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `context` (`context`,`item_id`),
  KEY `workflow_id` (`workflow_id`,`workflow_state_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Truncate table before insert `xkboa_wf_instances`
--

TRUNCATE TABLE `xkboa_wf_instances`;
--
-- Dumping data for table `xkboa_wf_instances`
--

INSERT INTO `xkboa_wf_instances` (`id`, `workflow_id`, `workflow_state_id`, `context`, `item_id`, `binding_id`, `created`, `modified`) VALUES
(1, 1, 1, 'com_content.article', 1, 1, '2014-09-01 14:21:37', '2014-09-20 08:54:41'),
(2, 1, 1, 'com_content.article', 2, 1, '2014-08-31 20:28:40', '2014-09-02 19:11:05'),
(3, 2, 6, 'com_jongman.reservation', 12, 2, '2015-04-20 13:23:55', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `xkboa_wf_states`
--

CREATE TABLE IF NOT EXISTS `xkboa_wf_states` (
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
  PRIMARY KEY (`id`),
  KEY `workflow_id` (`workflow_id`),
  KEY `workflow_id_2` (`workflow_id`,`start_state`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;

--
-- Truncate table before insert `xkboa_wf_states`
--

TRUNCATE TABLE `xkboa_wf_states`;
--
-- Dumping data for table `xkboa_wf_states`
--

INSERT INTO `xkboa_wf_states` (`id`, `workflow_id`, `title`, `alias`, `start_state`, `created`, `created_by`, `modified`, `modified_by`, `checked_out_time`, `checked_out`, `published`, `ordering`, `access`, `language`) VALUES
(1, 1, 'Draft', 'draft', 1, '2014-08-23 05:30:14', 223, '2014-09-09 12:38:48', 223, '0000-00-00 00:00:00', 0, 1, 1, 2, '*'),
(2, 1, 'On Review', 'on-review', 0, '2014-08-23 06:26:16', 223, '2014-08-27 18:05:50', 223, '0000-00-00 00:00:00', 0, 1, 2, 1, '*'),
(3, 1, 'On Approval', 'on-approval', 0, '2014-08-23 06:26:50', 223, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 0, 1, 3, 1, '*'),
(4, 1, 'Approved', 'approved', 0, '2014-08-23 06:27:41', 223, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 0, 1, 4, 1, '*'),
(5, 1, 'Rejected', 'rejected', 0, '2014-08-23 06:27:51', 223, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 0, 1, 5, 1, '*'),
(6, 2, 'Draft', 'draft', 1, '2015-04-20 12:49:46', 614, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 0, 1, 1, 1, '*'),
(7, 2, 'On Approval', 'on-approval', 0, '2015-04-20 12:50:05', 614, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 0, 1, 2, 1, '*'),
(8, 2, 'Acknkowledged', 'acknkowledged', 0, '2015-04-20 12:50:59', 614, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 0, 1, 3, 1, '*'),
(9, 2, 'Rejected', 'rejected', 0, '2015-04-20 13:09:09', 614, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 0, 1, 4, 1, '*'),
(11, 2, 'Approved', 'approved', 0, '2015-04-20 13:12:32', 614, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 0, 1, 6, 1, '*');

-- --------------------------------------------------------

--
-- Table structure for table `xkboa_wf_state_transitions`
--

CREATE TABLE IF NOT EXISTS `xkboa_wf_state_transitions` (
  `state_id` int(11) NOT NULL COMMENT 'from state',
  `transition_id` int(11) NOT NULL,
  UNIQUE KEY `state_id` (`state_id`,`transition_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `xkboa_wf_state_transitions`
--

TRUNCATE TABLE `xkboa_wf_state_transitions`;
--
-- Dumping data for table `xkboa_wf_state_transitions`
--

INSERT INTO `xkboa_wf_state_transitions` (`state_id`, `transition_id`) VALUES
(1, 1),
(2, 2),
(2, 4),
(3, 3),
(3, 4),
(5, 5),
(6, 6),
(7, 7),
(7, 8),
(9, 9);

-- --------------------------------------------------------

--
-- Table structure for table `xkboa_wf_transitions`
--

CREATE TABLE IF NOT EXISTS `xkboa_wf_transitions` (
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
  PRIMARY KEY (`id`),
  KEY `workflow_id` (`workflow_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

--
-- Truncate table before insert `xkboa_wf_transitions`
--

TRUNCATE TABLE `xkboa_wf_transitions`;
--
-- Dumping data for table `xkboa_wf_transitions`
--

INSERT INTO `xkboa_wf_transitions` (`id`, `workflow_id`, `title`, `alias`, `target_state_id`, `created`, `created_by`, `modified`, `modified_by`, `checked_out`, `checked_out_time`, `published`, `ordering`, `access`, `language`) VALUES
(1, 1, 'Submit', 'submit', 2, '2014-08-23 06:40:12', 223, '2014-08-23 06:43:56', 223, 0, '0000-00-00 00:00:00', 1, 1, 1, '*'),
(2, 1, 'Agree', 'agree', 3, '2014-08-27 18:10:49', 223, '2014-08-27 18:11:07', 223, 0, '0000-00-00 00:00:00', 1, 2, 1, '*'),
(3, 1, 'Approve', 'approve', 4, '2014-08-27 18:12:04', 223, '2014-09-02 17:22:21', 223, 0, '0000-00-00 00:00:00', 1, 3, 1, '*'),
(4, 1, 'Reject', 'reject', 5, '2014-08-27 18:13:04', 223, '2014-08-27 18:13:21', 223, 0, '0000-00-00 00:00:00', 1, 4, 1, '*'),
(5, 1, 'To Draft', 'to-draft', 1, '2014-08-27 18:14:24', 223, '2014-08-27 18:14:37', 223, 0, '0000-00-00 00:00:00', 1, 5, 1, '*'),
(6, 2, 'Submit', 'submit', 7, '2015-04-20 13:08:41', 614, '0000-00-00 00:00:00', 0, 0, '0000-00-00 00:00:00', 1, 1, 1, '*'),
(7, 2, 'Approve', 'approve', 11, '2015-04-20 13:13:02', 614, '2015-04-20 13:16:19', 614, 0, '0000-00-00 00:00:00', 1, 2, 1, '*'),
(8, 2, 'Reject', 'reject', 9, '2015-04-20 13:13:28', 614, '2015-04-20 13:16:05', 614, 0, '0000-00-00 00:00:00', 1, 3, 1, '*'),
(9, 2, 'Re-Draft', 're-draft', 6, '2015-04-20 13:15:27', 614, '2015-04-20 13:15:46', 614, 0, '0000-00-00 00:00:00', 1, 4, 1, '*');

-- --------------------------------------------------------

--
-- Table structure for table `xkboa_wf_transition_logs`
--

CREATE TABLE IF NOT EXISTS `xkboa_wf_transition_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `context` varchar(100) NOT NULL,
  `item_id` int(11) NOT NULL,
  `title` varchar(200) DEFAULT NULL,
  `comment` text,
  `from_state_id` int(11) NOT NULL,
  `transition_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) NOT NULL DEFAULT '0',
  `checked_out` int(11) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `context` (`context`,`item_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Truncate table before insert `xkboa_wf_transition_logs`
--

TRUNCATE TABLE `xkboa_wf_transition_logs`;
-- --------------------------------------------------------

--
-- Table structure for table `xkboa_wf_triggers`
--

CREATE TABLE IF NOT EXISTS `xkboa_wf_triggers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL DEFAULT 'trigger',
  `name` varchar(200) NOT NULL,
  `namespace` varchar(200) NOT NULL,
  `folder` varchar(50) NOT NULL COMMENT 'folder under trigger based',
  `element` varchar(50) NOT NULL DEFAULT 'trigger',
  `client_id` tinyint(3) NOT NULL,
  `enabled` tinyint(2) NOT NULL DEFAULT '1',
  `protected` tinyint(2) NOT NULL DEFAULT '0',
  `manifest_cache` text NOT NULL,
  `custom_data` text NOT NULL,
  `system_data` text NOT NULL,
  `params` text NOT NULL,
  `checked_out` int(11) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `published` tinyint(3) NOT NULL DEFAULT '1',
  `ordering` int(11) NOT NULL,
  `access` tinyint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='stores workflow trigger, e.g. transition guards' AUTO_INCREMENT=8 ;

--
-- Truncate table before insert `xkboa_wf_triggers`
--

TRUNCATE TABLE `xkboa_wf_triggers`;
--
-- Dumping data for table `xkboa_wf_triggers`
--

INSERT INTO `xkboa_wf_triggers` (`id`, `type`, `name`, `namespace`, `folder`, `element`, `client_id`, `enabled`, `protected`, `manifest_cache`, `custom_data`, `system_data`, `params`, `checked_out`, `checked_out_time`, `created_by`, `created`, `modified_by`, `modified`, `published`, `ordering`, `access`) VALUES
(3, 'trigger', 'trg_action_contentupdate', 'Workflow.Transition.Action.Contentupdate', 'action', 'contentupdate', 0, 0, 0, '{"name":"trg_action_contentupdate","type":"trigger","creationDate":"February 2014","author":"Prasit Gebsaap","copyright":"Copyright (C) 2005 - 2014 Prasit Gebsaap. All rights reserved.","authorEmail":"soap@joomlant.org","authorUrl":"www.joomlant.org","version":"1.0.1","description":"TRG_ACTION_CONTENTUPDATE_XML_DESCRIPTION","group":""}', '', '', '{}', 0, '0000-00-00 00:00:00', 223, '2014-08-27 04:39:54', 223, '2014-08-27 09:11:13', 1, 0, 1),
(5, 'trigger', 'trg_guard_owner', 'Workflow.Transition.Guard.Owner', 'guard', 'owner', 0, 1, 0, '{"name":"trg_guard_owner","type":"trigger","creationDate":"August 2014","author":"Prasit Gebsaap","copyright":"Copyright (C) 2005 - 2014 Prasit Gebsaap. All rights reserved.","authorEmail":"soap@joomlant.org","authorUrl":"www.joomlant.org","version":"1.0.1","description":"TRG_GUARD_OWNER_XML_DESCRIPTION","group":""}', '', '', '{}', 0, '0000-00-00 00:00:00', 223, '2014-09-04 14:10:25', 0, '0000-00-00 00:00:00', 1, 0, 1),
(6, 'trigger', 'trg_guard_usergroup', 'Workflow.Transition.Guard.Usergroup', 'guard', 'usergroup', 0, 1, 0, '{"name":"trg_guard_usergroup","type":"trigger","creationDate":"February 2014","author":"Prasit Gebsaap","copyright":"Copyright (C) 2005 - 2014 Prasit Gebsaap. All rights reserved.","authorEmail":"soap@joomlant.org","authorUrl":"www.joomlant.org","version":"1.0.1","description":"TRG_GUARD_USERGROUP_XML_DESCRIPTION","group":""}', '', '', '{}', 0, '0000-00-00 00:00:00', 223, '2014-09-05 14:56:51', 0, '0000-00-00 00:00:00', 1, 0, 1),
(7, 'trigger', 'trg_action_joomlacontent', 'Workflow.Transition.Action.Joomlacontent', 'action', 'joomlacontent', 0, 0, 0, '{"name":"trg_action_joomlacontent","type":"trigger","creationDate":"September 2014","author":"Prasit Gebsaap","copyright":"Copyright (C) 2005 - 2014 Prasit Gebsaap. All rights reserved.","authorEmail":"soap@joomlant.org","authorUrl":"www.joomlant.org","version":"1.0.1","description":"TRG_ACTION_JOOMLACONTENT_XML_DESCRIPTION","group":""}', '', '', '{}', 0, '0000-00-00 00:00:00', 223, '2014-09-08 07:31:52', 0, '0000-00-00 00:00:00', 1, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `xkboa_wf_trigger_instances`
--

CREATE TABLE IF NOT EXISTS `xkboa_wf_trigger_instances` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `trigger_id` int(11) NOT NULL,
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Stores trigger instance (w config) for each transition' AUTO_INCREMENT=12 ;

--
-- Truncate table before insert `xkboa_wf_trigger_instances`
--

TRUNCATE TABLE `xkboa_wf_trigger_instances`;
--
-- Dumping data for table `xkboa_wf_trigger_instances`
--

INSERT INTO `xkboa_wf_trigger_instances` (`id`, `trigger_id`, `transition_id`, `title`, `namespace`, `trigger_config`, `published`, `created_by`, `created`, `modified_by`, `modified`, `checked_out`, `checked_out_time`, `ordering`, `note`) VALUES
(1, 5, 1, 'Allow owner to submit', 'Workflow.Transition.Guard.Owner', '{"allow_mode":"1","owner_fields":"created_by","allow_superadmin":"0"}', 0, 223, '2014-09-04 14:15:40', 223, '2014-09-05 09:57:01', 0, '0000-00-00 00:00:00', 1, ''),
(2, 6, 2, 'Allow editor to review article', 'Workflow.Transition.Guard.Usergroup', '{"groups":["4"],"inherited_group":"0","allowowner":"0","ownerfield":"created_by"}', 1, 223, '2014-09-05 14:58:32', 223, '2014-09-08 14:49:03', 0, '0000-00-00 00:00:00', 1, ''),
(3, 6, 3, 'Allow publisher to approve article publishing', 'Workflow.Transition.Guard.Usergroup', '{"groups":["5"],"inherited_group":"0","allowowner":"0","ownerfield":"created_by"}', 1, 223, '2014-09-08 05:34:03', 223, '2014-09-08 14:48:39', 0, '0000-00-00 00:00:00', 1, ''),
(4, 6, 4, 'Allow edit or publisher to do transition', 'Workflow.Transition.Guard.Usergroup', '{"groups":["4","5"],"inherited_group":"0","allowowner":"0","ownerfield":"created_by"}', 1, 223, '2014-09-08 05:34:58', 223, '2014-09-08 15:41:55', 0, '0000-00-00 00:00:00', 1, ''),
(5, 5, 5, 'Allow owner to draft article', 'Workflow.Transition.Guard.Owner', '{"allow_mode":"1","owner_fields":"created_by","allow_superadmin":"1"}', 1, 223, '2014-09-08 05:35:52', 223, '2014-09-08 05:36:08', 0, '0000-00-00 00:00:00', 1, ''),
(7, 7, 3, 'Update Joomla content state', 'Workflow.Transition.Action.Joomlacontent', '{"item_type":"com_content.article","publishing_state":"1","featured_state":"unchange"}', 1, 223, '2014-09-08 07:33:17', 223, '2014-09-08 07:39:28', 0, '0000-00-00 00:00:00', 2, ''),
(8, 5, 6, 'Owner submit for approval', 'Workflow.Transition.Guard.Owner', '{"allow_mode":"1","owner_fields":"owner_id","allow_superadmin":"1"}', 1, 614, '2015-04-20 13:10:56', 614, '2015-04-23 14:34:01', 0, '0000-00-00 00:00:00', 1, ''),
(9, 6, 7, 'Approve Reservation', 'Workflow.Transition.Guard.Usergroup', '{"groups":["7","8"],"inherited_group":"0","allowowner":"0","ownerfield":"created_by"}', 1, 614, '2015-04-20 13:16:53', 614, '2015-04-20 13:17:34', 0, '0000-00-00 00:00:00', 1, ''),
(10, 6, 8, 'Reject Reservation', 'Workflow.Transition.Guard.Usergroup', '{"groups":["7","8"],"inherited_group":"0","allowowner":"0","ownerfield":"created_by"}', 1, 614, '2015-04-20 13:18:08', 614, '2015-04-20 13:18:19', 0, '0000-00-00 00:00:00', 1, ''),
(11, 5, 9, 'Reset to Draft', 'Workflow.Transition.Guard.Owner', '{"allow_mode":"1","owner_fields":"","allow_superadmin":"1"}', 1, 614, '2015-04-20 13:18:57', 614, '2015-04-20 13:19:08', 0, '0000-00-00 00:00:00', 1, '');

-- --------------------------------------------------------

--
-- Table structure for table `xkboa_wf_user_roles`
--

CREATE TABLE IF NOT EXISTS `xkboa_wf_user_roles` (
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

--
-- Truncate table before insert `xkboa_wf_user_roles`
--

TRUNCATE TABLE `xkboa_wf_user_roles`;
-- --------------------------------------------------------

--
-- Table structure for table `xkboa_wf_waiting_items`
--

CREATE TABLE IF NOT EXISTS `xkboa_wf_waiting_items` (
  `id` int(11) NOT NULL,
  `context` varchar(50) NOT NULL COMMENT 'in com_xxx.entity format',
  `item_id` int(11) NOT NULL COMMENT 'PK of working item',
  `role_type` varchar(200) NOT NULL DEFAULT 'joomla.user' COMMENT 'joomla.user, joomla.group, workflow.role',
  `role_id` int(11) NOT NULL,
  `params` text NOT NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL,
  `checked_out` int(11) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `context` (`context`,`item_id`,`role_type`,`role_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `xkboa_wf_waiting_items`
--

TRUNCATE TABLE `xkboa_wf_waiting_items`;
-- --------------------------------------------------------

--
-- Table structure for table `xkboa_wf_workflows`
--

CREATE TABLE IF NOT EXISTS `xkboa_wf_workflows` (
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
  PRIMARY KEY (`id`),
  KEY `published` (`published`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Truncate table before insert `xkboa_wf_workflows`
--

TRUNCATE TABLE `xkboa_wf_workflows`;
--
-- Dumping data for table `xkboa_wf_workflows`
--

INSERT INTO `xkboa_wf_workflows` (`id`, `title`, `alias`, `description`, `category_id`, `created`, `created_by`, `modified`, `modified_by`, `checked_out_time`, `checked_out`, `published`, `ordering`, `access`, `language`) VALUES
(1, 'Content Publishing', 'content-publishing', '', 0, '2014-08-22 04:02:42', 223, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 0, 0, 1, 1, '*'),
(2, 'Reservation Approval', 'reservation-approval', '', 0, '2015-04-20 12:49:06', 614, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 0, 1, 2, 1, '*');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

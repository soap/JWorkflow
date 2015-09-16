ALTER TABLE `#__wf_states` ADD asset_id INT(255) NOT NULL DEFAULT '0' AFTER id;
ALTER TABLE `#__wf_waiting_items` MODIFY id int(11) NOT NULL AUTO_INCREMENT; 
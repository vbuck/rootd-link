<?php

/**
 * Added support for linking to attachments.
 *
 * @package   Rootd_Link
 * @author    Rick Buczynski <me@rickbuczynski.com>
 * @copyright 2014 Rick Buczynski. All Rights Reserved.
 * @version   0.0.4
 */

$installer = $this;

$installer->run("
    SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
    SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
    SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

    -- -----------------------------------------------------
    -- Table `rootd_link_node_attachment`
    -- -----------------------------------------------------
    DROP TABLE IF EXISTS `rootd_link_node_attachment`;

    CREATE  TABLE IF NOT EXISTS `rootd_link_node_attachment` (
        `attachment_id` INT NOT NULL AUTO_INCREMENT,
        `link_id`       INT NOT NULL,
        `type_id`       INT NOT NULL,
        `description`   VARCHAR(255),
        `target_path`   TEXT,
        PRIMARY KEY (`attachment_id`),
        INDEX (`attachment_id`, `link_id`),
        FOREIGN KEY `FK_object_attachment_id` (`link_id`) REFERENCES `rootd_link_node` (`link_id`)
            ON DELETE CASCADE
    )
    ENGINE = InnoDB;

    SET SQL_MODE=@OLD_SQL_MODE;
    SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
    SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
");

// Attempt to create attachments directory
$attachmentBase = (string) Mage::getConfig()->getNode('default/rootd_link/attachment_base');
if ($attachmentBase) {
    $path = Mage::getBaseDir() . DS . $attachmentBase;
    
    if (!file_exists($path)) {
        @mkdir($path);
    }

    @chmod($path, 0755);
}
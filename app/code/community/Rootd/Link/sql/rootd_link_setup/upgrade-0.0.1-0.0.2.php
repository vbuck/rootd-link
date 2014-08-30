<?php

/**
 * Added active link functionality to module.
 *
 * @package   Rootd_Link
 * @author    Rick Buczynski <me@rickbuczynski.com>
 * @copyright 2014 Rick Buczynski. All Rights Reserved.
 * @version   0.0.2
 */

$installer = $this;

$installer->run("
    SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
    SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
    SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

    ALTER TABLE `rootd_link_node` ADD COLUMN `is_active` TINYINT(1);
    ALTER TABLE `rootd_link_node` ADD COLUMN `active_from` DATE NULL;
    ALTER TABLE `rootd_link_node` ADD COLUMN `active_to` DATE NULL;

    SET SQL_MODE=@OLD_SQL_MODE;
    SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
    SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
");
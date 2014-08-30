<?php

$installer = $this;

$installer->run("
    SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
    SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
    SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

    -- -----------------------------------------------------
    -- Table `rootd_link_node`
    -- -----------------------------------------------------
    DROP TABLE IF EXISTS `rootd_link_node`;

    CREATE  TABLE IF NOT EXISTS `rootd_link_node` (
        `link_id`         INT NOT NULL AUTO_INCREMENT,
        `store_id`        INT NOT NULL,
        `customer_id`     INT NOT NULL,
        `object_id`       INT NOT NULL,
        `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at`      TIMESTAMP NULL,
        `description`     VARCHAR(255),
        `request_path`    TEXT,
        `target_path`     TEXT,
        PRIMARY KEY (`link_id`),
        INDEX (`link_id`, `object_id`)
    )
    ENGINE = InnoDB;

    SET SQL_MODE=@OLD_SQL_MODE;
    SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
    SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
");
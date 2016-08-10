<?php

$installer = $this;

$installer->startSetup();

$installer->run(
    "

-- -----------------------------------------------------
-- Table `mydb`.`exclusion`
-- -----------------------------------------------------
DROP TABLE IF EXISTS {$this->getTable('exclusion')} ;

CREATE  TABLE IF NOT EXISTS {$this->getTable('exclusion')} (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `id_store` INT NULL ,
  `term` VARCHAR(255) NULL ,
  `list` VARCHAR(255) NULL ,
  `created_at` DATETIME NULL ,
  `updated_at` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;

"
);


$installer->endSetup();

?>
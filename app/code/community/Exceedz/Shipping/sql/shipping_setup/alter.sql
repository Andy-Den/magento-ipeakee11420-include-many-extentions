ALTER TABLE `shipping_tablerate` ADD `weight_rate` DECIMAL( 12, 4 ) NOT NULL AFTER `condition_value`;
ALTER TABLE `shipping_tablerate` ADD `markup` DECIMAL( 12, 4 ) NOT NULL AFTER `price`; 
#Milandirect
#===========

#Status check
#select count(*) from sales_flat_quote;               #505644  -> 6814
#select count(*) from sales_flat_quote_address;       #1004358 -> 13628
#select count(*) from sales_flat_quote_item;          #525146  -> 11487
#select count(*) from sales_flat_quote_item_option;   #1539211 -> 29253
#select count(*) from sales_flat_quote_payment;       #41463   -> 6807
#select count(*) from sales_flat_quote_shipping_rate; #554889  -> 12439
#Parameter group params updates:
#MAX_HEAP_TABLE_SIZE: 67108864
#TMP_TABLE_SIZE: 67108864


#Delete useless tables
drop table core_url_rewrite_bk_19062013;
drop table core_config_data_copy;
drop table core_config_data_copy2;
drop table catalog_category_entity_varchar_copy;
drop table catalog_category_entity_varchar_new;

# Delete quotes.
create table sales_flat_quote_tmp like sales_flat_quote;
insert into sales_flat_quote_tmp select q.* from sales_flat_quote as q inner join sales_flat_order as o on (q.entity_id = o.quote_id) group by q.entity_id;
rename table sales_flat_quote to sales_flat_quote_bk;
rename table sales_flat_quote_tmp to sales_flat_quote;

# Delete quote addresses.
create table sales_flat_quote_address_tmp like sales_flat_quote_address;
insert into sales_flat_quote_address_tmp select a.* from sales_flat_quote_address as a inner join sales_flat_quote as q on (a.quote_id = q.entity_id) group by a.address_id;
rename table sales_flat_quote_address to sales_flat_quote_address_bk;
rename table sales_flat_quote_address_tmp to sales_flat_quote_address;

# Delete quote items.
create table sales_flat_quote_item_tmp like sales_flat_quote_item;
insert into sales_flat_quote_item_tmp select i.* from sales_flat_quote_item as i inner join sales_flat_quote as q on (i.quote_id = q.entity_id) group by i.item_id;
rename table sales_flat_quote_item to sales_flat_quote_item_bk, sales_flat_quote_item_tmp to sales_flat_quote_item;

# Delete quote item options.
create table sales_flat_quote_item_option_tmp like sales_flat_quote_item_option;
insert into sales_flat_quote_item_option_tmp select io.* from sales_flat_quote_item_option as io inner join sales_flat_quote_item as i on (io.item_id = i.item_id) group by io.option_id;
rename table sales_flat_quote_item_option to sales_flat_quote_item_option_bk, sales_flat_quote_item_option_tmp to sales_flat_quote_item_option;

# Delete quote payments.
create table sales_flat_quote_payment_tmp like sales_flat_quote_payment;
insert into sales_flat_quote_payment_tmp select p.* from sales_flat_quote_payment as p inner join sales_flat_quote as q on (p.quote_id = q.entity_id) group by p.payment_id;
rename table sales_flat_quote_payment to sales_flat_quote_payment_bk, sales_flat_quote_payment_tmp to sales_flat_quote_payment;

# Delete quote shipping rate.
create table sales_flat_quote_shipping_rate_tmp like sales_flat_quote_shipping_rate;
insert into sales_flat_quote_shipping_rate_tmp select sr.* from sales_flat_quote_shipping_rate as sr inner join sales_flat_quote_address as a on (sr.address_id = a.address_id) group by sr.rate_id;
rename table sales_flat_quote_shipping_rate to sales_flat_quote_shipping_rate_bk, sales_flat_quote_shipping_rate_tmp to sales_flat_quote_shipping_rate;

# Delete enterprise customer quotes.
truncate enterprise_customer_sales_flat_quote;
# Delete enterprise customer quote addresses.
truncate enterprise_customer_sales_flat_quote_address;

# Drop all the backup table (ignore foreign key constraints).
SET FOREIGN_KEY_CHECKS=0;
drop table sales_flat_quote_bk;
drop table sales_flat_quote_item_bk;
drop table sales_flat_quote_address_bk;
drop table sales_flat_quote_item_option_bk;
drop table sales_flat_quote_payment_bk;
drop table sales_flat_quote_shipping_rate_bk;
SET FOREIGN_KEY_CHECKS=1;



# Fix enterprise_customer_sales_flat_quote foreign keys.
## THIS CAN HELP IN CASE OF PRODUCT IS NOT ADDED INTO CART 
#ALTER TABLE `enterprise_customer_sales_flat_quote` DROP FOREIGN KEY `FK_ENT_CSTR_SALES_FLAT_QUOTE_ENTT_ID_SALES_FLAT_QUOTE_ENTT_ID`;
#ALTER TABLE `enterprise_customer_sales_flat_quote_address` DROP FOREIGN KEY `FK_E152BECD370CBCC294EEFDD7035E9C9F`;


#ALTER TABLE `enterprise_customer_sales_flat_quote` DROP FOREIGN KEY `FK_ENTERPRISE_CUSTOMER_SALES_QUOTE`;
#ALTER TABLE `enterprise_customer_sales_flat_quote` ADD CONSTRAINT `FK_ENTERPRISE_CUSTOMER_SALES_QUOTE` FOREIGN KEY (`entity_id`) REFERENCES `sales_flat_quote` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE;
# Fix enterprise_customer_sales_flat_quote_address foreign keys.
#ALTER TABLE `enterprise_customer_sales_flat_quote_address` DROP FOREIGN KEY `FK_ENTERPRISE_CUSTOMER_SALES_QUOTE_ADDRESS`;
#ALTER TABLE `enterprise_customer_sales_flat_quote_address` ADD CONSTRAINT `FK_ENTERPRISE_CUSTOMER_SALES_QUOTE_ADDRESS` FOREIGN KEY (`entity_id`) REFERENCES `sales_flat_quote_address` (`address_id`) ON DELETE CASCADE ON UPDATE CASCADE;

# Add back sales_flat_quote_payment foreign keys.
#ALTER TABLE `sales_flat_quote_payment` ADD CONSTRAINT `FK_SALES_QUOTE_PAYMENT_SALES_QUOTE` FOREIGN KEY (`quote_id`) REFERENCES `sales_flat_quote` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE;

# Add back  sales_flat_quote_item foreign keys.
#ALTER TABLE `sales_flat_quote_item` ADD CONSTRAINT `FK_SALES_FLAT_QUOTE_ITEM_PARENT_ITEM` FOREIGN KEY (`parent_item_id`) REFERENCES `sales_flat_quote_item` (`item_id`) ON DELETE CASCADE ON UPDATE CASCADE;
#ALTER TABLE `sales_flat_quote_item` ADD CONSTRAINT `FK_SALES_QUOTE_ITEM_CATALOG_PRODUCT_ENTITY` FOREIGN KEY (`product_id`) REFERENCES `catalog_product_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE;
#ALTER TABLE `sales_flat_quote_item` ADD CONSTRAINT `FK_SALES_QUOTE_ITEM_SALES_QUOTE` FOREIGN KEY (`quote_id`) REFERENCES `sales_flat_quote` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE;
#ALTER TABLE `sales_flat_quote_item` ADD CONSTRAINT `FK_SALES_QUOTE_ITEM_STORE` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE SET NULL ON UPDATE CASCADE;
#ALTER TABLE `sales_flat_quote_address` ADD CONSTRAINT `FK_SALES_QUOTE_ADDRESS_SALES_QUOTE` FOREIGN KEY (`quote_id`) REFERENCES `sales_flat_quote` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE;

# Add back sales_flat_quote_shipping_rate foreign keys.
#ALTER TABLE `sales_flat_quote_shipping_rate` ADD CONSTRAINT `FK_SALES_QUOTE_SHIPPING_RATE_ADDRESS` FOREIGN KEY (`address_id`) REFERENCES `sales_flat_quote_address` (`address_id`) ON DELETE CASCADE ON UPDATE CASCADE;

# Add back sales_flat_quote_item_option foreign keys.
#ALTER TABLE `sales_flat_quote_item_option` ADD CONSTRAINT `FK_SALES_QUOTE_ITEM_OPTION_ITEM_ID` FOREIGN KEY (`item_id`) REFERENCES `sales_flat_quote_item` (`item_id`) ON DELETE CASCADE ON UPDATE CASCADE;


# remove staging tables
drop table s_customer_address_entity;
drop table s_customer_address_entity_datetime;
drop table s_customer_address_entity_decimal;
drop table s_customer_address_entity_int;
drop table s_customer_address_entity_text;
drop table s_customer_address_entity_varchar;
drop table s_customer_eav_attribute;
drop table s_customer_eav_attribute_website;
drop table s_customer_entity;
drop table s_customer_entity_datetime;
drop table s_customer_entity_decimal;
drop table s_customer_entity_int;
drop table s_customer_entity_text;
drop table s_customer_entity_varchar;
drop table s_customer_form_attribute;
drop table s_wishlist;
drop table s_wishlist_item;
drop table s_wishlist_item_option;


# Remove system core url.
delete from core_url_rewrite where is_system=1;

# Remove all the index data.
SET FOREIGN_KEY_CHECKS=0;
truncate catalog_category_anc_categs_index_idx;
truncate catalog_category_anc_categs_index_tmp;
truncate catalog_category_anc_products_index_idx;
truncate catalog_category_anc_products_index_tmp;
truncate catalog_category_product_index;
truncate catalog_category_product_index_enbl_idx;
truncate catalog_category_product_index_enbl_tmp;
truncate catalog_category_product_index_idx;
truncate catalog_category_product_index_tmp;
truncate catalog_product_bundle_price_index;
truncate catalog_product_bundle_stock_index;
truncate catalog_product_enabled_index;
truncate catalog_product_index_eav;
truncate catalog_product_index_eav_decimal;
truncate catalog_product_index_eav_decimal_idx;
truncate catalog_product_index_eav_decimal_tmp;
truncate catalog_product_index_eav_idx;
truncate catalog_product_index_eav_tmp;
truncate catalog_product_index_price;
truncate catalog_product_index_price_bundle_idx;
truncate catalog_product_index_price_bundle_opt_idx;
truncate catalog_product_index_price_bundle_opt_tmp;
truncate catalog_product_index_price_bundle_sel_idx;
truncate catalog_product_index_price_bundle_sel_tmp;
truncate catalog_product_index_price_bundle_tmp;
truncate catalog_product_index_price_cfg_opt_agr_idx;
truncate catalog_product_index_price_cfg_opt_agr_tmp;
truncate catalog_product_index_price_cfg_opt_idx;
truncate catalog_product_index_price_cfg_opt_tmp;
truncate catalog_product_index_price_downlod_idx;
truncate catalog_product_index_price_downlod_tmp;
truncate catalog_product_index_price_final_idx;
truncate catalog_product_index_price_final_tmp;
truncate catalog_product_index_price_idx;
truncate catalog_product_index_price_opt_agr_idx;
truncate catalog_product_index_price_opt_agr_tmp;
truncate catalog_product_index_price_opt_idx;
truncate catalog_product_index_price_opt_tmp;
truncate catalog_product_index_price_tmp;
truncate catalog_product_index_tier_price;
truncate catalog_product_index_website;
truncate enterprise_catalogpermissions_index;
truncate enterprise_catalogpermissions_index_product;
truncate enterprise_targetrule_index;
truncate enterprise_targetrule_index_crosssell;
truncate enterprise_targetrule_index_related;
truncate enterprise_targetrule_index_upsell;
truncate index_event;
truncate index_process;
truncate index_process_event;
truncate report_compared_product_index;
truncate report_viewed_product_index;
SET FOREIGN_KEY_CHECKS=1;


# drop flat tables
drop table catalog_category_flat_store_2;
drop table catalog_category_flat_store_3;
drop table catalog_category_flat_store_5;
drop table catalog_product_flat_2 ;
drop table catalog_product_flat_3 ;
drop table catalog_product_flat_4;
drop table catalog_product_flat_5 ;


#This values are not supported in 1.14
update core_config_data set value ='' where path ='catalog/seo/product_url_suffix';
update core_config_data set value ='' where path ='catalog/seo/category_url_suffix';
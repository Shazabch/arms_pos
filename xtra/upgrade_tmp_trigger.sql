/*create table tmp_trigger_sku like tmp_trigger;
create table tmp_trigger_sku_items like tmp_trigger;
alter table tmp_trigger_sku drop tablename;
alter table tmp_trigger_sku_items drop tablename;
insert into tmp_trigger_sku select branch_id,id,counter_id,sync from tmp_trigger
where tablename = 'sku';
insert into tmp_trigger_sku_items select branch_id,id,counter_id,sync from tmp_trigger
where tablename = 'sku_items';
*/

create table if not exists counter_trigger_log(
	branch_id int,
	counter_id int,
	tablename char(50),
	row_index int,
	primary key (branch_id, counter_id, tablename)
);

create table if not exists tmp_trigger_log(
    row_index int not null primary key auto_increment,
	tablename char(50),
	id int not null default 0,
	timestamp timestamp,
	unique (tablename, id)
);

create table if not exists tmp_member_trigger_log(
    row_index int not null primary key auto_increment,
	nric char(20) not null,
	timestamp timestamp,
	unique (nric)
)ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/* pos_trigger_branch_insert */
drop trigger if exists `pos_trigger_branch_insert`;

delimiter |
CREATE TRIGGER `pos_trigger_branch_insert` AFTER insert ON `branch`
FOR EACH ROW BEGIN
replace into tmp_trigger_log (tablename, id) values ('branch', NEW.id);
delete from tmp_trigger where tablename='branch' and id=NEW.id;
END;|

/* pos_trigger_branch_update */
delimiter ;
DROP TRIGGER if exists `pos_trigger_branch_update`;

delimiter |
CREATE TRIGGER `pos_trigger_branch_update` AFTER update ON `branch`
FOR EACH ROW BEGIN
replace into tmp_trigger_log (tablename, id) values ('branch', NEW.id);
delete from tmp_trigger where tablename='branch' and id=NEW.id;
END;|

/* pos_trigger_sku_update */
delimiter ;
DROP TRIGGER if exists `pos_trigger_sku_update`;

delimiter |
CREATE TRIGGER `pos_trigger_sku_update` AFTER UPDATE ON `sku`
FOR EACH ROW BEGIN
replace into tmp_trigger_log (tablename, id) values ('sku', NEW.id);
delete from tmp_trigger where tablename='sku' and id=NEW.id;
END;|

/* pos_trigger_sku_insert */
delimiter ;
DROP TRIGGER if exists `pos_trigger_sku_insert`;

delimiter |
CREATE TRIGGER `pos_trigger_sku_insert` AFTER INSERT ON `sku`
FOR EACH ROW BEGIN
replace into tmp_trigger_log (tablename, id) values ('sku', NEW.id);
delete from tmp_trigger where tablename='sku' and id=NEW.id;
END;|

/* pos_trigger_sku_items_update */
delimiter ;
DROP TRIGGER if exists `pos_trigger_sku_items_update`;

delimiter |
CREATE TRIGGER `pos_trigger_sku_items_update` AFTER UPDATE ON `sku_items`
FOR EACH ROW BEGIN
replace into tmp_trigger_log (tablename, id) values ('sku_items', NEW.id);
delete from tmp_trigger where tablename='sku_items' and id=NEW.id;
END;|

/* pos_trigger_sku_items_insert */
delimiter ;
DROP TRIGGER if exists `pos_trigger_sku_items_insert`;

delimiter |
CREATE TRIGGER `pos_trigger_sku_items_insert` AFTER INSERT ON `sku_items`
FOR EACH ROW BEGIN
replace into tmp_trigger_log (tablename, id) values ('sku_items', NEW.id);
delete from tmp_trigger where tablename='sku_items' and id=NEW.id;
END;|


/* pos_trigger_category_insert */
delimiter ;
DROP TRIGGER if exists `pos_trigger_category_insert`;

delimiter |
CREATE TRIGGER `pos_trigger_category_insert` AFTER INSERT ON `category`
FOR EACH ROW BEGIN
replace into tmp_trigger_log (tablename, id) values ('category', NEW.id);
delete from tmp_trigger where tablename='category' and id=NEW.id;
END;|

/* pos_trigger_category_update */
delimiter ;
DROP TRIGGER if exists `pos_trigger_category_update`;

delimiter |
CREATE TRIGGER `pos_trigger_category_update` AFTER update ON `category`
FOR EACH ROW BEGIN
replace into tmp_trigger_log (tablename, id) values ('category', NEW.id);
delete from tmp_trigger where tablename='category' and id=NEW.id;
END;|

/* pos_trigger_counter_settings_insert */
delimiter ;
DROP TRIGGER if exists `pos_trigger_counter_settings_insert`;

delimiter |
CREATE TRIGGER `pos_trigger_counter_settings_insert` AFTER INSERT ON `counter_settings`
FOR EACH ROW BEGIN
replace into tmp_trigger_log (tablename, id) values ('counter_settings', NEW.id);
delete from tmp_trigger where tablename='counter_settings' and id=NEW.id;
END;|

/* pos_trigger_counter_settings_update */
delimiter ;
DROP TRIGGER if exists `pos_trigger_counter_settings_update`;

delimiter |
CREATE TRIGGER `pos_trigger_counter_settings_update` AFTER update ON `counter_settings`
FOR EACH ROW BEGIN
replace into tmp_trigger_log (tablename, id) values ('counter_settings', NEW.id);
delete from tmp_trigger where tablename='counter_settings' and id=NEW.id;
END;|

/* pos_trigger_membership_insert */
delimiter ;
DROP TRIGGER if exists `pos_trigger_membership_insert`;

delimiter |
CREATE TRIGGER `pos_trigger_membership_insert` AFTER INSERT ON `membership`
FOR EACH ROW BEGIN
replace into tmp_member_trigger_log (nric) values (NEW.nric);
delete from tmp_member_trigger where nric=NEW.nric;
END;|

/* pos_trigger_membership_update */
delimiter ;
DROP TRIGGER if exists `pos_trigger_membership_update`;

delimiter |
CREATE TRIGGER `pos_trigger_membership_update` AFTER update ON `membership`
FOR EACH ROW BEGIN
replace into tmp_member_trigger_log (nric) values (NEW.nric);
delete from tmp_member_trigger where nric=NEW.nric;
END;|

/* pos_trigger_membership_redemption_sku_insert */
delimiter ;
DROP TRIGGER if exists `pos_trigger_membership_redemption_sku_insert`;

delimiter |
CREATE TRIGGER `pos_trigger_membership_redemption_sku_insert` AFTER INSERT ON `membership_redemption_sku`
FOR EACH ROW BEGIN
replace into tmp_trigger_log (tablename, id) values ('membership_redemption_sku', NEW.id);
delete from tmp_trigger where tablename='membership_redemption_sku' and id=NEW.id;
END;|

/* pos_trigger_membership_redemption_sku_update */
delimiter ;
DROP TRIGGER if exists `pos_trigger_membership_redemption_sku_update`;

delimiter |
CREATE TRIGGER `pos_trigger_membership_redemption_sku_update` AFTER update ON `membership_redemption_sku`
FOR EACH ROW BEGIN
replace into tmp_trigger_log (tablename, id) values ('membership_redemption_sku', NEW.id);
delete from tmp_trigger where tablename='membership_redemption_sku' and id=NEW.id;
END;|

/* pos_trigger_promotion_insert */
delimiter ;
DROP TRIGGER if exists `pos_trigger_promotion_insert`;

delimiter |
CREATE TRIGGER `pos_trigger_promotion_insert` AFTER INSERT ON `promotion`
FOR EACH ROW BEGIN
replace into tmp_trigger_log (tablename, id) values ('promotion', NEW.id*1000+NEW.branch_id);
delete from tmp_trigger where tablename='promotion' and id=(NEW.id*1000+NEW.branch_id);
END;|

/* pos_trigger_promotion_update */
delimiter ;
DROP TRIGGER if exists `pos_trigger_promotion_update`;

delimiter |
CREATE TRIGGER `pos_trigger_promotion_update` AFTER update ON `promotion`
FOR EACH ROW BEGIN
replace into tmp_trigger_log (tablename, id) values ('promotion', NEW.id*1000+NEW.branch_id);
delete from tmp_trigger where tablename='promotion' and id=(NEW.id*1000+NEW.branch_id);
END;|

/* pos_trigger_uom_insert */
delimiter ;
DROP TRIGGER if exists `pos_trigger_uom_insert`;

delimiter |
CREATE TRIGGER `pos_trigger_uom_insert` AFTER INSERT ON `uom`
FOR EACH ROW BEGIN
replace into tmp_trigger_log (tablename, id) values ('uom', NEW.id);
delete from tmp_trigger where tablename='uom' and id=NEW.id;
END;|

/* pos_trigger_uom_update */
delimiter ;
DROP TRIGGER if exists `pos_trigger_uom_update`;

delimiter |
CREATE TRIGGER `pos_trigger_uom_update` AFTER update ON `uom`
FOR EACH ROW BEGIN
replace into tmp_trigger_log (tablename, id) values ('uom', NEW.id);
delete from tmp_trigger where tablename='uom' and id=NEW.id;
END;|

/* pos_trigger_user_insert */
delimiter ;
DROP TRIGGER if exists `pos_trigger_user_insert`;

delimiter |
CREATE TRIGGER `pos_trigger_user_insert` AFTER INSERT ON `user`
FOR EACH ROW BEGIN
replace into tmp_trigger_log (tablename, id) values ('user', NEW.id);
delete from tmp_trigger where tablename='user' and id=NEW.id;
END;|

/* pos_trigger_user_update */
delimiter ;
DROP TRIGGER if exists `pos_trigger_user_update`;

delimiter |
CREATE TRIGGER `pos_trigger_user_update` AFTER update ON `user`
FOR EACH ROW BEGIN
replace into tmp_trigger_log (tablename, id) values ('user', NEW.id);
delete from tmp_trigger where tablename='user' and id=NEW.id;
END;|

/* END */
delimiter ;
/*
select si.id, si.sku_id, si.sku_item_code, si.packing_uom_id,
si.mcode, si.link_code, si.receipt_description, if(sp.price is null, si.selling_price, sp.price) as selling_price,si.active,si.open_price,si.decimal_qty,uom.code as uom_code, if(uom.fraction is null,1,uom.fraction) as uom_fraction, if (trade_discount_code is null, default_trade_discount_code, trade_discount_code) as trade_discount_code, si.open_price, si.active
from sku_items si left join sku on sku_id = sku.id
left join sku_items_price sp on sp.sku_item_id = si.id and sp.branch_id = 6
left join uom on si.packing_uom_id = uom.id
left join tmp_trigger tmp on tmp.id = si.id and tmp.branch_id =6 and tmp.counter_id=23
where (tmp.sync is null or tmp.sync=1)
*/

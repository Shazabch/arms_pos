/* pos_trigger_branch_insert */
drop trigger if exists `pos_trigger_branch_insert`;

delimiter |
CREATE TRIGGER `pos_trigger_branch_insert` AFTER insert ON `branch`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('branch', NEW.id, @new_id);
delete from tmp_trigger where tablename='branch' and id=NEW.id;
END;|

/* pos_trigger_branch_update */
delimiter ;
DROP TRIGGER if exists `pos_trigger_branch_update`;

delimiter |
CREATE TRIGGER `pos_trigger_branch_update` AFTER update ON `branch`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('branch', NEW.id, @new_id);
delete from tmp_trigger where tablename='branch' and id=NEW.id;
END;|

/* pos_trigger_sku_update */
delimiter ;
DROP TRIGGER if exists `pos_trigger_sku_update`;

delimiter |
CREATE TRIGGER `pos_trigger_sku_update` AFTER UPDATE ON `sku`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('sku', NEW.id, @new_id);
delete from tmp_trigger where tablename='sku' and id=NEW.id;
END;|

/* pos_trigger_sku_insert */
delimiter ;
DROP TRIGGER if exists `pos_trigger_sku_insert`;

delimiter |
CREATE TRIGGER `pos_trigger_sku_insert` AFTER INSERT ON `sku`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('sku', NEW.id, @new_id);
delete from tmp_trigger where tablename='sku' and id=NEW.id;
END;|

/* pos_trigger_sku_items_update */
delimiter ;
DROP TRIGGER if exists `pos_trigger_sku_items_update`;

delimiter |
CREATE TRIGGER `pos_trigger_sku_items_update` AFTER UPDATE ON `sku_items`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('sku_items', NEW.id, @new_id);
delete from tmp_trigger where tablename='sku_items' and id=NEW.id;
END;|

/* pos_trigger_sku_items_insert */
delimiter ;
DROP TRIGGER if exists `pos_trigger_sku_items_insert`;

delimiter |
CREATE TRIGGER `pos_trigger_sku_items_insert` AFTER INSERT ON `sku_items`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('sku_items', NEW.id, @new_id);
delete from tmp_trigger where tablename='sku_items' and id=NEW.id;
END;|


/* pos_trigger_category_insert */
delimiter ;
DROP TRIGGER if exists `pos_trigger_category_insert`;

delimiter |
CREATE TRIGGER `pos_trigger_category_insert` AFTER INSERT ON `category`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('category', NEW.id, @new_id);
delete from tmp_trigger where tablename='category' and id=NEW.id;
END;|

/* pos_trigger_category_update */
delimiter ;
DROP TRIGGER if exists `pos_trigger_category_update`;

delimiter |
CREATE TRIGGER `pos_trigger_category_update` AFTER update ON `category`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('category', NEW.id, @new_id);
delete from tmp_trigger where tablename='category' and id=NEW.id;
END;|

/* pos_trigger_counter_settings_insert */
delimiter ;
DROP TRIGGER if exists `pos_trigger_counter_settings_insert`;

delimiter |
CREATE TRIGGER `pos_trigger_counter_settings_insert` AFTER INSERT ON `counter_settings`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('counter_settings', NEW.id, @new_id);
delete from tmp_trigger where tablename='counter_settings' and id=NEW.id;
END;|

/* pos_trigger_counter_settings_update */
delimiter ;
DROP TRIGGER if exists `pos_trigger_counter_settings_update`;

delimiter |
CREATE TRIGGER `pos_trigger_counter_settings_update` AFTER update ON `counter_settings`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('counter_settings', NEW.id, @new_id);
delete from tmp_trigger where tablename='counter_settings' and id=NEW.id;
END;|

/* pos_trigger_membership_insert */
delimiter ;
DROP TRIGGER if exists `pos_trigger_membership_insert`;

delimiter |
CREATE TRIGGER `pos_trigger_membership_insert` AFTER INSERT ON `membership`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_member_trigger_log);
replace into tmp_member_trigger_log (nric, row_index) values (NEW.nric, @new_id);
delete from tmp_member_trigger where nric=NEW.nric;
END;|

/* pos_trigger_membership_update */
delimiter ;
DROP TRIGGER if exists `pos_trigger_membership_update`;

delimiter |
CREATE TRIGGER `pos_trigger_membership_update` AFTER update ON `membership`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_member_trigger_log);
replace into tmp_member_trigger_log (nric, row_index) values (NEW.nric, @new_id);
delete from tmp_member_trigger where nric=NEW.nric;
END;|

/* pos_trigger_membership_redemption_sku_insert */
delimiter ;
DROP TRIGGER if exists `pos_trigger_membership_redemption_sku_insert`;

delimiter |
CREATE TRIGGER `pos_trigger_membership_redemption_sku_insert` AFTER INSERT ON `membership_redemption_sku`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('membership_redemption_sku', NEW.id, @new_id);
delete from tmp_trigger where tablename='membership_redemption_sku' and id=NEW.id;
END;|

/* pos_trigger_membership_redemption_sku_update */
delimiter ;
DROP TRIGGER if exists `pos_trigger_membership_redemption_sku_update`;

delimiter |
CREATE TRIGGER `pos_trigger_membership_redemption_sku_update` AFTER update ON `membership_redemption_sku`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('membership_redemption_sku', NEW.id, @new_id);
delete from tmp_trigger where tablename='membership_redemption_sku' and id=NEW.id;
END;|

/* pos_trigger_promotion_insert */
delimiter ;
DROP TRIGGER if exists `pos_trigger_promotion_insert`;

delimiter |
CREATE TRIGGER `pos_trigger_promotion_insert` AFTER INSERT ON `promotion`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('promotion', NEW.id*1000+NEW.branch_id, @new_id);
delete from tmp_trigger where tablename='promotion' and id=(NEW.id*1000+NEW.branch_id);
END;|

/* pos_trigger_promotion_update */
delimiter ;
DROP TRIGGER if exists `pos_trigger_promotion_update`;

delimiter |
CREATE TRIGGER `pos_trigger_promotion_update` AFTER update ON `promotion`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('promotion', NEW.id*1000+NEW.branch_id, @new_id);
delete from tmp_trigger where tablename='promotion' and id=(NEW.id*1000+NEW.branch_id);
END;|

/* pos_trigger_uom_insert */
delimiter ;
DROP TRIGGER if exists `pos_trigger_uom_insert`;

delimiter |
CREATE TRIGGER `pos_trigger_uom_insert` AFTER INSERT ON `uom`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('uom', NEW.id, @new_id);
delete from tmp_trigger where tablename='uom' and id=NEW.id;
END;|

/* pos_trigger_uom_update */
delimiter ;
DROP TRIGGER if exists `pos_trigger_uom_update`;

delimiter |
CREATE TRIGGER `pos_trigger_uom_update` AFTER update ON `uom`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('uom', NEW.id, @new_id);
delete from tmp_trigger where tablename='uom' and id=NEW.id;
END;|

/* pos_trigger_user_insert */
delimiter ;
DROP TRIGGER if exists `pos_trigger_user_insert`;

delimiter |
CREATE TRIGGER `pos_trigger_user_insert` AFTER INSERT ON `user`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('user', NEW.id, @new_id);
delete from tmp_trigger where tablename='user' and id=NEW.id;
END;|

/* pos_trigger_user_update */
delimiter ;
DROP TRIGGER if exists `pos_trigger_user_update`;

delimiter |
CREATE TRIGGER `pos_trigger_user_update` AFTER update ON `user`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('user', NEW.id, @new_id);
delete from tmp_trigger where tablename='user' and id=NEW.id;
END;|

/* pos_trigger_sa_insert */
delimiter ;
DROP TRIGGER if exists `pos_trigger_sa_insert`;

delimiter |
CREATE TRIGGER `pos_trigger_sa_insert` AFTER INSERT ON `sa`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('sa', NEW.id, @new_id);
delete from tmp_trigger where tablename='sa' and id=NEW.id;
END;|

/* pos_trigger_sa_update */
delimiter ;
DROP TRIGGER if exists `pos_trigger_sa_update`;

delimiter |
CREATE TRIGGER `pos_trigger_sa_update` AFTER update ON `sa`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('sa', NEW.id, @new_id);
delete from tmp_trigger where tablename='sa' and id=NEW.id;
END;|

/* pos_trigger_pos_settings_insert */
/*delimiter ;
DROP TRIGGER if exists `pos_trigger_pos_settings_insert`;
delimiter |
CREATE TRIGGER `pos_trigger_pos_settings_insert` AFTER INSERT ON `pos_settings`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('pos_settings', 1, @new_id);
END;|*/

/* pos_trigger_pos_settings_update */
/*delimiter ;
DROP TRIGGER if exists `pos_trigger_pos_settings_update`;

delimiter |
CREATE TRIGGER `pos_trigger_pos_settings_update` AFTER update ON `pos_settings`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('pos_settings', 1, @new_id);
END;|*/

/* END */
delimiter ;

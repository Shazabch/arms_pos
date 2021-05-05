/* START */

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

/* END */
delimiter ;

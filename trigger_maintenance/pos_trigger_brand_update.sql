/* pos_trigger_brand_update */

delimiter |
CREATE TRIGGER `pos_trigger_brand_update` AFTER update ON `brand`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('brand', NEW.id, @new_id);
END;|

delimiter ;
/* pos_trigger_brand_insert */

delimiter |
CREATE TRIGGER `pos_trigger_announcement_insert` AFTER INSERT ON `pos_announcement`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('pos_announcement', NEW.id*10000+NEW.branch_id, @new_id);
END;|

delimiter ;
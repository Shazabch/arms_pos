/* pos_trigger_membership_package_insert */

delimiter |
CREATE TRIGGER `pos_trigger_membership_package_insert` AFTER INSERT ON `membership_package`
FOR EACH ROW BEGIN
set @new_id = (select if(max(row_index) is null, 0, max(row_index))+1 from tmp_trigger_log);
replace into tmp_trigger_log (tablename, id, row_index) values ('membership_package', NEW.unique_id, @new_id);
END;|

delimiter ;
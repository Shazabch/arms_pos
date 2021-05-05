/* membership_points_pos_insert_trigger */
DROP TRIGGER if exists `membership_points_pos_insert_trigger`;

delimiter |
CREATE TRIGGER `membership_points_pos_insert_trigger`
AFTER INSERT ON `pos`
FOR EACH ROW BEGIN
delete from tmp_membership_points_trigger where card_no = NEW.member_no ;
IF (NEW.member_no<>'') THEN
	replace into tmp_member_pos_trigger (card_no, receipt_ref_no) values (NEW.member_no, NEW.receipt_ref_no);
END IF;
END;|

delimiter ;

/* membership_points_pos_update_trigger */
DROP TRIGGER if exists `membership_points_pos_update_trigger`;

delimiter |
CREATE TRIGGER `membership_points_pos_update_trigger`
AFTER UPDATE ON `pos`
FOR EACH ROW BEGIN
delete from tmp_membership_points_trigger where card_no = NEW.member_no or card_no = OLD.member_no ;
IF (NEW.member_no<>'') THEN
	replace into tmp_member_pos_trigger (card_no, receipt_ref_no) values (NEW.member_no, NEW.receipt_ref_no);
	IF (NEW.receipt_ref_no<>OLD.receipt_ref_no) THEN
		replace into tmp_member_pos_trigger (card_no, receipt_ref_no) values (NEW.member_no, OLD.receipt_ref_no);
	END IF;
END IF;

IF (OLD.member_no<>'' and NEW.member_no<>OLD.member_no) THEN
	replace into tmp_member_pos_trigger (card_no, receipt_ref_no) values (OLD.member_no, NEW.receipt_ref_no);
	IF (NEW.receipt_ref_no<>OLD.receipt_ref_no) THEN
		replace into tmp_member_pos_trigger (card_no, receipt_ref_no) values (OLD.member_no, OLD.receipt_ref_no);
	END IF;
END IF;

END;|

delimiter ;
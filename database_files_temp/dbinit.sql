replace into user (id,u,l,p,active,default_branch_id,level,is_arms_user) values (1,'admin','admin', md5('123'),1,1,9999,1);
replace into user (id,u,l,p,active,default_branch_id,level,is_arms_user) values (2,'arms','arms', md5('123'),1,1,9999,1);
replace into user_privilege (user_id, branch_id, privilege_code, allowed) (select 1,1,code,1 from privilege);
replace into user_privilege (user_id, branch_id, privilege_code, allowed) (select 2,1,code,1 from privilege);
replace into branch (id,code,report_prefix,active) values (1,'HQ','HQ',1);
replace into uom (id,code,description,fraction,active) values (1,'EACH','EACH',1,1);
replace into sku_type(code, description, active) values('OUTRIGHT', 'Outright', 1);
replace into sku_type(code, description, active) values('CONSIGN', 'Consignment', 1);
replace into sku_type (code, description, active) values ('CONCESS', 'Concessionaire', 1);
replace into approval_order (id, description) values (1, 'Follow Sequence');
replace into approval_order (id, description) values (2, 'All (No Sequence)');
replace into approval_order (id, description) values (3, 'Anyone');
replace into approval_order (id, description) values (4, 'No Approver');
replace into vendortype values('OUT','Outright', 1);
replace into vendortype values('CON','Consignment', 1);
insert into category (id, root_id, level, code, description, tree_str, no_inventory, is_fresh_market) values (1,0,1,'LINE', 'LINE','(0)', 'no', 'no');
insert into category (id, root_id, level, code, description, tree_str) values (2,1,2,'DEPT', 'DEPARTMENT','(0)(1)');
insert into trade_discount_type (code, description) values ('N1','N1'), ('N2','N2'), ('N3','N3'), ('B1','B1'), ('B2','B2'), ('B3','B3');

CREATE TABLE `category_cache` ( `category_id` int(11) NOT NULL, `no_inventory` enum('yes','no') COLLATE latin1_general_ci NOT NULL DEFAULT 'no', `is_fresh_market` enum('yes','no') COLLATE latin1_general_ci NOT NULL DEFAULT 'no', `input_tax` int(11) DEFAULT '0', `output_tax` int(11) DEFAULT '0', `inclusive_tax` enum('yes','no') COLLATE latin1_general_ci NOT NULL DEFAULT 'no', `p0` int(11) DEFAULT '0', `p1` int(11) DEFAULT '0', `p2` int(11) DEFAULT '0', `p3` int(11) DEFAULT '0', `p4` int(11) DEFAULT '0', `p5` int(11) DEFAULT '0', `p6` int(11) DEFAULT '0', `p7` int(11) DEFAULT '0', PRIMARY KEY (`category_id`), KEY `p0` (`p0`), KEY `p1` (`p1`), KEY `p2` (`p2`), KEY `p3` (`p3`), KEY `p4` (`p4`), KEY `p5` (`p5`), KEY `p6` (`p6`), KEY `p7` (`p7`) ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci


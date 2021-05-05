create table if not exists counter_trigger_log(
	branch_id int,
	counter_id int,
	tablename char(50),
	row_index int,
	primary key (branch_id, counter_id, tablename)
) DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

create table if not exists tmp_trigger_log(
    row_index int not null primary key,
	tablename char(50),
	id int not null default 0,
	timestamp timestamp,
	unique (tablename, id)
) DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

create table if not exists tmp_member_trigger_log(
    row_index int not null primary key,
	nric char(20) not null,
	timestamp timestamp,
	unique (nric)
) DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;


CREATE TABLE if not exists `tmp_trigger` ( `branch_id` int(11) NOT NULL DEFAULT '0', `id` int(11) NOT NULL DEFAULT '0', `counter_id` int(11) NOT NULL DEFAULT '0', `tablename` char(50) COLLATE latin1_general_ci NOT NULL DEFAULT '', `sync` tinyint(1) DEFAULT '0', PRIMARY KEY (`branch_id`,`id`,`counter_id`,`tablename`), KEY `id` (`id`), KEY `tablename` (`tablename`), KEY `counter_id` (`counter_id`), KEY `branch_id` (`branch_id`,`counter_id`) ) DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

CREATE TABLE if not exists `tmp_member_trigger` ( `branch_id` int(11) NOT NULL DEFAULT '0', `counter_id` int(11) NOT NULL DEFAULT '0', `nric` varchar(20) COLLATE latin1_general_ci NOT NULL DEFAULT '', `sync` tinyint(1) DEFAULT '0', PRIMARY KEY (`nric`,`counter_id`,`branch_id`) ) DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

alter table tmp_trigger_log modify row_index int;
alter table tmp_member_trigger_log modify row_index int; 
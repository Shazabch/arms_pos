<?php
/*
7/10/2013 11:20 AM Andy
- Add UBS Export module in Extra. change to check privilege "WB".

07/17/2013 03:04 PM Justin
- Enhanced to always export all dbf files even there is no record.
*/
include("../../include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
//if (!privilege('POS_REPORT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'POS_REPORT', BRANCH_CODE), "/index.php");
if (!privilege('WB')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'WB', BRANCH_CODE), "/index.php");

class UBS_EXPORT extends Module{
	var $branches = array();
	var $branches_group = array();
	var $branch_id = 0;
	
	function __construct($title){
		global $con, $smarty, $sessioninfo;

		$this->init_selection();
        if(BRANCH_CODE == 'HQ'){
			$this->branch_id = mi($_REQUEST['branch_id']);
		}else{
			$this->branch_id = mi($sessioninfo['branch_id']);
		}
		
		parent::__construct($title);
	}
	
	function _default(){
		global $con;

		$this->display("ngiukee/ubs_export.tpl");
	}
	
	private function init_selection(){
	    global $con, $smarty, $sessioninfo;
	    	
	    if(!isset($_REQUEST['date']))	$_REQUEST['date'] = date("Y-m-d");
	    if(BRANCH_CODE=='HQ' && !isset($_REQUEST['branch_id']))	$_REQUEST['branch_id'] = $sessioninfo['branch_id'];
	    
		$con->sql_query("select * from branch where active=1 and id>0");
		while($r = $con->sql_fetchassoc()){
			$this->branches[$r['id']] = $r;
		}
		$con->sql_freeresult();
		$smarty->assign('branches',$this->branches);
	}
	
	function ubs_export(){
		global $con, $smarty, $sessioninfo;
		
		$bid = mi($this->branch_id);
		$date = trim($_REQUEST['date']);
		$show_type = mi($_REQUEST['show_type']);
		$filter = $err = array();
		$has_info = false;
		
		if(!$bid)	$err[] = "Please select branch.";
		if(!strtotime($date))	$err[] = "Please select date";
		
		if($err){
			$smarty->assign("err", $err);
			$this->display("ngiukee/ubs_export.tpl");
			exit;
		}
		
		if(!is_dir("../../dbase")){
			mkdir("../../dbase");
			chmod("../../dbase",0777);
		}
		
		//****************************************************************************** GRN

		
		$filter = array();
		$filter[] = "grr.branch_id=".mi($bid);
		$filter[] = "grr.rcv_date=".ms($date);
		$filter[] = "grr.active=1 and grn.active=1 and grn.approved=1 and grn.status=1";
		$filter[] = "gri.type in ('INVOICE','OTHER')";
		$filter = "where ".join(' and ', $filter);
		
		// grn
		$file_name = "RECMAST.dbf";
		$sql = "select grn.id as grn_id, grr.id as grr_id,v.code as vendorcode, v.description as vendor_desc, 
				gri.doc_no, gri.type, grr.rcv_date, v.term, gri.amount, c.code as line_code, b.report_prefix
				from grr_items gri 
				left join grr on gri.grr_id = grr.id and gri.branch_id = grr.branch_id
				left join grn on grn.grr_id = grr.id and grn.branch_id =grr.branch_id
				left join branch b on b.id = grn.branch_id
				left join vendor v on grr.vendor_id = v.id
				left join category_cache cc on cc.category_id = grr.department_id
				left join category c on c.id = cc.p1
				$filter
				order by grn.id";

		$q1 = $con->sql_query($sql);

		if(file_exists("../../dbase/".$file_name)){
			unlink("../../dbase/".$file_name);
		}

		// database "header"
		$def = array(
			array("CRECNO", "C", 20),
			array("CSECDESC", "C", 69),
			array("CTYPE", "C", 3),
			array("DDATE", "C", 30),
			array("CREF2", "C", 20),
			array("CREF1", "C", 20),
			array("CSUPCODE", "C", 15),
			array("CSUPNAME", "C", 50),
			array("CREMARK1", "C", 200),
			array("CREMARK2", "C", 10),
			array("NTOTAL", "N", 15, 2),
			array("NTTLDISCRM", "C", 1),
			array("_NullFlags", "N", 4, 2)
		);
		
		// database creation
		if (!dbase_create("../../dbase/".$file_name, $def)) {
			$err[] = "Error, can't create the database $file_name";
		}
		
		if($con->sql_numrows($q1) > 0){
			// open in read-write mode
			$db = dbase_open("../../dbase/".$file_name, 2);

			if(!$err){
				if($db){
					while($r = $con->sql_fetchassoc($q1)){
						$grn_no = $r['report_prefix'].str_pad($r['grn_id'], 5, "0", STR_PAD_LEFT);
						$rcv_date = date("m/d/y", strtotime($r['rcv_date']))." 12:00 AM";
						$grr_no = $r['report_prefix'].str_pad($r['grr_id'], 5, "0", STR_PAD_LEFT);
						$doc_no = $r['doc_no']." ".$r['line_code'];
						$vd_code = $r['vendorcode'];
						$vd_name = $r['vendor_desc'];
						$doc_amt = $r['amount'];

						dbase_add_record($db, 
							array(
								$grn_no,
								"PURCHASES", 
								"REC", 
								$rcv_date,
								$grr_no,
								$doc_no,
								$vd_code,
								$vd_name,
								"",
								"",
								$doc_amt,
								0,
								0
							)
						);
					}
					$has_info = true;
					dbase_close($db);
				}else{
					$err[] = "Can't open the database $file_name";
				}
			}
		}

		$con->sql_freeresult($q1);

		// grn items
		$file_name = "RECTRAN.dbf";
		$sql = "select grn.id as grn_id, grr.id as grr_id,
				((if(gi.acc_ctn is null and gi.acc_pcs is null, (gi.ctn * rcv_uom.fraction) + gi.pcs, (gi.acc_ctn * rcv_uom.fraction) + gi.acc_pcs) - (ifnull(gi.return_ctn * rcv_uom.fraction,0) + ifnull(gi.return_pcs,0))) * if(gi.acc_cost is null, gi.cost, gi.acc_cost) / rcv_uom.fraction +
				ifnull(((if(gi.acc_ctn is null and gi.acc_pcs is null, (gi.ctn * rcv_uom.fraction) + gi.pcs, (gi.acc_ctn *rcv_uom.fraction) + gi.acc_pcs) - (ifnull(gi.return_ctn * rcv_uom.fraction,0) + ifnull(gi.return_pcs,0))) * if(gi.acc_cost is null, gi.cost, gi.acc_cost) / rcv_uom.fraction) *
				(grn.grn_tax/100), 0)) as gi_amount, 
				ifnull(dept.code, cat.code) as prefix_code, SUBSTRING(si.sku_item_code FROM 3) as prefix_si_code, 
				b.report_prefix
				from grn_items gi
				left join grn on grn.id = gi.grn_id and grn.branch_id = gi.branch_id
				left join grr on grn.id = grn.grr_id and grr.branch_id = grn.branch_id
				left join grr_items gri on gri.grr_id = grr.id and gri.branch_id = grr.branch_id
				left join branch b on b.id = grn.branch_id
				left join sku_items si on si.id = gi.sku_item_id
				left join sku on sku.id = si.sku_id
				left join uom sell_uom on gi.selling_uom_id=sell_uom.id
				left join uom rcv_uom on gi.uom_id=rcv_uom.id
				left join category_cache cc on cc.category_id = sku.category_id
				left join category dept on dept.id = cc.p2
				left join category cat on cat.id = cc.p3
				$filter
				order by gi.grn_id";
		//print $sql;
		$q1 = $con->sql_query($sql);

		if(file_exists("../../dbase/".$file_name)){
			unlink("../../dbase/".$file_name);
		}
		
		// database "header"
		$def = array(
			array("CRECNO", "C", 20),
			array("CTYPE", "C", 3),
			array("CSTOCODE", "C", 13),
			array("NAMOUNT", "N", 15, 2),
			array("CSTOCKCODE", "C", 13),
			array("_NullFlags", "N", 4, 2)
		);
		
		// database creation
		if (!dbase_create("../../dbase/".$file_name, $def)) {
			$err[] = "Error, can't create the database $file_name";
		}

		if($con->sql_numrows($q1) > 0){
			// open in read-write mode
			$db = dbase_open("../../dbase/".$file_name, 2);
			
			if(!$err){
				if($db){
					while($r = $con->sql_fetchassoc($q1)){
						$grn_no = $r['report_prefix'].str_pad($r['grn_id'], 5, "0", STR_PAD_LEFT);  
						if(!$r['prefix_code']) $r['prefix_code'] = "00";
						$prefix_code = $r['prefix_code']." ".$r['prefix_si_code'];
						$gi_amt = $r['gi_amount'];
						$prefix_si_code = $r['prefix_si_code'];

						dbase_add_record($db, 
							array(
								$grn_no,
								"REC", 
								$prefix_code,
								$gi_amt,
								$prefix_si_code,
								0
							)
						);
					}
					$has_info = true;
					dbase_close($db);
				}else{
					$err[] = "Can't open the database $file_name";
				}
			}
		}

		$con->sql_freeresult($q1);
		
		//************************************************************************** End of GRN

		//************************************************************************** DO
		
		$filter = array();
		$filter[] = "do.do_branch_id=".mi($bid);
		$filter[] = "do.do_date=".ms($date);
		$filter[] = "do.approved=1 and do.active=1 and do.status=1 and do.checkout=1";
		$filter[] = "do.do_type = 'transfer'";
		$filter = "where ".join(' and ', $filter);
		
		// do
		$file_name = "INOMAST.dbf";
		$sql = "select do.id as do_id, do.do_no, do.do_date, do.total_amount, c.code as line_code,
				b.code as branch_code, b.description as branch_desc
				from do
				left join branch b on b.id = do.branch_id
				left join category_cache cc on cc.category_id = do.dept_id
				left join category c on c.id = cc.p1
				$filter
				order by do.id";
		//print $sql;
		$q1 = $con->sql_query($sql);
		
		if(file_exists("../../dbase/".$file_name)){
			unlink("../../dbase/".$file_name);
		}
		
		// database "header"
		$def = array(
			array("CINONO", "C", 20),
			array("DDATE", "C", 17),
			array("CREF2", "C", 20),
			array("CREF1", "C", 20),
			array("CSUPCODE", "C", 15),
			array("CSUPNAME", "C", 50),
			array("NTOTAL", "N", 7, 2),
			array("_NullFlags", "N", 4, 2)
		);

		// database creation
		if (!dbase_create("../../dbase/".$file_name, $def)) {
			$err[] = "Error, can't create the database $file_name";
		}

		if($con->sql_numrows($q1) > 0){
			// open in read-write mode
			$db = dbase_open("../../dbase/".$file_name, 2);

			if(!$err){
				if($db){
					while($r = $con->sql_fetchassoc($q1)){
						$do_no = $r['do_no'];
						$do_date = date("m/d/y", strtotime($r['do_date']))." 12:00 AM";
						$line_code = $r['line_code'];
						$bcode = $r['branch_code'];
						$bdesc = $r['branch_desc'];
						$doc_amt = $r['total_amount'];

						dbase_add_record($db, 
							array(
								$do_no,
								$do_date,
								"", 
								$line_code, 
								$bcode,
								$bdesc,
								$doc_amt,
								0
							)
						);
					}
					$has_info = true;
					dbase_close($db);
				}else{
					$err[] = "Can't open the database $file_name";
				}
			}
			
		}

		$con->sql_freeresult($q1);

		// do items
		$file_name = "INOTRAN.dbf";
		$sql = "select di.*, do.id as do_id, do.do_no, ifnull(dept.code, cat.code) as prefix_code, 
				SUBSTRING(si.sku_item_code FROM 3) as prefix_si_code, uom.fraction as uom_fraction, do.do_markup, 
				do.markup_type
				from do_items di
				left join do on do.id = di.do_id and do.branch_id = di.branch_id
				left join uom on uom.id = di.uom_id
				left join sku_items si on si.id = di.sku_item_id
				left join sku on sku.id = si.sku_id
				left join category_cache cc on cc.category_id = sku.category_id
				left join category dept on dept.id = cc.p2
				left join category cat on cat.id = cc.p3
				$filter
				order by di.do_id";

		$q1 = $con->sql_query($sql);

		if(file_exists("../../dbase/".$file_name)){
			unlink("../../dbase/".$file_name);
		}

		// database "header"
		$def = array(
			array("CINONO", "C", 20),
			array("CSTOCODE", "C", 13),
			array("NAMOUNT", "N", 15, 2),
			array("CSTOCKCODE", "C", 13),
			array("_NullFlags", "N", 4, 2)
		);
		
		// database creation
		if (!dbase_create("../../dbase/".$file_name, $def)) {
			$err[] = "Error, can't create the database $file_name";
		}
		
		if($con->sql_numrows($q1) > 0){
			// open in read-write mode
			$db = dbase_open("../../dbase/".$file_name, 2);
			
			if(!$err){
				if($db){
					while($r = $con->sql_fetchassoc($q1)){
						$amt_ctn = 0;
						$amt_pcs = 0;
						$row_amt = 0;

						$cost = $r['cost_price'];
						
						$r['do_markup_arr'] = explode("+", $r['do_markup']);
						if($r['markup_type']=='down'){
							$r['do_markup_arr'][0] *= -1;
							$r['do_markup_arr'][1] *= -1;
						}
						if($r['do_markup_arr'][0]){
							$cost = $cost * (1+($r['do_markup_arr'][0]/100));
						}
						if($r['do_markup_arr'][1]){
							$cost = $cost * (1+($r['do_markup_arr'][1]/100));
						}
						
						$amt_ctn = $cost*$r['ctn'];
						$amt_pcs = ($cost/$r['uom_fraction'])*$r['pcs'];
						
						$row_amt = round($amt_pcs+$amt_ctn,2);
						$do_no = $r['do_no'];
						if(!$r['prefix_code']) $r['prefix_code'] = "00";
						$prefix_code = $r['prefix_code']." ".$r['prefix_si_code'];
						$di_amt = $row_amt;
						$prefix_si_code = $r['prefix_si_code'];

						dbase_add_record($db, 
							array(
								$do_no,
								$prefix_code,
								$di_amt,
								$prefix_si_code,
								0
							)
						);
					}
					$has_info = true;
					dbase_close($db);
				}else{
					$err[] = "Can't open the database $file_name";
				}
			}
		}
		
		//************************************************************************** End of DO
		
		//************************************************************************** GRA
		
		$filter = array();
		$filter[] = "gra.branch_id=".mi($bid);
		$gra_date_from = $date." 00:00:00";
		$gra_date_to = $date." 23:59:59";
		$filter[] = "gra.return_timestamp between ".ms($gra_date_from)." and ".ms($gra_date_to);
		$filter[] = "gra.status=0 and gra.returned=1 and gra.type = 'Return'";
		$filter = "where ".join(' and ', $filter);
		
		// gra
		$file_name = "RETMAST.dbf";
		$sql = "select gra.id as gra_id, gra.return_timestamp as gra_date, (gra.amount+gra.extra_amount) as gra_amount,
				v.code as vd_code, v.description as vd_desc, gra.remark, b.report_prefix
				from gra
				left join vendor v on v.id = gra.vendor_id
				left join branch b on b.id = gra.branch_id
				$filter
				order by gra.id";
		//print $sql;
		$q1 = $con->sql_query($sql);
		
		if(file_exists("../../dbase/".$file_name)){
			unlink("../../dbase/".$file_name);
		}
		
		// database "header"
		$def = array(
			array("CRETNO", "C", 20),
			array("CDNNO", "C", 15),
			array("DDATE", "C", 17),
			array("CREF2", "C", 20),
			array("CREF1", "C", 20),
			array("CSUPCODE", "C", 15),
			array("CSUPNAME", "C", 50),
			array("NTOTAL", "N", 7, 2),
			array("_NullFlags", "N", 4, 2)
		);

		// database creation
		if (!dbase_create("../../dbase/".$file_name, $def)) {
			$err[] = "Error, can't create the database $file_name";
		}

		if($con->sql_numrows($q1) > 0){
			// open in read-write mode
			$db = dbase_open("../../dbase/".$file_name, 2);

			if(!$err){
				if($db){
					while($r = $con->sql_fetchassoc($q1)){
						$gra_no = $r['report_prefix'].str_pad($r['gra_id'], 5, "0", STR_PAD_LEFT);  
						$gra_date = date("m/d/y", strtotime($r['gra_date']))." 12:00 AM";
						$remark = $r['remark'];
						$vd_code = $r['vd_code'];
						$vd_desc = $r['vd_desc'];
						$doc_amt = $r['gra_amount'];

						dbase_add_record($db, 
							array(
								$gra_no,
								$gra_no,
								$gra_date,
								"",
								$remark, 
								$vd_code,
								$vd_desc,
								$doc_amt,
								0
							)
						);
					}
					$has_info = true;
					dbase_close($db);
				}else{
					$err[] = "Can't open the database $file_name";
				}
			}
			
		}

		$con->sql_freeresult($q1);

		// gra items
		$existed_extra_items = $grr_dept_list = array();
		$file_name = "RETTRAN.dbf";
		$sql = "select gra.id as gra_id, ifnull(dept.code, cat.code) as prefix_code, gra.extra, mst_c.code as dept_code,
				SUBSTRING(si.sku_item_code FROM 3) as prefix_si_code, gra.remark, gra.extra, mst_c.code as dept_code,
				b.report_prefix
				from gra_items gi
				left join gra on gra.id = gi.gra_id and gra.branch_id = gi.branch_id
				left join branch b on b.id = gra.branch_id
				left join sku_items si on si.id = gi.sku_item_id
				left join sku on sku.id = si.sku_id
				left join category_cache cc on cc.category_id = sku.category_id
				left join category dept on dept.id = cc.p2
				left join category cat on cat.id = cc.p3
				left join category_cache mst_cc on mst_cc.category_id = gra.dept_id
				left join category mst_c on mst_c.id = cc.p2
				$filter
				order by gi.gra_id";

		$q1 = $con->sql_query($sql);

		if(file_exists("../../dbase/".$file_name)){
			unlink("../../dbase/".$file_name);
		}
		
		// database "header"
		$def = array(
			array("CRETNO", "C", 20),
			array("CSTOCODE", "C", 13),
			array("NAMOUNT", "N", 15, 2),
			array("NAMOUNT", "N", 15, 2),
			array("CSTOCKCODE", "C", 13),
			array("_NullFlags", "N", 4, 2)
		);
		
		// database creation
		if (!dbase_create("../../dbase/".$file_name, $def)) {
			$err[] = "Error, can't create the database $file_name";
		}

		if($con->sql_numrows($q1) > 0){
			// open in read-write mode
			$db = dbase_open("../../dbase/".$file_name, 2);
			
			if(!$err){
				if($db){
					while($r = $con->sql_fetchassoc($q1)){
						if(!$r['prefix_code']) $r['prefix_code'] = "00";
						$gra_no = $r['report_prefix'].str_pad($r['gra_id'], 5, "0", STR_PAD_LEFT); 
						$prefix_code = $r['prefix_code']." ".$r['prefix_si_code'];
						$gi_amt = mf($r['cost']*$r['qty']);
						$prefix_si_code = $r['prefix_si_code'];
						$extra = unserialize($r['extra']);
					
						if($extra && !$existed_extra_items[$r['gra_id']]){ // found got items not in ARMS
							if(!$grr_dept_list[$r['gra_id']]){ // get prefix code from grn
								$grn_info = array();
								if(preg_match("/^Rejected from GRN/", $r['remark'])){
									$grn_id = mi(preg_replace("/^Rejected from GRN/", "", $r['remark']));
									
									$q2 = $con->sql_query("select *, c.code as prefix_code
														   from grn 
														   left join category c on c.id = grn.department_id
														   where id = ".mi($grn_id)." and branch_id = ".mi($bid));
									$grn_info = $con->sql_fetchassoc($q2);
									$con->sql_freeresult($q2);
									
								}
								
								// if not found from GRN, then pickup from gra
								if(!$grn_info['prefix_code']) $grn_info['prefix_code'] = $grn_info['dept_code'];
								
								// if it is still not found, then set it as "00"
								if(!$grn_info['prefix_code']) $grn_info['prefix_code'] = "00";

								$grr_dept_list[$r['gra_id']] = $grn_info['prefix_code'];
							}
							
							$extra_prefix_ccode = $grr_dept_list[$r['gra_id']];
							
							foreach($extra['code'] as $idx=>$code){
								if(preg_match("/^28/", $code) && strlen($code) == 12){
									$extra_prefix_si_code = preg_replace("/^28/", "", $code);
								}else{
									$extra_prefix_si_code = $code;
								}
								$extra_cost = $gra['extra']['cost'][$idx];
								$extra_qty = $gra['extra']['qty'][$idx];
								
								$extra_prefix_code = $extra_prefix_ccode." ".$extra_prefix_si_code;
								$extra_gi_amt = mf($extra_cost*$extra_qty);

								dbase_add_record($db, 
									array(
										$gra_no,
										$extra_prefix_code,
										$extra_gi_amt,
										$extra_prefix_si_code,
										0
									)
								);
							}
							
							$existed_extra_items[$r['gra_id']] = true;
						}
						
						dbase_add_record($db, 
							array(
								$gra_no,
								$prefix_code,
								$gi_amt,
								$prefix_si_code,
								0
							)
						);
					}
					$has_info = true;
					dbase_close($db);
				}else{
					$err[] = "Can't open the database $file_name";
				}
			}
		}
		
		//************************************************************************** End of GRA
		
		//****************************************************************************** GRN with DN
		
		$filter = array();
		$filter[] = "grr.branch_id=".mi($bid);
		$filter[] = "grr.rcv_date=".ms($date);
		$filter[] = "grr.active=1 and grn.active=1 and grn.approved=1 and grn.status=1 and grn.buyer_adjustment != 0";
		$filter[] = "gri.type in ('INVOICE','OTHER')";
		$filter = "where ".join(' and ', $filter);
		
		// grn's DN
		$file_name = "GRDNMAST.dbf";
		$sql = "select grn.id as grn_id, grr.id as grr_id,v.code as vendorcode, v.description as vendor_desc, 
				gri.doc_no, gri.type, grr.rcv_date, v.term, gri.amount, b.report_prefix
				from grr_items gri 
				left join grr on gri.grr_id = grr.id and gri.branch_id = grr.branch_id
				left join grn on grn.grr_id = grr.id and grn.branch_id =grr.branch_id
				left join vendor v on grr.vendor_id = v.id
				left join branch b on b.id = grr.branch_id
				$filter
				order by grn.id";

		$q1 = $con->sql_query($sql);

		if(file_exists("../../dbase/".$file_name)){
			unlink("../../dbase/".$file_name);
		}
		
		// database "header"
		$def = array(
			array("CGRDNNO", "C", 20),
			array("CDNNO", "C", 69),
			array("DDATE", "C", 30),
			array("CREF2", "C", 20),
			array("CREF1", "C", 20),
			array("CSUPCODE", "C", 15),
			array("CSUPNAME", "C", 50),
			array("NTOTAL", "N", 15, 2),
			array("_NullFlags", "N", 4, 2)
		);

		// database creation
		if (!dbase_create("../../dbase/".$file_name, $def)) {
			$err[] = "Error, can't create the database $file_name";
		}

		if($con->sql_numrows($q1) > 0){
			// open in read-write mode
			$db = dbase_open("../../dbase/".$file_name, 2);

			if(!$err){
				if($db){
					while($r = $con->sql_fetchassoc($q1)){
						$grn_no = $r['report_prefix'].str_pad($r['grn_id'], 5, "0", STR_PAD_LEFT);
						$rcv_date = date("m/d/y", strtotime($r['rcv_date']))." 12:00 AM";
						$grr_no = $r['report_prefix'].str_pad($r['grr_id'], 5, "0", STR_PAD_LEFT);
						//$doc_no = $r['doc_no']." ".$r['line_code'];
						$vd_code = $r['vendorcode'];
						$vd_name = $r['vendor_desc'];
						$doc_amt = $r['amount'];

						dbase_add_record($db, 
							array(
								$grn_no,
								$grn_no,
								$rcv_date,
								"",
								$grr_no,
								$vd_code,
								$vd_name,
								$doc_amt,
								0
							)
						);
					}
					$has_info = true;
					dbase_close($db);
				}else{
					$err[] = "Can't open the database $file_name";
				}
			}
		}

		$con->sql_freeresult($q1);

		// grn items DN
		$file_name = "GRDNTRAN.dbf";
		$sql = "select grn.id as grn_id, grr.id as grr_id,
				((if(gi.acc_ctn is null and gi.acc_pcs is null, (gi.ctn * rcv_uom.fraction) + gi.pcs, (gi.acc_ctn * rcv_uom.fraction) + gi.acc_pcs) - (ifnull(gi.return_ctn * rcv_uom.fraction,0) + ifnull(gi.return_pcs,0))) * if(gi.acc_cost is null, gi.cost, gi.acc_cost) / rcv_uom.fraction +
				ifnull(((if(gi.acc_ctn is null and gi.acc_pcs is null, (gi.ctn * rcv_uom.fraction) + gi.pcs, (gi.acc_ctn *rcv_uom.fraction) + gi.acc_pcs) - (ifnull(gi.return_ctn * rcv_uom.fraction,0) + ifnull(gi.return_pcs,0))) * if(gi.acc_cost is null, gi.cost, gi.acc_cost) / rcv_uom.fraction) *
				(grn.grn_tax/100), 0)) as gi_amount, b.report_prefix,
				ifnull(dept.code, cat.code) as prefix_code, SUBSTRING(si.sku_item_code FROM 3) as prefix_si_code
				from grn_items gi
				left join grn on grn.id = gi.grn_id and grn.branch_id = gi.branch_id
				left join grr on grn.id = grn.grr_id and grr.branch_id = grn.branch_id
				left join grr_items gri on gri.grr_id = grr.id and gri.branch_id = grr.branch_id
				left join sku_items si on si.id = gi.sku_item_id
				left join sku on sku.id = si.sku_id
				left join uom sell_uom on gi.selling_uom_id=sell_uom.id
				left join uom rcv_uom on gi.uom_id=rcv_uom.id
				left join category_cache cc on cc.category_id = sku.category_id
				left join category dept on dept.id = cc.p2
				left join category cat on cat.id = cc.p3
				left join branch b on b.id = grn.branch_id
				$filter
				order by gi.grn_id";
		//print $sql;
		$q1 = $con->sql_query($sql);

		if(file_exists("../../dbase/".$file_name)){
			unlink("../../dbase/".$file_name);
		}
		
		// database "header"
		$def = array(
			array("CGRDNNO", "C", 20),
			array("CSTOCODE", "C", 13),
			array("NAMOUNT", "N", 15, 2),
			array("CSTOCKCODE", "C", 13),
			array("_NullFlags", "N", 4, 2)
		);
		
		// database creation
		if (!dbase_create("../../dbase/".$file_name, $def)) {
			$err[] = "Error, can't create the database $file_name";
		}

		if($con->sql_numrows($q1) > 0){
			// open in read-write mode
			$db = dbase_open("../../dbase/".$file_name, 2);
			
			if(!$err){
				if($db){
					while($r = $con->sql_fetchassoc($q1)){
						$grn_no = $r['report_prefix'].str_pad($r['grn_id'], 5, "0", STR_PAD_LEFT);  
						if(!$r['prefix_code']) $r['prefix_code'] = "00";
						$prefix_code = $r['prefix_code']." ".$r['prefix_si_code'];
						$gi_amt = $r['gi_amount'];
						$prefix_si_code = $r['prefix_si_code'];

						dbase_add_record($db, 
							array(
								$grn_no,
								$prefix_code,
								$gi_amt,
								$prefix_si_code,
								0
							)
						);
					}
					$has_info = true;
					dbase_close($db);
				}else{
					$err[] = "Can't open the database $file_name";
				}
			}
		}

		$con->sql_freeresult($q1);
		
		//************************************************************************** End of GRN with DN
		
		if($err){
			//if(!$has_info) $err[] = "No record found";
			$smarty->assign("err", $err);
			$this->display("ngiukee/ubs_export.tpl");
		}else{
			if(file_exists("../../dbase/ubs_export.zip")){
				unlink("../../dbase/ubs_export.zip");
			}
			//header('Content-Type: application/msexcel');
			//header('Content-Disposition: attachment;filename='.$file_name);
			//readfile("../../dbase/$file_name");
			exec("cd ../../dbase; zip -9 ubs_export.zip *.dbf");
			header("Content-type: application/zip");
			header("Content-Disposition: attachment; filename=ubs_export.zip");
			readfile("../../dbase/ubs_export.zip");
		}
	}
}

$UBS_EXPORT = new UBS_EXPORT('UBS Export');
?>

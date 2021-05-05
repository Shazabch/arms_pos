<?php
/*
05/25/2010 05:36:34 PM  yinsee
- new script
- format: mcode, description, unit price, "N","0"
- for mcode with 5 (upwell use 6) character

5/31/2010 12:11:11 PM yinsee
- make output format CRLF

4/26/2011 2:56:11 PM  Justin
- Created different output format which includes default (current format), mettle toledo, digi and TM A series.

4/29/2011 06:01:02 PM Justin
- Amended the TM A Series format.

5/3/2011 11:25:43 AM Justin
- Amended the TM A Series base on user feedback.
  > Not to display header while export.
  > Removed the ' from MCode and SKU Item Code.
  > Changed the MCode from 7 into 5 digits (does not add "00" in this case).
  > Changed the output extension from CSV become TXT.
  > Modified the SKU description to display up to 24 characters only.
  
5/6/2011 04:59:43 PM Justin
- Modified selling price will not multiply with 10 when scale type is "By Count".
- Fixed the bugs where the by count should be "0" and by weight should be "1".

5/9/2011 12:23:41 PM Andy
- Fix a bugs which cause the export always use master selling price.

6/20/2011 2:35:51 PM Andy
- Change scale type to become default SKU property and not depends to fresh market, but disable for consignment mode.
- Change "Export Weighing Scale Items" module to only export active SKU.

6/24/2011 3:17:23 PM Andy
- Make all branch default sort by sequence, code.

6/27/2011 10:54:42 AM Justin
- Applied the new format of export for Mettle Toledo.
- Fixed the wrong indicator for Price Type of Mettle Toledo.
- Changed back the existing indicator for Price Type since the previous format is the valid one.

7/28/2011 11:18:32 AM Justin
- Fixed the weight type display wrong.

8/11/2011 11:45:11 AM Justin
- Found some of the customers do not have "SSH_CONNECTION" from putty, added another checking "SHELL" to differentiate between access from backend or cronjob.
- Added config to prefix some of the values of columns from default format.
- Added new format "Mettle Toledo PKT" (format is similar as default).

9/5/2011 2:07:32 PM Justin
- Added new feature that able to generate (cron job) multiple branches instead of one branch only.

2/17/2012 9:46:36 AM Andy
- Add new export format "Ishida".

6/7/2012 5:24:23 PM Justin
- Fixed bugs for TM_Series that setting wrong scale type while import to CSV.

7/2/2012 3:48:34 PM Justin
- Added new vendor filter.
- Added to accept vendor ID and code filter.
- Enhanced to accept scale type set by SKU or item.

7/3/2012 10:38:34 AM Justin
- Fixed bug of never filter off those sku item inheriting parent SKU with "No" scale type.

7/10/2012 2:28:34 PM Justin
- Modified ishida format for following changes.
  * OpenPrice => refer to current SKU item open price status, 0 => no and 2 => yes.
  * UpperLimit => from 15000 become 300000.
  * PosFlag => prefix it as 22.
  
5/14/2013 4:14 PM Justin
- Added new format "Ishida 2" as default format.

6/7/2013 9:26 AM Justin
- Added CH as 23 POSFlag.

6/11/2013 11:26 AM Justin
- Enhanced format "Ishida" to use config for customize POSFlag.

6/26/2013 10:24 AM Justin
- Enhanced to have new format "Mettle Toledo (China)".
- Bug fixed on format "Ishida" that does not refer to default posflag from config and posflag does nto refer to config for other customers.

3/21/2015 Yinsee
- add ExMessage 2 tax code type (for segi used)
- update selling with gst include tax

6/26/2015 11:34 AM Andy
- Enhanced to prevent duplicate process run in the terminal.

10/21/2015 4:21 PM Andy
- Enhanced to put in terminal sample.

11/31/2015 2:00 PM Qiu Ying
- Add to accept multiple branch code.
- Add next params to exclude branch
- Add new weightscale format (ishida_3) -- hide by Andy

5/30/2017 17:00 Qiu Ying
- Added new format "Digi SM-500".

10/30/2017 4:30 PM Andy
- Change to clone last line for ishida (Segi branch BAN).

3/15/2018 3:05 PM HockLee
- Add new export format "BC11 800" and "BC11 800 v2".

3/23/2018 10:13 AM Andy
- Fixed if all items have no scale type, bc11 800 and bc11 800 v2 will display blank page.

3/26/2018 5:29 PM Andy
- Change Digi SM 500 column selling price to always have decimal point, this will make it compatible to Digi SM 5300.

8/2/2018 9:59 AM Andy
- Remove junk variable "GST_ENABLED" and fixed selling price zero problem when server no turn on gst config.

6/10/2019 10:06 AM William
- Added new output format "Rongta".

6/28/2019 4:27 PM William
- Added new output format "Digi SM320 with Scale Type".

7/29/2019 2:03 PM William
- Fixed bug system get all result with "Non-weigh".

2/25/2020 3:05 PM William
- Enhanced to change connection "$con" to use report server connection "$con_multi".

4/10/2020 5:25 PM Andy
- Enhanced ishida and rongta to check config.weight_scale_use_promo_price.

6/24/2020 3:34 PM Andy
- Change the checking of terminal.
- Fixed to removed warning message under terminal.

02/16/2021 2:26 PM Rayleen
- Added new output format "TM A Barcode".

02/24/2021 4:44 PM Rayleen
- Tma_barcode export = change "decription" to "receipt_description"
*/ 

/*
terminal sample
php admin.weightcode_export.php export gurun default default_db
php admin.weightcode_export.php export all ishida_2 ish_db

Advanced (multiple branch and exclude branch)
php admin.weightcode_export.php export gurun,dev ishida_3 ish_db -exclude_branch=dev,test

*/

// Check if it is from putty / terminal
if(!(!isset($_SERVER['SSH_CONNECTION']) && !isset($_SERVER['SHELL']))){
  	define("TERMINAL",1);
}

include("include/common.php");

if(!defined('TERMINAL')){	// from backend
	if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
	if (!privilege('SKU_EXPORT')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'SKU_EXPORT', BRANCH_CODE), "/index.php");

	$smarty->assign("PAGE_TITLE", "Export Weighing Scale Items");
  	$a = $_REQUEST['a'];
}else{ // is from putty/crontab
	$arg = $_SERVER['argv'];
	$a = $arg[1];
	
	// check if myself is running, exit if yes
	if (!preg_match('/(root|arms|admin|wsatp)/', `whoami`) || $config['arms_go_modules']){
		@exec('ps x | grep -v grep | grep -v /bin/sh | grep '.basename(__FILE__), $exec);
		print "Checking other process using ps x\n";
	}else{
		@exec('ps ax | grep -v grep | grep -v /bin/sh | grep '.basename(__FILE__), $exec);
		print "Checking other process using ps ax\n";
	}
		
	if (count($exec)>1)
	{
		print date("[H:i:s m.d.y]")." Another process is already running\n";
		print_r($exec);
		exit;
	}
}

if(!$con_multi)	$con_multi = $appCore->reportManager->connectReportServer();

if (isset($a))
{
	switch ($a)
	{
		case 'export':
			do_export();
			exit;
	}
}

if (BRANCH_CODE == 'HQ')
{
	$con_multi->sql_query("select id, code from branch where code <> 'HQ' order by sequence, code");
	$smarty->assign("branch", $con_multi->sql_fetchrowset());
	$con_multi->sql_freeresult();
}

$con_multi->sql_query("select * from vendor where active=1 order by code, description");
$smarty->assign("vendors", $con_multi->sql_fetchrowset());
$con_multi->sql_freeresult();

$smarty->display("admin.weightcode_export.tpl");
exit;

// php admin.weightcode_export.php export all ishida2 /www/arms/csv
function do_export()
{
	global $config, $con, $arg, $con_multi;
	
	$contents = array();
	$wlen = $config['sku_weight_code_length'];
	$format_type = "CSV";
	$curr_min = mi(date("i"));
	
	if (!$wlen) $wlen = 5; // default is 5
	if(!defined('TERMINAL')){
		$branch_id_list[$_REQUEST['branch_id']] = strtolower(get_branch_code($_REQUEST['branch_id']));
		$report_format = $_REQUEST['report_format'];
	}else{
		$branch_code = $arg[2];
		if($branch_code != "all"){
			$branch_code_list = explode(",",$branch_code);	
				
			for ($i = 0; $i < count($branch_code_list); $i++){
				$branch_code_list[$i] = "code = '$branch_code_list[$i]'";
			}
			$b_filter = " and (". join(" or ", $branch_code_list) . ")";
		}
		
		if (strpos($arg[5], "-exclude_branch=") == 0){
			list($dummy, $branch_code_list2) = explode("=", $arg[5], 2);
			$branch_code_list2 = explode(",", $branch_code_list2);
			for ($i = 0; $i < count($branch_code_list2); $i++){
				$branch_code_list2[$i] = "code <> '$branch_code_list2[$i]'";
			}
			$b_filter .= " and (". join(" and ", $branch_code_list2) . ")";
		}
		
		$con_multi->sql_query("select id, code from branch where active=1 and code <> 'HQ' $b_filter order by sequence,code");
		
		while($r = $con_multi->sql_fetchrow()){
			$branch_id_list[$r['id']] = strtolower($r['code']);
		}
		$con_multi->sql_freeresult();

		$report_format = $arg[3];
		if($arg[4]) $dictory_name = trim($arg[4]);
		
		//if($branch_code != "all" && !$dictory_name) die("\nPlease provide file location.");
	}

	if(count($branch_id_list) == 0)	die("\nInvalid Branch!");
	if(!$report_format) $report_format = "default"; // present default report format if found not set
	
	$filter[] = "length(si.mcode) = $wlen";
	$filter[] = "si.active=1";
	
	if($report_format != "default"){
		$filter[] = "(sku.scale_type>0 || si.scale_type != 0)";
	}

	if($_REQUEST['vendor_id']) $filter[] = "v.id = ".mi($_REQUEST['vendor_id']);
	elseif($_REQUEST['vendor_code']) $filter[] = "v.code = ".ms($_REQUEST['vendor_code']);

	if(file_exists("mcode_22.csv")){
		$f = fopen("mcode_22.csv","rt");
		$line = fgetcsv($f);

		while($r = fgetcsv($f)){
			$tmp_mcode = trim($r[0]);
			$mcode_22[$tmp_mcode] = $tmp_mcode;
		}
		fclose($f);
	}
	
	foreach($branch_id_list as $branch_id=>$branch_code){
		$contents = array();
		$branch_is_under_gst = 0;
		
		// Check branch got GST
		if($config['enable_gst']){
			$prms = array();
			$prms['branch_id'] = $branch_id;
			$prms['date'] = date("Y-m-d");
			$branch_is_under_gst = check_gst_status($prms);
		}
		
		if($branch_is_under_gst){
			$str_selling = "round(if(if(if(si.inclusive_tax='inherit', sku.mst_inclusive_tax, si.inclusive_tax)='inherit', cc.inclusive_tax, if(si.inclusive_tax='inherit', sku.mst_inclusive_tax, si.inclusive_tax))='yes', ifnull(sip.price, si.selling_price), ifnull(sip.price, si.selling_price)*(100+output_gst.rate)/100),2) as selling";
		}else{
			$str_selling = "if(sip.price is null, si.selling_price, sip.price) as selling";
		}
		
		$sql = "select si.id as sid, si.sku_item_code, si.mcode, si.description, si.receipt_description, $str_selling , output_gst.id as output_gst_id, output_gst.rate as output_gst_rate,
				sku.scale_type as mst_scale_type, si.scale_type as dtl_scale_type, si.open_price
				from sku_items si
				left join sku_items_price sip on sip.sku_item_id = si.id and sip.branch_id = ".mi($branch_id)."
				left join sku on sku.id = si.sku_id
				left join category_cache cc on cc.category_id=sku.category_id
				left join gst output_gst on output_gst.id=if(if(si.output_tax<0,sku.mst_output_tax,si.output_tax)<0,cc.output_tax,if(si.output_tax<0,sku.mst_output_tax,si.output_tax))
				left join vendor v on v.id = sku.vendor_id
				where ".join(" and ", $filter)."
				order by si.mcode";
				
		$query1 = $con_multi->sql_query($sql) or die(mysql_error());

		if($con_multi->sql_numrows($query1) > 0){
			if($report_format == "default"){
				$contents[] = "PLU NO,Item Number,PLU Name,PLU Second Name,group Number,Unit Price,Second Price,Unit Price Rule,Price Type,Tare Number,Quantity,Fix Weight(kg):,Label Number,Second Label Number,Sell by Days,Best Before Days,Print Package Date,Print Sell by Date,Print Best Before Date,New Price Enable,Discount Enable,ET number,NF Number,DepartmentNO\n";

				while($r=$con_multi->sql_fetchrow($query1)){
					//print "$r[mcode],$r[receipt_description],$r[selling]"
					//$r['mcode'] = str_replace('"','""',$r['mcode']);
					//$r['receipt_description'] = str_replace('"','""',$r['receipt_description']);
					$r['selling'] = round($r['selling'],2);
					$contents[] = "$r[mcode],$r[sku_item_code],$r[receipt_description],,0,$r[selling],0.00,0,1,0,0,0.0000,".mi($config['weight_export_prefix_value']['label_no']).",0,0,0,".mi($config['weight_export_prefix_value']['prt_pkg_date']).",0,0,0,0,0,0,0\r\n";
				}
			}elseif($report_format == "mettle_toledo"){
				$contents[] = "PLU NO,Item Number,PLU Name,PLU Second Name,Group Number,Unit Price,Second Price,Unit Price Rule,Price Type\n";
				
				while($r=$con_multi->sql_fetchrow($query1)){
					if($r['dtl_scale_type'] == -1) $r['scale_type'] = $r['mst_scale_type'];// inherit master
					else $r['scale_type'] = $r['dtl_scale_type'];// refer to own scale type
					
					if(!$r['scale_type']) continue;
				
					$r['description'] = substr($r['description'],0,24);
					$r['selling'] = round($r['selling'],2);
					if($r['scale_type'] == 2) $r['scale_type'] = 0; // it is by weight
					$contents[] = "$r[mcode],$r[mcode],$r[description],$r[sku_item_code],,$r[selling],,,$r[scale_type]\r\n";
				}
			}elseif($report_format == "digi"){
				$contents[] = "PLU Code,Unit Price,Link Dept,Label Format,Tare,Item Code,Flag,RHS,Barcode Format,Status,Quantity,Symbol,Use By Date,Start Date,Start Time,End Date,End Time,Discount Price,Discount Type,Markdown Type,Commodity 1,Commodity 2,Ingredient 1,Ingredient ,Ingredient 3,Ingredient 4,Ingredient 5,Ingredient 6,Ingredient 7,Ingredient 8\n";
				
				while($r=$con_multi->sql_fetchrow($query1)){
					if($r['dtl_scale_type'] == -1) $r['scale_type'] = $r['mst_scale_type'];// inherit master
					else $r['scale_type'] = $r['dtl_scale_type'];// refer to own scale type
					
					if(!$r['scale_type']) continue;

					if($r['scale_type'] == 2) $r['weight_type'] = "By Weight";
					else $r['weight_type'] = "By Count";
				
					$r['selling'] = round($r['selling'],2);
					$contents[] = "$r[mcode],$r[selling],,,,$r[mcode],,,,$r[weight_type],,,,,,,,,,,$r[description],$r[sku_item_code],,,,,,,,\r\n";
				}
			}elseif($report_format == "tma_series"){
				//$contents[] = "PLU NO,Name,Code,Price,Mode,Shelflife,Tare,Label NO,RemarkA,Shop NO\n";
				$row_count=1;
				$format_type = "txt";

				while($r=$con_multi->sql_fetchrow($query1)){
					//$r['mcode'] = sprintf("%07d",$r['mcode']);
					if($r['dtl_scale_type'] == -1) $r['scale_type'] = $r['mst_scale_type'];// inherit master
					else $r['scale_type'] = $r['dtl_scale_type'];// refer to own scale type
					
					if(!$r['scale_type']) continue;
					
					$r['description'] = substr($r['description'],0,24);
					if($r['scale_type'] == 1){
						$r['scale_type'] = 1; // it is by fixed price
						$r['selling'] = round($r['selling'],2);
					}else{
						$r['scale_type'] = 0; // it is by weight
						$r['selling'] = round($r['selling'],2) * 10;
					}
					$contents[] = "$row_count,$r[description],$r[mcode],$r[selling],$r[scale_type],,,,$r[sku_item_code],$row_count,29\r\n";
					$row_count++;
				}
			}elseif($report_format == "mettle_toledo_pkt"){ // special make for PKT
				$contents[] = "PLU NO,Item Number,PLU Name,PLU Second Name,group Number,Unit Price,Second Price,Unit Price Rule,Price Type,Tare Number,Quantity,Fix Weight(kg):,Label Number,Second Label Number,Sell by Days,Best Before Days,Print Package Date,Print Sell by Date,Print Best Before Date,New Price Enable,Discount Enable,ET number,NF Number,DepartmentNO\n";

				while($r=$con_multi->sql_fetchrow($query1)){
					if($r['dtl_scale_type'] == -1) $r['scale_type'] = $r['mst_scale_type'];// inherit master
					else $r['scale_type'] = $r['dtl_scale_type'];// refer to own scale type
					
					if(!$r['scale_type']) continue;

					$r['selling'] = round($r['selling'],2);
					if($r['scale_type'] == 2) $r['scale_type'] = 0; // it is by weight
					$contents[] = "$r[mcode],$r[mcode],$r[receipt_description],$r[sku_item_code],0,$r[selling],0.00,0,$r[scale_type],0,0,0.0000,2,0,0,0,2,0,0,0,0,0,,0\r\n";
				}
			}elseif($report_format == 'ishida'){
				if($config['weight_scale_use_promo_price']){
					require_once("include/price_checker.include.php");
				}
				$uc_bc = strtoupper($branch_code);
				$need_check_csv = false;
				if($config['customize_weight_scale_posflag']){
					$posflag = $config['customize_weight_scale_posflag']['by_branch'][$uc_bc];
					if(!$posflag) $posflag = $config['customize_weight_scale_posflag']['default'];
					if($config['customize_weight_scale_posflag']['need_check_csv'][$uc_bc]) $need_check_csv = true;
				}else{
					if($branch_code == "tp" || $branch_code == "bc" || $branch_code == "su" || $branch_code == "ba" || $branch_code == "pu" || $branch_code == "ch" || $branch_code == "u8"){
						$posflag = 23;
					}else $posflag = 22;
				}
			
				$contents[] = "Plu_No,SalesMode,UnitPrice,Weight,PackQuant,Tare,DateFlag,ShelfLife,ExMessage1,PosCode,ShelfLifeType,bLabelType,UseByType,PointFlag,DateCalculation,WeightUnit,FreeMsg13,FreeMsg14,FreeMsg12,CurrencyType,TaxMode,SchemeNo,TraceFlag,LotNo,FreeMsg7,FreeMsg6,CookingNo,TargetFlag,PointType,TargetLimit,Fee,FeeFlag,FreeMsg15,FreeMsg9,FreeMsg8,FreeMsg11,Points,FreeMsg10,PopMsg,ExMessage2,ExMessage3,Coupon,Logo1,Logo2,BarCodeNum,ExpTime,PackTime,OpenPrice,PosSelect,DeptCode,GroupCode,MarkPrice,MarkFlag,TimeFlag,ExpTimeFlag,Unit,BestBeforeFlag,BestBefore,ItemCode,RegCode,LabelFormatNo1,CostPrice,UpperLimit,LowerLimit,Posflag,ImageNo,Logo3,NutritionFlag,SecondPrice,SafeHandlingNo,ForcedTare,No2PrintFlag,WrappingMode,WrappingSpeed,Volume,LabellingPos,Rotate,TotalsFlag,TopPrintFlag,BottomPrintFlag,EyeCatchPrintFlag,TopBarFlag,PrintFlag,TrayFlag,PopFlag,TopTypeFlag,CostPriceFlag,ServeHeight,LabellingMode,Wrapping,PosFlagSelect,SecondBotPrintFlag,FeedRate,TaiwanKgPrint,StockFlag,TaxNo,Random,SalesLife,PropTare,StoreId,LabelFormatNo2,No2Tare,Ingredients,TrayNo,StoreTemp,StorageInstruct,Origin,Comment,FreeMsg1,FreeMsg2,FreeMsg3,FreeMsg4,FreeMsg5,PackType,SafeHandlingFlag,Line1,Line2\n";
				
				while($r=$con_multi->sql_fetchassoc($query1)){
					$tmcode = trim($r['mcode']);
					if($mcode_22[$tmcode]){
						if($config['customize_weight_scale_posflag']){ // set by config
							if($need_check_csv || !$config['customize_weight_scale_posflag']['by_branch'][$uc_bc]) $posflag = 22;
							else $posflag = 23;
						}else{ // follow default harcoded
							if($branch_code != "ba" && $branch_code != "pu" && $branch_code != "ch" && $branch_code != "u8") $posflag = 22;
						}
					}else{
						if(!$config['customize_weight_scale_posflag']['by_branch'][$uc_bc]){
							if($config['customize_weight_scale_posflag']['default']) $posflag = $config['customize_weight_scale_posflag']['default'];
							else $posflag = 22;
						}else $posflag = $config['customize_weight_scale_posflag']['by_branch'][$uc_bc];
					}

					if($r['dtl_scale_type'] == -1) $r['scale_type'] = $r['mst_scale_type'];// inherit master
					else $r['scale_type'] = $r['dtl_scale_type'];// refer to own scale type

					if(!$r['scale_type']) continue;
					
					// Check if weighing scale need to use promotion price
					if($config['weight_scale_use_promo_price']){
						$params = array();
						$params['branch_id'] = $branch_id;
						$params['sku_item_id'] = $r['sid'];
						$sku_price_check = check_price($params);
						if($sku_price_check){
							if($sku_price_check['non_member_price']>0 && $sku_price_check['non_member_price']<$r['selling']){
								// Use Promotion Price
								$r['selling'] = round($sku_price_check['non_member_price'], 2);
							}
						}
					}
					
					$r['selling'] = round($r['selling'],2) * 100;
					if($r['scale_type'] == 2) $r['scale_type'] = 0; // it is by weight
					
					// open price
					if($r['open_price'] == 1) $open_price = 2;
					else $open_price = 0;
					
					// description cut
					if(strlen($r['description']) > 14){
						$desc1 = substr($r['description'], 0, 14);
						$desc2 = substr($r['description'], 14, 18);
					}else{
						$desc1 = $r['description'];
						$desc2 = "";
					}
					
					$selling = $r['selling'];
					if (!$branch_is_under_gst) {
						$ExMessage2 = 0;
					}
					else
					{
						$ExMessage2 = $r['output_gst_id'];
					}
					$line_str = ''.$r['mcode'].','.$r['scale_type'].','.$selling.',0,1,0,0,1000,0,'.$r['mcode'].',0,0,0,0,0,0,0,0,0,0,0,0,0,"0",0,0,0,1,0,0,0,3,0,0,0,0,0,0,0,'.$ExMessage2.',0,0,0,0,0,0,0,'.$open_price.',0,0,0,0,0,0,0,0,0,0,'.$r['mcode'].',0,0,0,300000,0,'.$posflag.',0,0,0,0,0,2,0,1,0,0,0,0,0,0,0,0,0,1,2,0,0,0,0,1,0,0,2,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,"0",1,"'.$desc1."\",\"".$desc2."\"\r\n";
					$contents[] = $line_str;
				}
				if(isset($config['weigh_scale_clone_last_row'][$uc_bc]) && isset($contents) && $contents && isset($line_str) && ($curr_min==0 || $curr_min%20==0)){
					$contents[] = $line_str;
				}
			}elseif($report_format == 'ishida_2'){
				// load posflag for every branch
				$uc_bc = strtoupper($branch_code);
				$posflag = 29; // default posflag
				if($config['customize_weight_scale_posflag']){ // found got customized posflag
					$posflag = $config['customize_weight_scale_posflag']['by_branch'][$uc_bc];
					if(!$posflag) $posflag = $config['customize_weight_scale_posflag']['default'];
				}
				$contents[] = "Plu_No,SalesMode,UnitPrice,Weight,PackQuant,Tare,DateFlag,ShelfLife,ExMessage1,PosCode,ShelfLifeType,bLabelType,UseByType,PointFlag,DateCalculation,WeightUnit,FreeMsg13,FreeMsg14,FreeMsg12,CurrencyType,TaxMode,SchemeNo,TraceFlag,LotNo,FreeMsg7,FreeMsg6,CookingNo,TargetFlag,PointType,TargetLimit,Fee,FeeFlag,FreeMsg15,FreeMsg9,FreeMsg8,FreeMsg11,Points,FreeMsg10,PopMsg,ExMessage2,ExMessage3,Coupon,Logo1,Logo2,BarCodeNum,ExpTime,PackTime,OpenPrice,PosSelect,DeptCode,GroupCode,MarkPrice,MarkFlag,TimeFlag,ExpTimeFlag,Unit,BestBeforeFlag,BestBefore,ItemCode,RegCode,LabelFormatNo1,CostPrice,UpperLimit,LowerLimit,Posflag,ImageNo,Logo3,NutritionFlag,SecondPrice,SafeHandlingNo,ForcedTare,No2PrintFlag,WrappingMode,WrappingSpeed,Volume,LabellingPos,Rotate,TotalsFlag,TopPrintFlag,BottomPrintFlag,EyeCatchPrintFlag,TopBarFlag,PrintFlag,TrayFlag,PopFlag,TopTypeFlag,CostPriceFlag,ServeHeight,LabellingMode,Wrapping,PosFlagSelect,SecondBotPrintFlag,FeedRate,TaiwanKgPrint,StockFlag,TaxNo,Random,SalesLife,PropTare,StoreId,LabelFormatNo2,No2Tare,Ingredients,TrayNo,StoreTemp,StorageInstruct,Origin,Comment,FreeMsg1,FreeMsg2,FreeMsg3,FreeMsg4,FreeMsg5,PackType,SafeHandlingFlag,Line1,Line2\n";
				
				while($r=$con_multi->sql_fetchassoc($query1)){
					if($r['dtl_scale_type'] == -1) $r['scale_type'] = $r['mst_scale_type'];// inherit master
					else $r['scale_type'] = $r['dtl_scale_type'];// refer to own scale type

					if(!$r['scale_type']) continue;
					
					$r['selling'] = round($r['selling'],2) * 100;
					if($r['scale_type'] == 2) $r['scale_type'] = 0; // it is by weight
					
					// open price
					if($r['open_price'] == 1) $open_price = 2;
					else $open_price = 0;
					
					// description cut
					if(strlen($r['description']) > 14){
						$desc1 = substr($r['description'], 0, 14);
						$desc2 = substr($r['description'], 14, 18);
					}else{
						$desc1 = $r['description'];
						$desc2 = "";
					}
					
					$selling = $r['selling'];
					if (!$branch_is_under_gst) {
						$ExMessage2 = 0;
					} else {
						$ExMessage2 = $r['output_gst_id'];
					}
					$contents[] = ''.$r['mcode'].','.$r['scale_type'].','.$selling.',0,1,0,0,1000,0,'.$r['mcode'].',0,0,0,0,0,0,0,0,0,0,0,0,0,"0",0,0,0,1,0,0,0,3,0,0,0,0,0,0,0,'.$ExMessage2.',0,0,0,0,0,0,0,'.$open_price.',0,0,0,0,0,0,0,0,0,0,'.$r['mcode'].',0,0,0,300000,0,'.$posflag.',0,0,0,0,0,2,0,1,0,0,0,0,0,0,0,0,0,1,2,0,0,0,0,1,0,0,2,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,"0",1,"'.$desc1."\",\"".$desc2."\"\r\n";
				}
			}elseif($report_format == "mettle_toledo_cn"){
				//$contents[] = "PLU NO,ARMS,PLU Name,PLU Second Name,Group Number,Unit Price,Second Price,Unit Price Rule,Price Type\n";
				
				$contents = array();
				while($r=$con_multi->sql_fetchrow($query1)){
					if($r['dtl_scale_type'] == -1) $r['scale_type'] = $r['mst_scale_type'];// inherit master
					else $r['scale_type'] = $r['dtl_scale_type'];// refer to own scale type
					
					if(!$r['scale_type']) continue;
				
					$r['description'] = substr($r['description'],0,24);
					$r['selling'] = round($r['selling'], 2);
					if($r['scale_type'] == 2){
						$r['scale_type'] = 0; // it is by weight
						$r['scale_weight'] = 100;
					}else{
						$r['scale_weight'] = 0;
					}
					$contents[] = "$r[mcode],$r[sku_item_code],0,$r[selling],1,0,0,0,0,$r[scale_weight],$r[scale_type],0,0,$r[description]\r\n";
				}
			}elseif($report_format == "digi_sm_500"){
				$uc_bc = strtoupper($branch_code);
				$posflag = 22;
				if($config['customize_weight_scale_posflag']){ // found got customized posflag
					$posflag = $config['customize_weight_scale_posflag']['by_branch'][$uc_bc];
					if(!$posflag) $posflag = $config['customize_weight_scale_posflag']['default'];
				}
				$contents = array();
				$contents[] = "Flag|PLUcode|ItemCode|CommodityName|SpecialMessage|UnitPrice|ItemStatus|PackDay|ExpiredDay\n";
				
				while($r=$con_multi->sql_fetchrow($query1)){
					if($r['dtl_scale_type'] == -1) $r['scale_type'] = $r['mst_scale_type'];// inherit master
					else $r['scale_type'] = $r['dtl_scale_type'];// refer to own scale type
					
					if(!$r['scale_type']) continue;
				
					$r['description'] = substr($r['description'],0,22);
					$selling = round($r['selling'], 2);
					if($r['scale_type'] != 2) $r['scale_type'] = 1;
					if (!$branch_is_under_gst) {
						$msg = "";
					} else {
						$msg = $r['output_gst_rate'] ."% GST";
					}
					
					$contents[] = "$posflag|$r[mcode]|$r[mcode]|$r[description]|$msg|".sprintf("%.2f", $selling)."|$r[scale_type]|0|0\r\n";
				}
			}elseif($report_format == "bc11_800"){
				//$contents[] = "Symbol,PLU NO,PLU Name,Unit Price,Sales Mode,Weight,Open Price,Shelf Life,Trace Code Enable,Barcode Number,Tare Weight,Text Unit Number,Store Code\n";
				$format_type = "txt";				
				$get_weight_fraction = get_pos_settings_value($branch_id, "weight_fraction");

				while($r=$con_multi->sql_fetchrow($query1)){
					if($r['dtl_scale_type'] == -1) $r['scale_type'] = $r['mst_scale_type'];// inherit master
					else $r['scale_type'] = $r['dtl_scale_type'];// refer to own scale type
					
					if(!$r['scale_type']) continue;
					
					$receipt_description = preg_replace('/[^A-Za-z0-9]/','',$r['receipt_description']);
					
					$unit_price = sprintf("%010s",intval($r['selling']*100));
					
					$scale_type = ($r['scale_type']==2?0:1);					
					
					$weight_fraction = ($get_weight_fraction=="1000"?0:1);

					$open_price = ($r['open_price']?1:0);
					
					$contents[] = "0,$r[mcode],$receipt_description,$unit_price,$scale_type,$weight_fraction,$open_price,000,0,99,00000,,00000000\r\n";					
				}
			}elseif($report_format == "bc11_800_v2"){
				//$contents[] = "Symbol,PLU NO,PLU Name,Unit Price,Sales Mode,Weight,Open Price,Shelf Life,Trace Code Enable,Barcode Number,Tare Weight,Text Unit Number,Store Code\n";
				$format_type = "txt";
				$get_weight_fraction = get_pos_settings_value($branch_id, "weight_fraction");

				while($r=$con_multi->sql_fetchrow($query1)){
					if($r['dtl_scale_type'] == -1) $r['scale_type'] = $r['mst_scale_type'];// inherit master
					else $r['scale_type'] = $r['dtl_scale_type'];// refer to own scale type
					
					if(!$r['scale_type']) continue;
					
					$receipt_description = preg_replace('/[^A-Za-z0-9]/','',$r['receipt_description']);
					
					$unit_price = sprintf("%010s",intval($r['selling']*100));
					
					$scale_type = ($r['scale_type']==2?0:1);
					
					$weight_fraction = ($get_weight_fraction=="1000"?0:1);					
					
					$open_price = ($r['open_price']?1:0);
					
					$contents[] = "0,$r[mcode],$receipt_description,$unit_price,$scale_type,$weight_fraction,$open_price,000,0,01,00000,,00000000\r\n";					
				}
			}elseif($report_format == "rongta"){
				if($config['weight_scale_use_promo_price']){
					require_once("include/price_checker.include.php");
				}
				//$contents[] = "Mcode ,Description ,Price ,Scale Type ,usedate\r\n";
				$format_type = "txt";
				
				$contents[] = "Plu,Description,Price,Uom,Usedate\r\n";
				while($r=$con_multi->sql_fetchrow($query1)){
					if($r['dtl_scale_type'] == -1) $r['scale_type'] = $r['mst_scale_type'];// inherit master
					else $r['scale_type'] = $r['dtl_scale_type'];// refer to own scale type
					
					if(!$r['scale_type']) continue;
					
					$receipt_description = $r['receipt_description'];
					
					// Check if weighing scale need to use promotion price
					if($config['weight_scale_use_promo_price']){
						$params = array();
						$params['branch_id'] = $branch_id;
						$params['sku_item_id'] = $r['sid'];
						$sku_price_check = check_price($params);
						if($sku_price_check){
							if($sku_price_check['non_member_price']>0 && $sku_price_check['non_member_price']<$r['selling']){
								// Use Promotion Price
								$r['selling'] = round($sku_price_check['non_member_price'], 2);
							}
						}
					}
					
					$unit_price = $r['selling']*100;
					
					$scale_type = ($r['scale_type']==2?0:1);	
					
					$contents[] = "$r[mcode],$receipt_description,$unit_price,$scale_type,0\r\n";					
				}
			}elseif($report_format == "digi_sm320_with_scale_type"){
				$format_type = "txt";
				$content = array();
				$uc_bc = strtoupper($branch_code);
				if($config['customize_weight_scale_posflag']){
					$posflag = $config['customize_weight_scale_posflag']['by_branch'][$uc_bc];
					if(!$posflag) $posflag = $config['customize_weight_scale_posflag']['default'];
				}
				
				while($r=$con_multi->sql_fetchrow($query1)){
					if($r['dtl_scale_type'] == -1) $r['scale_type'] = $r['mst_scale_type'];// inherit master
					else $r['scale_type'] = $r['dtl_scale_type'];// refer to own scale type
					
					if(!$r['scale_type']) continue;
					
					$receipt_description = $r['receipt_description'];
					$unit_price =  sprintf("%0.2f",$r['selling']); 
					$scale_type = ($r['scale_type']==2?"Weight":"Non-weigh");
					$output_gst_rate = ($r['output_gst_rate']==0?0:1);
					$contents[] ="$posflag,$r[mcode],$receipt_description,$unit_price,$scale_type,$output_gst_rate\r\n";
				}
			}elseif($report_format == "tma_barcode"){
				$contents[] = "Name,Price,Item Code,Unit\n";
				while($r=$con_multi->sql_fetchrow($query1)){
					if($r['dtl_scale_type'] == -1) $r['scale_type'] = $r['mst_scale_type'];// inherit master
					else $r['scale_type'] = $r['dtl_scale_type'];// refer to own scale type
					
					if(!$r['scale_type']) continue;
					
					$unit = '';
					if($r['scale_type'] == 1){
						$unit = 2;
					}else{
						$unit = 1;
					}
					$r['selling'] = round($r['selling'],2);
					$contents[] = "$r[receipt_description],$r[selling],$r[mcode],$unit\r\n";
				}
			}
			/*elseif($report_format == "ishida_3"){
				$uc_bc = strtoupper($branch_code);
				$need_check_csv = false;
				if($config['customize_weight_scale_posflag']){
					$posflag = $config['customize_weight_scale_posflag']['by_branch'][$uc_bc];
					if(!$posflag) $posflag = $config['customize_weight_scale_posflag']['default'];
					if($config['customize_weight_scale_posflag']['need_check_csv'][$uc_bc]) $need_check_csv = true;
				}else{
					if($branch_code == "tp" || $branch_code == "bc" || $branch_code == "su" || $branch_code == "ba" || $branch_code == "pu" || $branch_code == "ch" || $branch_code == "u8"){
						$posflag = 23;
					}else $posflag = 22;
				}
			
				$contents[] = "Plu_No,SalesMode,UnitPrice,Tare,PackQuant,Unit,DateFlag,ShelfLife,PosCode,ExMessage2,Line1,Line2\n";
				
				while($r=$con->sql_fetchassoc($query1)){
					$tmcode = trim($r['mcode']);
					if($mcode_22[$tmcode]){
						if($config['customize_weight_scale_posflag']){ // set by config
							if($need_check_csv || !$config['customize_weight_scale_posflag']['by_branch'][$uc_bc]) $posflag = 22;
							else $posflag = 23;
						}else{ // follow default harcoded
							if($branch_code != "ba" && $branch_code != "pu" && $branch_code != "ch" && $branch_code != "u8") $posflag = 22;
						}
					}else{
						if(!$config['customize_weight_scale_posflag']['by_branch'][$uc_bc]){
							if($config['customize_weight_scale_posflag']['default']) $posflag = $config['customize_weight_scale_posflag']['default'];
							else $posflag = 22;
						}else $posflag = $config['customize_weight_scale_posflag']['by_branch'][$uc_bc];
					}

					if($r['dtl_scale_type'] == -1) $r['scale_type'] = $r['mst_scale_type'];// inherit master
					else $r['scale_type'] = $r['dtl_scale_type'];// refer to own scale type

					if(!$r['scale_type']) continue;
					
					$r['selling'] = round($r['selling'],2) * 100;
					if($r['scale_type'] == 2) $r['scale_type'] = 0; // it is by weight
					
					// description cut
					if(strlen($r['description']) > 25){
						$desc1 = substr($r['description'], 0, 25);
						$desc2 = substr($r['description'], 25);
					}else{
						$desc1 = $r['description'];
						$desc2 = "";
					}
					
					$selling = $r['selling'];
					if (!GST_ENABLED) {
						$ExMessage2 = 0;
					}
					else
					{
						$tax = get_sku_gst('output_tax', $r['sid'], array('no_check_use_zero_rate'=>1));
						$ExMessage2 = $tax['id'];
					}
					$contents[] = ''.$r['mcode'].','.$r['scale_type'].','.$selling.',0,1,0,0,1000,'.$r['mcode'].','.$ExMessage2.',"'.$desc1."\",\"".$desc2."\"\r\n";
				}
			}*/
			else{
				die("Invalid Export Format");
			}
		}/*else{
			if(!isset($_SERVER['SSH_CONNECTION']) && !isset($_SERVER['SHELL'])){
				die("No record found!");
			}
		}*/
		$con_multi->sql_freeresult($query1);

		if(count($contents) > 0){
			$content = join("", $contents);
			if(!defined('TERMINAL')){
				header("Content-type: text/plain");
				header('Content-Disposition: attachment;filename=ARMS_WEIGHTCODE_'.$branch_code.'.'.$format_type);
				print $content;
			}else{
				$prefix_file_name = "";
				if($dictory_name) $prefix_file_name = $dictory_name."/";
				$prefix_file_name .= $branch_code."_weight.csv";
				file_put_contents($prefix_file_name,$content);
				chmod($prefix_file_name, 0777);
			}
		}else{
			if(!defined('TERMINAL')){
				die("No record found!");
			}
		}
	}
	exit;
}
?>

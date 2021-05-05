<?
/*
5/19/2011 12:31:53 PM Alex
- create by me

7/5/2011 2:24:48 PM Alex
- change use prefix code of vendor
- fix get category code bugs in while loop 
*/
include("../../include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");

if (isset($_REQUEST['a']))
{
	switch ($_REQUEST['a'])
	{
		case 'ajax_get_max_artno':
			ajax_get_max_artno();
		exit;
		default:
		exit;
	}
}

function ajax_get_max_artno(){
	global $con;

	$price_type=$_REQUEST['default_trade_discount_code'];
	
	if ($price_type == "OF")
		$artno_start=$price_type;
	else{
		//check category code
		if ($_REQUEST['category_id']){
			$cat_id=$_REQUEST['category_id'];
			$cat_sql="select code from category where id='$cat_id'";
			$cat_rid=$con->sql_query($cat_sql);
			while ($cat=$con->sql_fetchassoc($cat_rid)){
				if (strlen($cat['code'])>1)	$err['error']="Invalid category code. Please edit category code to 1 digit only.";
				elseif (!$cat['code'])	$err['error']="Missing category code. Please add 1 digit category code.";
				else	$cat_code=$cat['code'];
			}
	
			$con->sql_freeresult($cat_rid);
		
			if ($err){
				print json_encode($err);
				exit;
			}
		}
		
		//get vendor code
		if ($_REQUEST['vendor_id']){
			$ven_id=$_REQUEST['vendor_id'];
			$ven_sql="select prefix_code as code from vendor where id='$ven_id'";
			$ven_rid=$con->sql_query($ven_sql);	
		
			while ($ven=$con->sql_fetchassoc($ven_rid)){
				//get 1 digit only
				if ($ven['code'])	$ven_code=substr($ven['code'],0,1);
			}
			$con->sql_freeresult($ven_rid);
		}
	
		$artno_start=$ven_code.$price_type.$cat_code;
	}
	$artno_end=$artno_start."Z";
	
	//get max artno
	$artno_sql="select max(artno) as max_artno from sku_items where artno between '$artno_start' and '$artno_end'";
	$artno_rid=$con->sql_query($artno_sql);
	$artno=$con->sql_fetchassoc($artno_rid);
	if ($artno['max_artno'])	$artno_code_arr=explode(" ",$artno['max_artno']);
	$con->sql_freeresult($artno_rid);
	
	$artno_sql2="select max(artno) as max_artno from sku_apply_items where artno between '$artno_start' and '$artno_end'";
	$artno_rid2=$con->sql_query($artno_sql2);
	$artno2=$con->sql_fetchassoc($artno_rid2);
	if ($artno2['max_artno'])	$artno_code_arr2=explode(" ",$artno2['max_artno']);
	$con->sql_freeresult($artno_rid2);

	if ($artno_code_arr || $artno_code_arr2)
		$artno_code=$artno_code_arr[0]>$artno_code_arr2[0] ? $artno_code_arr[0] : $artno_code_arr2[0];  
	else	$artno_code=$artno_start;
	
	$num=mi(preg_replace("/^$artno_start/",'',$artno_code));
	
	$max_artno_code['code']=$artno_start;
	$max_artno_code['num']=$num;

	print json_encode($max_artno_code);
	//json_encode();
}
?>
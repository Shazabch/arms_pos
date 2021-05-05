<?php
/*
12/27/2007 3:28:35 PM yinsee
- add detect for local sku photo 

12/29/2008 11:35:15 AM yinsee
- remove photos (SLLEE)

8/15/2014 1:53 PM Justin
- Bug fixed on system to cut off those code with 13 digits start with "28" instead of "2".
*/
define('SKIP_BROWSER',1);
define('DEBUG',0);
define('PRICE_CHECKER',1);
include("../include/common.php");
include("../include/price_checker.include.php");

$smarty->template_dir = './templates';
$smarty->compile_dir = './templates_c';
if (!is_dir($smarty->compile_dir)) @mkdir($smarty->compile_dir,0777);

$con->sql_query("select id from branch where code = " . ms(BRANCH_CODE));
$b=$con->sql_fetchrow();
$branch_id=$b[0];


if ($_REQUEST['code']=='') header("Location: idle.php");
$code = $_REQUEST['code'];
if (preg_match('/^28/',$code)) $code = substr($code,0,12);
/*$code = ms($code);

$con->sql_query("
	select sku_items.id, sku_items.sku_item_code, sku_items.mcode, sku_items.artno, sku_items.link_code, sku_items.description, sku_items.selling_price as master_price, sku_items.sku_apply_items_id, sku_apply_items.photo_count, sku_items_price.price, sku_apply_items.photo_count, brand.description as brand, branch.code as branch_code, branch.ip as branch_ip
	from sku_items
		left join sku on sku_id = sku.id
		left join brand on brand_id = brand.id
		left join branch on apply_branch_id = branch.id
		left join sku_apply_items on sku_apply_items_id = sku_apply_items.id
		left join sku_items_price on sku_items_price.sku_item_id = sku_items.id and branch_id = $branch_id
	where sku_items.active=1 and (sku_items.mcode = $code or sku_items.link_code = $code or sku_items.sku_item_code = $code) order by sku_items.sku_item_code limit 1");
$sku = $con->sql_fetchrow();
if (!$sku)
{
	$smarty->display("check.not_found.tpl");
}
else
{
    // use master price if local price is not defined
	if ($sku['price']==0) $sku['price'] = $sku['master_price'];

	// check promotion table and calculate promotion price
	$con->sql_query("select p.title, p.date_from, p.date_to, p.time_from, p.time_to, pi.* 
		from promotion p left join promotion_items pi on p.branch_id = pi.branch_id and p.id = pi.promo_id
		where 
		promo_branch_id like '%".BRANCH_CODE."%' and 
		pi.sku_item_id = $sku[id] and
		CURDATE() between date_from and date_to and p.approved = 1 and p.active = 1
		");
	
	$member = array();
	$non_member = array();
	while($p=$con->sql_fetchrow())
	{
		// member_disc_p	member_disc_a	non_member_disc_p	non_member_disc_a
		if ($p['member_disc_a']>0)
		{
			$member_price = $p['member_disc_a'];
		}
		elseif (strstr($p['member_disc_p'],'%'))
		{
			$member_price = $sku['price'] * (1 - doubleval($p['member_disc_p'])/100);
		}
		else
		{
			$member_price = $sku['price']  - doubleval($p['member_disc_p']);
		}
		$member_price = round($member_price,2);
		if ($p['non_member_disc_a']>0)
		{
			$non_member_price = $p['non_member_disc_a'];  
		}
		elseif (strstr($p['non_member_disc_p'],'%'))
		{
			$non_member_price = $sku['price'] * (1 - doubleval($p['non_member_disc_p'])/100);
		}
		else
		{
			$non_member_price = $sku['price']  - doubleval($p['non_member_disc_p']);
		}
		$non_member_price = round($non_member_price,2);
		if ($member_price>$non_member_price) $member_price = $non_member_price;
		
	
		$member[] = $member_price;
		$non_member[] = $non_member_price;
	}

	$sku['member_price'] = @min($member);
	$sku['member_discount'] = intval(100-round($sku['member_price']/$sku['price'],2)*100);
	$sku['non_member_price'] = @min($non_member);
    $sku['non_member_discount'] = intval(100-round($sku['non_member_price']/$sku['price'],2)*100);

   // if ($sku['photo_count']>0)
//		$sku['imgpath'] = get_branch_file_url($sku['branch_code'], $sku['branch_ip']);

 //	$sku['photos'] = get_sku_item_photos($sku['id']);   
 
	$smarty->assign("sku", $sku);
    $smarty->display("check.detail.tpl");
}*/

	$params = array();
	$params['branch_id'] = $branch_id;
	$params['code'] = $code;
    $sku = check_price($params);
    if ($sku['error'])
	{
		$smarty->display("check.not_found.tpl");
		exit;
	}
	$smarty->assign("sku", $sku);
    $smarty->display("check.detail.tpl");
?>
<meta http-equiv="refresh" content="30;URL=idle.php">

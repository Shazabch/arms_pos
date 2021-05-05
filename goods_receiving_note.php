<?php
/*
5/25/2008 3:40:25 PM - yinsee
- fix grn cost - last cost of HQ should take from HQ's GRN only

10/22/2008 4:54:54 PM yinsee
- no branch filter when search

11/6/2008 2:28:52 PM yinsee
- when add item, selling_price only take from masterfile (remove PO and GRN check)

6/23/2009 4:05 PM Andy
add cheking on $config['grn_alt_print_template'] to allow custom print

6/30/2009 4:04 PM Andy
- add GRN Tax
	- alter table grn add grn_tax double
	- alter table grn_items add original_cost double after uom_id
	- alter table tmp_grn_items add original_cost double after uom_id
	
8/3/2009 3:29:15 PM Andy
- Add reset function

8/7/2009 3:13:25 PM Andy
- collect sku items packing_uom_id as master_uom_id when add or load items

12/9/2009 4:39:05 PM Andy
- add don't filter branch if consignment module

1/4/2010 6:11:14 PM Andy
- change GRN owner filter to include PO owner

4/2/2010 4:05:24 PM Andy
- Make user as account peronal if the grn creator is last approval
- Add "Multiple add item" feature for GRN

4/7/2010 11:30:52 AM Andy
- Change to even if user is last approval, also don't direct verifid the GRN, but put it for amount check first

4/21/2010 11:41:22 AM Andy
- Fix GRN cannot delete item bugs

7/2/2010 4:11:39 PM Alex
- Add $config['document_page_size'] to set limit items per page and fix search bugs

10/23/2012 5:57 PM Andy
- Increase maintenance version to 165.

8/12/2013 2:14 PM Andy
- Enhance to check maintenance version 208.

03/08/2016 10:20 Edwin
- Enhanced to check HQ whether have license to access module

07/01/2016 15:30 Edwin
- Enhanced on user able to view although they don't have official module.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('GRN')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'GRN', BRANCH_CODE), "/index.php");
if (BRANCH_CODE == 'HQ' && $config['arms_go_modules'] && !$config['arms_go_enable_official_modules'] && $_REQUEST['a'] != 'view' && $_REQUEST['a'] != 'print') js_redirect($LANG['NEED_ARMS_GO_HQ_LICENSE'], "/index.php");
$maintenance->check(208);
$maintenance->check(165,true);

$smarty->assign("PAGE_TITLE", "GRN (Goods Receiving Note)");

if($config['use_grn_future']){
	include("goods_receiving_note2.include.php");
	require("goods_receiving_note2.php");
}else{
	include("goods_receiving_note1.include.php");
	require("goods_receiving_note1.php");
}
?>

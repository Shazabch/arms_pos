<?php
/*
Revision History
----------------
9 Apr 2007 - yinsee
- added branch filter. HQ allow to view all branches.

12/20/2007 4:07:56 PM yinsee
- use config for card settings

9/2/2010 1:15:38 PM Justin
- Added a new row to display the "Cash Collection from Redemption" for every counter on top of sub total.
- Added a total at the end of the page for Cash Collection from Redemption.
- Added and removed some php codes on the report and css.

11/1/2010 2:57:52 PM Justin
- Solved the bugs where cannot view the total cash collected from redemption.

6/24/2011 6:14:13 PM Andy
- Make all branch default sort by sequence, code.

8/11/2011 12:38:22 PM Justin
- Fixed the alignment for some numeric fields that does not align to right.
- Added missing background color for Cash on Hand from System Estimation column. 

9/27/2012 3:48 PM Justin
- Fixed bug of Estimation figures not align to right that causes confusion.

10/30/2012 10:35 AM Justin
- Enhanced to round cash on hand to 2 decimal points.

1/4/2013 5:24 PM Justin
- Enhanced to have new feature that allows user to edit cash domination.
- Added new privilege checking for edit cash domination.

11/5/2013 11:45 AM Fithri
- change all term "Cash Domination" to "Cash Denomination"

3/27/2015 6:20 PM Justin
- Enhanced to have GST info.

4/26/2017 8:31 AM Khausalya
- Enhanced changes from RM to use config setting. 
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('RPT_MEMBERSHIP')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'RPT_MEMBERSHIP', BRANCH_CODE), "/index.php");

if (BRANCH_CODE == 'HQ')
	$branch_id = intval($_REQUEST['branch_id']);
else
	$branch_id = $sessioninfo['branch_id'];

if ($branch_id) {
	$branch_filter =  "branch_id = $branch_id";
	$mri_branch_filter =  "and ri.branch_id = $branch_id";
}else $branch_filter = 1;

if (isset($_REQUEST['a']))
{
    switch($_REQUEST['a'])
    {
        case 'ajax_refresh_table':
            // ajax call to refresh table by selected date
            show_table();
            exit;
        case 'print':
        	small_report();
        	exit;
		case 'ajax_update_cash_domination':
			ajax_update_cash_domination();
			exit;
		default:
			print "<h1>Error: Unhandled Request</h1>";
		    print_r($_REQUEST);
		    exit;
	}
}

// by defauilt show table
$con->sql_query("select id,code from branch order by sequence,code");
$smarty->assign("branches", $con->sql_fetchrowset());

$smarty->assign("PAGE_TITLE", "Membership Counter Reports");
$smarty->display("report.mem_counter.tpl");

// minimalist report
function small_report()
{
	global $con, $branch_filter, $config;

	$dt = dmy_to_sqldate($_REQUEST['date']);
print '<pre>Membership Counter Report
Date: '.$_REQUEST['date'].'

';
	$c = $con->sql_query("select counter_settings.id as counter_id, network_name, branch_id, branch.code as branch from counter_settings left join branch on branch_id = branch.id where membership_settings like '%allow_membership%' and $branch_filter");
	//$con->sql_query("select id as counter_id, network_name from counter_settings where $branch_filter");
	while ($r = $con->sql_fetchrow($c))
	{
		print "Counter: $r[branch]/$r[network_name]\n";
		$q = $con->sql_query("select i.remark as remark, count(i.card_no) as cnt, sum(i.amount) as amt from (membership_receipt r left join membership_receipt_items i on r.id = i.receipt_id and r.counter_id = i.counter_id and r.branch_id = i.branch_id) where r.counter_id = $r[counter_id] and r.branch_id = $r[branch_id] and r.status = 0 and DATE(timestamp) = '$dt' group by remark");
		$amt = 0;
		$cnt = array();
		while($i=$con->sql_fetchrow($q))
		{
			$cnt[$i['remark']] = $i['cnt'];
			$amt+=$i['amt'];
		}
		printf ("Total Amount (" . $config["arms_currency"]["symbol"] . ")       %0.2f\n", $amt);
		printf ("New Membership          %d\n", $cnt['N']);
		printf ("Membership Renewal      %d\n", $cnt['R']);
		printf ("Lost/Replacement        %d\n", $cnt['L']);
		printf ("Lost and Renewal        %d\n", $cnt['LR']);
		printf ("Exchange & Renew        %d\n", $cnt['ER']);
		
		$q = $con->sql_query("select card_no from (membership_receipt r left join membership_receipt_items i on r.id = i.receipt_id and r.counter_id = i.counter_id and r.branch_id = i.branch_id) where remark <> 'R' and r.counter_id = $r[counter_id] and r.branch_id = $r[branch_id] and r.status = 0 and DATE(timestamp) = '$dt'");
		$ctype = array();
		while($i=$con->sql_fetchrow($q))
		{
			
			foreach($config['membership_cardtype'] as $t => $ct)
			{
				if (preg_match($ct['pattern'], $i['card_no'])) 
				{
					$ctype[$t]++;
					break;
				}
			}
		}
		printf ("\nCards Issued\n");
		foreach($config['membership_cardtype'] as $t => $ct)
		{
			printf("%-24s%d\n", $ct['description'], $ctype[$t]);
		}
		printf ("-----------\n");
	}

	print "</pre>\n<script>window.print();</script>";
}

//  detailed report
function print_table()
{

print '
<link rel="stylesheet" href="/templates/print.css" type="text/css">
<p align=center>
<h1>Membership Counter Reports</h1>
Date: '.$_REQUEST['date'].'
</p>
';
show_table();
print '
<table style="padding-top: 150px">
<tr>
<td align=center width=150 style="border-top:1px solid black">Checked By</td>
<td width=50>&nbsp;</td>
<td align=center width=150 style="border-top:1px solid black">Verified By</td>
<td width=50>&nbsp;</td>
<td align=center width=150 style="border-top:1px solid black">Approved By</td>
</tr>
</table>
';
print "<script>window.print();</script>";
}

function show_table()
{

	global $con;
	global $total_issued, $sub_total_issued;
	global $total_amount, $total_gross_amount, $total_gst_amount, $sub_total_amount, $sub_total_gst_amount, $sub_total_gross_amount;
	global $branch_filter;
	global $config;
	
	// memership coutner report
	if (!isset($_REQUEST['date'])) $dt = 'CURDATE()';
	else $dt = ms(dmy_to_sqldate($_REQUEST['date']));

	//$c = $con->sql_query("select counter_id, c.network_name, count(*) as cnt, sum(amount) as amt from (membership_receipt r left join counter_settings c on r.counter_id = c.id) where status = 0 and DATE(timestamp) = $dt group by counter_id");

	$c = $con->sql_query("select counter_settings.id as counter_id, network_name, branch_id, branch.code as branch from counter_settings left join branch on branch_id = branch.id where membership_settings like '%allow_membership%' and $branch_filter");

    print "<table border=0 cellspacing=0 cellpadding=2 style=\"font:12px Arial\">";
    
    foreach($config['membership_cardtype'] as $t => $ct){ $total_issued[$t] = 0; }
		
	$total_amount = 0;
	while ($r = $con->sql_fetchrow($c))
	{
		foreach($config['membership_cardtype'] as $t => $ct){ $sub_total_issued[$t] = 0; } 
		$sub_total_amount = $sub_total_gst_amount = $sub_total_gross_amount = 0;
		
		$con->sql_query("select count(*) as cnt, sum(amount) as amt from membership_receipt where status = 0 and DATE(timestamp) = $dt and counter_id = $r[counter_id] and branch_id = $r[branch_id]");
		$r2 = $con->sql_fetchrow();

		print "<tr><td colspan=7><h3>$r[branch]: $r[network_name]</h3>";
		print "Total Sales Amount: ".number_format($r2['amt'],2)."<br>";
		print "Total Transaction: ".intval($r2['cnt'])."<br>";
		print "</td></tr>";
		print "<tr>";
	    print "<td class=tbh colspan=3 width=300>Counter Activity</td>";
		print "<td width=80 class=tbh align=center>Cash On<br>Hand</td>";
		foreach($config['membership_cardtype'] as $t => $ct)
		{
			$img = sprintf($config['membership_cardimg'], $t);
			print "<td width=80 class=tbh align=center><img src=$img height=20><br>$ct[description]</td>";
		}
		/*
		print "<td width=80 class=tbh><img src=/images/akad-R.gif height=20><br>Red</td>";
		print "<td width=80 class=tbh><img src=/images/akad-G.gif height=20><br>Green</td>";
		*/
        print "</tr>";

		$h = $con->sql_query("select user.u as username, inventory, ori_inventory, timestamp, type, h.id from membership_inventory_history h left join user on h.user_id = user.id where counter_id = $r[counter_id] and branch_id = $r[branch_id] and DATE(timestamp) = $dt order by timestamp");
	    $time_start = '';
	    while ($iv = $con->sql_fetchrow($h))
	    {
		    $iv['inventory'] = unserialize($iv['inventory']);
		    $iv['ori_inventory'] = unserialize($iv['ori_inventory']);
			if ($iv['type'] == 'OPEN')
			{
	            if ($time_start != '') get_activities($r['counter_id'], $r['branch_id'], $lastrecord, $time_start, $iv['timestamp']);
				$lastrecord = $iv;
			}
	        elseif ($iv['type'] == 'ADD')
			{
	            if ($time_start != '') get_activities($r['counter_id'], $r['branch_id'], $lastrecord, $time_start, $iv['timestamp']);
				$lastrecord['inventory']['COH'] += $iv['inventory']['COH'];
				foreach($config['membership_cardtype'] as $t => $ct){ 
					$lastrecord['inventory']['CARD_'.$t] += $iv['inventory']['CARD_'.$t];
				}
				/*$lastrecord['inventory']['CARD_R'] += $iv['inventory']['CARD_R'];
				$lastrecord['inventory']['CARD_G'] += $iv['inventory']['CARD_G'];*/
			}
	        elseif ($iv['type'] == 'CLOSE')
	        {
	            if ($time_start != '')
				{
					if (get_activities($r['counter_id'], $r['branch_id'], $lastrecord, $time_start, $iv['timestamp']))
					{
			            // show estimation
				    	print "<tr>";
					    print "<td colspan=3 class=tbe>System Estimation</td>";
					    $inv = $lastrecord['inventory'];
						print "<td class=tbe align=right>&nbsp;".number_format($inv['COH'],2)."</td>";
						foreach($config['membership_cardtype'] as $t => $ct){ 
							print "<td class=tbe align=right>&nbsp;".$inv["CARD_$t"]."</td>";
						}
						//print "<td class=tbe>$inv[CARD_R]</td>";
						//print "<td class=tbe>$inv[CARD_G]</td>";
					    print "</tr>";
				    }
			    }

			}
			$time_start = $iv['timestamp'];
			
		    //print_r($iv);
		   // print "$iv[type] Inventory ($iv[timestamp]): <br>";
			if(!$config['membership_disable_edit_cd'] && privilege('MEMBERSHIP_EDIT_CD')){
				$extra_print = "<a href=\"javascript:cd_menu_dialog(".ms($iv['id']).",".ms($r['branch_id']).",".ms($r['counter_id']).");\"><img src=\"ui/ed.png\" title=\"Edit this Cash Denomination\" border=0></a>";
			}
		   
		    print "<tr>";
		    print "<td class=tbr>
				   $extra_print $iv[type]
				   <input type=\"hidden\" id=\"type_$iv[id]_$r[branch_id]_$r[counter_id]\" value=\"$iv[type]\" />
				   <input type=\"hidden\" id=\"cashier_$iv[id]_$r[branch_id]_$r[counter_id]\" value=\"$iv[username]\" />
				   <input type=\"hidden\" id=\"time_$iv[id]_$r[branch_id]_$r[counter_id]\" value=\"$iv[timestamp]\" />
				   </td>";
			print "<td class=tbr>$iv[username]</td>";
		    print "<td class=tbr>$iv[timestamp]</td>";
		    $inv = $iv['inventory'];
			if(!$iv['ori_inventory']) $ori_inv = $inv;
			else  $ori_inv = $iv['ori_inventory'];
			print "<td class=tbr align=right>
				  ".number_format($inv['COH'],2)."
				  <input type=\"hidden\" id=\"coh_$iv[id]_$r[branch_id]_$r[counter_id]\" value=\"$inv[COH]\" />
				  <input type=\"hidden\" id=\"ori_coh_$iv[id]_$r[branch_id]_$r[counter_id]\" value=\"$ori_inv[COH]\" />
				  </td>";
			foreach($config['membership_cardtype'] as $t => $ct){
				print "<td class=tbr align=right>&nbsp;
					  ".$inv['CARD_'.$t]."
					  <input type=\"hidden\" id=\"card_".$t."_".$iv['id']."_".$r['branch_id']."_".$r['counter_id']."\" value=\"".$inv['CARD_'.$t]."\" />
					  <input type=\"hidden\" id=\"ori_card_".$t."_".$iv['id']."_".$r['branch_id']."_".$r['counter_id']."\" value=\"".$ori_inv['CARD_'.$t]."\" />
					  </td>";
			}
			/*print "<td class=tbr>$inv[CARD_R]</td>";
			print "<td class=tbr>$inv[CARD_G]</td>";*/
		    print "</tr>";
        }
        
        if ($time_start != '')
		{
			if (get_activities($r['counter_id'], $r['branch_id'], $lastrecord, $time_start))
			{
				print "<tr>";
			    print "<td colspan=3 class=tbe>System Estimation</td>";
			    $inv = $lastrecord['inventory'];
				print "<td class=tbe align=right>&nbsp;".number_format($inv['COH'],2)."</td>";
				foreach($config['membership_cardtype'] as $t => $ct){
					print "<td class=tbe align=right>&nbsp;".$inv["CARD_$t"]."</td>";
				}
				/*print "<td class=tbe>$inv[CARD_R]</td>";
				print "<td class=tbe>$inv[CARD_G]</td>";*/
			    print "</tr>";
		    }
	   
		}

		print "<tr>";
	    print "<td class=tbst colspan=3>Sub Total</td>";
		print "<td class=tbst align=right>&nbsp;+".number_format($sub_total_gross_amount,2)."</td>";
		foreach($config['membership_cardtype'] as $t => $ct){
			print "<td class=tbst align=right>&nbsp;$sub_total_issued[$t]</td>";
		}
	    print "</tr>";
		
		if($sub_total_gst_amount > 0){
			print "<tr>";
			print "<td class=tbst colspan=3>GST</td>";
			print "<td class=tbst align=right>&nbsp;+".number_format($sub_total_gst_amount,2)."</td>";
			foreach($config['membership_cardtype'] as $t => $ct){
				print "<td class=tbst align=right>&nbsp;</td>";
			}
			print "</tr>";
			
			print "<tr>";
			print "<td class=tbst colspan=3>Sub Total Include GST</td>";
			print "<td class=tbst align=right>&nbsp;+".number_format($sub_total_amount,2)."</td>";
			foreach($config['membership_cardtype'] as $t => $ct){
				print "<td class=tbst align=right>$sub_total_issued[$t]</td>";
			}
			print "</tr>";
		}
	    
	    print "<tr><td>&nbsp;</td></tr>";
 	}
 	
	// count the membership make redemption
	$mr = $con->sql_query("select sum(ifnull(mri.cash_need*mri.qty, 0)) as total_cash_needed
						   from membership_redemption mr
						   left join membership_redemption_items mri on mri.membership_redemption_id = mr.id and mri.branch_id = mr.branch_id 
						   where mr.active=1 and mr.status=0 and mr.verified=1
						   and mr.date = ".$dt."
						   $mri_branch_filter
						   group by mr.date");

	$r3 = $con->sql_fetchrow($mr);
 	
 	if($r3['total_cash_needed']){
	 	print "<tr>";
	    print "<td class=tbtrc colspan=3 width=300>Total Cash Collection from Redemption</td>";
		print "<td class=tbtrc align=right width=80>&nbsp;".number_format($r3['total_cash_needed'],2)."</td>";
		foreach($config['membership_cardtype'] as $t => $ct){
			print "<td class=tbtrc align=right width=80>-</td>";
		}
	    print "</tr>";
	}
 	
	print "<tr>";
    print "<td class=tbt colspan=3>Total</td>";
	print "<td class=tbt align=right>&nbsp;+".number_format($total_gross_amount,2)."</td>";
	foreach($config['membership_cardtype'] as $t => $ct){
		print "<td class=tbt align=right>&nbsp;$total_issued[$t]</td>";
	}
    print "</tr>";
	
	if($total_gst_amount > 0){
		print "<tr>";
		print "<td class=tbt colspan=3>Total GST</td>";
		print "<td class=tbt align=right>&nbsp;+".number_format($total_gst_amount,2)."</td>";
		foreach($config['membership_cardtype'] as $t => $ct){
			print "<td class=tbt align=right>&nbsp;</td>";
		}
		print "</tr>";
		
		print "<tr>";
		print "<td class=tbst colspan=3>Total Include GST</td>";
		print "<td class=tbst align=right>&nbsp;+".number_format($total_amount,2)."</td>";
		foreach($config['membership_cardtype'] as $t => $ct){
			print "<td class=tbst align=right>$total_issued[$t]</td>";
		}
		print "</tr>";
	}
	
	/*print "<td class=tbt>$total_issued[R]</td>";
	print "<td class=tbt>$total_issued[G]</td>";*/
    print "</table>";
    
}

function get_activities($counter_id, $branch_id, &$lastrecord, $time_start, $time_end = '')
{
	// get activities within the time
	global $con, $config;
	global $total_issued, $sub_total_issued;
	global $total_amount, $total_gross_amount, $total_gst_amount, $sub_total_amount, $sub_total_gst_amount, $sub_total_gross_amount;

	if ($time_end != '')
		$dt2 = "'$time_end'";
	else
		$dt2 = "ADDDATE(DATE('$time_start'), 1)";
	
	$q = $con->sql_query("select i.remark, i.card_no, r.amount, r.timestamp, r.gross_amount, r.gst_amount, r.is_under_gst from (membership_receipt r left join membership_receipt_items i on r.id = i.receipt_id and r.counter_id = i.counter_id and r.branch_id = i.branch_id) where r.counter_id = $counter_id and r.branch_id = $branch_id and r.status = 0 and timestamp > '$time_start' and timestamp < $dt2 order by timestamp");
	while ($i = $con->sql_fetchrow($q))
	{
		foreach($config['membership_cardtype'] as $t => $ct){ $qty[$t] = "&nbsp;"; }
		/*$qty["G"] = "&nbsp;";
		$qty["B"] = "&nbsp;";*/
		if ($i['remark'] != 'R')
		{
			foreach($config['membership_cardtype'] as $t => $ct)
			{
				
				if (preg_match($ct['pattern'], $i['card_no']))
		        {
					$qty[$t] = '-1';
			        $sub_total_issued[$t]--;
			        $total_issued[$t]--;
			        $lastrecord['inventory']['CARD_'.$t]--;
			    }
			}
		    /*if ($m[1] == '1')
		    {
	  		}
		    elseif ($m[1] == '6')
		    {
		        $qty['R'] = '-1';
		        $sub_total_issued['R']--;
		        $total_issued['R']--;
		        $lastrecord['inventory']['CARD_R']--;
	  		}
		    elseif ($m[1] == '8')
		    {
		        $qty['G'] = '-1';
		        $sub_total_issued['G']--;
		        $total_issued['G']--;
		        $lastrecord['inventory']['CARD_G']--;
	  		}*/
		}
		if(!$i['gross_amount']) $i['gross_amount'] = $i['amount'];

		$lastrecord['inventory']['COH']+=$i['amount'];
		$sub_total_amount+=$i['amount'];
		$sub_total_gst_amount+=$i['gst_amount'];
		$sub_total_gross_amount+=$i['gross_amount'];
		$total_amount+=$i['amount'];
		$total_gross_amount+=$i['gross_amount'];
		$total_gst_amount+=$i['gst_amount'];
	    print "<tr>";
		print "<td colspan=2>$i[remark] ($i[card_no])</td>";
	    print "<td>$i[timestamp]</td>";
	    print "<td align=right>&nbsp;+".number_format($i['amount'],2)."</td>";
	    
	    foreach($config['membership_cardtype'] as $t => $ct){
			print "<td align=right>&nbsp;$qty[$t]</td>";
		}
		/*print "<td>$qty[R]</td>";
		print "<td>$qty[G]</td>";*/
		print "</tr>";
	}
	
	return $con->sql_numrows($q);
}

// update cash domination
function ajax_update_cash_domination(){
	global $sessioninfo, $con, $config;
	
	$ori_inventory = $ret = array();
	$form = $_REQUEST;

	$ori_inventory['COH'] = round($form['coh'],2);
	$msg = "Updated: Membership Cash Denomination (ID#".$form['id'].", Branch ID#".mi($form['bid']).", Counter ID#".mi($form['cid']).", Cash on Hand: ".mf($form['coh']);
	foreach($form['card'] as $ct=>$inv){
		ucase($ct);
		$ori_inventory['CARD_'.$ct] = $inv;
		$msg .= ", ".$config['membership_cardtype'][$ct]['description'].": ".$inv;
	}
	$msg .= ")";
	
	$ori_inventory = serialize($ori_inventory);
	
	$q1 = $con->sql_query("update membership_inventory_history 
						 set ori_inventory = if(ori_inventory is null or ori_inventory = '', inventory, ori_inventory), inventory = ".ms($ori_inventory).", timestamp = timestamp
						 where id = ".mi($form['id'])." and branch_id = ".mi($form['bid'])." and counter_id = ".mi($form['cid']));
	
	if($con->sql_affectedrows($q1) > 0){
		$ret['ok'] = true;
		log_br($sessioninfo['id'], 'MEMBERSHIP (CD)', $form['id'], $msg);
	}else $ret['failed_reason'] = "Nothing to update.";
	
	print json_encode($ret);
}
?>

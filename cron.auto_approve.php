<?php
/*
REVISION HISTORY
===================
3/5/2008 10:52:02 AM gary
- change link purchase_order.php to new po (po.php).

4/16/2008 4:03:19 PM gary
- add special approver.

5/16/2016 4:10 PM Andy
- Enhanced to compatible with php7.
*/

include("include/common.php");

$con->sql_query("select id from branch where code = ".ms(BRANCH_CODE));
$r = $con->sql_fetchrow();
if (!$r) die("Invalid branch ".BRANCH_CODE);
$branch_id = $r[0];

//sku checking > period (done)
if(BRANCH_CODE=='HQ'){
	$q0=$con->sql_query("select approval_history.approvals , sku.*
from approval_history 
left join sku on sku.approval_history_id=approval_history.id
where ref_table ='sku' and sku.timestamp< DATE_SUB(CURDATE(),INTERVAL $config[special_auto_approve] DAY) and approvals<>'|' and sku.status<2");	
	while ($r = $con->sql_fetchrow($q0)){
		$approver_list = preg_split("/\|/", $r['approvals']);
		$r['approver_now']=$approver_list['1'];
		
		//is NOT last approver
		if (!preg_match("/^\|\d+\|$/", $r['approvals'])){
			auto_approve_sku($r);	
		}
		//is last approver && have special approver 
		elseif($config['sku_special_approver']){
			$q1=$con->sql_query("select id from user where u=".ms($config['sku_special_approver']));
			$r1=$con->sql_fetchrow($q1);
			if($r1 && $r1['id']!=$r['approver_now']){
				auto_approve_sku($r);
				//$con->sql_query("update approval_history set approvals='|$r1[id]|' where id=$r[approval_history_id]");
				echo "update approval_history set approvals='|$r1[id]|' where id=$r[approval_history_id]<br>";		
			}
		}
	}
}

function auto_approve_sku($sku){
	global $con, $smarty;
	
	echo "SKU Auto Approval ($sku[id], $sku[approver_now], $sku[approval_history_id])<br>";
	/*
	$approve=1;	
	// update SKU status
	$con->sql_query("update sku set status=$approve where id=".ms($sku['id']));
	
	log_br( $sku['approver_now'], 'MASTERFILE', $sku['id'], "New SKU Auto Approval (ID#$sku[id]), Status: Approved)");
	
	$approval['general']='Auto Approve';
	
	$q1=$con->sql_query("select id from sku_apply_items where sku_id=".ms($sku['id']));
	$i=0;
	while ($r1 = $con->sql_fetchrow($q1)){
		$approval[$i]='Auto Approve';
		$i++;
	}		
	$sz = sz($approval);
		
	// update approval items
	$con->sql_query("insert into approval_history_items (approval_history_id, user_id, status, log) values ($sku[approval_history_id], $sku[approver_now], $approve , $sz)");

	// get the PM list
	$q2=$con->sql_query("select flow_approvals, approvals, sku.apply_by, notify_users from approval_history left join sku on approval_history.ref_id = sku.id where approval_history.id =".ms($sku['approval_history_id']));
	
	$r2 = $con->sql_fetchrow($q2);
	$recipients = $r2[3]; 

   	$recipients = str_replace("|$sku[approver_now]|", "|", $recipients);
   	$to = preg_split("/\|/", $recipients);

	// send pm
	send_pm($to, "New SKU Application Auto Approval (ID#$sku[id]) Approved", "masterfile_sku_application.php?a=view&id=$sku[id]");

	// remove current user from the approval list
	$con->sql_query("update approval_history set status = $approve, approvals = replace(approvals, '|$sku[approver_now]|', '|') where id =".ms($sku['approval_history_id']));
	*/
}

/*====================================================================================*/

//PO checking > period
$q0=$con->sql_query("select branch_approval_history.approvals , po.*
from branch_approval_history 
left join po on po.approval_history_id=branch_approval_history.id and branch_approval_history.branch_id = po.branch_id 
where ref_table ='po' and po.last_update< DATE_SUB(CURDATE(),INTERVAL $config[special_auto_approve] DAY) and approvals<>'|' and branch_approval_history.branch_id =$branch_id and (po.status = 1 or po.status = 3) and not po.approved and po.active and branch_approval_history.status=0");
while ($r = $con->sql_fetchrow($q0)){
	$approver_list = preg_split("/\|/", $r['approvals']);
	$r['approver_now']=$approver_list['1'];
	//is NOT last approver
	if (!preg_match("/^\|\d+\|$/", $r['approvals'])){
		auto_approve_po($r,$branch_id);	
	}
	//is last approver
	elseif($config['po_special_approver']){
		$q1=$con->sql_query("select id from user where u=".ms($config['po_special_approver']));
		$r1=$con->sql_fetchrow($q1);
		if($r1 && $r1['id']!=$r['approver_now']){
			auto_approve_sku($r);	
			//$con->sql_query("update branch_approval_history set approvals='|$r1[id]|' where id=$r[approval_history_id] and branch_id=$branch_id");
			echo "update branch_approval_history set approvals='|$r1[id]|' where id=$r[approval_history_id] and branch_id=$branch_id<br>";
		}	
	}
}

function auto_approve_po($po,$branch_id){
	global $con, $smarty, $branch_id;
	
	echo "PO Auto Approval ($po[id], $branch_id, $po[approver_now], $po[approval_history_id])<br>";	
	/*
	$approve=1;	
	
	// update PO status
	$con->sql_query("update po set status = $approve where id =".ms($po['id'])." and branch_id = $branch_id");
	log_br($po['approver_now'], 'PURCHASE ORDER', $po['id'], "PO Auto Approval (ID#$po[id], Status: Approved)");
	
	// update approval records
	$con->sql_query("insert into branch_approval_history_items (approval_history_id, branch_id, user_id, status, log) values ($po[approval_history_id], $branch_id, $po[approver_now], $approve, 'Auto Approved')");
	
	$q1=$con->sql_query("select flow_approvals, approvals, po.user_id, notify_users 
from branch_approval_history 
left join po on branch_approval_history.ref_id = po.id and branch_approval_history.branch_id = po.branch_id 
where branch_approval_history.id=$po[approval_history_id] and branch_approval_history.branch_id = $branch_id");
	$r1 = $con->sql_fetchrow($q1);	
	$recipients = $r1[3];
	$po_owner = $r1[2];
   	$recipients = str_replace("|$po[approver_now]|", "|", $recipients);
   	$to = preg_split("/\|/", $recipients);
   	
	// remove current user from the approval list
	$con->sql_query("update branch_approval_history set status = $approve, approvals = replace(approvals, '|$po[approver_now]|', '|') where id = $po[approval_history_id] and branch_id = $branch_id");
	
	//select from po to get vendor_id and dept_id
	$q2=$con->sql_query("select vendor_id, department_id from po where id = $po[id] and branch_id = $branch_id");
	$r2 = $con->sql_fetchrow($q2);

	// add vendor info in send_pm
	$q3 = $con->sql_query("select description from vendor where id=$r2[vendor_id]");
	$r3 = $con->sql_fetchrow($q3);
	$vendor=$r3['description'];
	
	// add category info in send_pm
	$q4 = $con->sql_query("select description from category where id=$r2[department_id]");
	$r4 = $con->sql_fetchrow($q4);
	$dept=$r4['description'];
	
	
	send_pm($to, "Purchase Order Auto Approval (ID#$po[id], Dept:$dept, Vendor:$vendor) Approved", "po.php?a=view&id=$po[id]&branch_id=$branch_id");
	*/

}



//AUTO SENDING EMAIL TO SUPPLIER FOR APPROVED PO
$q1=$con->sql_query("select po.id as po_id, po.branch_id as po_branch_id, vendor.contact_email as vendor_email 
from po
left join vendor on vendor.id=po.vendor_id
where po.approved and po.active and po.email_sent=0");
while ($r1= $con->sql_fetchrow($q1)){
	if($r1['vendor_email']){
		//echo"<pre>";print_r($r1);echo"</pre>";
		email_to_supplier($r1);
	}
}
exit;

function email_to_supplier($po){
	global $con, $sessioninfo, $smarty;	
	echo "PO Approved Email To $po[vendor_email]<br>";
	/*
	ini_set("display_errors",1);
	$mailer = new PHPMailer();
	$mailer->Subject="Purchase Order From Aneka";
	$mailer->Host="mail.aneka.com.my";
	$mailer->Body="write somethg related";

	//$mailer->AddAddress("yinsee@wsatp.com");
	$mailer->AddAddress("garykoay@wsatp.com");
	//$mailer->AddAddress("$po[vendor_email]");
	//$mailer->AddAddress("sllee@aneka.com.my");
	print "Sending";
	$send = $mailer->Send();
	var_dump($send);
	
	$con->sql_query("update po set email_sent=1 where id=".ms($po['po_id'])." and branch_id=".ms($po['po_branch_id']));
	*/
}

?>

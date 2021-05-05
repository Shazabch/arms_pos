{*
Revision History
================
4/20/07 2:38:52 PM yinsee
- added branch_id for all PM.php call

5/21/2007 3:12:59 PM yinsee
- remove PM consolidation of same message

9/21/2007 12:13:05 PM gary
- add GRR Notification.

9/27/2007 2:38:49 PM gary
- added Inactive Users Notification.

11/14/2007 5:54:00 PM yinsee
- add price change notification

11/15/2007 12:41:22 PM gary
- change inactive users notification.

11/23/2007 10:41:10 AM gary
-remove inactive users notification.

11/23/2007 5:10:48 PM gary
- add adjustment notification.

12/3/2007 1:05:58 PM gary
- modify the adjustment notification.

12/3/2007 5:56:01 PM gary
- modify do notification.

3/5/2008 11:47:11 AM gary
- change old po link to new po link.

12/1/2009 5:37:30 PM Andy
- add Consignment Invoice notification.

2/6/2009 6:17:09 PM yinsee
- add disk space monitoring

9/7/2009 3:12:12 PM yinsee
- new sku notification

10/1/2009 10:25 AM andy
- add new DO Request notification

1/7/2010 11:17:41 AM Andy
- Fix link to sku approval error screen

3/16/2010 4:05:06 PM Andy
- Add member summary at notification

7/19/2010 3:45:55 PM Andy
- Add counter collection un-finalized notice at main page. (Need privilege to see)

7/27/2010 10:50:11 AM Andy
- Unfinalized pos notification change to not show "+" if there is only 1 day.

8/18/2010 11:12:52 AM Justin
- Created a new notification feature on home page to allow user view to be expired redemption items (need privilege "NT_RDM_ITEM").
- Added the redemption notification on home page (need config.membership_redemption_use_approval).

8/24/2010 3:11:36 PM Justin
- Created Redemption Item Approval notification.
- Required MEMBERSHIP_ITEM_CFRM to view and approve membership redemption item.

12/2/2010 3:43:58 PM Andy
- Fix notification when click "Mark as read and close", it could close wrong pm.

1/13/2011 3:18:52 PM Andy
- Add checking if found $config['counter_collection_server'] will popup windows to use remote server.

2/21/2011 9:59:34 AM Andy
- Change "un-finalized POS" to "Non-finalized POS", and add a comment.

2/28/2011 2:48:52 PM Andy
- Fix counter collection open in data center bugs. (date not pass when redirect)

5/24/2011 5:48:28 PM Andy
- Add show GRN Distribution Status at notification page.

9/6/2011 4:56:31 PM Andy
- Add show notification for Stock Reorder.

9/8/2011 5:22:42 PM Andy
- Add can delete GRN distribution status from notification. (must level 9999)

9/12/2011 5:57:05 PM Andy
- Re-arrange "Vendor Stock Reorder" layout, wrap "Est SKU" to next line.

9/30/2011 12:14:42 PM Justin
- Modified the GRN approval flow area to have GRN confirmation listing that allows user to click in and do confirmation.

10/10/2011 6:31:32 PM Justin
- Fixed the GRR notification days to base on config set value instead of fixed it as 3.

11/17/2011 2:57:43 PM Andy
- Fix PO Overdue to only show those po_option=0

12/12/2011 1:23:00 PM Kee Kee
- Show Lock Price Items

2/14/2012 11:47:43 AM Justin
- Modified the link Membership Redemption to use login.php for single server mode problem.

4/17/2012 5:08:32 PM Justin
- Added new approval notification "Batch Price Change".

7/3/2012 2:37 PM Andy
- Add pregen reorder list can define reorder type by using config.

7/18/2012 11:18 AM Justin
- Added batch price change notification by using ajax call.
- Added to have user level checking for batch price change, hidden while user is a guest.
- Enhanced to redirect get batch price change to use ajax_notification.php.

7/26/2012 4:46:34 PM Justin
- Bug fixed for some of the users can't view batch price change items.

8/10/2012 11:13 AM Andy
- Add purchase agreement approval notification.

7/8/2013 9:42 AM Justin
- Bug fixed on batch price change approval that is not accessible.

7/10/2013 11:06 AM Justin
- Added GRA approval notification.

10/18/2013 10:33 AM Fithri
- Limit notification font size to maximum of 10em

12/23/2013 10:23 AM Fithri
- new module 'Stucked Documents Approval'

2/11/2014 2:45 PM Justin
- Enhanced to order PO overdue info to sort by latest PO date.

3/24/2014 5:27 PM Justin
- Modified the wording from "Finalize" to "Finalise".

3/11/2015 2:34 PM Andy
- Add urlencode to variable 'server' in all hyperlink.

4/23/2015 3:37 PM Andy
- Enhanced to able to click on GRN Distribution deliver % to view GRN Distribution Report.
- Enhanced GRN Distribution Status to load only limited data, user will need to click on link to view more data.

6/1/2015 1:28 PM Justin
- Enhanced to show invoice no. list while found the GRR is having PO & invoice.
- Enhanced to show total count by different status of GRN documents.

6/23/1015 5:29 PM Eric
- Login Notification Enhancement 
- Move most of the code to notifications_left & right_sidebar.tpl so the left and right sidebar will load with ajax

7/23/2015 10:55 AM Andy
- Enhanced to have delay 1 second before load left and load right notification.

11/17/2015 16:30 PM Qiu Ying
- Enhance PM layout and allow user to delete

11/25/2015 1:20 PM Qiu Ying
- pm check opened, bold if not yet opened

1/25/2016 1:40 PM Andy
- Change the word "Clear" to "Clear All".

3/16/2017 10:57 AM Andy
- Added "Last DB Cutoff Date".

2017-08-24 14:32 PM Qiu Ying
- Enhanced to add pagination in dashboard pm

5/22/2018 10:38 AM Justin
- Enhanced to add "Announcements" into the notification sidebar.

7/2/2018 1:23 PM Andy
- Hide "Announcements" area.

8/29/2018 1:15 PM Andy
- Add "SST Amendment Notice" at Announcements.

9/27/2018 4:59 PM Andy
- Add Annoucement list can load from file.

04/08/2020 4:07 PM Sheila
- Modified layout to compatible with new UI.

05/07/2020 4:07 PM Sheila
- Fixed "free space" table width

*}
{literal}
<style>
.ntc {
	font-size:0.8em;
	color:#666;
}
ul {
padding:0;margin:0;
}
</style>
{/literal}

<script>
var user_level = '{$sessioninfo.level}';
var userz = '{$sessioninfo.u}';

{literal}
function delete_grn_distribution(bid, grn_id){
	var img = $('img_delete_grn_distribution-'+bid+'-'+grn_id);
	
	if(img.src.indexOf('clock')>=0){
		alert('Please wait...');
		return false;
	}
	var ori_src = img.src;
	
	img.src = '/ui/clock.gif';
	
	new Ajax.Request('ajax_autocomplete.php', {
		parameters:{
			a: 'ajax_delete_grn_distribution',
			bid: bid,
			grn_id: grn_id,
		},
		onComplete: function(msg){
			var str = msg.responseText.trim();
			if(str == 'OK'){
				$('div_grn_distribution-'+bid+'-'+grn_id).remove();
			}else{
				alert(str);
				img.src = ori_src;
			}
		}
	});
}

// MOVE TO ajax_notification and notifications_right_sidebar.tpl
// function ajax_notification_updates(){
// 	ajax_get_bpc();
// }

// function ajax_get_bpc(){
// 	if(user_level == 0) return; // not guest

// 	new Ajax.Request('ajax_notification.php', {
// 		parameters:{
// 			a: 'ajax_get_bpc'
// 		},
// 		onComplete: function(msg){
// 			var str = msg.responseText.trim();
// 			var ret = {};
// 			var err_msg = '';

// 			ret = JSON.parse(str); // try decode json object
// 			if(ret['ok'] == 1 && ret['html']){
// 				$('div_bpc_items').update(ret['html']);
// 			}else{
// 				$('div_bpc').hide();
// 			}
// 		}
// 	});
// }

function ajax_left_sidebar(){
	new Ajax.Request('ajax_notification.php', {
		parameters:{
			a: 'ajax_left_sidebar'
		},
		onComplete: function(msg){
			var str = msg.responseText.trim();
			
			$('left_content').update(str);
			
			// start load after 1s
			setTimeout(function(){ ajax_right_sidebar(); }, 1000);
		}
	});
}

function ajax_right_sidebar(){
	new Ajax.Request('ajax_notification.php', {
		parameters:{
			a: 'ajax_right_sidebar'
		},
		onComplete: function(msg){
			var str = msg.responseText.trim();
			
			$('right_content').update(str);
		}
	});
}

function ajax_get_pm(page_start, load_leftsidebar){
	if(load_leftsidebar == undefined){
		load_leftsidebar = 0;
	}
	$('pm').update(_loading_+ ' Please wait...');
	new Ajax.Request('pm.php', {
		parameters:{
			a: 'ajax_get_pm',
			s: page_start
		},
		onComplete: function(msg){
			var str = msg.responseText.trim();
			
			$('pm').update(str);
			
			if(load_leftsidebar){
				setTimeout(function(){ ajax_left_sidebar(); }, 1000);
			}
		}
	});
}

function pm_delete(branch_id,id)
{
	new Ajax.Request("pm.php",
	{
		parameters:'a=ajax_mark_read&branch_id='+branch_id+'&id='+id,
		onComplete:function()
		{
			new Effect.Fade('pm-'+branch_id+'-'+id, 'slow');
			if($("s")){
				var s = $("s").value;
			}else{
				var s = 0;
			}
			ajax_get_pm(s);
		}
	}); 
	return false;
}

function clear_all_pm()
{
    if (confirm("Are you sure you want to mark all as read?") == true) 
	{
		new Ajax.Updater('pm', 'pm.php',{
			parameters: 'a=mark_all_read',
			evalScripts: true,
			onComplete: function(m){
				ajax_get_pm(0);
			}
		});
		
	}
	return false;
}

function change_style(obj,branch_id,id){
	obj.style.fontWeight = "normal";
	$('pm-'+branch_id+'-'+id).style.backgroundColor  = "white";
}
{/literal}
</script>

<!-- start left -->
<div class="leftbar" style="float:left; padding-right:10px; border-right: 1px dashed #ddd; width:200px;">


<!-- announcement-->
{if $announcementList}
<div class="leftbar-div">
	<h5>
	{*<img src="/ui/icons/bell.png" align="absmiddle" border="0"> *}
	<i class="icofont-megaphone-alt icofont"></i> Announcements</h5>
	<div style="margin-bottom:10px; border-bottom: 1px solid #eee;">
		<ul>		
			{foreach from=$announcementList key=announcement_code item=r}
				<li id="li_announcement-{$announcement_code}">
					<a target="_blank" href="announcement.php?a=view&code={$announcement_code}">
						{if $r.is_new}<b>{/if}
						{$r.title}
						{if $r.is_new}
							</b>
							{if $r.is_new}<img src="/ui/icons/new.png" align="absmiddle" border="0">{/if}
						{/if}
					</a>
				</li>
			{/foreach}
		</ul>
	</div>
</div>
{/if}

<!-- disk space -->
{if $disk_space}
<div class="leftbar-div">
	<h5>
	{*<img src=/ui/icons/database.png align=absmiddle border=0>*}
	<i class="icofont-database icofont"></i> Free Space</h5>
	<table class="tb small free_space_tbl" cellpadding=4 cellspacing=0 >
	<tr bgcolor=#ffee99 class="tbl-thead"><th>Device</th><th>Free</th><th>Mount</th></tr>
	{foreach from=$disk_space item=ds}
	{assign var=pct value=$ds[3]/$ds[1]*100|intval}
	<tr {if $pct<10}style="color:red;background:yellow"{/if}><td>{$ds[0]}</td><td>{$ds[3]/1024|intval}MB ({$pct}%)</td><td>{$ds[5]}</td></tr>
	{/foreach}
	</table>
</div>
{/if}
{if $config.db_last_cutoff_date}
	<div class="ntc">Last DB Cutoff Date: {$config.db_last_cutoff_date}</div>
{/if}

<div id="left_content">
	<p><img src="/ui/clock.gif" align="absmiddle"> Loading content, please wait. . .</p>
</div>

<!-- Inactive User notification -->
{*
{if $inactive_users && $sessioninfo.level>=9999}
<h5><img src=/ui/notify_mm.png align=absmiddle border=0> Inactive Users</h5>
<div class=ntc>The following users are inactived in ARMS more than 1 month :</div>
<div style="border:1px solid #ccc;padding:5px;height:200px;overflow:auto;">
{foreach from=$inactive_users item=e key=branch}
	<div style="border:1px solid #eee;background-color:#ccc;color:red;">
	{$inactive_users.$branch[i].b_code}
	</div>
	{section name=i loop=$inactive_users.$branch}
	{if $inactive_users.$branch[i].u}
	<div style="border-bottom:1px solid #eee">{$inactive_users.$branch[i].u}</div>
	{/if}
	{/section}
{/foreach}
</div>
{/if}
*}
</div>
<!-- end left -->


<!-- start right -->
<div class="rightbar" style="float:right; padding-left:10px; border-left: 1px dashed #ddd; width:200px;">


<div id="right_content">
	<p><img src="/ui/clock.gif" align="absmiddle"> Loading content, please wait. . .</p>
</div>

<!-- Offline Documents -->
{if $off_docs}
<div class="leftbar-div">
	<h5><img src=/ui/store.png align=absmiddle border=0> Offline Documents</h5>
	<div class=ntc>The following documents have been uploaded from Offline Server</div>
	<div style="border:1px solid #ccc;padding:5px;height:200px;overflow:auto;">
		{foreach from=$off_docs key=m item=dl}
			{foreach from=$dl item=r}
				{if $m eq 'adj'}
				<div style="border-bottom:1px solid #eee">
					<a href="/adjustment.php?a=open&id={$r.id}&branch_id={$r.branch_id}" target="_blank">Adjustment #{$r.id}</a>
					<br />
					<font color="#666666" class="small">
						Received Date : {$r.added}<br>
					</font>
				</div>
				{elseif $m eq 'sku'}
				<div style="border-bottom:1px solid #eee">
					<a href="/masterfile_sku_application.php?a=view&id={$r.id}" target="_blank">SKU #{$r.id}</a>
					<br />
					<font color="#666666" class="small">
						Received Date : {$r.added}<br>
					</font>
				</div>
				{elseif $m eq 'do'}
				<div style="border-bottom:1px solid #eee">
					<a href="/do.php?a=open&id={$r.id}&branch_id={$r.branch_id}&do_type={$r.do_type}" target="_blank">DO #{$r.id}</a>
					&nbsp;&nbsp;&nbsp;&nbsp;<font color="006600" class="small">{$r.do_type|upper}</font>
					<br />
					<font color="#666666" class="small">
						Received Date : {$r.added}<br>
					</font>
				</div>
				{elseif $m eq 'po'}
				<div style="border-bottom:1px solid #eee">
					<a href="/po.php?a=open&id={$r.id}&branch_id={$r.branch_id}" target="_blank">PO #{$r.id}</a>
					<br />
					<font color="#666666" class="small">
						Received : {$r.added}<br>
					</font>
				</div>
				{elseif $m eq 'gra'}
				<div style="border-bottom:1px solid #eee">
					<a href="/goods_return_advice.php?a=open&id={$r.id}&branch_id={$r.branch_id}" target="_blank">GRA{$r.id|string_format:'%05d'}</a>
					<br />
					<font color="#666666" class="small">
						Received : {$r.added}<br>
					</font> 
				</div>
				{elseif $m eq 'grn'}
				<div style="border-bottom:1px solid #eee">
					<a href="/goods_receiving_note.php?a=open&id={$r.id}&branch_id={$r.branch_id}&action=edit" target="_blank">GRN{$r.id|string_format:'%05d'}</a>
					<br />
					<font color="#666666" class="small">
						Received : {$r.added}<br>
					</font>
				</div>
				{elseif $m eq 'grr'}
				<div style="border-bottom:1px solid #eee">
					<a href="/goods_receiving_record.php?a=view&id={$r.id}&branch_id={$r.branch_id}" target="_blank">GRR{$r.id|string_format:'%05d'}</a>
					<br />
					<font color="#666666" class="small">
						Received : {$r.added}<br>
					</font>
				</div>
				{/if}
			{/foreach}
		{/foreach}
	</div>
</div>
{/if}
</div>
<!-- end right -->

<div style="margin-left:220px;margin-right:220px;">
<!-- SKU revise notification -->
{if $sku_revision}
<div class="rightbar-div">
	<h5><img src=/ui/notify_sku_reject.png align=absmiddle border=0> Rejected SKU Applications</h5>
	<div class=ntc>Please Revise your SKU Application and submit again.</div>
	<ul style="margin-bottom:10px; border-bottom: 1px solid #eee;">
	{section name=i loop=$sku_revision}
	<li> <a href="masterfile_sku_application.php?a=revise&id={$sku_revision[i].id}">#{$sku_revision[i].id} - {$sku_revision[i].brand_desc} {$sku_revision[i].category_desc} (Department: {$sku_revision[i].department})</a>
	<br><font color=#666666 class=small>{$sku_revision[i].added}</font>
	{/section}
	</ul>
</div>
{/if}

<!-- SKU pending notification -->
{if $sku_pending}
<div class="rightbar-div">
	<h5><img src=/ui/notify_sku_pending.png align=absmiddle border=0> Pending SKU Applications</h5>
	<div class=ntc>The following applications are Pending. Please Review and Approve them.</div>
	<ul style="margin-bottom:10px; border-bottom: 1px solid #eee;">
	{section name=i loop=$sku_pending}
	<li> <a href="masterfile_sku_approval.php?id={$sku_pending[i].id}">#{$sku_pending[i].id} - {$sku_pending[i].u} ({$sku_pending[i].apply_branch})
	{$sku_pending[i].brand_desc} {$sku_pending[i].category_desc}
	(Department: {$sku_pending[i].department})
	</a>
	<br><font color=#666666 class=small>{$sku_pending[i].added}</font>
	{/section}
	</ul>
</div>
{/if}

<!-- PO revise notification -->
{if $po_revision}
<div class="rightbar-div">
	<h5><img src=/ui/rejected.png align=absmiddle border=0> Rejected PO</h5>
	<div class=ntc>Please Revise your Purchase Orders and submit again.</div>
	<ul style="margin-bottom:10px; border-bottom: 1px solid #eee;">
	{section name=i loop=$po_revision}
	<li> 
	{*
	<a href="purchase_order.php?a=open&id={$po_revision[i].id}&branch_id={$po_revision[i].branch_id}">
	*}
	<a href="po.php?a=open&id={$po_revision[i].id}&branch_id={$po_revision[i].branch_id}">
	#{$po_revision[i].id} - (Department: {$po_revision[i].department})</a>
	<br><font color=#666666 class=small>{$po_revision[i].last_update}</font>
	{/section}
	</ul>
</div>
{/if}

<!-- Promotion reject notification -->
{if $promo_reject}
<div class="rightbar-div">
	<h5><img src=/ui/rejected.png align=absmiddle border=0> Rejected Promotion</h5>
	<div class=ntc>Please Revise your Promotion and submit again.</div>
	<ul style="margin-bottom:10px; border-bottom: 1px solid #eee;">
	{section name=i loop=$promo_reject}
	<li> 
	<a href="promotion.php?a=open&id={$promo_reject[i].id}&branch_id={$promo_reject[i].branch_id}">
	#{$promo_reject[i].id} {$promo_reject[i].title}</a>
	<br><font color=#666666 class=small>{$promo_reject[i].last_update}</font>
	{/section}
	</ul>
</div>
{/if}


<!-- DO revise notification -->
{if $do_revision}
<div class="rightbar-div">
	<h5><img src=/ui/rejected.png align=absmiddle border=0> Rejected DO</h5>
	<div class=ntc>Please Revise your Delivery Orders and submit again.</div>
	<ul style="margin-bottom:10px; border-bottom: 1px solid #eee;">
	{section name=i loop=$do_revision}
	<li> 
	<a href="do.php?a=open&id={$do_revision[i].id}&branch_id={$do_revision[i].branch_id}">
	#{$do_revision[i].id} - (Branch: {$do_revision[i].branch})</a>
	<br><font color=#666666 class=small>{$do_revision[i].last_update}</font>
	{/section}
	</ul>
</div>
{/if}

<!-- PM notification -->
<div id=pm>
	<p><img src="/ui/clock.gif" align="absmiddle"> Loading content, please wait. . .</p>
</div>
</div>
<script>
{literal}
//ajax_notification_updates();
setTimeout(function(){ ajax_get_pm(0,1); }, 1000);
//ajax_left_sidebar();
//ajax_right_sidebar();
{/literal}
</script>
{*
8/20/2009 3:20:34 PM Andy
- add reset remark

7/2/2010 4:11:39 PM Alex
- fix search bugs

10/6/2010 3:34:47 PM Justin
- Added GRN future to show different tabs base on config set.

2/18/2011 5:55:27 PM Justin
- Check on request 't' else default 1 on first load at function list_sel

6/8/2011 4:21:11 PM Justin
- Fixed the bugs found when with/without using GRN future.

6/28/2011 9:50:12 AM Justin
- Fixed the bugs while found use GRN future config and create bugs because found it is equal to zero.

9/8/2011 5:35:43 PM Justin
- Renamed the function of print_summary become print_grn.
- Re-aligned the printing options to fill into table.

9/15/2011 11:53:11 AM Justin
- Fixed the GRN Variance Report cannot be printed.

11/10/2011 2:04:43 PM Justin
- Fixed the bugs show empty status message when not using GRN future.

11/24/2011 4:30:02 PM Andy
- Fix PO link to GRN cannot auto search the related GRN.

12/15/2011 4:05:32 PM Justin
- Fixed the wrong assign of tab pane access key.

6/28/2012 4:23:34 PM Justin
- Enhanced to have more printing options.
- Placed GRN report into the list of printing options.
- Added new printing option "GRN Performa Report".

7/2/2012 1:45:12 PM Justin
- Changed the GRN Performa Report name become GRA Report.
- Modified JS function to have this changes.

7/3/2012 3:34:44 PM Justin
- Added new feature that allows user to change GRN owner when GRN document is save as draft.

7/4/2012 4:24:12 PM Justin
- Added new feature to allow user print new report "Debit Note Report" while config is on.

7/13/2012 11:05:23 AM Justin
- Added to do checking whether document can/cannot print D/N Report.
- Enhanced D/N report can print on waiting for approval and approved tabs.

7/19/2012 10:20:23 AM Justin
- Bug fixed cannot find DN report in JS.

7/31/2012 11:42:34 AM Justin
- Enhanced to have vendor search by autocomplete.

12/24/2012 1:39 PM Justin
- Enhanced to prevent user double tab and double import PO/DO items while creating new GRN from GRR.

2/7/2013 5:05 PM Justin
- Enhanced to show "GRN Report" instead of "GRA Report".

3/27/2014 2:35 PM Justin
- Bug fixed on page no responding while do printing that certain GRN future functions is turned on.

4/10/2014 11:20 AM Justin
- Enhanced to add "GRN Summary (ACC Copy)" into printing option, this option available while in approved tab.

11/25/2014 1:22 PM Justin
- Bug fixed on system will not show the printing option as every tab differently when using search engine.

3/14/2015 9:42 AM Justin
- Enhanced to have generate and print DN feature.

4/7/2015 11:44 AM Justin
- Bug fixed on Print Performance and Variance report options some times will not displayed.

4/8/2015 1:48 PM Justin
- Enhanced to allow user can print returned items under account summary.

11/3/2015 1:59 PM DingRen
- add GRN approval link

11/11/2015 6:03 PM DingRen
- remove GRN approval link

11/25/2015 11:00 AM Qiu Ying
- GRN can search GRR

2/29/2016 2:30 PM Qiu Ying
- Auto reset GRR when GRN is canceled

2017-09-13 10:41 AM Qiu Ying
- Bug fixed on treating special characters as wildcard character

5/23/2019 9:58 AM William
- Enhance "GRR","GRN" word to use report_prefix.

06/25/2020 03:25 PM Sheila
- Updated button css.

11/9/2020 10:58 AM William
- Added export grn items function.
*}

{include file=header.tpl}
{literal}
<style>
.sh{
    background-color:#ff9;
}

.stdframe.active{
 	background-color:#fea;
	border: 1px solid #f93;
}

.calendar, .calendar table {
	z-index:100000;
}
</style>
{/literal}

<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes
   adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

<script>
{if $smarty.request.t eq 0}
	var tab = 1;
{else}
	var tab = '{$smarty.request.t}';
{/if}

var use_grn_future = '{$config.use_grn_future}';
var grn_enable_dn_report = '{$config.grn_enable_dn_report}';

{literal}
function curtain_clicked(){
	curtain(false);
	hidediv('print_dialog');
	hidediv('print_dn_dialog');
}


function do_print(id,bid){
	document.f_prn.id.value=id;
	document.f_prn.branch_id.value=bid;
	
	if(use_grn_future == 1){
		var active = $('active_'+id+'_'+bid).value;
		var status = $('status_'+id+'_'+bid).value;
		var authorized = $('authorized_'+id+'_'+bid).value;
		var approved = $('approved_'+id+'_'+bid).value;
		if(active == 1 && authorized == 0 && status == 0 && approved == 0){ // saved
			tab = 1;
		}else if(active == 1 && authorized == 1 && status == 0 && approved == 0){ // account verification
			tab = 3;
		}else if(active == 1 && authorized == 1 && status == 1 && approved == 0){ // waiting for approval
			tab = 5;
		}else if(active == 1 && authorized == 1 && status == 1 && approved == 1){ // approved
			tab = 6;
		}
	}
	show_print_dialog();
}

function show_print_dialog(){
	if(use_grn_future == 1){
		if(tab == 5){
			document.f_prn.print_grn_var_report.checked = false;
			document.f_prn.print_grn_perform_report.checked = false;
			document.f_prn.print_gra_report.checked = true;
			document.f_prn.print_grn_future_report.checked = false;
			$('span_gra_report').show();
			$('span_acc_grn_summary').hide();
			if(grn_enable_dn_report==1) $('span_dn_report').show();
			$('ori_print_option').hide();
		}else{
			document.f_prn.print_gra_report.checked = false;
			$('span_gra_report').hide();
			$("span_acc_grn_summary").hide();
			if(tab >= 3 && tab != 4){
				if(grn_enable_dn_report==1){
					$('span_dn_report').show();
					document.f_prn.print_dn_report.checked = false;
				}
			}else{
				if(grn_enable_dn_report==1) $('span_dn_report').hide();
			}
			
			if(tab <= 4 && tab !=3){ // saved GRN
				document.f_prn.print_grn_future_report.checked = true;
				document.f_prn.print_grn_var_report.checked = false;
				document.f_prn.print_grn_perform_report.checked = false;
				var id = document.f_prn.id.value;
				var branch_id = document.f_prn.branch_id.value;
				print_grn(id, branch_id);
				return;
			}else if(tab == 3){ // account verification
				document.f_prn.print_grn_future_report.checked = false;
				document.f_prn.print_grn_var_report.checked = false;
				document.f_prn.print_grn_perform_report.checked = false;
				$('ori_print_option').hide();
			}else if(tab == 6){ // approved
				document.f_prn.print_grn_perform_report.checked = true;
				document.f_prn.print_grn_var_report.checked = true;
				document.f_prn.print_gra_report.checked = true;
				$('span_gra_report').show();
				$("span_acc_grn_summary").show();
				$('ori_print_option').show();
			}
		}
	}else{
		if(tab == 4){
			$("span_acc_grn_summary").show();
		}else{
			$("span_acc_grn_summary").hide();
		}
	}

	if(use_grn_future == 1 && grn_enable_dn_report==1 && $('span_dn_report').style.display == ""){
		var id = document.f_prn.id.value;
		var branch_id = document.f_prn.branch_id.value;
		new Ajax.Request("goods_receiving_note.php", {
			method:'post',
			parameters: 'a=ajax_validate_dn_report&id='+id+'&branch_id='+branch_id,
			evalScripts: true,
			onFailure: function(m) {
				alert(m.responseText);
			},
			onSuccess: function(m) {
				var msg = m.responseText;
				
				if(msg.trim() == "no"){
					$('span_dn_report').hide();
				}
			}
		});
	}
	
	curtain(true);
	center_div('print_dialog');
	$('print_dialog').style.display = '';
	$('print_dialog').style.zIndex = 10000;
}

function do_print_preview(id,bid){
	window.open('goods_receiving_note.php?id='+id+'&branch_id='+bid+'&a=print&print_grn_perform_report=1&noprint=1','','width=800,height=600,scrollbars=yes,resizable=yes');
	 
}

function print_ok(){
	document.f_prn.a.value='print';
	$('print_dialog').style.display = 'none';
	//document.f_prn.target = "ifprint";
	document.f_prn.target = "_blank";
	document.f_prn.submit();
	curtain(false);
}

function print_grn(id, branch_id){
	document.f_prn.a.value='print';
	document.f_prn.id.value=id;
	document.f_prn.branch_id.value=branch_id;
	document.f_prn.print_grn_var_report.checked = false;
	document.f_prn.print_grn_perform_report.checked = false;
	document.f_prn.print_gra_report.checked = false;
	if(grn_enable_dn_report==1) document.f_prn.print_dn_report.checked = false;
	document.f_prn.target = "_blank";
	document.f_prn.submit();
}

function grn_change_owner(id,branch_id){
	var p = prompt('Enter the username for new PO owner:');
	if (p.trim()=='' || p==null) return;
	
	new Ajax.Request('/goods_receiving_note.php?a=grn_change_owner&id='+id+'&branch_id='+branch_id+'&new_owner='+p, { evalScripts: true, onComplete: function(m) { alert(m.responseText); list_sel(1) }});
}

function prevent_doubleclick(obj){
	if(obj.rel=="#"){
		obj.rel = obj.href;
		setTimeout(function() { 
				obj.href = obj.rel; 
				obj.rel = "#"; 
			}
		, 1000);
	}else{
		obj.href = "#";
	}
}

function toggle_dn_printing_menu(grn_id, branch_id){
	$('inv_no').value = "";
	$('inv_date').value = "";
	$('remark').value = "";
	$("dn_grn_id").value = grn_id;
	$("dn_bid").value = branch_id;
	showdiv("print_dn_dialog");
	center_div("print_dn_dialog");
	curtain(true);
}

function print_dn_ok(){
	if(!$('dn_grn_id').value || !$('dn_bid').value){
		alert("GRN ID or BID not found");
		return false;
	}
	
	if((!$("inv_no").value.trim() && !$("inv_date").value.trim()) || ($("inv_no").value.trim() && $("inv_date").value.trim())){
		// do nothing
	}else{
		alert("Please provide both Invoice No and Date.");
		return false;
	}
	
	window.open("goods_receiving_note.php?a=print_arms_dn&id="+$('dn_grn_id').value+"&branch_id="+$('dn_bid').value+"&inv_no="+$('inv_no').value+"&inv_date="+$('inv_date').value+"&remark="+$('remark').value, '_blank');
	list_sel(1);
	curtain_clicked();
}

function export_grn_item(id, bid){
	document.f_prn.id.value=id;
	document.f_prn.branch_id.value=bid;
	document.f_prn.a.value='export_grn_item';
	document.f_prn.submit();	
}

{/literal}
</script>

{if $msg}<p align=center style="color:#00f">{$msg}</p>{/if} 
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>
{if $smarty.request.t eq 'save'}
<img src="/ui/approved.png" align="absmiddle"> GRN saved as {$smarty.request.report_prefix}{$smarty.request.id|string_format:"%05d"}<br>
{elseif $smarty.request.t eq 'grr_save'}
<img src="/ui/approved.png" align="absmiddle"> {$smarty.request.report_prefix}{$smarty.request.id|string_format:"%05d"} saved ({$smarty.request.msg_type})
{elseif $smarty.request.t eq 'cancel'}
<img src="/ui/cancel.png" align="absmiddle"> {$smarty.request.report_prefix}{$smarty.request.id|string_format:"%05d"} was cancelled and {$smarty.request.report_prefix}{$smarty.request.grr_id|string_format:"%05d"} was reset.<br>
{elseif $smarty.request.t eq 'confirm'}
{if $smarty.request.la}
<img src="/ui/approved.png" align="absmiddle"> {$smarty.request.report_prefix}{$smarty.request.id|string_format:"%05d"} is now official<br>
{else}
<img src="/ui/approved.png" align="absmiddle"> {$smarty.request.report_prefix}{$smarty.request.id|string_format:"%05d"} has been confirmed{if $smarty.request.msg_type} and sent to {$smarty.request.msg_type}{/if}.<br>
{/if}
{elseif $smarty.request.t eq 'approve'}
{if $smarty.request.la}
<img src="/ui/approved.png" align="absmiddle"> {$smarty.request.report_prefix}{$smarty.request.id|string_format:"%05d"} is now official<br>
{else}
<img src="/ui/approved.png" align="absmiddle"> {$smarty.request.report_prefix}{$smarty.request.id|string_format:"%05d"} has been submitted for verification<br>
{/if}
{elseif $smarty.request.t eq 'reset' || $smarty.request.t eq 'reject'}
<img src="/ui/notify_sku_reject.png" align="absmiddle"> {$smarty.request.report_prefix}{$smarty.request.id|string_format:"%05d"} was {$smarty.request.t}{if $smarty.request.msg_type} and reverted back to {$smarty.request.msg_type}{/if}.
{/if}

<!-- print dialog -->
<div id="print_dialog" style="background:#fff;border:3px solid #000;width:300px;position:absolute; padding:10px; display:none;">
<form name="f_prn" method="get">
<table border="0" width="100%">
	<tr>
		<td rowspan="2"><img src="ui/print64.png" hspace="10" align="left"></td>
		<td><h3>Print Options</h3></td>
	</tr>
	<tr>
		<td>
			<span id="span_acc_grn_summary">
				<input type="checkbox" name="print_grn_summary" value="1"> GRN Summary (ACC Copy)<br />
				<img src="ui/pixel.gif" width="19"><input type="checkbox" name="print_returned_items" value="1"> Print Returned Items<br />
			</span>
			{if $config.use_grn_future}
				<input type="checkbox" name="print_grn_future_report" value="1" checked> GRN Summary<br />
				<span id="span_gra_report">
					<input type="checkbox" name="print_gra_report" value="1" checked> GRN Report<br />
				</span>
				{if $config.grn_enable_dn_report}
					<span id="span_dn_report">
						<input type="checkbox" name="print_dn_report" value="1" checked> Debit Note Report<br />
					</span>
				{/if}
			{/if}
			<span id="ori_print_option">
				<input type="checkbox" name="print_grn_{if $config.use_grn_future}var_{/if}report" value="1" checked> GRN Variance Report<br />
				<input type="checkbox" name="print_grn_perform_report" value="1"> GRN Performance Report<br />
			</span>
		</td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<br />
			<input type="button" value="Print" onclick="print_ok()"> 
			<input type="button" value="Cancel" onclick="curtain_clicked()">
		</td>
	</tr>
</table>
<input type="hidden" name="a" value="print">
<input type="hidden" name="load" value="1">
<input type="hidden" name="id" value="">
<input type="hidden" name="branch_id" value="">
<input type="hidden" name="action" value="{$smarty.request.action}">
<input type="hidden" name="newly_added" value="{$smarty.request.newly_added}">
</form>
</div>

<!-- print DN dialog -->
<div id="print_dn_dialog" style="position:absolute;z-index:10000;background:#fff;border:3px solid #000;width:350px;padding:10px;display:none;">
<table border="0" width="100%">
	<tr>
		<td colspan="2"><h3>Print D/N:</h3></td>
	</tr>
	<tr>
		<td><b>Invoice No.: </b></td>
		<td><input type="text" id="inv_no"></td>
	</tr>
	<tr>
		<td><b>Invoice Date: </b></td>
		<td><input type="text" id="inv_date" size="10" readonly><img align="absmiddle" src="ui/calendar.gif" id="invdate" style="cursor: pointer;" title="Select Date"></td>
	</tr>
	<tr>
		<td valign="top"><b>Remark: </b></td>
		<td><textarea id="remark"></textarea></td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<br />
			<input type="hidden" id="dn_grn_id" value="" />
			<input type="hidden" id="dn_bid" value="" />
			<input type="button" value="Print" onclick="print_dn_ok()"> 
			<input type="button" value="Cancel" onclick="curtain_clicked()">
		</td>
	</tr>
</table>
</div>

<iframe width=1 height=1 style="visibility:hidden" name=ifprint></iframe>
{*<ul>
{if $sessioninfo.privilege.GRN_APPROVAL}
		<li><img src="/ui/notify_sku_approve.png" align="absmiddle" /><a href="/goods_receiving_note_approval.php">GRN Approval</a></li>
	{/if}
</ul>*}
<ul style="list-style-type: none;">
{if !$config.use_grn_future}
<li> <form><img src=ui/new.png align="absmiddle"> <a href="javascript:void(togglediv('grr'))">Create GRN from GRR</a> &nbsp;&nbsp; <b>Find Document No.</b> <input name=find_grr value="{$smarty.request.find_grr}" size=5> <input type=submit value="Find"></form>
<!--- from GRR -->
<div id=grr style="padding:10 0px;{if !$smarty.request.find_grr}display:none{/if}">
{include file=goods_receiving_note.grr_list.tpl}
</div>
</li>
{/if}
<!--- end from GRR -->
<li class="bg-white" style="max-width: 150px;padding: 10px;"> <span class="ml-4">Existing GRN</span></li>
</ul>
{literal}
<script>
function list_sel(n,s){
	var i;

	tab = n;
	for(i=0;i<=7;i++){
		if($('lst'+i) == null) continue;
		if (i==n)
		    $('lst'+i).addClassName('selected');
		else
		    $('lst'+i).removeClassName('selected');
	}
	if(use_grn_future != 0){
		if(n == 7){
			$('grn_list').style.display = "none";
			$('grr_list').style.display = "";
			$('div_grn').style.display = "none";
			return;
		}else{
			$('grn_list').style.display = "";
			$('grr_list').style.display = "none";
			$('div_grn').style.display = "";
		}
	}

	//if (s=='') return;
	$('grn_list').innerHTML = '<img src=ui/clock.gif align="absmiddle"> Loading...';

	var pg = '';
	if (s!=undefined) pg = '&s='+s;
	if (n==0) pg +='&search='+ $('search').value+'&vendor_id='+document.f_s['vendor_id'].value;
	else $('search_area').hide();

	new Ajax.Updater('grn_list', 'goods_receiving_note.php', {
		parameters: encodeURI('a=ajax_load_grn_list&t='+n+pg),
		evalScripts: true
	});
	
}

function search_tab_clicked(obj){
	$('lst'+tab).addClassName('selected');
	$('search_area').show();
	obj.addClassName('selected');
	$('grn_list').update();
	if($('grr_list') != undefined) $('grr_list').style.display = "none";
	$('div_grn').style.display = "";
}
</script>
{/literal}

<div style="padding:10px 0;">
	<form name="f_s" onsubmit="list_sel(0,0); return false;">
	<div class=" mx-3">
		<div class="tab row mx-3 mb-3 " style="white-space:nowrap;">
			{if $config.use_grn_future}
				<a href="javascript:list_sel(7)" id="lst7" class="fs-07 ml-1 btn btn-outline-primary btn-rounded">GRR</a>
				<a href="javascript:list_sel(1)" id="lst1" class="fs-07 ml-1  btn btn-outline-primary btn-rounded">Saved GRN</a>
				<a href="javascript:list_sel(2)" id="lst2" class="fs-07 ml-1  btn btn-outline-primary btn-rounded">Pending Documents</a>
				<a href="javascript:list_sel(3)" id="lst3" class="fs-07 ml-1  btn btn-outline-primary btn-rounded">Account Verification</a>
				<a href="javascript:list_sel(4)" id="lst4" class="fs-07 ml-1  btn btn-outline-primary btn-rounded">Cancelled/Terminated</a>
				<a href="javascript:list_sel(5)" id="lst5" class="fs-07 ml-1  btn btn-outline-primary btn-rounded">Waiting for Approval</a>
				<a href="javascript:list_sel(6)" id="lst6" class="fs-07 ml-1  btn btn-outline-primary btn-rounded">Approved</a>
			{else}
				<a href="javascript:list_sel(1)" id="lst1" class="fs-07 ml-1 btn btn-outline-primary btn-rounded">Saved GRN</a>
				<a href="javascript:list_sel(2)" id="lst2" class="fs-07 ml-1  btn btn-outline-primary btn-rounded">Cancelled/Terminated</a>
				<a href="javascript:list_sel(3)" id="lst3" class="fs-07 ml-1  btn btn-outline-primary btn-rounded">Waiting for Verification</a>
				<a href="javascript:list_sel(4)" id="lst4" class="fs-07 ml-1  btn btn-outline-primary btn-rounded">Verified</a>
			{/if}
			<a name="find" class="fs-07 ml-1 mt-2  btn btn-outline-primary btn-rounded" id="lst0" onclick="search_tab_clicked(this);" style="cursor:pointer;">
				<span >Find GRN / Doc No / Vendor</span>
			</a>
	</div>
	</div>
	<div id="div_grn" >
		<div id="search_area" {if (!$smarty.request.search && !$smarty.request.vendor_id) && $smarty.request.t ne '0'}style="display:none;"{/if}>
			<div class="card mx-3">
				<div class="card-body">
					<table>
						<tr>
							<th align="left" class="form-label">Vendor</th>
							<td colspan="2">
								<input name="vendor_id" type="hidden" size="1" value="{$smarty.request.vendor_id}" readonly>
								<input class="form-control" id="autocomplete_vendor" name="vendor" value="{$smarty.request.vendor}" size=50>
								<div id="autocomplete_vendor_choices" class="autocomplete"></div><br />
							</td>
						</tr>
						<tr>
							<th align="left" class="form-label">Find GRN / Doc No / GRR</th>
							<td><input class="form-control" name="search" id="search" name="find" value="{$smarty.request.search}"></td>
							<td align="center"><input class="btn btn-primary" type="submit" value="Go"></td>
						</tr>
					</table>
				</div>
			</div>
		</div>
		<div id="grn_list">
			{include file=goods_receiving_note.list.tpl}
		</div>
	</div>
	</form>
	{if $config.use_grn_future}
		<div id="grr_list" style="{if !$smarty.request.find_grr}display:none{/if}">
			{include file=goods_receiving_note.grr_list.tpl}
		</div>
	{/if}
</div>

{include file=footer.tpl}
<script>
{if $config.use_grn_future}
	{if $smarty.request.t eq 'grr_save'}
		alert('{$smarty.request.report_prefix}{$smarty.request.id|string_format:"%05d"} saved ({$smarty.request.msg_type})');
	{elseif $smarty.request.t eq 'cancel'}
		alert('{$smarty.request.report_prefix}{$smarty.request.id|string_format:"%05d"} was cancelled.');
	{elseif $smarty.request.t eq 'reset' || $smarty.request.t eq 'reject'}
		alert('{$smarty.request.report_prefix}{$smarty.request.id|string_format:"%05d"} was {$smarty.request.t} and reverted back to {$smarty.request.msg_type}.');
	{elseif $smarty.request.t eq 'confirm'}
		{if $smarty.request.la}
			alert('{$smarty.request.report_prefix}{$smarty.request.id|string_format:"%05d"} is now official.');
		{else}
			alert('{$smarty.request.report_prefix}{$smarty.request.id|string_format:"%05d"} has been confirmed and sent to {$smarty.request.msg_type}.');
		{/if}
	{/if}
{/if}
{literal}
new Ajax.Autocompleter("autocomplete_vendor", "autocomplete_vendor_choices", "ajax_autocomplete.php?a=ajax_search_vendor", { afterUpdateElement: function (obj, li) { document.f_s.vendor_id.value = li.title; }});
{/literal}
list_sel(tab, '{$smarty.request.search}');

{literal}
	Calendar.setup({
		inputField     :    "inv_date",     // id of the input field
		ifFormat       :    "%Y-%m-%d",      // format of the input field
		button         :    "invdate",  // trigger for the calendar (button ID)
		align          :    "Bl",           // alignment (defaults to "Bl")
		singleClick    :    true
	});
{/literal}
</script>

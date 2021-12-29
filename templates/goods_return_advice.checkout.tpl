{*
Revision History
================
4/29/2011 4:21:11 PM Justin
- Fixed the print window that could not be close while click on curtain.

6/27/2011 12:20:22 PM Andy
- Add print preview page for GRA.

7/19/2012 5:18:23 PM Justin
- Added new function "GRA Disposal".
- Added new JS functions to support GRA Disposal.
- Added new tab "Disposed".

7/31/2012 11:42:34 AM Justin
- Enhanced to have vendor search by autocomplete.

8/7/2012 10:10:43 AM Justin
- Enhanced to to align error message into middle of division.

7/4/2013 2:36 PM Justin
- Enhanced to point first tab to pickup approved GRA instead fo saved GRA.
- Modified the tab name from "Saved" become "Approved" GRA.

07/19/2013 11:24 AM Justin
- Enhanced to show different info while config "gra_no_approval_flow" while is turned on.

3/24/2015 11:09 AM Justin
- Enhanced to have print DN feature.

4/29/2015 4:52 PM Justin
- Enhanced to pickup remark from hidden field while generating D/N.
- Enhanced to remove invoice no. and date.

5/22/2019 2:09 PM William
- Enhance "GRN" word to use report_prefix.

06/25/2020 3:30 PM Sheila
- Updated button css
*}
{include file=header.tpl}
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>
{literal}
<style>
#tbl_vvc input { 
	font: 10px "MS Sans Serif" normal;
}

.border{
	background-color:black;
}

.calendar, .calendar table {
	z-index:100000;
}
</style>
{/literal}
<script>

var gra_enable_disposal = '{$config.gra_enable_disposal}';
var gra_no_approval_flow = '{$config.gra_no_approval_flow}';

{if $smarty.request.t eq 0}
	var tab = 1;
{else}
	var tab = '{$smarty.request.t}';
{/if}
{literal}
function do_print_checklist(id,bid)
{
	var a = prompt('Enter Packing List #, leave blank to print the latest.');
	if (a==null) return false;
	ifprint.location = 'goods_return_advice.php?id='+id+'&bid='+bid+'&a=print_checklist&bno='+a;
	
}

function do_print(id,bid,no_dialog)
{
	document.f_prn.id.value=id;
	document.f_prn.branch_id.value=bid;
	if (no_dialog==true)
	{
	    print_ok();
	    return;
	}
	curtain(true);
	show_print_dialog();
}

function show_print_dialog()
{
	center_div('print_dialog');
	$('print_dialog').style.display = '';
	$('print_dialog').style.zIndex = 10000;
}

function print_ok()
{
    if(!document.f_prn['own_copy'].checked&&!document.f_prn['vendor_copy'].checked){
		alert('Please Select at least one copy');
		return false;
	}
	
	$('print_dialog').style.display = 'none';
	//document.f_prn.target = "ifprint";
	document.f_prn.target = "_blank";
	document.f_prn.submit();
	curtain(false);
}

function print_cancel()
{
	$('print_dialog').style.display = 'none';
	curtain(false);
}

function curtain_clicked()
{
	hidediv('print_dialog');
	if(gra_enable_disposal) hidediv('disposal_dialog');
	hidediv('print_dn_dialog');
	curtain(false);
}

function show_disposal_dialog(){
	curtain(true);
	center_div('disposal_dialog');
	$('disposal_dialog').style.display = '';
	$('disposal_dialog').style.zIndex = 10000;
	$('disposal_items').update();
}

function ajax_search_gra(){
	var due_date = document.f_dispose.due_date.value.trim();
	if(due_date == "") return; // not guest

	$('disposal_items').update(_loading_);
	new Ajax.Request('goods_return_advice.checkout.php', {
		parameters:{
			a: 'ajax_search_gra',
			due_date: due_date
		},
		onComplete: function(msg){
			var str = msg.responseText.trim();

			if(str){
				$('disposal_items').update(str);
				$('btn_area').show();
			}else{
				$('disposal_items').update("- No data -");
				$('btn_area').hide();
			}
		}
	});
}

function gra_dispose(){
	var	item = $('disposal_items').getElementsByClassName("disposal_item");
	var item_count = item.length;
	var got_item_checked = false;

	if(item_count > 0){
		$A(item).each(
			function (r,idx){
				if(r.checked == true) got_item_checked = true;
			}
		);
	}
	
	if(!got_item_checked){
		alert("No item selected!");
		return;
	}
	
	document.f_dispose.submit();
	curtain(false);
}

function init_calendar(){
	if(gra_enable_disposal){
		Calendar.setup({
			inputField     :    "due_date",     // id of the input field
			ifFormat       :    "%Y-%m-%d",      // format of the input field
			button         :    "b_due_date",  // trigger for the calendar (button ID)
			align          :    "Bl",           // alignment (defaults to "Bl")
			singleClick    :    true,
			onUpdate	   :	ajax_search_gra
		});
	}
}

function search_tab_clicked(obj){
	$('lst'+tab).removeClassName('selected');
	$('search_area').show();
	obj.addClassName('selected');
	$('gra_list').update();
}

function toggle_dn_printing_menu(gra_id, branch_id){
	$("dn_gra_id").value = gra_id;
	$("dn_bid").value = branch_id;
	if($("remark_"+gra_id+"_"+branch_id) != undefined && $("remark_"+gra_id+"_"+branch_id).value.trim() != ""){
		$('dn_remark').value = $("remark_"+gra_id+"_"+branch_id).value.trim();
	}else $('dn_remark').value = "";
	showdiv("print_dn_dialog");
	center_div("print_dn_dialog");
	curtain(true);
}

function print_dn_ok(){
	if(!$('dn_gra_id').value || !$('dn_bid').value){
		alert("GRA ID or BID not found");
		return false;
	}
	
	var url = "goods_return_advice.checkout.php?a=print_arms_dn&id="+$('dn_gra_id').value+"&branch_id="+$('dn_bid').value+"&remark="+URLEncode(escape($('dn_remark').value));
	window.open(url, '_blank');
	list_sel(1);
	curtain_clicked();
}
</script>

{/literal}
{if $msg}<p align=center style="color:#00f">{$msg}</p>{/if}
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 m-2 ml-md-1 mb-0"></span>
		</div>
	</div>
</div>
{if $smarty.request.t eq 'save'}
<img src=/ui/approved.png align=absmiddle> GRA saved as {$smarty.request.report_prefix}{$smarty.request.id|string_format:"%05d"}<br>
{elseif $smarty.request.t eq 'confirm'}
<img src=/ui/approved.png align=absmiddle> {$smarty.request.report_prefix}{$smarty.request.id|string_format:"%05d"} confirmed. <span class=hilite>Printing of GRA will begin automatically</span>, you can select the GRA from below (under Completed tab) to print again.<br>
{elseif $smarty.request.t eq 'dispose'}
<img src=/ui/approved.png align=absmiddle> Following {$smarty.request.gra_list} have been disposed.
{elseif $smarty.request.t eq 'cancel'}
<img src=/ui/rejected.png align=absmiddle> {$smarty.request.report_prefix}{$smarty.request.id|string_format:"%05d"} hsa been cancelled.<br>
{/if}
<!-- print dialog -->
<div id=print_dialog style="background:#fff;border:3px solid #000;width:250px;height:140px;position:absolute; padding:10px; display:none;">
<form name=f_prn method=get>
<img src=ui/print64.png hspace=10 align=left> <h3>Print Options</h3>
<input type=hidden name=a value="print">
<input type=hidden name=load value=1>
<input type=hidden name=id value="">
<input type=hidden name=branch_id value="">
<input type=checkbox name="print_gra" checked> GRA Note (A5)<Br>
<div><Br>
<input type=checkbox name="own_copy" checked> Own Copy<Br>
<input type=checkbox name="vendor_copy" checked> Vendor Copy<Br>
</div>
<p align=center><input type=button value="Print" onclick="print_ok()"> <input type=button value="Cancel" onclick="print_cancel()"></p>
</form>
</div>

<iframe width=1 height=1 style="visibility:hidden" name=ifprint></iframe>

{if $config.gra_enable_disposal}
	<div id="disposal_dialog" style="background:#fff;border:3px solid #000;width:500px;height:450px;position:absolute; padding:10px; display:none;">
	<form name="f_dispose" method="post" onsubmit="ajax_search_gra(); return false;">
	<h3>Disposal Menu</h3>
	<fieldset style="width:300px;">
	<legend><b>Search Menu</b></legend>
	<br />
	<b>Due Date</b> <input type="text" name="due_date" id="due_date" size="15" value="{$smarty.now|date_format:'%Y-%m-%d'}" /> <img align="absbottom" src="ui/calendar.gif" id="b_due_date" style="cursor: pointer;" title="Select Date">
	<input class="btn btn-primary" type="button" value="Search" onclick="ajax_search_gra();">
	</fieldset>
	<br />
	<fieldset>
	<legend><b>GRA</b></legend>
	<div id="disposal_items" style="height:230px !important; overflow-y:auto;">- No data -</div>
	</fieldset>
	<p align="center">
		<input class="btn btn-warning" type="button" value="Dispose" onclick="gra_dispose()">
		<input class="btn btn-danger" type="button" value="Close" onclick="curtain_clicked();">
		<input type="hidden" name="a" value="gra_disposal" />
	</p>
	</form>
	</div>

	<ul>
		<li> 
			<img src="ui/icons/lorry_error.png" align="absmiddle"> <a href="javascript:void(show_disposal_dialog('grr'))">GRA Disposal</a> &nbsp;&nbsp;
		</li>
	</ul>
{/if}

<!-- print DN dialog -->
<div id="print_dn_dialog" style="position:absolute;z-index:10000;background:#fff;border:3px solid #000;width:350px;padding:10px;display:none;">
<table border="0" width="100%">
	<tr>
		<td colspan="2"><h3>Print D/N:</h3></td>
	</tr>
	<tr>
		<td valign="top"><b>Remark: </b></td>
		<td><textarea id="dn_remark"></textarea></td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<br />
			<input type="hidden" id="dn_gra_id" value="" />
			<input type="hidden" id="dn_bid" value="" />
			<input type="button" value="Print" onclick="print_dn_ok()"> 
			<input type="button" value="Cancel" onclick="curtain_clicked()">
		</td>
	</tr>
</table>
</div>

<script>
{if $smarty.request.vendor_id} var vendor_id= {$smarty.request.vendor_id}; {/if}
{literal}
function list_sel(n,s)
{
	var i;
	var ttl_tabs = 3;

	tab = n;
	if(gra_enable_disposal) ttl_tabs += 1;
	
	for(i=0;i<=ttl_tabs;i++)
	{
		if (i==n)
		    $('lst'+i).addClassName('selected');
		else
		    $('lst'+i).removeClassName('selected');
	}
	$('gra_list').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';

	var pg = '';
	if (typeof vendor_id!='undefined') pg = 'vendor_id='+vendor_id;
	if (s!=undefined) pg = 's='+s;
	if (n==0) pg +='&search='+ $('search').value+'&search_vendor_id='+document.f_l['search_vendor_id'].value;
	else $('search_area').hide();
	
	if(!gra_no_approval_flow && n==1) n = 6; // show approved GRA

	new Ajax.Updater('gra_list', 'goods_return_advice.checkout.php', {
		parameters: 'a=ajax_load_gra_list&t='+n+'&'+pg,
		evalScripts: true
		});
}
</script>
{/literal}
<div style="padding:10px 0;">
	<form name="f_l" onsubmit="list_sel(0,0);return false;">
	<div class="card mx-3">
		<div class="card-body">
			<div class="tab row  mx-4 mb-3" style="white-space:nowrap;">
				<a href="javascript:list_sel(1)" id=lst1 class="fs-08 m-2 ml-md-1 btn btn-outline-primary btn-rounded">{if $config.gra_no_approval_flow}Saved{else}Approved{/if} GRA</a>
				<a href="javascript:list_sel(2)" id=lst2 class="fs-08 m-2 ml-md-1 btn btn-outline-primary btn-rounded">Completed</a><br class="d-inline d-sm-none">
				<a href="javascript:list_sel(3)" id=lst3 class="fs-08 m-2 ml-md-1 btn btn-outline-primary btn-rounded">Cancelled/Terminated</a>
				{if $config.gra_enable_disposal}
					<a href="javascript:list_sel(4)" id=lst4 class="fs-08 m-2 ml-md-1 btn btn-outline-primary btn-rounded">Disposed</a>
				{/if}
				<a name="find" class="fs08 m-2 ml-md-1 btn btn-outline-primary btn-rounded" id="lst0" onclick="search_tab_clicked(this);" style="cursor:pointer;">Find GRA / Vendor</a>
				</div>
		</div>
	</div>
	<div >
		<div id="search_area" {if (!$smarty.request.search && !$smarty.request.vendor_id) && $smarty.request.t ne '0'}style="display:none;"{/if}>
			<div class="card mx-3">
				<div class="card-body">
					<table>
						<tr>
							<th align="left" class="form-label">Vendor</th>
							<td colspan="2">
								<input class="form-control" name="search_vendor_id" type="hidden" size="1" value="{$smarty.request.search_vendor_id}" readonly>
								<input class="form-control" id="autocomplete_vendor" name="vendor" value="{$smarty.request.vendor}" size=50>
								<div id="autocomplete_vendor_choices" class="autocomplete"></div><br />
							</td>
						</tr>
						<tr>
							<th align="left" class="form-label">Find GRA</th>
							<td><input class="form-control" name="search" id="search" name="find" value="{$smarty.request.search}"></td>
							<td align="right"><input class="btn-primary" type="submit" value="Go"></td>
						</tr>
					</table>
				</div>
			</div>
		</div>
		<div id="gra_list" align="center">
			{include file=goods_return_advice.list.tpl}
		</div>
	</div>
	</form>
</div>

{include file=footer.tpl}

<script>
{literal}
new Ajax.Autocompleter("autocomplete_vendor", "autocomplete_vendor_choices", "ajax_autocomplete.php?a=ajax_search_vendor", { afterUpdateElement: function (obj, li) { document.f_l.search_vendor_id.value = li.title; }});
{/literal}
{if $smarty.request.vendor_id}
	list_sel(0);
{else}
	{if $smarty.request.t eq 'confirm'}
		list_sel(2);
		if (confirm('Do you like to print the GRA now?')) do_print({$smarty.request.id},{$sessioninfo.branch_id},true);
	{elseif $smarty.request.t eq 'dispose'}
		list_sel(4);
	{else}
		list_sel(1);
	{/if}
{/if}
init_calendar();
</script>


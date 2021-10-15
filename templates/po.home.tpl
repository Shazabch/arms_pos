{*
REVISION HISTORY
================
3/17/2008 6:18:25 PM gray
- after save , confirm and approved use re-direct.

8/20/2009 3:22:56 PM Andy
- add reset remark

7/2/2010 4:11:39 PM Alex
- fix search bugs

10/4/2011 11:47:43 AM Justin
- Added new printing option "GRN Performance Report".
- Auto disable/enable this report option base on the GRN approved status.
- Re-aligned the printing options to fill into table.

10/14/2011 3:28:12 PM Justin
- Modified the print option "GRN Performance Report" to base on config.

12/8/2011 5:36:42 PM Justin
- Modified the "copy_id" into front of the link to prevent the html auto convert into special symbol.

1/13/2012 5:52:43 PM Justin
- Added to show new print option "Print DO (Size & Color)" when found config "do_sz_clr_print_template".

1/16/2012 3:38:21 PM Justion
- Fixed the PO Size  & Color printing option is not checked by default.

4/24/2012 6:06:32 PM Justin
- Added new function "Send Email to Vendor" for those approved PO.

7/31/2012 11:42:34 AM Justin
- Enhanced to have vendor search by autocomplete.

8/10/2012 11:12 AM Andy
- Add purchase agreement control.

8/13/2012 11:29 AM Justin
- Enhanced to have Create PO from Agreement link if found the file existed and config set.

10/1/2013 2:32 PM Justin
- Enhanced to allow user can maintain and send email custom message to vendor.

10/21/2013 9:33 AM Justin
- Modified to change the "Print" become "send" from email to vendor.

3/25/2014 2:13 PM Justin
- Modified the wording from "Color" to "Colour".

11/11/2015 13:35 AM DingRen
- Fix print checklist error when PO is HQ Payment and only 1 branch

11/16/2015 4:35 PM Qiu Ying
- Change new.png to view.png for Vendor SKU List

2017-09-13 10:41 AM Qiu Ying
- Bug fixed on treating special characters as wildcard character

8/19/2020 1:47 PM Andy
- Enhanced to have print preview page for PO Checklist.

11/4/2020 10:43 AM William
- Enhanced to add export po items function.

01/07/2021 5:55 PM Rayleen
- In Export PO Items, if it is multiple branches approved PO, add a popup for user to choose which delivery branch to export
- Add checkbox to "Merge All Branch Data into one csv file"
*}
{include file=header.tpl}
{literal}
<script>
{/literal}
var po_disable_grn_perform_report = '{$config.po_disable_grn_perform_report}';

{if $smarty.request.t eq 0}
	var tab = 1;
{else}
	var tab = '{$smarty.request.t}';
{/if}
{literal}

var po_branch_id_list = {};
function do_print_distribution(id, bid){
	//alert(id+'/'+bid);
	document.f_b.id.value=id;
	document.f_b.branch_id.value=bid;
	curtain(true);
	show_print_distribution_dialog();
}

/*function do_print_checklist(id, bid){
	//alert(id+'/'+bid);
	document.f_c.id.value=id;
	document.f_c.branch_id.value=bid;
	curtain(true);
	show_print_distribution_dialog();
}*/

function show_print_distribution_dialog(){
	center_div('print_distribution_dialog');
	$('print_distribution_dialog').style.display = '';
	$('print_distribution_dialog').style.zIndex = 10000;
}

function show_print_checklist_dialog(){
	center_div('print_checklist_dialog');
	merge_all_branch.style.display='none';
	$('print_checklist_dialog').style.display = '';
	$('print_checklist_dialog').style.zIndex = 10000;
}

function print_distribution_ok(){
	$('print_distribution_dialog').style.display = 'none';
	document.f_b.target = "ifprint";
	//document.f_b.target = '_blank';
	document.f_b.submit();
	curtain(false);
}

function do_print(id,bid,approved)
{
	if(po_disable_grn_perform_report == 0){
		if(approved==1) document.f_a['print_grn_perform_report'].disabled = false; // while approved PO can print GRN performance report
		else document.f_a['print_grn_perform_report'].disabled = true; // else just disabled
	}
	toggle_print_po_branch_id_div();
	document.f_a.id.value=id;
	document.f_a.branch_id.value=bid;
	curtain(true);
	show_print_dialog();
}

function do_print_checklist(id,bid,extra)
{
	f_c_header.innerText ='PINRT OPTIONS';
	document.f_c.print_checklist.value = 'Print';
	document.f_c.a.value='print';
	toggle_print_po_branch_id_div();
	if (extra == 'checklist') toggle_print_po_branch_id_div(po_branch_id_list[bid+'_'+id]);
	document.f_c.id.value=id;
	document.f_c.branch_id.value=bid;
	curtain(true);
	show_print_checklist_dialog();
}

function show_print_dialog()
{
	center_div('print_dialog');
	$('print_dialog').style.display = '';
	$('print_dialog').style.zIndex = 10000;
}

function print_ok()
{
	$('print_dialog').style.display = 'none';
	//document.f_a.target = "ifprint";
	document.f_a.target = '_blank';
	document.f_a.submit();
	curtain(false);
}

function print_checklist_ok()
{
	$('print_checklist_dialog').style.display = 'none';
	//document.f_c.target = "ifprint";
	document.f_c.target = '_blank';
	document.f_c.submit();
	curtain(false);
}


function print_all()
{
	document.f_a.print_vendor_copy.checked = true;
	document.f_a.print_branch_copy.checked = true;
	print_ok();
}

function print_cancel()
{
	$('print_distribution_dialog').style.display = 'none';
	$('print_checklist_dialog').style.display = 'none';
	$('print_dialog').style.display = 'none';
	$('div_email_to_vendor_dialog').style.display = 'none';
	curtain(false);
}

function po_chown(id,branch_id)
{
	var p = prompt('Enter the username for new PO owner:');
	if (p.trim()=='' || p==null) return;
	
	new Ajax.Request('/po.php?a=chown&id='+id+'&branch_id='+branch_id+'&new_owner='+p, { evalScripts: true, onComplete: function(m) { alert(m.responseText); list_sel(1) }});
}

function po_email_to_vendor(id,branch_id){
	if(!confirm("Are you sure want to send email to this vendor?")) return;
	
	$('div_email_to_vendor_dialog').style.display = 'none';
	document.f_d.target = "ifprint";
	//document.f_b.target = '_blank';
	document.f_d.submit();
	curtain(false);
}

function toggle_print_po_branch_id_div(bid_list){
	$('div_branch_list_fieldset').hide();
	var all_chx = $$('#div_branch_list_fieldset .chx_po_branch_id');
	for(var i=0; i<all_chx.length; i++){
		all_chx[i].disabled = true;
		all_chx[i].checked = false;
		$('span_chx_po_branch_id_'+all_chx[i].value).hide();
	}
	
	if(bid_list){
		if(bid_list.length>0){
			for(var i=0; i<all_chx.length; i++){
				for(var j=0; j<bid_list.length; j++){
					if(bid_list[j]==all_chx[i].value){
						all_chx[i].checked = true;
						all_chx[i].disabled = false;
						$('span_chx_po_branch_id_'+all_chx[i].value).show();
						break;
					}
				}
			}
			$('div_branch_list_fieldset').show();
		}	
	}
}

function email_to_vendor_dialog(id, bid){
	document.f_d.id.value=id;
	document.f_d.branch_id.value=bid;
	showdiv('div_email_to_vendor_dialog');
	center_div('div_email_to_vendor_dialog');
}

function export_po_item(id,bid,po_delivery){
	document.f_c.a.value='export_po_item';
	document.f_c.id.value=id;
	document.f_c.branch_id.value=bid;
	f_c_header.innerText ='EXPORT OPTIONS';
	document.f_c.print_checklist.value = 'Export';
	if(po_delivery>1){
		toggle_print_po_branch_id_div();
		toggle_print_po_branch_id_div(po_branch_id_list[bid+'_'+id]);
		curtain(true);
		show_print_checklist_dialog();
		merge_all_branch.style.display='inherit';
	}else{
		document.f_c.submit();
	}
}

</script>
{/literal}
{assign var=type value=$smarty.request.type}
{assign var=pono value=$smarty.request.pono}
{assign var=id value=$smarty.request.id}

{if $type eq 'save' or $type eq 'req_save'}
<h1>PO Saved as ID#{$id}</h1>
<p>
Note that your PO is not yet submitted. You can still review the PO and made changes to it. To send out the PO for approval, please click the "Confirm" button in the PO screen.
</p>
{elseif $type eq 'confirm'}
<h1>PO ID#{$id} Submitted (PO No: {$pono})</h1>
<p>
Your PO is now submitted for approval. 
</p>
{elseif $type eq 'req_confirm'}
<h1>PO Request ID#{$id} Submitted (PO No: {$pono})</h1>
<p>
You PO Request is now submitted for approval.
</p>
{elseif $type eq 'delete'}
<h1>PO ID#{$id} Deleted</h1>
{elseif $type eq 'cancel'}
<h1>PO ID#{$id} Cancelled</h1>
{elseif $type eq 'approved'}
<h1>PO Generated (PO No: {$pono})</h1>
<p>
The PO ID#{$id} is now official.
</p>
{elseif $type eq 'revoke'}
<h1>PO Revoked as ID#{$id}</h1>
<p>
The cancelled/terminated PO has been revoked as new PO ID#{$id}.
</p>
{elseif $smarty.request.t eq 'reset'}
<img src=/ui/notify_sku_reject.png align=absmiddle> PO ID#{$smarty.request.save_id} was reset.
{else}
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>

{/if}

<!-- print dialog -->
<div id=print_distribution_dialog style="background:#fff;border:3px solid #000;width:250px;position:absolute; padding:10px; display:none;">
<form name=f_b method=get>
<img src=ui/print64.png hspace=10 align=left> <h3>Print Options</h3>
<input type=hidden name=a value="print_distribution">
<input type=hidden name=id value="">
<input type=hidden name=branch_id value="">
<input type=checkbox name="with_total_qty"> Print With Total Qty<br>
<p align=center>
<input type=button value="Print" onclick="print_distribution_ok()"> <input type=button value="Cancel" onclick="print_cancel()"></p>
</form>
</div>
<!--end print dialog-->

<!-- print dialog -->
<div id=print_dialog style="background:#fff;border:3px solid #000;width:300px;position:absolute; padding:10px; display:none;">
<form name=f_a method=get>
<table border="0" width="100%">
	<tr>
		<td rowspan="2"><img src="ui/print64.png" hspace=10 align=left></td>
		<td><h3>Print Options</h3></td>
	</tr>
	<tr>
		<td>
			<input type=checkbox name="print_vendor_copy" checked> Vendor's Copy<Br>
			<input type=checkbox name="print_branch_copy" checked> Branch's Copy (Internal)<br>
			{if !$config.po_disable_grn_perform_report}
				<input type=checkbox name="print_grn_perform_report" checked> GRN Performance Report<br>
			{/if}
			{if $config.po_sz_clr_print_template}
				<input type="checkbox" name="print_sz_clr" checked> Print PO (Size & Colour)<br />
			{/if}
		</td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<br />
			<input type=button value="Print" onclick="print_ok()">
			<input type=button value="Cancel" onclick="print_cancel()">
		</td>
	</tr>
</table>
<input type=hidden name=a value="print">
<input type=hidden name=load value=1>
<input type=hidden name=id value="">
<input type=hidden name=branch_id value="">
</form>
</div>
<!--end print dialog-->

<!-- print checklist dialog -->
<div id=print_checklist_dialog style="background:#fff;border:3px solid #000;width:250px;position:absolute; padding:10px; display:none;">
<form name=f_c method=get>
<img src=ui/print64.png hspace=10 align=left> <h3 id="f_c_header">Print Options</h3>
<input type=hidden name=a value="print">
<input type=hidden name=load value=1>
<input type=hidden name=checklist value=1>
<input type=hidden name=id value="">
<input type=hidden name=branch_id value="">
<input type=hidden name="print_branch_copy" value=1>
<div id="div_branch_list_fieldset" style="margin-left:20px;">
	<fieldset>
		<legend>Select Branch:</legend>
		{foreach from=$branch item=b}
			<span id="span_chx_po_branch_id_{$b.id}"><input type="checkbox" class="chx_po_branch_id" value={$b.id} name="print_branch_id[]" />{$b.code}<br /></span>
		{/foreach}
	</fieldset>
	<br>
	<span id="merge_all_branch" style="display:none;"><input type="checkbox" value="1" name="merge_all_branch" checked="" /> Merge All Branch Data <br>into one csv file</span>
</div>
<p align=center><input type=button value="Print" onclick="print_checklist_ok()" name="print_checklist"> <input type=button value="Cancel" onclick="print_cancel()"></p>
</form>
</div>
<!--end print checklist dialog-->

<!-- start email to vendor dialog -->
<div id="div_email_to_vendor_dialog" style="background:#fff;border:3px solid #000;position:absolute; padding:10px; display:none;">
<form name="f_d" method="get">
<h3>Send Email to Vendor:</h3>
<b>* Key in below to send customize message to vendor (use default if left blank)</b><br />
<input type="hidden" name="a" value="print">
<input type="hidden" name="print_vendor_copy" value="1">
<input type="hidden" name="send_by_email" value="1">
<input type="hidden" name="load" value="1">
<input type="hidden" name="id" value="">
<input type="hidden" name="branch_id" value="">
<textarea name="custom_msg" cols="60" rows="20">{$custom_email_msg}</textarea><br />
<input type="checkbox" value="1" name="save_msg_as_default" /> Save as default
<p align="center">
	<input type="button" value="Send" onclick="po_email_to_vendor();">
	<input type="button" value="Cancel" onclick="print_cancel();">
</p>
</form>
</div>
<!--end email to vendor dialog-->

<iframe width=1 height=1 style="visibility:hidden" name=ifprint></iframe>

<div class="card mx-3">
	<div class="card-body">
		<ul class="list-group list-group-flush">
			<li class="list-group-item list-group-item-action"> <img src=ui/view.png> <a href="vendor_sku.print_list.php">Vendor SKU List</a></li>
			{if ($type eq 'save' || $type eq 'confirm') && $id}
				{if !$config.enable_po_agreement || ($config.enable_po_agreement && $sessioninfo.privilege.PO_AGREEMENT_OPEN_BUY)}
					<li class="list-group-item list-group-item-action"> <img src=ui/new.png align=absmiddle> <a href="po.php?copy_id={$id}&a=open&id=0&branch_id={$sessioninfo.branch_id}">Create New PO with similar vendor and department</a></li>
				{/if}
			{elseif $type eq 'req_save' && $id}
				<li class="list-group-item list-group-item-action"> <img src=ui/new.png align=absmiddle> <a href="po_request.php?copy_id={$id}&a=open&id=0&branch_id={$sessioninfo.branch_id}">Create New PO Request with similar vendor and department</a></li>
			{/if}
			{if $sessioninfo.privilege.PO}
				{if !$config.enable_po_agreement || ($config.enable_po_agreement and $sessioninfo.privilege.PO_AGREEMENT_OPEN_BUY)}
					<li class="list-group-item list-group-item-action"> <img src=ui/new.png align=absmiddle> <a href="po.php?a=open&id=0">Create New PO</a></li>
					<li class="list-group-item list-group-item-action"> <img src=ui/new.png align=absmiddle> <a href="po.from_vendor.php">Create PO from Vendor SKU</a></li>
				{/if}
				{if $config.enable_po_agreement and file_exists('po.po_agreement.php')}
					<li class="list-group-item list-group-item-action"> <img src=ui/new.png align=absmiddle> <a href="po.po_agreement.php">Create PO from Agreement</a></li>
				{/if}
			{/if}
		</ul>
	</div>
</div>
<br>
{literal}
<script>
function list_sel(n,s)
{
	var i;
	tab = n;
	for(i=0;i<=6;i++)
	{
		if ($('lst'+i)!=undefined)
		{
			if (i==n)
			    $('lst'+i).addClassName('selected');
			else
			    $('lst'+i).removeClassName('selected');
		}
	}
	$('po_list').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';

	var pg = '';
	if (s!=undefined) pg = '&s='+s;
	if (n==0) pg +='&search='+ $('search').value+'&vendor_id='+document.f_l['vendor_id'].value;
	else $('search_area').hide();
	
	new Ajax.Updater('po_list', 'po.php', {
		parameters: encodeURI('a=ajax_load_po_list&t='+n+pg),
		evalScripts: true
		});
}

function search_tab_clicked(obj){
	$('lst'+tab).removeClassName('selected');
	$('search_area').show();
	obj.addClassName('selected');
	$('po_list').update();
}
</script>
{/literal}

<form name="f_l" onsubmit="list_sel(0,0);return false;">
<div class="row mx-3 mb-3">
	<div class=tab style="white-space:nowrap;">
		<a href="javascript:list_sel(1)" id="lst1" class="btn btn-outline-primary btn-rounded">Saved PO</a>
		<a href="javascript:list_sel(2)" id="lst2" class="btn btn-outline-primary btn-rounded">Waiting for Approval</a>
		<a href="javascript:list_sel(5)" id="lst5" class="btn btn-outline-primary btn-rounded">Rejected</a>
		<a href="javascript:list_sel(3)" id="lst3" class="btn btn-outline-primary btn-rounded">Cancelled/Terminated</a>
		<a href="javascript:list_sel(4)" id="lst4" class="btn btn-outline-primary btn-rounded">Approved</a>
		{if BRANCH_CODE eq 'HQ'}
		<a href="javascript:list_sel(6)" id="lst6" class="btn btn-outline-primary btn-rounded">HQ Distribution List</a>
		{$conf}
		{/if}
		<a name="find_po" id="lst0" class="btn btn-outline-primary btn-rounded" onclick="search_tab_clicked(this);" style="cursor:pointer;">Find PO / Vendor</a>
		</div>
</div>
<div>
	<div id="search_area" {if (!$smarty.request.search && !$smarty.request.vendor_id) && $smarty.request.t ne '0'}style="display:none;"{/if}>
		<div class="card mx-3">
			<div class="card-body">
				<table>
					<tr>
						<th><b class="form-label mb-3" >Vendor</b></th>
						<td colspan="2">
							<input class="form-control" name="vendor_id" type="hidden" size="1" value="{$smarty.request.vendor_id}" readonly>
							<input class="form-control" id="autocomplete_vendor" name="vendor" value="{$smarty.request.vendor}" size=50>
							<div id="autocomplete_vendor_choices" class="autocomplete"></div><br />
						</td>
					</tr>
					<tr>
						<th align="left"><b class="form-label mb-1">Find PO</b></th>
						<td><input class="form-control" name="search" id="search" name="find" value="{$smarty.request.search}"></td>
						<td align="center">
							<input class="btn btn-primary" type="submit" value="Go"></td>
					</tr>
				</table>
			</div>
		</div>

	</div>
	<div id="po_list">
		{include file=po.list.tpl}
	</div>
</div>
</form>
{include file=footer.tpl}

<script>
{literal}
new Ajax.Autocompleter("autocomplete_vendor", "autocomplete_vendor_choices", "ajax_autocomplete.php?a=ajax_search_vendor", { afterUpdateElement: function (obj, li) { document.f_l.vendor_id.value = li.title; }});
{/literal}
list_sel(1);
</script>

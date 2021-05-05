{* 
##REVISION HISOTRY##
8/27/2007 2:28:45 PM gary
- add control the list of cancelled pproducts.

12/28/2007 12:49:35 PM gary
- add print preview for grn performance report.

9/23/2010 5:27:21 PM Justin
- Added prompt out screen for user to print report after save GRN.

11/10/2010 11:55:26 AM Justin
- Modified the print window to show Correction Sheet instead of summary after save GRN.
- Added a checkbox on Correction Sheet report menu to allow user decide whether want to print Reconcile status.

11/30/2010 2:00:20 PM Justin
- Replaced back the print out to skip display on screen.

7/31/2012 11:42:34 AM Justin
- Enhanced to have vendor search by autocomplete.

11/23/2012 2:17:00 PM Fithri
- after monthly report has been printed, user cannot do further edit (reject, approval or submit) on that month - for consignment only

3/8/2013 11:14 AM Justin
- Bug fixed on pagination not working.

6/12/2014 12:03 PM Justin
- Bug fixed on amount check is not working after new updates of Firefox.
*}
{include file=header.tpl}
<script>

{if $smarty.request.t eq 0}
	var tab = 1;
{else}
	var tab = '{$smarty.request.t}';
{/if}

{literal}
function check_amount(id)
{
	var sstr = "["+id+"]";
	if (document.f_a.elements['amount'+sstr].value!='')
		mf(document.f_a.elements['amount'+sstr]);
	//document.f_a.elements['amount'+sstr].value = float(document.f_a.elements['amount'+sstr].value);
	if (document.f_a.elements['amount'+sstr].value == '')
	{
	    $('as'+id).innerHTML = "";
	}
	else if (float(document.f_a.elements['amount'+sstr].value) != float(document.f_a.elements['grn_amount'+sstr].value))
	{
	    $('as'+id).innerHTML = "<img src=/ui/cancel.png align=absmiddle>";
	}
	else
	{
		$('as'+id).innerHTML = "<img src=/ui/approved.png align=absmiddle>";
	}
}

function fcheck()
{
	
	//$('err').innerHTML = "<div class=errmsg><ul><li>error here</li></ul></div>";
	//return false;
	
	/*
	new Ajax.Request("adjustment_approval.php",{
		method:'post',
		parameters: 'a=check_printed_report&curr_date='+document.f_b.curr_date.value,
	    evalScripts: true,
		onComplete: function (e) {
			var resp = e.responseText.trim();
			if (resp == '0') {
				document.f_b.comment.value = 'Approve';
				if (confirm('Press OK to Approve the Adjustment.'))
				{
					document.f_b.a.value = "approve";
					document.f_b.submit();
				}
			}
			else $('err').innerHTML = resp;
    	}
	});
	*/
	
	var i;
	var obj = document.f_a.elements["id[]"];
	for(i=0;i<obj.length;i++)
	{
		if (obj[i].value>0)
		{
			var sstr = '['+obj[i].value+']';
			if (document.f_a.elements['amount'+sstr].value != '' && document.f_a.elements['account_doc_no'+sstr].value == '')
			{
				document.f_a.elements['account_doc_no'+sstr].focus();
				alert('Please enter Invoice/DO No.');
				return false;
			}
			if (document.f_a.elements['amount'+sstr].value == '' && document.f_a.elements['account_doc_no'+sstr].value != '')
			{
				document.f_a.elements['amount'+sstr].focus();
				alert('Please enter Invoice/DO amount.');
				return false;
			}
		}
	}
	return true;
}

function curtain_clicked()
{
	curtain(false);
	$('print_dialog').style.display = 'none';
	$('print2_dialog').style.display = 'none';
}


function do_print(id,bid)
{
	document.f_prn.id.value=id;
	document.f_prn.branch_id.value=bid;
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
	$('print_dialog').style.display = 'none';
	document.f_prn.target = "ifprint";
	document.f_prn.submit();
	curtain(false);
}

function print_cancel()
{
	$('print_dialog').style.display = 'none';
	curtain(false);
}

function show_completed(s)
{

	$('grn_list').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';

	var pg = '';
	if (s!=undefined) pg = 's='+s;

	new Ajax.Updater('grn_list', 'goods_receiving_note_approval.account.php', {
		parameters: 'a=ajax_load_grn_complete&'+pg,
		evalScripts: true
		});
}

function do_print2(id,bid)
{
	document.f_prn2.id.value=id;
	document.f_prn2.branch_id.value=bid;
	curtain(true);
	show_print2_dialog();
}

function show_print2_dialog()
{
	center_div('print2_dialog');
	$('print2_dialog').style.display = '';
	$('print2_dialog').style.zIndex = 10000;
}

function print2_ok()
{
	$('print2_dialog').style.display = 'none';
	document.f_prn2.target = "ifprint";
	document.f_prn2.submit();
	curtain(false);
}

function print2_cancel()
{
	$('print2_dialog').style.display = 'none';
	curtain(false);
}

function grn_chown(id,branch_id)
{
	var p = prompt('Enter the username for new GRN Account owner:');
	if (p.trim()=='' || p==null) return;

	new Ajax.Request('/goods_receiving_note_approval.account.php?a=chown&id='+id+'&branch_id='+branch_id+'&new_owner='+p, { evalScripts: true, onComplete: function(m) { alert(m.responseText); list_sel(2) }});
}

function do_print_preview(id,bid){
	window.open('goods_receiving_note.php?id='+id+'&branch_id='+bid+'&a=print&print_grn_perform_report=1&noprint=1','','width=800,height=600,scrollbars=yes,resizable=yes');
	 
}

function search_tab_clicked(obj){
	$('lst'+tab).className = '';
	$('search_area').show();
	obj.className = 'active';
	$('grn_list').update();
	$('div_grn').style.display = "";
}
</script>
{/literal}
</script>

<h1>Account GRN Verification</h1>

{if $smarty.request.printed eq '1' or $printed eq '1'}
<div class=errmsg><ul><li>{$printed_msg}</li></ul></div>
{/if}

<!-- print dialog -->
<div id=print_dialog style="background:#fff;border:3px solid #000;width:330;height:130px;position:absolute; padding:10px; display:none;">
<form name=f_prn method=get>
<img src=ui/print64.png hspace=10 align=left> <h3>Print Options</h3>
<input type=hidden name=a value="print_grn_correction">
<input type=hidden name=load value=1>
<input type=hidden name=id value="">
<input type=hidden name=branch_id value="">
<input type=checkbox name="print_grn_report" checked> GRN Correction Sheet (A4 Landscape)<br />
<input type=checkbox name="reconcile"> Include Reconcile Status<br />
<p align=center>
	<input type=button value="Print" onclick="print_ok()">
	<input type=button value="Cancel" onclick="print_cancel()">
</p>
</form>
</div>
<div id=print2_dialog style="background:#fff;border:3px solid #000;width:330px;height:120px;position:absolute; padding:10px; display:none;">
<form name=f_prn2 method=get>
<img src=ui/print64.png hspace=10 align=left> <h3>Print Options</h3>
<input type=hidden name=a value="print_grn_complete">
<input type=hidden name=load value=1>
<input type=hidden name=save_type value="{$smarty.request.t}">
<input type=hidden name=id value="">
<input type=hidden name=branch_id value="">
<input type=checkbox name="print_grn_report" checked> GRN Verification Report (A4 Portrait) <br />
<input type=checkbox name="print_grn_perform_report"> GRN Performance Report (A4 Portrait)<br />
<p align=center><input type=button value="Print" onclick="print2_ok()"> <input type=button value="Cancel" onclick="print2_cancel()"></p>
</form>
</div>
<iframe style="visibility:hidden" width=1 height=1 name=ifprint></iframe>

{literal}
<script>
function list_sel(n,s)
{
	var i;

	tab = n;
	for(i=0;i<=4;i++)
	{
		if (i==n)
		    $('lst'+i).className='active';
		else
		    $('lst'+i).className='';
	}
	$('grn_list').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';

	var pg = '';
	if (s!=undefined) pg = 's='+s;
	var s = document.f_s['search'].value;
	if (n==0) pg +='&search='+s+'&vendor_id='+document.f_s['vendor_id'].value;
	else $('search_area').hide();

	var a = '';
	if (n==0)
	    a = 'ajax_find';
    else if (n==1)
	    a = 'ajax_load_grn_amount_check';
	else if (n==2)
	    a = 'ajax_load_grn_correction';
	else if (n==3)
	    a = 'ajax_load_grn_complete';
	else if (n==4)
	    a = 'ajax_load_grn_cancel';

	new Ajax.Updater('grn_list', 'goods_receiving_note_approval.account.php', {
		parameters: 'a='+a+'&'+pg,
		evalScripts: true
		});
}
</script>
{/literal}

<div style="padding:10px 0;">
	<div class=tab style="height:20px;white-space:nowrap;">
	&nbsp;&nbsp;&nbsp;
	<a href="javascript:list_sel(1)" id=lst1 class=active>Amount Check</a>
	<a href="javascript:list_sel(2)" id=lst2 class=active>Pending GRN Correction</a>
	<a href="javascript:list_sel(4)" id=lst4>Cancelled GRN</a>
	<a href="javascript:list_sel(3)" id=lst3>Verified GRN</a>
	<a name="find" id="lst0" onclick="search_tab_clicked(this);" style="cursor:pointer;">
		Find GRN / Doc No / Vendor
	</a>
	</div>
	<div id="div_grn" style="border:1px solid #000">
		<form name="f_s" onsubmit="list_sel(0);return false;">
			<div id="search_area" {if (!$smarty.request.search && !$smarty.request.vendor_id) && $smarty.request.t ne '0'}style="display:none;"{/if}>
				<table>
					<tr>
						<th align="left">Vendor</th>
						<td colspan="2">
							<input name="vendor_id" type="hidden" size="1" value="{$smarty.request.vendor_id}" readonly>
							<input id="autocomplete_vendor" name="vendor" value="{$smarty.request.vendor}" size=50>
							<div id="autocomplete_vendor_choices" class="autocomplete"></div><br />
						</td>
					</tr>
					<tr>
						<th align="left">Find GRN / Doc No</th>
						<td><input name="search" id="search" name="find" value="{$smarty.request.search}"></td>
						<td align="right"><input type="submit" value="Go"></td>
					</tr>
				</table>
			</div>
		</form>
		<div id="grn_list"></div>
 	</div>
</div>

<script>
{if $smarty.request.printed eq '1'}
	list_sel(1);
{elseif $printed eq '1'}
	list_sel(2);
{else}
	{if $smarty.request.t eq 'confirm'}
		if (confirm('GRN{$smarty.request.id|string_format:"%05d"} Confirmed. Do you want to print the summary now?'))
			do_print2({$smarty.request.id},{$sessioninfo.branch_id});
		list_sel(3);
	{elseif $smarty.request.t eq 'save'}
		if (confirm('GRN{$smarty.request.id|string_format:"%05d"} Saved. Do you want to print the Correction Sheet Report now?'))
			do_print({$smarty.request.id},{$sessioninfo.branch_id});
		list_sel(2);
	{elseif $smarty.request.t eq 'verify'}
		var msg = '';
		{if $smarty.request.c ne ''} 
		msg += 'GRN matches Invoice/DO (Please print the GRN Summary):\n{$smarty.request.c}\n';
		{/if}
		{if $smarty.request.v ne ''}
		msg += 'GRN contain variance (Please print the GRN Correction Sheet):\n{$smarty.request.v}\n';
		{/if}
		{if $smarty.request.sc ne ''}
		msg += 'The following GRN require checking (select Pending GRN Correction):\n{$smarty.request.sc}\n';
		{/if}
		alert(msg);
		{if $config.grn_always_require_correction}
		list_sel(2);
		{else}
		list_sel(1);
		{/if}
	{else}
		list_sel(1);
	{/if}
{/if}
{literal}
new Ajax.Autocompleter("autocomplete_vendor", "autocomplete_vendor_choices", "ajax_autocomplete.php?a=ajax_search_vendor", { afterUpdateElement: function (obj, li) { document.f_s.vendor_id.value = li.title; }});
{/literal}
</script>

{include file=footer.tpl}

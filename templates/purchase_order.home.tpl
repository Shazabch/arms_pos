{*
REVISION HISTORY
================

11/1/2007 5:30:35 PM gary
- add print dialog to print distribution list.
*}
{include file=header.tpl}
{literal}
<script>

function do_print_distribution(id, bid){
	//alert(id+'/'+bid);
	document.f_b.id.value=id;
	document.f_b.branch_id.value=bid;
	curtain(true);
	show_print_distribution_dialog();
}

function show_print_distribution_dialog(){
	center_div('print_distribution_dialog');
	$('print_distribution_dialog').style.display = '';
	$('print_distribution_dialog').style.zIndex = 10000;
}

function print_distribution_ok(){
	$('print_distribution_dialog').style.display = 'none';
	document.f_b.target = "ifprint";
	document.f_b.submit();
	curtain(false);
}

function do_print(id,bid)
{
	document.f_a.id.value=id;
	document.f_a.branch_id.value=bid;
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
	document.f_a.target = "ifprint";
	document.f_a.submit();
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
	$('print_dialog').style.display = 'none';
	curtain(false);
}

function po_chown(id,branch_id)
{
	var p = prompt('Enter the username for new PO owner:');
	if (p.trim()=='' || p==null) return;
	
	new Ajax.Request('/purchase_order.php?a=chown&id='+id+'&branch_id='+branch_id+'&new_owner='+p, { evalScripts: true, onComplete: function(m) { alert(m.responseText); list_sel(1) }});
}
</script>
{/literal}
{if $type eq 'save' or $type eq 'req_save'}
<h1>PO Saved as ID#{$id}</h1>
<p>
Note that your PO is not yet submitted. You can still review the PO and made changes to it. To send out the PO for approval, please click the "Confirm" button in the PO screen.
</p>
{elseif $type eq 'confirm'}
<h1>PO ID#{$id} Submitted (PO No: {$pono})</h1>
<p>
You PO is now submitted for approval. 
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
{else}
<h1>{$PAGE_TITLE}</h1>
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
<div id=print_dialog style="background:#fff;border:3px solid #000;width:250px;position:absolute; padding:10px; display:none;">
<form name=f_a method=get>
<img src=ui/print64.png hspace=10 align=left> <h3>Print Options</h3>
<input type=hidden name=a value="print">
<input type=hidden name=load value=1>
<input type=hidden name=id value="">
<input type=hidden name=branch_id value="">
<input type=checkbox name="print_vendor_copy" checked> Vendor's Copy<Br>
<input type=checkbox name="print_branch_copy" checked> Branch's Copy (Internal)<br>
<p align=center><input type=button value="Print" onclick="print_ok()"> <input type=button value="Cancel" onclick="print_cancel()"></p>
</form>
</div>
<!--end print dialog-->

<iframe width=1 height=1 style="visibility:hidden" name=ifprint></iframe>

<ul>
<li> <img src=ui/print.png> <a href="vendor_sku.print_list.php">Print Vendor SKU List</a>
{if ($type eq 'save' || $type eq 'confirm') && $id}
<li> <img src=ui/new.png align=absmiddle> <a href=purchase_order.php?a=open&id=0&copy_id={$id}&branch_id={$sessioninfo.branch_id}>Create New PO with similar vendor and department</a>
{elseif $type eq 'req_save' && $id}
<li> <img src=ui/new.png align=absmiddle> <a href=purchase_order_request.php?a=open&id=0&copy_id={$id}&branch_id={$sessioninfo.branch_id}>Create New PO Request with similar vendor and department</a>
{/if}
{if $sessioninfo.privilege.PO}
<li> <img src=ui/new.png align=absmiddle> <a href=purchase_order.php?a=open&id=0>Create New PO</a>
<li> <img src=ui/new.png align=absmiddle> <a href=purchase_order.from_vendor.php>Create PO from Vendor SKU</a>
{/if}
</ul>
<br>
{literal}
<script>
function list_sel(n,s)
{
	var i;
	for(i=0;i<=6;i++)
	{
		if ($('lst'+i)!=undefined)
		{
			if (i==n)
			    $('lst'+i).className='active';
			else
			    $('lst'+i).className='';
		}
	}
	$('po_list').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';

	var pg = '';
	if (s!=undefined) pg = 's='+s;
	
	new Ajax.Updater('po_list', 'purchase_order.php', {
		parameters: 'a=ajax_load_po_list&t='+n+'&'+pg,
		evalScripts: true
		});
}
</script>
{/literal}

<form onsubmit="list_sel(0,pono.value);return false;">
<div class=tab style="height:25px;white-space:nowrap;">
&nbsp;&nbsp;&nbsp;
<a href="javascript:list_sel(1)" id=lst1 class=active>Saved PO</a>
<a href="javascript:list_sel(2)" id=lst2>Waiting for Approval</a>
<a href="javascript:list_sel(5)" id=lst5>Rejected</a>
<a href="javascript:list_sel(3)" id=lst3>Cancelled/Terminated</a>
<a href="javascript:list_sel(4)" id=lst4>Approved</a>
{if BRANCH_CODE eq 'HQ'}
<a href="javascript:list_sel(6)" id=lst6>HQ Distribution List</a>
{/if}
<a name=find_po id=lst0>Find PO <input name=pono> <input type=submit value="Go"></a>
</div>
</form>
<div id=po_list style="border:1px solid #000">
</div>
{include file=footer.tpl}

<script>
list_sel(1);
</script>

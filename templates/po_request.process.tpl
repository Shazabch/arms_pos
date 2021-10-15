{*
Revision History
================
4/25/2007  Gary
- show Artno/Mcode column
- reject with reason function

12/17/2007 10:12:42 AM gary
- point to new PO file po.php

10/09/2009 09:44:55 AM edward
- popup alert if $_REQUEST[alert] set

9/20/2011 10:48:25 AM Alex
- add checking check box when reject items

04/20/2020 05:33 PM Sheila
- Modified layout to compatible with new UI.


*}
{include file=header.tpl}
{literal}
<script>
var no_delete=null;

function multiple_reject_items(branch_id){
	var checked=false;
	$$('.select_box').each(function(ele,index){
		if (ele.checked)	checked=true;
	});
	if (checked)	reject_items(0,branch_id);
	else	alert("No item had been selected");
}

function reject_items(n,branch_id){
	no_delete = n;
	center_div('reject_item');
	curtain(true);
	Element.show('reject_item');
	document.f_p.branch_id.value=branch_id;
}

function reject_popup(){
    curtain(false);
	Element.hide('reject_item');
}

function close_popup(val){
	Element.hide('reject_item');
	curtain(false);
	if($('reject_comment').value!=''){
		    document.f_p.reject_comment.value=$('reject_comment').value;
			document.f_p.delete_id.value=no_delete;
			document.f_p.a.value='reject';
			document.f_p.submit();
   	}
	else{
		alert('Please provide reject comment.');
	}
}


function show_items()
{
	$('request').innerHTML = '<img src=/ui/clock.gif align=absmiddle> Loading...';
	new Ajax.Updater('request', 'po_request.process.php',
		{
			parameters: Form.serialize(document.f_a)+'&a=show_items',
			evalScripts: true	
		}
	);
}

function check_all(tbody, obj)
{
	// clear all checkboxes
	var cb = $('tb').getElementsByTagName('input');
	for (var i=0;i<cb.length;i++) 
	{
		if (cb[i] != obj && cb[i].type != 'radio')
		{
			cb[i].checked = false;
			cb[i].disabled = true;
		}
	}

	if (tbody == undefined) return;
	
	// select all local checkboxes
	$(tbody).style.display = '';
	var cb = $(tbody).getElementsByTagName('input');
	for (var i=0;i<cb.length;i++) 
	{
		cb[i].checked = obj.checked;
		cb[i].disabled = false;
	}
}


function get_price_history(obj,id){
	Position.clone(obj, $('price_history'), {setHeight: false, setWidth:false});
	Element.show('price_history');
	$('price_history_list').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';
	new Ajax.Updater(
	'price_history_list',
	'ajax_autocomplete.php',
		{
		    parameters: 'a=sku_cost_history&id='+id
		}
	);
}


</script>
{/literal}
{if $msg}<p align=center style="color:#00f">{$msg}</p>{/if}
{if $smarty.request.alert}<script>alert('{$smarty.request.alert}');</script>{/if}
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>

<div id=reject_item style="display:none;position:absolute;z-index:10000;background:#fff;border:2px solid #000;padding:5px;width:250;height:120">
<p align=center>
Enter the reason to reject request item:
<br><br>
<input id="reject_comment" size="30" maxlength="30" />
<br><br>
<input type=button value="Reject" onclick="close_popup();">&nbsp;&nbsp;&nbsp;
<input type=button value="Cancel" onclick="reject_popup();">
</p>

</div>

{if $smarty.request.t eq 'complete'}
<p>
<b>The following PO was generated:</b> {$smarty.request.po}<br>
<img src=/ui/act.png align=absmiddle> <a href="/po.php">goto Purchase Order screen</a>
</p>
{/if}

<div class="card mx-3">
	<div class="card-body">
		<div class=stdframe style="margin:5px 0;background:#fff;">
			<!-- sku search -->
			<form name=f_a>
			<div class="row">
				<div class="col-md-4">
					<b class="form-label">Department</b>
			<select class="form-control" name=department_id onchange="show_items()">
			{section name=i loop=$dept}
			<option value="{$dept[i].id}" {if $smarty.request.department_id eq $dept[i].id}selected{/if}>{$dept[i].description}</option>
			{/section}
			</select>
				</div>
			
			<div class="col-md-4">
				{if $BRANCH_CODE eq 'HQ'}
			<b class="form-label">Branch</b>
			 <select class="form-control" name=branch_id onchange="show_items()">
			<option value=0>-- All --</option>
			{section name=i loop=$branches}
			<option value="{$branches[i].id}" {if $smarty.request.form_branch_id eq $branches[i].id}selected{/if}>{$branches[i].code}</option>
			{/section}
			</select>
			{/if}
			</div>
			
			<div class="col-md-4">
				<input class="btn btn-primary mt-4" type=button value="Show PO Request" onclick="show_items()"></td>
			</div>
			</div>
			</form>
			</div>
	</div>
</div>

<div class="alert alert-primary mx-3">
	<b><div id=request></div></b>
</div>
{include file=footer.tpl}

<script>
show_items();
</script>

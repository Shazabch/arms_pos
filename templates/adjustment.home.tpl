{*
8/20/2009 3:19:18 PM Andy
- add reset notice

7/2/2010 4:11:39 PM Alex
- fix search bugs

11/8/2010 12:45:04 PM Andy
- Change word "Cancelled" to "Canceled".

11/8/2010 1:23:42 PM Alex
- add branch searching

3/9/2011 2:05:34 PM Justin
- Added the 2 options on print menu while config['adjustment_use_custom_print'] is on.

3/24/2014 5:56 PM Justin
- Modified the wording from "Canceled" to "Cancelled".

2017-09-13 10:41 AM Qiu Ying
- Bug fixed on treating special characters as wildcard character

10/14/2020 2:29 PM Andy
- Enhanced Adjustment Printing to can choose what sku fields to show (ARMS Code / MCode / Art No / Link Code).

10/28/2020 2:11 PM William
- Enhanced to add export adjustment item function.
*}

{include file=header.tpl}
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>

{literal}
<script>
function list_sel(n,s){
	var i;
	for(i=0;i<=6;i++){
		if ($('lst'+i)!=undefined){
			if (i==n){
			    $('lst'+i).addClassName('selected');			
			}
			else{
				$('lst'+i).removeClassName('selected');
			}			    
		}
	}
	$('adjust_list').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';

	var pg = '';
	if (s!=undefined) pg = '&s='+s;
	if (n==0) pg +='&search='+ $('search').value;
	if (n==6) pg +='&search='+ $('search_bid').value;
	
	new Ajax.Updater('adjust_list', 'adjustment.php', {
		parameters: encodeURI('a=ajax_load_adjust_list&t='+n+pg),
		evalScripts: true
		});
}

function curtain_clicked(){
	$('print_dialog').style.display = 'none';
	curtain(false);
}

function do_print(id,bid){
	document.f_print.id.value=id;
	document.f_print.branch_id.value=bid;
	curtain(true);
	show_print_dialog();
}

function show_print_dialog(){
	document.f_print.a.value='print';
	center_div('print_dialog');
	$('print_dialog').style.display = '';
	$('print_dialog').style.zIndex = 10000;
}

function print_ok(){
	// Check Print Column
	var print_col_count = 0;
	$$("#print_dialog input.cbx_print_col").each(function(inp){
		if(inp.checked)	print_col_count++;
	});
	if(print_col_count<=0){
		alert('Please select at least one SKU Code column.');
		return false;
	}
	
	$('print_dialog').style.display = 'none';
	document.f_print.a.value='print';
	//document.f_print.target = 'ifprint';
	//document.f_print.method='get';
	document.f_print.target = '_blank';
	document.f_print.submit();	
	curtain(false);
}

function export_adjustment_item(id,bid){
	document.f_print.id.value=id;
	document.f_print.branch_id.value=bid;
	document.f_print.a.value='export_adjustment_item';
	document.f_print.submit();	
}
</script>
{/literal}


<!-- Start print dialog -->
<div id="print_dialog" style="background:#fff;border:3px solid #000;{if $config.adjustment_use_custom_print}width:260px;height:350px;{else}width:260px;height:300px;{/if}position:absolute; padding:10px; display:none;">
<form name=f_print method="get">
<input type=hidden name=a>
<input type=hidden name=id>
<input type=hidden name=branch_id>
<table width="100%">
	
	<tr>
		<td colspan="2">
			<fieldset>
				<legend><b>Select SKU Code Column</b></legend>
				<ul style="list-style:none;">
					<li> <input type="checkbox" class="cbx_print_col" name="print_col[sku_item_code]" value="1" {if $config.adj_print_col_list.sku_item_code}checked {/if} {if $config.adj_alt_print_template}disabled{/if} /> ARMS Code</li>
					<li> <input type="checkbox" class="cbx_print_col" name="print_col[mcode]" value="1" {if $config.adj_print_col_list.mcode}checked {/if} {if $config.adj_alt_print_template}disabled{/if} /> MCode</li>
					<li> <input type="checkbox" class="cbx_print_col" name="print_col[artno]" value="1" {if $config.adj_print_col_list.artno}checked {/if} {if $config.adj_alt_print_template}disabled{/if} /> Art No</li>
					<li> <input type="checkbox" class="cbx_print_col" name="print_col[link_code]" value="1" {if $config.adj_print_col_list.link_code}checked {/if} {if $config.adj_alt_print_template}disabled{/if} /> {$config.link_code_name}</li>
				</ul>
				{if $config.adj_alt_print_template}
					<div style="color:red;">
						You are using own custom printing format, so this feature is disabled
					</div>
				{/if}
			</fieldset>
		</td>
	</tr>
	
	{if $config.adjustment_use_custom_print}
		<tr>
			<td colspan="2">
				<fieldset>
					<div class="form-label">
						<legend><b >Custom Selection&nbsp;</b></legend>
					<ul style="list-style:none;">
						<li> <input type="checkbox" name="cost_enable" value="1" checked /> Show Cost</li>
						<li> <input type="checkbox" name="sp_enable" value="1" checked /> Show Selling Price</li>
					</ul>
					</div>
				</fieldset>
			</td>
		</tr>
	{/if}
	
	
	<tr>
		<td colspan="2" align="center">
			<div class="alert alert-primary rounded mx-3">
				This Adjustment will Print with <br> <b>A4 Portrait</b> Format.
			</div>
		</td>
	</tr>
	
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr>
		<td colspan="2" align="center">
			<input type=button class="btn btn-primary" value="Print" onclick="print_ok()">
			<input type=button class="btn btn-danger" value="Cancel" onclick="curtain_clicked();">
		</td>
	</tr>
</table>
</p>
</form>
</div>
<!--End print dialog -->

<iframe width=1 height=1 style="visibility:hidden" name=ifprint></iframe>

<div id=show_last>
{if $smarty.request.t eq 'save'}
<img src=/ui/approved.png align=absmiddle> Adjustment saved as ID#{$smarty.request.id}<br>
{elseif $smarty.request.t eq 'delete'}
<img src=/ui/cancel.png align=absmiddle> Adjustment ID#{$smarty.request.id} was deleted<br>
{elseif $smarty.request.t eq 'confirm'}
<img src=/ui/approved.png align=absmiddle> Adjustment ID#{$smarty.request.id} confirmed. 
{elseif $smarty.request.t eq 'reset'}
<img src=/ui/notify_sku_reject.png align=absmiddle> Adjustment ID#{$smarty.request.save_id} was reset.
{/if}
</div>

<div class="card mx-3">
	<div class="card-body">
		<ul style="list-style-type: none; color: black;">
			<li > <img src="ui/new.png" align="absmiddle">&nbsp; <a class="text-dark bg-gray-100 p-2 rounded" href="adjustment.php?a=open&id=0">Create New Adjustment</a></li>
			{if $sessioninfo.privilege.ADJ_WORK_ORDER}<br>
				<li> <img src="ui/new.png" align="absmiddle"> &nbsp;<a class="text-dark bg-gray-100 p-2 rounded" href="work_order.php" target="_blank">Work Order</a></li>
			{/if}
		</ul>
	</div>
</div>

<br>

<form onsubmit="list_sel(0,0);return false;">
<div class="tab" style="white-space:nowrap;">

<div class="card mx-3">
	<div class="card-body">
		<div class="row">
			<div class="col">
				<a href="javascript:list_sel(1)"  id="lst1" class="fs-08 btn btn-outline-primary btn-rounded" >Saved Adjustment</a>
		<a href="javascript:list_sel(2)" id="lst2" class="fs-08 btn btn-outline-primary btn-rounded" >Waiting for Approval</a><br class="d-inline d-sm-none"><br class="d-inline d-sm-none">
		<a href="javascript:list_sel(5)" id="lst5" class="fs-08 btn btn-outline-primary btn-rounded" >Rejected</a><br class="d-none d-md-inline d-lg-none"><br class="d-none d-md-inline d-lg-none">
		<a href="javascript:list_sel(3)" id="lst3" class="fs-08 btn btn-outline-primary btn-rounded" >Cancelled/Terminated</a><br class="d-inline d-sm-none"><br class="d-inline d-sm-none">
		<a href="javascript:list_sel(4)" id="lst4" class="fs-08 btn btn-outline-primary btn-rounded" >Approved</a><br>
		
			</div>
			<div class="col">
				<div class="form-inline mt-2 ml-1">
					<a class="" name="find_po" id="lst0">Find Adjustment 
						<input class="form-control" id=search name=pono> 
						<input type="submit" class="btn btn-primary" value="Go">
					</a>
				</div>
			</div>
	</div>
	</div>
</div>


{if $BRANCH_CODE eq 'HQ' && $config.consignment_modules}
	<a id=lst6>
		Branch
		<select name="branch_id" id="search_bid">
		    {foreach from=$branches item=b}
		        <option value="{$b.id}" {if $smarty.request.branch_id eq $b.id}selected {/if}>{$b.code}</option>
			{/foreach}
		</select>
		<input type=button onclick="list_sel(6);" value="Go">
	</a>
{/if}
</div>
</form>
<div id="adjust_list">
</div>
{include file=footer.tpl}

<script>
list_sel(1);
</script>

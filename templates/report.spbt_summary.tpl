{*
5/24/2010 10:38:32 AM Alex
- create new spbt report

7/5/2010 3:54:55 PM Alex
- add order and percentage column while request deliver

7/9/2010 4:35:36 PM Alex
-Add area filter

10/10/2011 12:31:45 PM alex
- add qty_nf control quantity decimal digit

*}

{include file=header.tpl}

{if !$no_header_footer}

<script>
var phpself = '{$smarty.server.PHP_SELF}';
{literal}


function change_batch_code(){
	var view_type = document.f_a['view_type'].value;
	var branch_id = document.f_a['branch_id'].value;
	var area_code = document.f_a['area_code'].value;
	var checkout = document.f_a['checkout'].checked;
    new Ajax.Request(phpself+'?a=ajax_change_code&ajax=1&branch_id='+branch_id+'&area_code='+area_code+'&view_type='+view_type+'&checkout='+checkout,{
		onComplete:function(e){
		$('span_code').innerHTML = e.responseText;
		}
	});
	if (view_type == "order") $('span_checkout').hide();
	else $('span_checkout').show();
	
}

function change_area_batch_code(){
	var view_type = document.f_a['view_type'].value;
	var branch_id = document.f_a['branch_id'].value;
	var checkout = document.f_a['checkout'].checked;
    new Ajax.Request(phpself+'?a=ajax_change_area_code&ajax=1&branch_id='+branch_id+'&view_type='+view_type+'&checkout='+checkout,{
		onComplete:function(e){

		eval("var json ="+e.responseText);
		$('span_code').innerHTML = json['sel1'];
		$('span_area').innerHTML = json['sel2'];
		}
	});
	if (view_type == "order") $('span_checkout').hide();
	else $('span_checkout').show();
}

function hide_show_checkout(obj){
	if (obj.value=='order') $('span_checkout').hide();
	else $('span_checkout').show();
}


{/literal}
</script>
{/if}

<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>


{if $err}
The following error(s) has occured:
<ul class=err>
{foreach from=$err item=e}
<div class="alert alert-danger rounded"><li> {$e} </li></div>
{/foreach}
</ul>
{/if}

{if !$no_header_footer}
<div class="card mx-3">
	<div class="card-body">
		<form method="post" class="form" name="f_a">

			<div class="row">
				{if $BRANCH_CODE eq 'HQ'}
				<div class="col-md-3">
					<b class="form-label ">Branch</b>
				<select class="form-control" name="branch_id" onchange="change_area_batch_code();">
					{foreach from=$branches key=bid item=b}
						{if !$branches_group.have_group.$bid}
							<option value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if}>{$b.code}</option>
						{/if}
					{/foreach}
					{foreach from=$branches_group.header key=bgid item=bg}
						<optgroup label="{$bg.code}">
							{foreach from=$branches_group.items.$bgid key=bid item=b}
								<option value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if}>{$b.code}</option>
							{/foreach}
						</optgroup>
					{/foreach}
				</select>
				</div>
			{else}
				<input class="form-control" type="hidden" name="branch_id" value="{$smarty.request.branch_id}">
			{/if}
		
				<div class="col-md-3">
					<b class="form-label">View By</b>
					<select class="form-control" name="view_type" onchange="hide_show_checkout(this);">
						<option {if !$smarty.request.view_type or $smarty.request.view_type eq 'order'}selected {/if} value="order" /> Order</option>
						<option {if $smarty.request.view_type eq 'deliver'}selected {/if} value="deliver" /> Deliver</option>
					</select>
				</div>
		
		<div class="col-md-3">
			<b class="form-label">Area</b>
			<span span id="span_area">
			<select class="form-control" name="area_code" onchange="change_batch_code();">
			{if $d_area}
				<option value='all' />----All----</option>
				{foreach from=$d_area key=acode item=a}
						<option value="{$acode}" {if $smarty.request.area_code eq $acode}selected {/if}>{$acode}</option>
				{/foreach}
			{else}
				<option value='' />-- No Data --</option>
		
			{/if}
		
			</select>
			</span>
		</div>
		
		
			{*
			<div class="col-md-3">
				<b class="form-label">Year</b>
			<select class="form-control" name="year">
				{foreach from=$years item=r}
					<option {if $smarty.request.year eq $r.year}selected {/if} value="{$r.year}">{$r.year}</option>
				{/foreach}
			</select>
			</div>
		
		
		<div class="col-md-3">
			<b class="form-label">Month</b>
			<select class="form-control" name="month">
				{foreach from=$months key=m item=month}
					<option {if $smarty.request.month eq $m}selected {/if} value="{$m}">{$month}</option>
				{/foreach}
			</select>
		</div>
		
			*}
		
			
		<div class="col-md-3">
			<b class="form-label">Batch Code</b>
			<span span id="span_code">
			<select class="form-control" name="batch_code" id="code">
				{if $batch}
				<option value='all' />----All----</option>
				{foreach from=$batch key=code item=ba}
					<option {if !$smarty.request.batch_code or $smarty.request.batch_code eq $code}selected {/if} value="{$code}" /> {$code}</option>
				{/foreach}
				{/if}
			</select>
			</span>
		</div>
			</div>
			<span id="span_checkout" style="display:none;">
			<input type="checkbox" class="mt-2"	name="checkout" {if $smarty.request.checkout} checked  {/if} > <b class="text-dark mt-2">Check Out</b>
			</span>
			<p>
			<input  type="hidden" name="submit" value="1" />
			<button class="btn btn-info mt-3" name="show_report">{#SHOW_REPORT#}</button>
			{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
			<button class="btn btn-primary mt-3" name="output_excel">{#OUTPUT_EXCEL#}</button>
			{/if}
			</p>
		</form>
	</div>
</div>


{/if}

{if !$area && !$items}
{if $smarty.request.submit && !$err}<p align=center>-- No data --</p>{/if}
{else}
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$report_title}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>

<div class="table-responsive">
	<table class="report_table small_printing" width="100%">
		<thead class="bg-gray-100">
			<tr class="header">
				{assign var=colspan value=0}
				{foreach from=$row.1 key=no item=rw}
					{if $des.$rw}
						{if $smarty.request.view_type eq 'deliver'}
							{assign var=add_colspan	value=2}
						{else}
							{assign var=add_colspan	value=0}
						{/if}
						
						{assign var=colspan value=$colspan+3+$add_colspan}
						<th width="1%" >No.</th>
						<th width="10%">Description</th>
						<th width="5%">Total {if $smarty.request.view_type eq 'deliver'} Deliver {else} Order {/if}</th>
						{if $smarty.request.view_type eq 'deliver'}
							<th width="5%">Total Order</th>
							<th width="5%">Total Percentage</th>
						{/if}
					{/if}
				{/foreach}
			</tr>
		</thead>
	
		{foreach from=$area key=place item=ar}
		<tr>
			<td colspan="{$colspan}" bgcolor="#FF99FF"><b>{if $place}{$place}{else}Undefined Area{/if}</b></td>
		</tr>
			{foreach from=$row key=rw item=rr}
		<tbody class="fs-08">
			<tr>
				{foreach from=$rr key=num item=r}
					<td bgcolor="#99FFFF" align="center">{$des.$num.num|ifzero:"-"}.</td>
					<td>{if $des.$num.si_rdes}{$des.$num.si_rdes}{else}{$des.$num.si_artno}{/if}</td>
					{assign var=id value=$des.$num.sku_item_id}
					<td class="r">{if $id}{$ar.items.$id.qty|ifzero:"0"|qty_nf}{/if}</td>
					{if $smarty.request.view_type eq 'deliver'}
						<td class="r">{if $id}{$ar.items.$id.order_qty|ifzero:"0"|qty_nf}{/if}</td>
	
						{assign var=total_sku_deliver value=$ar.items.$id.qty}
						{assign var=total_sku_order value=$ar.items.$id.order_qty}
	
						{if $total_sku_deliver == 0 || $total_sku_order == 0}{assign var=total_sku_percent value=0}
							  {else}{assign var=total_sku_percent value=$total_sku_deliver/$total_sku_order*100}
						{/if}
						<td class="r">{$total_sku_percent|number_format:0|ifzero:"":"%"}</td>
					{/if}
	
					
					
				{/foreach}
			</tr>
		</tbody>
			{/foreach}
		{/foreach}
	</table>
</div>
{/if}
{include file=footer.tpl}

<script>
{literal}
if (document.f_a['view_type'].value == 'deliver'){
	$('span_checkout').show();

}
{/literal}
</script>





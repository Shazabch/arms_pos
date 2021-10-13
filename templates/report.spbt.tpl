{*
5/27/2010 5:19:01 PM AM Alex
- create new spbt report

7/9/2010 4:35:36 PM Alex
- Add area filter

10/10/2011 12:31:45 PM alex
- add qty_nf control quantity decimal digit

11/23/2011 2:14:05 PM Andy
- Add can view by qty or amt.

10/15/2015 2:23 PM Justin
- Enhanced to show note if config did not turn on.
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
<div class="alert alert-danger"><li> {$e} </li></div>
{/foreach}
</ul>
{/if}

{if !$no_header_footer}
<div class="card mx-3">
	<div class="card-body">
		<form method="post" class="form" name="f_a">

		<div class="row">
			{if $BRANCH_CODE eq 'HQ'}
			<div class="col">
				<b class="form-label">Branch</b>
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
			
			<div class="col">
				<b class="form-label">View By</b>
				<select class="form-control" name="view_type" onchange="hide_show_checkout(this);">
					<option {if !$smarty.request.view_type or $smarty.request.view_type eq 'order'}selected {/if} value="order" /> Order</option>
					<option {if $smarty.request.view_type eq 'deliver'}selected {/if} value="deliver" /> Deliver</option>
				</select>
			</div>
				
			<div class="col">
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
			<div class="col">
				<b class="form-label">Year</b>
				<select class="form-control" name="year">
					{foreach from=$years item=r}
						<option {if $smarty.request.year eq $r.year}selected {/if} value="{$r.year}">{$r.year}</option>
					{/foreach}
				</select>
			</div>
			
			
				<div class="col">
					<b class="form-label">Month</b>
				<select class="form-control" name="month">
					{foreach from=$months key=m item=month}
						<option {if $smarty.request.month eq $m}selected {/if} value="{$m}">{$month}</option>
					{/foreach}
				</select>
				</div>
			
				*}
			
				<div class="col">
					<b class="form-label">Batch Code</b>
				<span span id="span_code">
				<select class="form-control" name="batch_code" id="code">
					{foreach from=$batch key=code item=ba}
						<option {if !$smarty.request.batch_code or $smarty.request.batch_code eq $code}selected {/if} value="{$code}" /> {$code}</option>
					{/foreach}
				</select>
				</span>
				</div>
				
				<div class="col">
					<b class="form-label">View By</b>
					<select class="form-control" name="view_by">
						<option value="qty" {if $smarty.request.view_by eq 'qty'}selected {/if}>Qty</option>
						<option value="amt" {if $smarty.request.view_by eq 'amt'}selected {/if}>Amt</option>
					</select>
				</div>
		</div>
			
			<span id="span_checkout" style="display:none;" class="mt-2">
			<div class="form-inline mt-2 form-label">
				<input type="checkbox"	name="checkout" {if $smarty.request.checkout} checked  {/if} >&nbsp; <b >Check Out</b>
			</div>
			</span>
		
			<p>
			<input type="hidden" name="submit" value="1" />
			<button class="btn btn-info mt-2" name="show_report">{#SHOW_REPORT#}</button>
			{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
			<button class="btn btn-primary mt-2" name="output_excel">{#OUTPUT_EXCEL#}</button>
			{/if}
			</p>
			
			{if !$config.sales_order_require_batch_code}
				<p>
					<b>* Sales Order with empty batch code will not be showed on this report.</b>
				</p>
			{/if}
		</form>
	</div>
</div>

{/if}

{if !$area && !$items}
{if $smarty.request.submit && !$err}<p align=center>-- No data --</p>{/if}
{else}
<h2>{$report_title}</h2>
{assign var=vtype value=$smarty.request.view_type}
{section name=separate start=1 loop=$parts+1 step=1}
{assign var=spart value=$smarty.section.separate.index}

{assign var=view_by value='qty'}
{if $smarty.request.view_by eq 'amt'}{assign var=view_by value='amt'}{/if}

<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table class=" report_table small_printing report_table table mb-0 text-md-nowrap  table-hover">
				<tr class="header">
					<th width="300">Debtor</th>
					<th width="100">Code</th>
					{foreach from=$items.$spart key=i item=it}
						<th width="50" valign="top">{$it.si_artno}</th>
					{/foreach}
					{if $spart == $parts}<th width="100">Total {if $view_by eq 'amt'}Amount{else}Quantity{/if}</th> {/if}
				</tr>
			
				{foreach from=$area key=a item=ar} <!-- Loop for each Area -->
					{assign var=area_order value=0}
					{assign var=area_total value=0}
					<tr bgcolor="#99FFFF">
						<td  colspan={if $spart != $parts}{$col.$spart+2}{else}{$col.$spart+3}{/if}><b>{if $a}{$a}{else}Undefined Area{/if}</b></td>
					</tr>
					
					<!-- Debtor Total -->
					{foreach from=$ar key=debtor_id item=debtor}	<!-- Loop for each debtor in this area -->
						<tr>
							<td nowrap>{if $debtor.d_des}{$debtor.d_des}{else}-{/if}</td>
							<td nowrap>{if $debtor.d_code}{$debtor.d_code}{else}-{/if}</td>
			
							{foreach from=$items.$spart key=i item=it}
								<td class="r">
									{if $view_by eq 'amt'}
										{$debtor.items.$i.amt|number_format:2|ifzero:""}
									{else}
										{$debtor.items.$i.qty|qty_nf|ifzero:""}
									{/if}
								</td>
							{/foreach}
			
							{if $spart == $parts}
								<td class="r">
									{if $view_by eq 'amt'}
										{$total.$a.$vtype.$debtor_id.total_amt|number_format:2|ifzero:""}
									{else}
										{$total.$a.$vtype.$debtor_id.total_qty|qty_nf|ifzero:""}
									{/if}
								</td>
							{/if}
						</tr>
						
						<!-- Calculate Area Total -->
						{if $view_by eq 'amt'}
							{assign var=area_total value=$area_total+$total.$a.$vtype.$debtor_id.total_amt}
							{assign var=area_order value=$area_order+$total.$a.order.$debtor_id.total_amt}
						{else}
							  {assign var=area_total value=$area_total+$total.$a.$vtype.$debtor_id.total_qty}
							{assign var=area_order value=$area_order+$total.$a.order.$debtor_id.total_qty}
						{/if}
					{/foreach}
					
					<!-- Area Total -->
					<tr bgcolor="#FFCCFF">
						<td colspan=2><b>{$a} {if $smarty.request.view_type == 'order'}Ordered{else}Delivered{/if}</b></td>
			
						{foreach from=$items.$spart key=i item=it}
							{assign var=total_area_sku value=0}
							{foreach from=$ar key=debtor_id item=debtor}
								{if $view_by eq 'amt'}
									{assign var=total_area_sku value=$total_area_sku+$debtor.items.$i.amt}
								{else}
									{assign var=total_area_sku value=$total_area_sku+$debtor.items.$i.qty}
								{/if}
								 
							{/foreach}
							<td class="r">
								{if $view_by eq 'amt'}
									{$total_area_sku|number_format:2|ifzero:""}
								{else}
									{$total_area_sku|qty_nf|ifzero:""}
								{/if}
							</td>
						{/foreach}
				
						{if $spart == $parts}
							<td class="r">
								{if $view_by eq 'amt'}
									{$area_total|number_format:2|ifzero:""}
								{else}
									{$area_total|qty_nf|ifzero:""}
								{/if}
							</td>
						{/if}
					</tr>
						
					<!-- Area Ordered -->
					{if $smarty.request.view_type == 'deliver'}
						<tr bgcolor="#FFCCFF">
							<td colspan=2><b>{$a} Ordered</b></td>
			
							{foreach from=$items.$spart key=i item=it}
								{assign var=total_area_sku value=0}
					
								{foreach from=$ar key=debtor_id item=debtor}
									{if $view_by eq 'amt'}
										{assign var=total_area_sku value=$total_area_sku+$debtor.items.$i.order_amt}
									{else}
											{assign var=total_area_sku value=$total_area_sku+$debtor.items.$i.order_qty}
										{/if}
								{/foreach}
					
								   <td class="r">
								   {if $view_by eq 'amt'}
										{$total_area_sku|number_format:2|ifzero:""}
								   {else}
										{$total_area_sku|qty_nf|ifzero:""}
								   {/if}
								</td>
							{/foreach}
				
							{if $spart == $parts}
								<td class="r">
									{if $view_by eq 'amt'}
										{$area_order|number_format:2|ifzero:""}
									{else}
										{$area_order|qty_nf|ifzero:""}
									{/if}
								</td>
							{/if}
			
						</tr>
						
						<!-- Percentage -->
						<tr bgcolor="#FFCCFF">
							<td colspan=2><b>{$a} Percentage</b></td>
							{assign var=total_percent_each value=0}
			
							{foreach from=$items.$spart key=i item=it}
								{assign var=total_area_sku_order value=0}
								{assign var=total_area_sku_deliver value=0}
								{assign var=percent_each value=0}
			
								{foreach from=$ar key=debtor_id item=debtor}
									  {assign var=total_area_sku_order value=$total_area_sku_order+$debtor.items.$i.order_qty}
									   {assign var=total_area_sku_deliver value=$total_area_sku_deliver+$debtor.items.$i.qty}
								  {/foreach}
			
								   {if $total_area_sku_order == 0 || $total_area_sku_deliver == 0}{assign var=percent_each value=0}
								   {else}{assign var=percent_each value=$total_area_sku_deliver/$total_area_sku_order*100}
								   {/if}
								   <td class="r">{$percent_each|number_format:0|ifzero:"":"%"}</td>
							{/foreach}
			
							   {if $area_total == 0 || $area_order == 0}
								{assign var=total_percent_each value=0}
							  {else}
								{assign var=total_percent_each value=$area_total/$area_order*100}
							{/if}
							
							{if $spart == $parts}
								<td class="r">{$total_percent_each|number_format:0|ifzero:"":"%"}</td>
							{/if}
						</tr>
					{/if}
				{/foreach}
				
			{if $smarty.request.area_code eq 'all'}
				<tr class="header">
					<td colspan=2><b>Total {if $smarty.request.view_type == 'order'}Ordered{else}Delivered{/if}</b></td>
					{foreach from=$items.$spart key=i item=it}
						{assign var=total_sku value=0}
						{foreach from=$area key=a item=ar}
							{foreach from=$ar key=debtor_id item=debtor}
								{if $view_by eq 'amt'}
									{assign var=total_sku value=$total_sku+$debtor.items.$i.amt}
								{else}
									{assign var=total_sku value=$total_sku+$debtor.items.$i.qty}
								{/if}
							{/foreach}
						   {/foreach}
						
						<td class="r">
							{if $view_by eq 'amt'}
								{$total_sku|number_format:2|ifzero:""}
							{else}
								{$total_sku|qty_nf|ifzero:""}
							{/if}
						</td>
						{assign var=total_all_d value=$total_all_d+$total_sku}
					{/foreach}
			
					{if $spart == $parts}
						<td class="r">
							{if $view_by eq 'amt'}
								{$total_all_d|number_format:2|ifzero:""}
							{else}
								{$total_all_d|qty_nf|ifzero:""}
							{/if}
						</td>
					{/if}
				</tr>
			
				{if $smarty.request.view_type == 'deliver'}
				<tr class="header">
					<td colspan=2><b>Total Ordered</b></td>
					{foreach from=$items.$spart key=i item=it}
						{assign var=total_sku value=0}
						{foreach from=$area key=a item=ar}
							{foreach from=$ar key=debtor_id item=debtor}
								{if $view_by eq 'amt'}
									{assign var=total_sku value=$total_sku+$debtor.items.$i.order_amt}
								{else}
									{assign var=total_sku value=$total_sku+$debtor.items.$i.order_qty}
								{/if}
							{/foreach}
						   {/foreach}
			
						<td class="r">
							{if $view_by eq 'amt'}
								{$total_sku|number_format:2|ifzero:""}
							{else}
								{$total_sku|qty_nf|ifzero:""}
							{/if}
						</td>
						{assign var=total_all_order value=$total_all_order+$total_sku}
					{/foreach}
					{if $spart == $parts}
						<td class="r">
							{if $view_by eq 'amt'}
								{$total_all_order|number_format:2|ifzero:""}
							{else}
								{$total_all_order|qty_nf|ifzero:""}
							{/if}
						</td>
					{/if}
				</tr>
			
				<tr class="header">
					<td colspan=2><b>Total Percentage</b></td>
					{foreach from=$items.$spart key=i item=it}
						{assign var=total_sku_deliver value=0}
						{assign var=total_sku_order value=0}
						{assign var=total_sku_percent value=0}
						{foreach from=$area key=a item=ar}
							{foreach from=$ar key=debtor_id item=debtor}
								{assign var=total_sku_deliver value=$total_sku_deliver+$debtor.items.$i.qty}
								{assign var=total_sku_order value=$total_sku_order+$debtor.items.$i.order_qty}
							{/foreach}
						   {/foreach}
			
						{if $total_sku_deliver == 0 || $total_sku_order == 0}{assign var=total_sku_percent value=0}
							  {else}{assign var=total_sku_percent value=$total_sku_deliver/$total_sku_order*100}
						{/if}
						<td class="r">{$total_sku_percent|number_format:0|ifzero:"":"%"}</td>
			
						{if $total_all_d == 0 || $total_all_order == 0}{assign var=total_all_percent value=0}
							  {else}{assign var=total_all_percent value=$total_all_d/$total_all_order*100}
						{/if}
					{/foreach}
					{if $spart == $parts} <td class="r">{$total_all_percent|number_format:0|ifzero:"":"%"}</td> {/if}
				</tr>
				{/if}
			{/if}
			
				
			</table>
		</div>
	</div>
</div>
<br>
{/section}
{/if}
{include file=footer.tpl}

<script>
{literal}
if (document.f_a['view_type'].value == 'deliver'){
	$('span_checkout').show();
}
{/literal}
</script>



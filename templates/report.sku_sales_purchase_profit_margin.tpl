{*
1/26/2021 10:16 AM William
- Change "Adj Out" to (-Adj Out).
*}
{include file=header.tpl}
<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

{if !$no_header_footer}
{literal}
<style>
.negative{
	color:red;
}
</style>
<script>
var MULTI_BRANCH_SALES = {
	initialize: function(){
		this.f = document.f_a;
	},
	submit_report: function(t){
		this.f['export_excel'].value = 0;
		
		if(t == 'excel'){
			this.f['export_excel'].value = 1;
		}
		
		toggle_select_all_opt(this.f['sku_code_list[]'], true);
		this.f.submit();
	},
	check_branch_by_group: function(is_select){
		var bgid = $('sel_brn_grp').value;
		
		if(bgid){	// got select branch group
			$$('#div_branch_list input.inp_branch_group-'+bgid).each(function(ele){
				ele.checked = is_select;
			});
		}else{	// all
			$$('#div_branch_list input.inp_branch').each(function(ele){
				ele.checked = is_select;
			});
		}
	}
}
</script>
{/literal}
{/if}

<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>


{if $err}
	<div class="alert alert-primary mx-3 rounded">
		<ul style="color:red;">
			{foreach from=$err item=e}
				<li><b>{$e}</b></li>
			{/foreach}
		</ul>
	</div>
{/if}

<div class="card mx-3">
	<div class="card-body">
		<form name="f_a" onsubmit="return check_form();" class="form" method="post">
			<input type="hidden" name="show_report" value="1" />
			<input type="hidden" name="export_excel" />
			
			{if !$no_header_footer}
				
					<div class="row">
						<div class="col-md-4">
							<b class="form-label">Year</b>
						<select class="form-control" name="year">
							{foreach from=$years item=y}
								<option value="{$y.year}" {if $smarty.request.year eq $y.year}selected {/if}>{$y.year}</option>
							{/foreach}
						</select>
						</div>
					
						<div class="col-md-4">
							<b class="form-label">Month</b>
						<select class="form-control" name="month">
							{foreach from=$months key=m item=months}
								<option value="{$m}" {if $smarty.request.month eq $m}selected {/if}>{$months}</option>
							{/foreach}
						</select>
						</div>
					</div>
				
					{if $BRANCH_CODE eq 'HQ'}
					<div>
						<div class="row">
							<div class="col-md-4">
								<b class="form-label">Select Branch By:</b>
						<select class="form-control" id="sel_brn_grp" >
							<option value="">-- All --</option>
							{foreach from=$branch_group.header key=bgid item=bg}
								<option value="{$bgid}" >{$bg.code} - {$bg.description}</option>
							{/foreach}
						</select>
							</div>

						<div class="col-md-4">
							<input class="btn btn-success mt-4" type="button"  value="Select " onclick="MULTI_BRANCH_SALES.check_branch_by_group(true);" />
						<input class="btn btn-danger mt-4" type="button"  value="De-select" onclick="MULTI_BRANCH_SALES.check_branch_by_group(false);" /><br /><br />
						
						</div>
						</div>
						<div id="div_branch_list" style="padding: 10px; width:100%;height:200px;border:1px solid #ddd;overflow:auto;">
							<table>
							{foreach from=$branches key=bid item=b}
								{assign var=bgid value=$branch_group.have_group.$bid.branch_group_id}
								<tr>
									<td>
										<input class="inp_branch {if $bgid}inp_branch_group-{$bgid}{/if}" type="checkbox" name="branch_id_list[]" value="{$bid}" {if (is_array($smarty.request.branch_id_list) and in_array($bid,$smarty.request.branch_id_list))}checked {/if} id="inp_branch-{$bid}" />
										<label for="inp_branch-{$bid}">{$b.code} - {$b.description}</label>
									</td>
								</tr>
							{/foreach}
							</table>
						</div>
					</div>
					{/if}
				</p>
				
				<p>{include file="category_autocomplete.tpl" all=true}</p>
				<p>{include file="sku_items_autocomplete_multiple_add2.tpl"}</p>
				
				<p>
					<div class="row">
						<div class="col-md-4">
							<span>
								<b class="form-label">Status:</b>
								<select class="form-control" name="status">
									<option value="all">All</option>
									<option value="1" {if $smarty.request.status eq '1' or !isset($smarty.request.status)}selected{/if}>Active</option>
									<option value="0" {if $smarty.request.status eq '0'}selected{/if}>Inactive</option>
								</select>
							</span>
						</div>
						
						<div class="col-md-4">
							<span>
								<b class="form-label">SKU Type: </b>
								<select class="form-control" name="sku_type">
									<option value="">-- All --</option>
									{foreach from=$sku_types item=r}
										<option value="{$r.code}" {if $smarty.request.sku_type eq $r.code}selected{/if}>{$r.description}</option>
									{/foreach}
								</select>
							</span>
						</div>
						
						<div class="col-md-4">
							<span>
								<div class="form-label form-inline mt-4">
									<input type="checkbox" name="group_by_sku" value="1" {if $smarty.request.group_by_sku}checked {/if} /> <b>&nbsp;Group by SKU</b>
								</div>
							</span>
						</div>
					</div>
				</p>
				
				<p>
					<button class="btn btn-primary mt-2" onClick="MULTI_BRANCH_SALES.submit_report();">{#SHOW_REPORT#}</button>
					{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
					<button class="btn btn-info mt-2" onClick="MULTI_BRANCH_SALES.submit_report('excel');">{#OUTPUT_EXCEL#}</button>
					{/if}
				</p>
		
			{/if}
		</form>
		
		
		<div class="alert alert-primary mt-2" style="max-width: 500px;">
			<ul>
				<li> GRN AVG(Cost) = GRN Cost / GRN Qty</li>
				<li> Total SKU Costing(Qty) = Opening Stock Qty + GRN Qty + Adj In + (-Adj Out) + Stock Take Adj</li>
				<li> Total SKU Costing(Cost) = Opening Stock Cost + GRN Cost</li>
				<li> AVG SKU Cost = Total SKU Costing(Cost) / Total SKU Qty</li>
				<li> Total Sales(Qty) = DO Qty + POS Qty</li>
				<li> Total Sales(AVG Cost) = Total Sales(Qty) * AVG SKU Cost</li>
				<li> Total Sales(Sales) = DO Sales Amt + POS Sales Amt</li>
				<li> Gross Profit  = Total Sales(Sales) - (AVG SKU Cost * Total Sales(Qty))</li>
				<li> GP% = Gross Profit / Total Sales(Sales)</li>
			</ul>
		</div>
	</div>
</div>

{if !$table}
	{if $smarty.request.a eq 'show_report' && !$err}<p>-- No Data --</p>{/if}
{else}
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">{$report_header}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>

	<div class="card mx-3">
		<div class="card-body">
			<div class="table-responsive">
				<table class="report_table table mb-0 text-md-nowrap  table-hover" width="100%" id="report_tbl">
					<thead class="bg-gray-100">
						<tr class="header">
							<th rowspan="2">ARMS Code</th>
							<th rowspan="2">Mcode</th>
							<th rowspan="2">ArtNo</th>
							<th rowspan="2">{$config.link_code_name}</th>
							<th rowspan="2">Description</th>
							<th colspan="2">Opening Stock</th>
							<th colspan="3">GRN</th>
							<th>ADJ In</th>
							<th>ADJ Out</th>
							<th>Stock Take Adj</th>
							<th colspan="2">Total SKU Costing</th>
							<th rowspan="2">Avg SKU Cost</th>
							<th colspan="2" colspan="2">DO</th>
							<th colspan="2" colspan="2">POS</th>
							<th colspan="3">Total Sales</th>
							<th rowspan="2">Gross Profit</th>
							<th rowspan="2">GP%</th>
						</tr>
						<tr class="header">
							<th>Qty</th>
							<th>Cost</th>
							<th>Qty</th>
							<th>Cost</th>
							<th>AVG Cost</th>
							<th>Qty</th>
							<th>Qty</th>
							<th>Qty</th>
							<th>Qty</th>
							<th>Cost</th>
							<th>Qty</th>
							<th>Sales Amt</th>
							<th>Qty</th>
							<th>Sales Amt</th>
							<th>Qty</th>
							<th>AVG Cost</th>
							<th>Sales</th>
						</tr>
					</thead>
					
					<tbody class="fs-08">
					{foreach from=$table item=r name=t}
					<tr class="r">
						<td align="left">{$r.sku_item_code}</td>
						<td align="left">{$r.mcode}</td>
						<td align="left">{$r.artno}</td>
						<td align="left">{$r.link_code}</td>
						<td align="left">{$r.description}</td>
						<td align="right" class="{if $r.opening_qty < 0}negative{/if}">{$r.opening_qty}</td>
						<td align="right" class="{if $r.opening_cost < 0}negative{/if}">{$r.opening_cost|number_format:$config.global_cost_decimal_points|ifzero}</td>
						<td align="right" class="{if $r.grn_qty < 0}negative{/if}">{$r.grn_qty}</td>
						<td align="right" class="{if $r.grn_cost < 0}negative{/if}">{$r.grn_cost|number_format:$config.global_cost_decimal_points|ifzero}</td>
						<td align="right" class="{if $r.grn_avg_cost < 0}negative{/if}">{$r.grn_avg_cost|number_format:$config.global_cost_decimal_points|ifzero}</td>
						<td align="right" class="{if $r.adj_in_qty < 0}negative{/if}">{$r.adj_in_qty}</td>
						<td align="right" class="{if $r.adj_out_qty < 0}negative{/if}">{$r.adj_out_qty}</td>
						<td align="right" class="{if $r.stock_take_adj_qty < 0}negative{/if}">{$r.stock_take_adj_qty}</td>
						<td align="right" class="{if $r.total_sku_qty < 0}negative{/if}">{$r.total_sku_qty}</td>
						<td align="right" class="{if $r.total_sku_cost < 0}negative{/if}">{$r.total_sku_cost|number_format:$config.global_cost_decimal_points|ifzero}</td>
						<td align="right" class="{if $r.avg_sku_cost < 0}negative{/if}">{$r.avg_sku_cost|number_format:$config.global_cost_decimal_points|ifzero}</td>
						<td align="right" class="{if $r.do_qty < 0}negative{/if}">{$r.do_qty}</td>
						<td align="right" class="{if $r.do_sales_amt < 0}negative{/if}">{$r.do_sales_amt|number_format:$config.global_cost_decimal_points|ifzero}</td>
						<td align="right" class="{if $r.pos_qty < 0}negative{/if}">{$r.pos_qty}</td>
						<td align="right" class="{if $r.pos_sales_amt < 0}negative{/if}">{$r.pos_sales_amt|number_format:$config.global_cost_decimal_points|ifzero}</td>
						<td align="right" class="{if $r.total_sales_qty < 0}negative{/if}">{$r.total_sales_qty}</td>
						<td align="right" class="{if $r.total_sales_avg_cost < 0}negative{/if}">{$r.total_sales_avg_cost|number_format:$config.global_cost_decimal_points|ifzero}</td>
						<td align="right" class="{if $r.total_sales < 0}negative{/if}">{$r.total_sales|number_format:$config.global_cost_decimal_points|ifzero}</td>
						<td align="right" class="{if $r.gp < 0}negative{/if}">{$r.gp|number_format:2}</td>
						<td align="right" class="{if $r.gp_percent < 0}negative{/if}">{$r.gp_percent|default:0|number_format:2}%</td>
					</tr>
					{/foreach}
					</tbody>
					
					<tr class="header">      
						<th colspan="5" class="r">Total</th>
						<th align="right" class="{if $total.opening_qty < 0}negative{/if}">{$total.opening_qty}</th>
						<th align="right" class="{if $total.opening_cost < 0}negative{/if}">{$total.opening_cost|number_format:$config.global_cost_decimal_points|ifzero}</th>
						<th align="right" class="{if $total.grn_qty < 0}negative{/if}">{$total.grn_qty}</th>
						<th align="right" class="{if $total.grn_cost < 0}negative{/if}">{$total.grn_cost|number_format:$config.global_cost_decimal_points|ifzero}</th>
						<th align="right" class="{if $total.grn_avg_cost < 0}negative{/if}">{$total.grn_avg_cost|number_format:$config.global_cost_decimal_points|ifzero}</th>
						<th align="right" class="{if $total.adj_in_qty < 0}negative{/if}">{$total.adj_in_qty}</th>
						<th align="right" class="{if $total.adj_out_qty < 0}negative{/if}">{$total.adj_out_qty}</th>
						<th align="right" class="{if $total.stock_take_adj_qty < 0}negative{/if}">{$total.stock_take_adj_qty}</th>
						<th align="right" class="{if $total.total_sku_qty < 0}negative{/if}">{$total.total_sku_qty}</th>
						<th align="right" class="{if $total.total_sku_cost < 0}negative{/if}">{$total.total_sku_cost|number_format:$config.global_cost_decimal_points|ifzero}</th>
						<th align="right" class="{if $total.avg_sku_cost < 0}negative{/if}">{$total.avg_sku_cost|number_format:$config.global_cost_decimal_points|ifzero}</th>
						<th align="right" class="{if $total.do_qty < 0}negative{/if}">{$total.do_qty}</th>
						<th align="right" class="{if $total.do_sales_amt < 0}negative{/if}">{$total.do_sales_amt|number_format:$config.global_cost_decimal_points|ifzero}</th>
						<th align="right" class="{if $total.pos_qty < 0}negative{/if}">{$total.pos_qty}</th>
						<th align="right" class="{if $total.pos_sales_amt < 0}negative{/if}">{$total.pos_sales_amt|number_format:$config.global_cost_decimal_points|ifzero}</th>
						<th align="right" class="{if $total.total_sales_qty < 0}negative{/if}">{$total.total_sales_qty}</th>
						<th align="right" class="{if $total.total_sales_avg_cost < 0}negative{/if}">{$total.total_sales_avg_cost|number_format:$config.global_cost_decimal_points|ifzero}</th>
						<th align="right" class="{if $total.total_sales < 0}negative{/if}">{$total.total_sales|number_format:$config.global_cost_decimal_points|ifzero}</th>
						<th align="right" class="{if $total.gp < 0}negative{/if}">{$total.gp|number_format:2}</th>
						<th align="right" class="{if $total.gp_percent < 0}negative{/if}">{$total.gp_percent|default:0|number_format:2}%</th>
					</tr>
				</table>
			</div>
		</div>
	</div>
{/if}

{if !$no_header_footer}
{literal}
<script type="text/javascript">
	MULTI_BRANCH_SALES.initialize();
</script>
{/literal}
{/if}
{include file=footer.tpl}

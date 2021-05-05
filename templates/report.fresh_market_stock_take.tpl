{*
2/10/2011 3:22:37 PM Andy
- Add calculate GRA on fresh market cost.
- Add back the SKU write-off cost.
- Add can preview fresh market stock take report.

9/29/2011 3:35:43 PM Justin
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs round up to base on config but not fixed by 2.

8/1/2014 4:54 PM Justin
- Enhanced to have POS qty, Expected Loss Qty & Cost.

5/28/2015 11:51 AM Andy
- Enhanced to show "Cost per RM".

11/13/2015 10:40 AM Andy
- Removed "Expected Loss".
- Fixed cost of goods sold calculation do not add the sku write-off cost.
- Add popup information for cost of goods sold.

4/25/2017 2:07 PM Khausalya
- Enhanced changes from RM to use config setting. 

7/7/2017 3:31 PM Justin
- Bug fixed on php error.

1/25/2019 2:46 PM Andy
- Fixed Fresh Market cost need to deduct DO cost.
*}

{include file='header.tpl'}

{if !$no_header_footer}
<style>
{literal}
.tbody_data tr:nth-child(even){
	background-color:#eeeeee;
}
.positive{
	font-weight: bold;
	color:green;
}
.negative{
    font-weight: bold;
	color:red;
}
.cost_per_rm{
	background-color: #fcf;
}
{/literal}
</style>

<script>
var phpself = '{$smarty.server.PHP_SELF}';

{literal}

function branch_changed(){
	var bid = document.f_a['branch_id'].value;
	var stock_take_type = getRadioValue(document.f_a['stock_take_type']);
	
	$('span_st_date').update(_loading_);
	document.f_a['refresh'].disabled = true;
	new Ajax.Request(phpself, {
		parameters:{
			'a': 'ajax_reload_date',
			'branch_id': bid,
			stock_take_type: stock_take_type
		},
		onComplete: function(m){
		    eval("var json = "+m.responseText);
		    
		    $('span_st_date').update(json['st_date']);
		    $('span_pre_st_date').update(json['pre_st_date']);
            document.f_a['refresh'].disabled = false;
		}
	});
}

function check_form(){
    var stock_take_type = getRadioValue(document.f_a['stock_take_type']);
    
    var sel_name = 'date';
    if(stock_take_type==1){
        
	}else if(stock_take_type==2){
        sel_name = 'pre_date';
	}
	
	if(!document.f_a[sel_name])   return false;   // in-case the dropdown is still loading, prevent it to submit

	if(document.f_a[sel_name].value==''){
		alert('Please select stock take date.');
		return false;
	}
		
	return true;
}

function change_stock_take_type(clicked_sel){
	// check current clicked element name
	var t = clicked_sel.name=='pre_date' ? 2 : 1;
	
	// get the stock take radio list
	var chx = document.f_a['stock_take_type'];
	
	// loop and checked
	for(var i=0; i<chx.length; i++){
		if(chx[i].value==t){
			chx[i].checked = true;
			return;
		}
	}
}

{/literal}
</script>
{/if}


<h1>{$PAGE_TITLE}</h1>

{if $err}
	The following error(s) has occured:
	<ul class="err">
		{foreach from=$err item=e}
			<li> {$e}</li>
		{/foreach}
	</ul>
{/if}

{if !$no_header_footer}
	<form method="post" name="f_a" class="form" onSubmit="return check_form();">
	    <input type="hidden" name="load_report" value="1" />
	    
	    {if $can_select_branch}
		    <b>Branch</b>
		    <select name="branch_id" onChange="branch_changed();">
		        {foreach from=$branches item=r}
		            <option value="{$r.id}" {if $smarty.request.branch_id eq $r.id or (!$smarty.request.branch_id and $r.code eq BRANCH_CODE)}selected {/if}>
						{$r.code} - {$r.description}
					</option>
		        {/foreach}
		    </select>&nbsp;&nbsp;&nbsp;&nbsp;
	    {/if}
		<b>SKU Type</b>
		<select name="sku_type">
		    <option value="">-- All --</option>
		    {foreach from=$sku_type item=r}
		        <option value="{$r.code}" {if $smarty.request.sku_type eq $r.code}selected {/if}>{$r.code}</option>
		    {/foreach}
		</select>&nbsp;&nbsp;&nbsp;&nbsp;<br />
		
		<p>
		<input type="radio" name="stock_take_type" value="1" {if $smarty.request.stock_take_type eq 1 || !$smarty.request.load_report}checked {/if} />
		<b>Use Real Stock Take</b>
	    <span id="span_st_date">
	        {include file='report.fresh_market_stock_take.date_sel.tpl' date_list=$date on_sel=1}
		</span>&nbsp;&nbsp;&nbsp;&nbsp;
		
		<input type="radio" name="stock_take_type" value="2" {if $smarty.request.stock_take_type eq 2}checked {/if} />
		<b>Use Pre Stock Take</b>
		<span id="span_pre_st_date">
		    {include file='report.fresh_market_stock_take.date_sel.tpl' date_list=$pre_date sel_name='pre_date'}
		</span>&nbsp;&nbsp;&nbsp;&nbsp;
		
		</p>
		<input type="submit" name="refresh" value="Refresh" />
		<br />
		<ul>
		    <li>The report is use to compare with last stock take.</li>
		</ul>

		<b>Calculation Method</b><br />
		Opening = Opening Stock Take Cost<br />
		In = GRN Cost<br />
		Out = GRA Cost + DO Cost<br />
		Closing = Closing Stock Take Cost<br />
		Cost of Goods Sold = Opening + In - Out - Closing<br />
	</form>
{/if}

{if !$data and !$data2}
	{if $smarty.request.load_report}<p>No Data</p>{/if}
{else}
	<h1>{$report_title}</h1>
	{if $data}
		{assign var=total_last_sc_qty value=0}
		{assign var=total_last_sc_cost value=0}
		{assign var=total_grn_qty value=0}
		{assign var=total_grn_cost value=0}
		{assign var=total_adj_qty value=0}
		{assign var=total_adj_cost value=0}
		{assign var=total_gra_qty value=0}
		{assign var=total_gra_cost value=0}
		{assign var=total_sc_qty value=0}
		{assign var=total_sc_cost value=0}
		{assign var=total_cogs_cost value=0}
		{assign var=total_pos_qty value=0}
		{assign var=total_pos_amt value=0}

	    <table width="100%" class="report_table">
	        <tr class="header">
	            <th rowspan="2">ARMS Code</th>
	            <th rowspan="2">Art.No</th>
	            <th rowspan="2">MCode</th>
	            <th rowspan="2">Description</th>
	            <th colspan="3">Last stock take<br />as opening</th>
	            <th colspan="2">GRN</th>
	            <th colspan="1">Adj /<br />(SKU Write-Off)</th>
	            <th colspan="2">GRA</th>
	            <th colspan="2">DO</th>
	            <th colspan="2">Stock Take</th>
	            <th rowspan="2">Cost of<br />goods sold
					<a href="javascript:void(alert('COGS: Openning cost + GRN cost - GRA cost - DO cost - Final Stock Take cost'));">
					    <img src="/ui/icons/information.png" align="absmiddle" border="0" />
					</a>
				</th>
	            <th colspan="2">POS</th>
	            {*<th colspan="2">Expected Loss</th>*}
	            <th rowspan="2">
					Cost per {$config.arms_currency.symbol}
					 <a href="javascript:void(alert('Cost per {$config.arms_currency.symbol} = Cost of goods sold / Total POS Amt'));">
					    <img src="/ui/icons/information.png" align="absmiddle" border="0" />
					</a>
				</th>
	            <th rowspan="2">GP</th>
	            <th rowspan="2">GP%</th>
	        </tr>
	        <tr class="header">
	            <th>Date</th>
	            <th>Qty</th>
	            <th>Total Cost</th>
	            <th>Qty</th>
	            <th>Total Cost</th>
	            <th>Qty
					<a href="javascript:void(alert('By using adjustment, the item total cost will not be change, but:\nWhen you write-off sku, the cost per {$config.arms_currency.symbol} will increase.\nWhen you increase sku qty, the cost per {$config.arms_currency.symbol} will reduce.'));">
					    <img src="/ui/icons/information.png" align="absmiddle" border="0" />
					</a>
				</th>
	            {*<th>Total Cost
					<a href="javascript:void(alert('By using adjustment, the item total cost will not be change, but:\nWhen you write-off sku, the cost per {$config.arms_currency.symbol} will increase.\nWhen you increase sku qty, the cost per {$config.arms_currency.symbol} will reduce.'));">
					    <img src="/ui/icons/information.png" align="absmiddle" border="0" />
					</a>
				</th>*}
				
				{* GRA *}
	            <th>Qty</th>
	            <th>Total Cost
                    <a href="javascript:void(alert('Goods return will reduce the item total cost.'));">
					    <img src="/ui/icons/information.png" align="absmiddle" border="0" />
					</a>
				</th>
				
				{* DO *}
	            <th>Qty</th>
	            <th>Total Cost
					<a href="javascript:void(alert('Delivery Order will reduce the item total cost.'));">
					    <img src="/ui/icons/information.png" align="absmiddle" border="0" />
					</a>
				</th>
				
				{* Stock Take *}
				<th>Qty</th>
	            <th>Total Cost</th>
				
	            <th>Qty</th>
	            <th>Total Amt</th>
	            {*
				<th>Qty</th>
	            <th>Total Cost</th>
				*}
	        </tr>
	        <tbody class="tbody_data">
	        {foreach from=$data item=r}
	            <tr>
	                <td>{$r.sc.sku_item_code}</td>
	                <td>{$r.sc.artno|default:'-'}</td>
	                <td>{$r.sc.mcode|default:'-'}</td>
	                <td>{$r.sc.sku_desc|default:'-'}</td>
	                <!-- Last stock take-->
	                <td>{$r.sc.last_sc_date}</td>
	                <td class="r {if $r.sc.last_sc_qty<0}negative {/if}">{$r.sc.last_sc_qty|qty_nf}</td>
	                {assign var=total_last_sc_qty value=$total_last_sc_qty+$r.sc.last_sc_qty}
	                <td class="r {if $r.sc.last_sc_cost<0}negative {/if}">{$r.sc.last_sc_cost|number_format:2}</td>
	                {assign var=total_last_sc_cost value=$total_last_sc_cost+$r.sc.last_sc_cost}

					<!-- GRN -->
	                <td class="r">{$r.grn.qty|qty_nf|ifzero:'-'}</td>
	                {assign var=total_grn_qty value=$total_grn_qty+$r.grn.qty}
	                <td class="r">{$r.grn.total_cost|number_format:2|ifzero:'-'}</td>
	                {assign var=total_grn_cost value=$total_grn_cost+$r.grn.total_cost}

	                {assign var=opening_cost value=$r.sc.last_sc_cost+$r.grn.total_cost}

					<!-- ADJ -->
	                <td class="r {if $r.adj.qty<0}negative {/if}">{$r.adj.qty|qty_nf|ifzero:'-'}</td>
	                {*<td class="r {if $r.adj.total_cost<0}negative {/if}">
						{if $r.adj.total_cost<0}+{/if}
						{$r.adj.total_cost*-1|number_format:2|ifzero:'-'}
					</td>*}
					
	                {assign var=total_adj_qty value=$total_adj_qty+$r.adj.qty}
                    {assign var=total_adj_cost value=$total_adj_cost+$r.adj.total_cost}

					<!-- GRA -->
					<td class="r">{$r.gra.qty|qty_nf|ifzero:'-'}</td>
	                <td class="r">{$r.gra.total_cost|number_format:2|ifzero:'-'}</td>
	                {assign var=total_gra_qty value=$total_gra_qty+$r.gra.qty}
                    {assign var=total_gra_cost value=$total_gra_cost+$r.gra.total_cost}
					
					<!-- DO -->
					<td class="r">{$r.do.qty|qty_nf|ifzero:'-'}</td>
	                <td class="r">{$r.do.total_cost|number_format:2|ifzero:'-'}</td>
	                {assign var=total_do_qty value=$total_do_qty+$r.do.qty}
                    {assign var=total_do_cost value=$total_do_cost+$r.do.total_cost}
	                
	                <!-- Current stock take -->
	                <td class="r {if $r.sc.sc_qty<0}negative {/if}">{$r.sc.sc_qty|qty_nf}</td>
	                <td class="r {if $r.sc.sc_cost<0}negative {/if}">{$r.sc.sc_cost|number_format:2}</td>
	                {assign var=total_sc_qty value=$total_sc_qty+$r.sc.sc_qty}
                    {assign var=total_sc_cost value=$total_sc_cost+$r.sc.sc_cost}
                    
	                <!-- Cost of goods sales -->
	                {assign var=cogs_cost value=$opening_cost-$r.gra.total_cost-$r.do.total_cost-$r.sc.sc_cost}
	                <td class="r {if $cogs_cost<0}negative {/if}">{$cogs_cost|number_format:2}
					</td>
	                {assign var=total_cogs_cost value=$total_cogs_cost+$cogs_cost}

	                <!-- POS -->
	                <td class="r">{$r.pos.qty|qty_nf}</td>
	                <td class="r">{$r.pos.amt|number_format:2}</td>
	                {assign var=total_pos_amt value=$total_pos_amt+$r.pos.amt}
	                {assign var=total_pos_qty value=$total_pos_qty+$r.pos.qty}

					{*
					<!-- Expected Loss -->
	                {assign var=expected_loss_qty value=$r.sc.sc_qty-$r.sc.last_sc_qty+$r.grn.qty-$r.adj.qty-$r.gra.qty-$r.pos.qty}
					{assign var=tmp_loss_qty value=$r.pos.qty+$expected_loss_qty}
	                {assign var=expected_loss_amt value=$cogs_cost/$tmp_loss_qty*$expected_loss_qty}
	                <td class="r">{$expected_loss_qty|qty_nf}</td>
	                <td class="r {if $expected_loss_amt<0}negative {/if}">{$expected_loss_amt|number_format:2}</td>
	                {assign var=total_loss_qty value=$total_loss_qty+$expected_loss_qty}
	                {assign var=total_loss_amt value=$total_loss_amt+$expected_loss_amt}
					*}
					
					<!-- Cost per RM -->
					{if $cogs_cost && $r.pos.amt}
						{assign var=cost_per_amt value=$cogs_cost/$r.pos.amt}
					{else}
						{assign var=cost_per_amt value=0}
					{/if}
					<td align="right" class="cost_per_rm {if $cost_per_amt>1}negative{/if}">{$cost_per_amt|number_format:2}</td>
					
	                <!-- GP -->
	                {assign var=gp value=$r.pos.amt-$cogs_cost}
	                <td class="r {if $gp<0}negative {/if}">{$gp|number_format:2}</td>
	                {if $gp && $r.pos.amt}
	                    {assign var=gp_per value=$gp/$r.pos.amt*100}
	                {else}
	                    {assign var=gp_per value=0}
	                {/if}
	                <td class="r {if $gp_per<0}negative {/if}">{$gp_per|num_format:2}%</td>
	            </tr>
	        {/foreach}
	        </tbody>
	        <tr class="header">
	        	<td colspan="4" class="r"><b>Total</b></td>
	        	<!-- Last stock take -->
	        	<td>-</td>
	        	<td class="r">{$total_last_sc_qty|qty_nf}</td>
	        	<td class="r">{$total_last_sc_cost|number_format:2}</td>

				<!-- GRN -->
				<td class="r">{$total_grn_qty|qty_nf}</td>
	        	<td class="r">{$total_grn_cost|number_format:2}</td>

	        	<!-- ADJ -->
	        	<td class="r">{$total_adj_qty|qty_nf}</td>
                {*<td class="r">{if $total_adj_cost<0}+{/if} {$total_adj_cost*-1|number_format:2}</td>*}
                
                <!-- GRA -->
                <td class="r">{$total_gra_qty|qty_nf}</td>
                <td class="r">{$total_gra_cost|number_format:2}</td>
				
				<!-- DO -->
                <td class="r">{$total_do_qty|qty_nf}</td>
                <td class="r">{$total_do_cost|number_format:2}</td>
                
	        	<!-- Current Stock take -->
	        	<td class="r">{$total_sc_qty|qty_nf}</td>
                <td class="r">{$total_sc_cost|number_format:2}</td>
                
	        	<!-- Cost of goods sales -->
	        	<td class="r">{$total_cogs_cost|number_format:2}</td>

	        	<!-- POS -->
	        	<td class="r">{$total_pos_qty|qty_nf}</td>
	        	<td class="r">{$total_pos_amt|number_format:2}</td>

				{*
				<!-- Expected Loss -->
				<td class="r">{$total_loss_qty|qty_nf}</td>
				<td class="r">{$total_loss_amt|number_format:2}</td>
				*}
				
				<!-- Cost per amt -->
				<td>&nbsp;</td>
				
	        	<!-- GP -->
	        	{assign var=gp value=$total_pos_amt-$total_cogs_cost}
	        	<td class="r">{$gp|number_format:2}</td>
	        	{if $gp && $total_pos_amt}
	                {assign var=gp_per value=$gp/$total_pos_amt*100}
	            {else}
	                {assign var=gp_per value=0}
	            {/if}
	            <td class="r">{$gp_per|num_format:2}%</td>
	        </tr>
	    </table>
    {/if}
    
    {if $data2}
        {assign var=total_sc_qty value=0}
        <h1>Item without last stock take to compare</h1>
        <table width="100%" class="report_table">
	        <tr class="header">
	            <th>ARMS Code</th>
	            <th>Art.No</th>
	            <th>MCode</th>
	            <th>Description</th>
	            <th>Stock Take Qty</th>
			</tr>
			<tbody class="tbody_data">
				{foreach from=$data2 item=r}
				    <tr>
				        <td>{$r.sc.sku_item_code}</td>
				        <td>{$r.sc.artno|default:'-'}</td>
		                <td>{$r.sc.mcode|default:'-'}</td>
		                <td>{$r.sc.sku_desc|default:'-'}</td>
		                <td class="r">{$r.sc.sc_qty|qty_nf}</td>
		                {assign var=total_sc_qty value=$total_sc_qty+$r.sc.sc_qty}
				    </tr>
				{/foreach}
			</tbody>
			<tr class="header">
	        	<td colspan="4" class="r"><b>Total</b></td>
	        	<td class="r">{$total_sc_qty|qty_nf}</td>
			</tr>
		 </table>
    {/if}
{/if}
{include file='footer.tpl'}

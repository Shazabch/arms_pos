{*
*}

<script>
{literal}
roundup_value = function (type,doc_allow_decimal,ele){
	//alert(ele.value.length);
	if (ele.value.length == 0)	return;
	if (type == 'qty'){
		if (doc_allow_decimal == 1){
			ele.value = float(round(ele.value, global_qty_decimal_points));
		}else{
			mi(ele);
		}
	}else if (type == 'cost'){
		if (doc_allow_decimal == 1){
			ele.value = float(round(ele.value, global_cost_decimal_points));
		}else
			ele.value = float(round(ele.value, 2));
	}
}

form_submit = function(){
	document.tbl.submit();
}
{/literal}
</script>

{include file='header.tpl'}

{config_load file=site.conf}

<h1>{$PAGE_TITLE}</h1>
<img src="/ui/icons/arrow_left.png" align="absmiddle" /><a href="/admin.stock_take.php?a=import_page"> Back to Import/Reset Stock Take</a>
<br /><br />

{if $pc_st_id_list}
	<h5>Found {count var=$pc_st_id_list} record(s) contains parent & child SKU items which did not fully stock take:<span id="span_refreshing"></span></h5>
	{if $pc_file_path && file_exists($pc_file_path)}
		<b><a href="{$pc_file_path}" download>Click here to download</a> for those missing SKU items which did not stock take completely.</b>
		<br /><br />
	{/if}
	<table  border=0 cellpadding=4 cellspacing=1>
		<tr bgcolor="{#TB_COLHEADER#}">
			<th bgcolor="{#TB_CORNER#}" width=40>#</th>
			<th>Arms Code</th>
			<th>Mcode</th>
			<th>Art No</th>
			<th>Description</th>
		</tr>
		{foreach name=f from=$pc_st_id_list item=val}
			<tr onmouseover="this.bgColor='{#TB_ROWHIGHLIGHT#}';" onmouseout="this.bgColor='';">
				<td>{$smarty.foreach.f.iteration}.</td>
				<td>{$val.sku_item_code}</td>
				<td>{$val.mcode}</td>
				<td>{$val.artno|upper}</td>
				<td>{$val.description|upper}</td>
			</tr>
		{/foreach}
	</table>
{/if}

{if $cost_st_id_list}
	{if !$sessioninfo.privilege.SHOW_COST}
		<h5>
			Found {count var=$cost_st_id_list} record(s) having cost price variance, you are required to have privileges below to do adjustments:<br />
			a) SHOW_COST<br />
			b) STOCK_TAKE_EDIT_COST
			<span id="span_refreshing"></span>
		</h5>
	{else}
		<h5>Found {count var=$cost_st_id_list} record(s) having cost price variance, 
			{if !$sessioninfo.privilege.STOCK_TAKE_EDIT_COST}
				you are required to have privilge "STOCK_TAKE_EDIT_COST" to do adjustment.
			{else}
				Please refer to the list below for adjustment.
			{/if}
			<br /><span id="span_refreshing"></span>
		</h5>
		<form name="tbl" action="admin.stock_take.php" method='post'>
			<input type="hidden" name="a" value="save_edit">
			<input type="hidden" name="date" value="{$form.date}" />
			<input type="hidden" name="location" value="{$form.location}" />
			<input type="hidden" name="shelf" value="{$form.shelf}" />
			<input type="hidden" name="branch_id" value="{$form.branch_id}">
			<input type="hidden" name="is_import_page" value="1">
			<table  border="0" cellpadding="4" cellspacing="1">
				<tr bgcolor="{#TB_COLHEADER#}">
					<th bgcolor="{#TB_CORNER#}" width="40">#</th>
					<th>Location</th>
					<th>Shelf</th>
					<th>Username</th>
					<th>Arms Code</th>
					<th>Mcode</th>
					<th>Art No</th>
					<th>Description</th>
					<th>Price Type</th>
					<th>Quantity</th>
					{if $sessioninfo.privilege.SHOW_COST}
						<th>Unit Cost <b>[<a href="javascript:void(show_cost_help());">?</a>]</b></th>
					{/if}
					<th>Reason</th>
				</tr>
				{foreach name=f from=$cost_st_id_list item=val}
					<tr onmouseover="this.bgColor='{#TB_ROWHIGHLIGHT#}';" onmouseout="this.bgColor='';">
						<td>{$smarty.foreach.f.iteration}.</td>
						<td>{$val.location|upper}</td>
						<td>{$val.shelf|upper}</td>
						<td>{$val.u|upper}</td>
						<td>{$val.sku_item_code}</td>
						<td>{$val.mcode}</td>
						<td>{$val.artno|upper}</td>
						<td>{$val.description|upper}</td>
						<td>{$val.trade_discount_code|upper}</td>
						<td align="right">
							<!--input type="text" size=3 name="qtys[{$val.id}]" tabindex='{$smarty.foreach.f.iteration}' value="{$val.qty}" onchange="roundup_value('qty','{$val.doc_allow_decimal}',this); recalc_variance({$val.id}, this.value, {$val.sb_qty|default:0});" style="text-align:right"-->
							<input type="hidden" name="qtys[{$val.id}]" value="{$val.qty}" />
							{$val.qty|qty_nf}
						</td>
						{if $sessioninfo.privilege.SHOW_COST}
							<td align="center">
								<input type="text" size=5 name="cost_prices[{$val.id}]" tabindex='{$smarty.foreach.f.iteration}' value="{$val.cost_price}" onchange="roundup_value('cost','{$val.doc_allow_decimal}',this)" style="text-align:right" {if !$sessioninfo.privilege.STOCK_TAKE_EDIT_COST} disabled {/if} >
							</td>
						{/if}
						<td>{$val.error}</td>
					</tr>
				{/foreach}
			</table>
			{if $sessioninfo.privilege.STOCK_TAKE_EDIT_COST}
				<input type="button" value="Save" onclick="form_submit();">
			{/if}
		</form>
	{/if}
{/if}
{include file='footer.tpl'}
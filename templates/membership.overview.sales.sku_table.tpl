{*
6/6/2017 10.54 AM Zhi Kai
- create table to load sku sales.
*}


<script type="text/javascript">
{literal}
JQ("#table_date_from").datepicker({ dateFormat: 'yy-mm-dd' });
JQ("#table_date_to").datepicker({ dateFormat: 'yy-mm-dd' });
{/literal}
</script>

<h2 align="center" class="form-label">Third Level Category Sales Report</h2>
	<p>
		<input type="hidden" name=a value="" />
		{if $c_type =='gender'}
			<b class="form-label">Gender :</b>
			<select class="form-control" id="table_gender">
				<option value="">--All--</option>
				{foreach from=$gen key=gid item=gn}
					<option value="{$gn}">{$gn}</option>
				{/foreach}
			</select>
		{/if}
		{if $c_type =='race'}
			<b class="form-label">Ethnicity :</b>
			<select class="form-control" id="table_race">
				<option value="">--All--</option>
				{foreach from=$rc key=rid item=r}
					<option value="{$r}">{$r}</option>
				{/foreach}
			</select>
		{/if}
		{if $c_type =='age'}
			<b class="form-label">Age :</b>
			<select class="form-control" id="table_age">
				<option value="">--All--</option>
				{foreach from=$age key=aid item=a}
					<option value="{$a}">{$a}</option>
				{/foreach}
			</select>
		{/if}
		
		<input type='text' style='position:relative;top:-500px;width:1px' />
		<b class="form-label"> Date From :</b>
		<input class="form-control" size="10" type="text" value="{$smarty.request.table_date_from}" name="table_date_from" id="table_date_from" placeholder="yyyy-mm-dd" >
		&nbsp;
		<b class="form-label">To</b> 
		<input class="form-control" size="10" type="text" value="{$smarty.request.table_date_to}" name="table_date_to"  id="table_date_to" placeholder="yyyy-mm-dd">
		&nbsp;&nbsp;
	
	<br />
	<br />
	<b class="form-label">Category :</b>
		<select class="form-control" id="cid">
			<option value="">--All--</option>  
			{foreach from=$cat_list key=cat_id item=c}   
				<option value="{$cat_id}" {if $cid eq $cat_id}selected{/if}>{$c.description}</option>   
			{/foreach}  
			{if $other_cat_list}
				<option value="OtherGroup" class="optionGroup" {if $cid eq 'OtherGroup'}selected{/if}>--Others--</option> 
				{foreach from=$other_cat_list key=oid item=o}
					<option value="{$oid}" class="item" {if $cid eq $oid}selected{/if}>&emsp;&emsp;{$o.description}</option>
				{/foreach}
			
			{/if}
		</select>
		&nbsp;&nbsp;&nbsp;&nbsp;
	<input class="btn btn-primary" type="button" value="Refresh" id="refresh_btn3" onclick="HOME.reload_table_report();">
	</p>	
	<br>	

<div id="myTablebig">	
<div class="table-responsive">
	<table id="myTable" border="1" align="center" style="display:none;text-align:center;" cellpadding="3" cellspacing="0" class="cTable">        
		<tbody> 
		{if $items}
			<tr bgcolor="#e2e3e5">
				<th align="center" >No.</td>
				<th align="center" width="130px">ARMS Code</td>
				<th align="center" width="130px">MCode</th>
				<th align="center" width="130px">Artno</th>
				<th align="center" width="230px">Description</th>
				<th align="center" width="80px">Quantity</th>
				<th align="center" width="120px">Total Amount ({$config.arms_currency.symbol})</th>
			</tr>
			
			{assign var=total_qty value=0}
			{assign var=total_amt value=0}
			{foreach from=$items key=sid item=it name=si}
				{assign var=total_qty value=$total_qty+$it.quantity}
				{assign var=total_amt value=$total_amt+$it.total|round:2}
				<tr>
					<td>{$smarty.foreach.si.iteration}.</td>  
					<td>{$it.sku_item_code}</td>
					<td align="left">{$it.mcode}</td>
					<td align="left">{$it.artno}</td>
					<td align="left">{$it.sku_description}</td>
					<td align="right">{$it.quantity|qty_nf}</td>
					<td align="right">{$it.total|number_format:2}</td>
				</tr>	
			{/foreach}
			<tr class="r">
				<th colspan="5">Total</th>
				<td>{$total_qty|qty_nf}</td>
				<td>{$total_amt|number_format:2}</td>
			</tr>
		{else}
			<div align="center"><b>No Sales Data</b></div>    
		{/if}
		  
		  
		</tbody>
		</table>
</div>
</div>

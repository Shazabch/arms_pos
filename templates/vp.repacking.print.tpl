{*
1/31/2013 11:23 AM Justin
- Enhanced to show unit cost and total cost columns.

8/19/2013 9:31 AM Fithri
- assign document no as filename when print
*}
{if !$skip_header}
	{include file='header.print.tpl'}
	
	<style>
	{* if $config.sales_order_printing_no_item_line *}
	{literal}
	.no_border_bottom td{
		border-bottom:none !important;
	}
	.total_row td, .total_row th{
	    border-top: 1px solid #000;
	}
	.td_btm_got_line td,.td_btm_got_line th{
	    border-bottom:1px solid black !important;
	}
	{/literal}
	{* /if *}
	
	{literal}
	.tr_group_changed td{
		border-top: 1px solid #000;
	}
	{/literal}
	</style>
	<script type="text/javascript">
	var doc_no = '{$form.id}';
	{literal}
	function start_print(){
		document.title = doc_no;
		window.print();
	}
	{/literal}
	</script>

	<body onload="start_print();">
{/if}

<!-- print sheet -->
<div class="printarea">
<table width="100%" cellspacing="0" cellpadding="0" border="0">
<tr class="small">
	<td><img src="{get_logo_url}" height="80" hspace="5" vspace="5"></td>
	<td width="100%">
		<h2>{$from_branch.description}</h2>
		{$from_branch.address|nl2br}<br>
		Tel: {$from_branch.phone_1}{if $from_branch.phone_2} / {$from_branch.phone_2}{/if}
		{if $from_branch.phone_3}
			&nbsp;&nbsp; Fax: {$from_branch.phone_3}
		{/if}
	</td>
	<td nowrap>
		<h2>{$vp_session.description}</h2>
		{$vp_session.address|nl2br}<br />
		Tel: {$vp_session.phone_1}{if $vp_session.phone_2} / {$vp_session.phone_2}{/if}
		{if $vp_session.phone_3}
			&nbsp;&nbsp; Fax: {$vp_session.phone_3}
		{/if}
	</td>
	<td align="right">
	    <table class="xlarge">
			<tr>
				<td colspan=2>
					<div style="background:#000;padding:4px;color:#fff" align=center>
						<b>
							Repacking
						</b>
					</div>
				</td>
			</tr>
			<tr bgcolor="#cccccc" height=22><td nowrap>ID</td><td nowrap>{$form.id}</td></tr>
		    <tr height=22><td nowrap>Date</td><td nowrap>{$form.repacking_date|date_format:$config.dat_format}</td></tr>
			<tr bgcolor="#cccccc" height=22><td nowrap>Printed By</td><td nowrap>{$vp_session.code|default:'&nbsp;'|upper}</td></tr>
			<tr bgcolor="#cccccc" height=22><td colspan=2 align=center>{$page}</td></tr>
	  	</table>
	</td>
</tr>
</table>

<br />

<table border="0" cellspacing="0" cellpadding="4" width="100%" class="tb">

	<tr bgcolor="#cccccc">
		<th width="5">&nbsp;</th>
		<th width="20">Type</th>
		<th nowrap>ARMS Code</th>
		<th nowrap>Article</th>
		<th>MCode</th>
		<th width="90%">SKU Description</th>
		<th nowrap width="40">Cost</th>
		<th nowrap width="40">Qty</th>
		<th nowrap width="40">Total Cost</th>
	</tr>
	{assign var=counter value=0}
	{assign var=last_item value=''}
	
	{foreach from=$items item=r name=fitem}
		{assign var=counter value=$smarty.foreach.fitem.iteration}
		{if $r.type eq "Lose"}
			{assign var=cost value=$r.cost}
		{else}
			{assign var=cost value=$r.calc_cost}
		{/if}
		
		<tr class="{if $PAGE_SIZE ne $counter}no_border_bottom{/if} {if $smarty.section.i.iteration eq $PAGE_SIZE and !$is_lastpage}td_btm_got_line{/if} {if $last_item and $r.group_id ne $last_item.group_id}tr_group_changed{/if}">
			<td>{$start_counter+$counter}</td>
			<td>{$r.type}</td>
			<td>{$r.sku_item_code}</td>
			<td nowrap>{$r.artno|default:'-'}</td>
			<td nowrap>{$r.mcode|default:'-'}</td>
			<td nowrap>{$r.description|default:'-'}</td>
			<td align="right">{$cost|number_format:$config.global_cost_decimal_points	}</td>
			<td align="right">{$r.qty}</td>
			<td align="right">{$r.qty*$cost|number_format:$config.global_cost_decimal_points}</td>
		</tr>
		
		{assign var=last_item value=$r}
		{assign var=ttl_cost value=$ttl_cost+$r.qty*$cost}
	{/foreach}
	
	{assign var=s2 value=$counter}
	{section name=s start=$counter loop=$PAGE_SIZE}
		{assign var=s2 value=$s2+1}
		<tr height=20 class="{if $PAGE_SIZE ne $s2}no_border_bottom{/if} {if $s2 eq $PAGE_SIZE and !$is_lastpage}td_btm_got_line{/if} {if $last_item}tr_group_changed{/if}">
		  	<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		{assign var=last_item value=''}
	{/section}
	<!--tr>
		<td colspan="8" align="right">Total</td>
		<td align="right">{$ttl_cost|number_format:$config.global_cost_decimal_points}</td>
	</tr-->
</table>

</div>

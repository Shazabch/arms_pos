{*
3/4/2019 5:43 PM Justin
- Enhanced to have new pre-printed layout for Hasani.

3/20/2019 11:03 AM Justin
- Enhanced to have remark and adjust some of the width.
*}
{config_load file="site.conf"}
{if !$skip_header}
	{include file='header.print.tpl'}
<style>

{literal}
body{
	#background-image:url('thumb.php?w=810&fit=1&img=templates/hasani/hasani-bg-old.jpg');
	background-image:url('thumb.php?w=900&fit=1&img=templates/hasani/cn.jpg');
	background-repeat: no-repeat;
	background-position: top left;
	width:850px;
	margin-left:10;
	margin-right:0;
}

.nowrap{
	white-space:nowrap;
}
{/literal}
</style>

<script type="text/javascript">
var doc_no = '#{$form.id|string_format:"%05d"}';
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
	<!-- Top: Logo and company address -->
	<div style="height:190px;border:0px solid black;">&nbsp;</div>
	
	<!-- Top: customer, deliver to and invoice info-->
	<table style="margin-left:10px; height:180px; width:850px; border:0px solid black;">
		<tr valign="top">
			<td width="240">
				<!-- Customer -->
				<div style="height:80px;width:350px;">
					<b>{$form.cust_name}</b><br />
					{$form.cust_address}<br />
					{if $form.cust_brn}
						BRN: {$form.cust_brn}
					{/if}
					<br />
				</div>
			</td>
			<td width="210">
				<!-- pre-printed "CREDIT NOTE" -->
			</td>
			<td>
				<!-- Invoice -->
				<div style="margin-top:10px;margin-left:100px;"></div>
				<div style="margin-left:130px;line-height:18px;">
					<span class="nowrap"><!-- CN NO -->{$form.cn_no}</span>
					<br />
					<span class="nowrap"><!-- PAGE -->{$page}</span>
					<br />
					<span class="nowrap"><!-- PAGE -->{$form.cn_date}</span>
					<br />
					<div style="height:20px;"></div>
					<span class="nowrap"><!-- GRA NO --></span>
					<br />
					<div style="height:20px;"></div>
					<span class="nowrap"><!-- SALESMAN --></span>
					<br />
					<span class="nowrap"><!-- AREA --></span>
				</div>
			</td>
		</tr>
	</table>
	
	<!-- MID: ITEMS TABLE -->
	<div style="width:850px;">
		<table width="100%" border="0" style="margin-left:5px;margin-top:22px;font-size:95%;">
			{assign var=counter value=0}
			{foreach from=$items key=item_index item=r name=i}
				<!-- {$counter++} -->
				<tr height="20">
					{* Row No *}
					<td align="center" width="30">
						{if !$page_item_info.$item_index.not_item}
							{$r.item_no+1}.
						{else}
							&nbsp;
						{/if}
					</td>
				
					{* Art No / MCode *}
					<td nowrap width="102">{if $r.artno <> ''}{$r.artno}{else}{$r.mcode|default:'&nbsp;'}{/if}</td>
					
					{* SKU Description *}
					<td ><div class="crop">{$r.description}</div></td>
					
					
					{if !$page_item_info.$item_index.not_item}
						{* Reason *}
						<td nowrap align="center" width="74"><div class="crop">{$r.reason|ucwords|default:"&nbsp;"}</div></td>
						
						{* Inv No *}
						<td nowrap align="center" width="75">{$r.return_inv_no|ucwords|default:"&nbsp;"}</td>
						
						{* Inv Date *}
						<td nowrap align="center" width="80">{$r.return_inv_date|ucwords|default:"&nbsp;"}</td>
						
						{* Price *}
						<td nowrap align="right" width="75">{$r.price/$r.uom_fraction|ucwords|number_format:2}</td>
						
						{* Qty *}
						{assign var=row_qty value=$r.ctn*$r.uom_fraction+$r.pcs}
						<td align="right" width="40">{$row_qty|qty_nf}</td>
						
						{* Inv Amt *}
						<td align="right" width="75">{$r.line_amt|number_format:2}</td>
					{else}
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					{/if}
				</tr>
			{/foreach}
			
			{assign var=s2 value=$counter}
			{section name=s start=$counter loop=$PAGE_SIZE}
				<!-- filler -->
				{assign var=s2 value=$s2+1}
				<tr height="20" class="no_border_bottom">
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
			{/section}
			
			{if $is_last_page}
			    {assign var=cols value=8}
				<tr><td>&nbsp;</td></tr>
				<tr>
					<td colspan="{$cols}">&nbsp;</td>
					<td style="text-align:right; margin-right:150px;line-height:20px;" align="right" class="large"><b>{$form.total_amount|number_format:2}</b>&nbsp;&nbsp;&nbsp;&nbsp;</td>
				</tr>
			{else}	
				<tr>
					<td style="line-height:40px;">&nbsp;</td>
				</tr>
			{/if}
		</table>
	</div>
	
	<!-- REMARK -->
	<div style="padding-left:7px; height:95px; width:325px; font-size:10px;">
		{$form.remark|default:'-'|nl2br}

		<br />
		<b>Adjustment Docs: </b>
		{foreach from=$form.adj_id_list item=adj_id name=fadj}
			{if !$smarty.foreach.fadj.first}, {/if}
			{$branch.report_prefix}{$adj_id|string_format:"%05d"}
		{/foreach}
	</div>
	
	<!-- BOTTOM -->
	<div style="margin-left:455px;margin-top:30px;"><!-- Bill checked by -->{$sessioninfo.u|default:'&nbsp;'|upper}
	</div>
</div>
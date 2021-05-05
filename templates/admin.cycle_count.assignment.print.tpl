{config_load file="site.conf"}
{if !$skip_header}
	{include file='header.print.tpl'}
<style>

{literal}
td div.crop{
  height:auto;
  max-height:2em;
  overflow:hidden;
}
{/literal}
</style>
<body onload="window.print()">
{/if}

<div class="printarea">
	<h3>{$st_branch.description|upper} - {$st_branch.code} {if $st_branch.company_no}({$st_branch.company_no}){/if}</h3>
	<table>
		<tr>
			<td nowrap><b>Document</b>:</td><td width="100" style="border-bottom:1px solid #000;">{$form.doc_no}</td>
			<td nowrap><b>Propose Stock Take Date</b>:</td><td width=80 style="border-bottom:1px solid #000;">{$form.propose_st_date}</td>
			<td nowrap><b>Print Date</b>:</td><td width=80 style="border-bottom:1px solid #000;">{$smarty.now|date_format:"%Y-%m-%d"}</td>
			<td nowrap><b>PIC</b>:</td><td width="100" style="border-bottom:1px solid #000;">{$form.pic_username}</td>
		</tr>
		<tr>
			<td nowrap><b>Page</b>:</td>
			<td style="border-bottom:1px solid #000;">
				{$page_num} / {$totalpage}
			</td>
			<td colspan="6">
				<i>(Item generated at {$form.print_time})</i>
			</td>
		</tr>
	</table>
	
	<table border="0" cellpadding="2" cellspacing="0" width="100%" class="tb">
		<tr>
			<th align="center" bgcolor="{#TB_COLHEADER#}" width="40">No.</th>
			<th align="center" bgcolor="{#TB_COLHEADER#}" width="100">ARMS Code/<br>MCode</th>
			<th align="center" bgcolor="{#TB_COLHEADER#}" width="80">Art No/<br>{$config.link_code_name}</th>
			<th align="center" bgcolor="{#TB_COLHEADER#}">Description</th>
			<th align="center" bgcolor="{#TB_COLHEADER#}" width="50">UOM</th>
			<th align="center" bgcolor="{#TB_COLHEADER#}" width="60">Qty</th>
		</tr>
		
		{foreach from=$items item=r}
			<tr height="35">
				<td align="center">{$r.item_id}.</td>
				<td>{$r.sku_item_code}<br>{$r.mcode}</td>
				<td>{$r.artno|default:'-'}<br>{$r.link_code|default:'-'}</td>
				<td><div class="crop">{$r.description|default:'&nbsp;'}</div></td>
				<td align="center">{$r.packing_uom_code|default:'&nbsp;'}</td>
				<td>&nbsp;</td>
			</tr>
		{/foreach}
	</table>
</div>
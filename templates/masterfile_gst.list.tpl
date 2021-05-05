{*
10/9/2014 4:41 PM Justin
- Enhanced to remove the vendor settings column.

10/16/2014 2:20 PM Justin
- Enhanced not to allow user amend Tax Code "NR".

1/26/2015 4:35 PM Justin
- Enhanced to add new column "Special Code for Vendor".

3/19/2015 10:09 AM Andy
- Enhanced to check user privilege MST_GST_EDIT to allow user to edit Masterfile GST.

7/26/2017 15:20 Qiu Ying
- Enhanced to add second tax code
*}

<div id="udiv" class="stdframe">

<table class="sortable"  id="gst_tbl" border="0" cellpadding="4" cellspacing="1" width="100%">
	<tr>
		<th bgcolor="{#TB_CORNER#}">&nbsp;</th>
		<th bgcolor="{#TB_COLHEADER#}">Tax Code</th>
		<th bgcolor="{#TB_COLHEADER#}">Second Tax Code</th>
		<th bgcolor="{#TB_COLHEADER#}">Description</th>
		<th bgcolor="{#TB_COLHEADER#}">Rate (%)</th>
		<th bgcolor="{#TB_COLHEADER#}">Purchase Price<br />Include GST</th>
		<th bgcolor="{#TB_COLHEADER#}">Type</th>
		<!--th bgcolor="{#TB_COLHEADER#}">Vendor GST Settings</th-->
		<th bgcolor="{#TB_COLHEADER#}">Indicator</th>
		<th bgcolor="{#TB_COLHEADER#}">Special Code<br />for Vendor</th>
	</tr>
	{foreach from=$gst_list key=row item=r}
		{assign var=gst_id value=$r.id}
		<tr onmouseover="this.bgColor='{#TB_ROWHIGHLIGHT#}';" onmouseout="this.bgColor='';">
			<td align="center" nowrap>
				{if $r.code ne "NR" && $r.code ne "TX-FR"}
					{if $sessioninfo.privilege.MST_GST_EDIT}
						<a onclick="MASTERFILE_GST_MODULE.edit('{$gst_id}', 0);"><img src="ui/ed.png" title="Edit" border="0"></a>
						<a onclick="MASTERFILE_GST_MODULE.toggle_activation('{$gst_id}', {if $r.active}0);"><img src="ui/deact.png" title="Deactivate" border="0">{else}1);"><img src="ui/act.png" title="Activate" border="0">{/if}</a>
					{/if}
				{else}
					<img src="ui/info.png" title="This Tax Code is default code and it cannot be de-activate or modify" height="18" />
					&nbsp;
				{/if}
			</td>
			<td><b>{$r.code}</b>{if !$r.active}<br><span class="small">(inactive)</span>{/if}</td>
			<td>{$r.second_tax_code}</td>
			<td>{$r.description}</td>
			<td align="right">{$r.rate}</td>
			<td align="center">{if $r.inc_item_cost}Yes{else}No{/if}</td>
			<td align="center">{$r.type|strtoupper}</td>
			<!--td align="center">{$r.vendor_gst_setting|default:"-"}</td-->
			<td align="center">{$r.indicator_receipt|default:"-"}</td>
			<td align="center">
				{if $r.is_vd_special_code}
					YES
				{else}
					NO
				{/if}
			</td>
		</tr>
	{foreachelse}
		<tr><td colspan="10" align="center">- No Data -</td></tr>
	{/foreach}
</table>
</div>

<script>
	ts_makeSortable($('gst_tbl'));
</script>
{*
8/5/2010 6:07:10 PM Andy
- SKU Group Drop "GRN cutoff date", "report usage", "department" and "allowed user list".

12/16/2011 3:30:54 PM Justin
- Added sort by header feature.

11/15/2012 4:52 PM Andy
- Add an icon link to "Vendor Portal SKU Group Item Date Control" for each sku group. (need config "enable_vendor_portal")

11/27/2012 1:43 PM Andy
- Remove update to div id "udiv". this row is no longer use.

12/12/2012 4:56:00 PM Fithri
- multiple user view/edit
- owner can share sku with other user
- dont allow delete item from sku group if the item got sales

1/25/2013 10:35 AM Justin
- Enhanced to show out share user while vendor portal is enabled.
*}

{config_load file=site.conf}
<div id="udiv" class="stdframe">

<table class="sortable" id="sku_group_tbl" border=0 cellpadding=4 cellspacing=1>
<tr>
	<th bgcolor="{#TB_CORNER#}" width="40" >&nbsp;</th>
	<th bgcolor="{#TB_COLHEADER#}" >Code</th>
	<th bgcolor="{#TB_COLHEADER#}" width="300">Description</th>
	<th bgcolor="{#TB_COLHEADER#}" >Numbers of<br>SKU</th>
</tr>
{foreach from=$sku_group item=r}
<tr onmouseover="this.bgColor='{#TB_ROWHIGHLIGHT#}';" onmouseout="this.bgColor='';">
	<td bgcolor={#TB_ROWHEADER#} nowrap>
		<a href="javascript:editGroup('{$r.sku_group_id}','{$r.branch_id}');"><img src=ui/ed.png title="Edit" border=0></a>
		{if $r.can_edit_share}
		<a href="javascript:deleteGroup('{$r.sku_group_id}','{$r.branch_id}');"><img src=ui/deact.png title="Delete" border=0></a>
		{/if}
		
		{if $config.enable_vendor_portal}
			<a href="masterfile_sku_group.vp_date_control.php?a=open&sku_group_bid={$r.branch_id}&sku_group_id={$r.sku_group_id}" target="_blank"><img src="/ui/icons/calendar_edit.png" title="Vendor Portal SKU Group Item Date Control" border="0" /></a>
			{if $r.can_edit_share}
			<a href="javascript:editShare('{$r.sku_group_id}','{$r.branch_id}');"><img src="/ui/icons/user_add.png" title="Edit share user" border=0 /></a>
			{/if}
		{/if}
	</td>

	<td><b>{$r.code}</b></td>
	<td nowrap>{$r.description}</td>
	<td class="r">{$r.item_count|default:'0'}</td>
</tr>
{/foreach}
</table>
</div>

<script type="text/javascript">
	//parent.window.document.getElementById('udiv').innerHTML = document.getElementById('udiv').innerHTML;
	ts_makeSortable($('sku_group_tbl'));
</script>

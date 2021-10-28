{*
12/16/2011 3:30:54 PM Justin
- Added sort by header feature.

7/11/2014 11:33 AM Justin
- Enhanced to have 4 decimal points for fraction.

10/15/2015 3:54 PM Andy
- Enhanced to not allow user to edit UOM ID 1.

1/7/2019 3:47 PM Andy
- Enhanced to load how many sku using the uom.
- Enhanced to not allow users to edit uom if got sku in-used.

5/7/2019 10:31 AM William
- Enhanced "sku in-used" can redirect to sku listing.
*}

{config_load file=site.conf}
<div id="udiv" class="stdframe">

<div class="table-responsive">
	<table class="sortable" id="uom_tbl" border=0 cellpadding=4 cellspacing=1>
		<thead class="bg-gray-100">
			<tr>
				{if $sessioninfo.privilege.MST_UOM}
				<th  width="40">&nbsp;</th>
				{/if}
				<th >Code</th>
				<th >Description</th>
				<th >Fraction</th>
				<th >SKU in-used</th>
				</tr>
		</thead>
		{section name=i loop=$uom}
		<tbody class="fs-08">
			<tr onmouseover="this.bgColor='{#TB_ROWHIGHLIGHT#}';" onmouseout="this.bgColor='';">
				{if $sessioninfo.privilege.MST_UOM}
				<td bgcolor={#TB_ROWHEADER#} nowrap>
				{if $uom[i].id eq 1}
					<img src="ui/info.png" title="This UOM is default code and it cannot be de-activate or modify" height="18" />
				{else}
					{if $uom[i].used_sku_count eq 0 or $sessioninfo.is_arms_user}
						<a href="javascript:void(ed({$uom[i].id}))"><img src="ui/ed.png" title="Edit" border="0" /></a>
					{else}
						<img src="ui/info.png" title="This UOM cannot be modify due to there are SKU in-used." height="18" />
					{/if}
					
					<a href="javascript:void(act({$uom[i].id},{if $uom[i].active}0))"><img src="ui/deact.png" title="Deactivate" border="0" />{else}1))"><img src="ui/act.png" title="Activate" border="0" />{/if}</a>
				{/if}
				</td>
				{/if}
				<td><b>{$uom[i].code}</b>{if !$uom[i].active}<br><span class="small">(inactive)</span>{/if}</td>
				<td>{$uom[i].description}</td>
				<td align="right">{$uom[i].fraction|number_format:4}</td>
				<td align="right"><a href="/masterfile_sku.php?load=1&uom_id={$uom[i].id}" target=_blank>{$uom[i].used_sku_count|number_format}</a></td>
			</tr>
		</tbody>
		{/section}
		</table>
</div>
</div>

<script>
	parent.window.document.getElementById('udiv').innerHTML = document.getElementById('udiv').innerHTML;
	ts_makeSortable($('uom_tbl'));
</script>

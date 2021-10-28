{*
12/16/2011 3:30:54 PM Justin
- Added sort by header feature.
*}

{config_load file=site.conf}
<div id="udiv" class="stdframe">

<div class="table-responsive">
	<table class="sortable" id="brgroup_tbl" width="100%" class=" table mb-0 text-md-nowrap  table-hover">
<thead class="bg-gray-100">
	<tr>
		{if $sessioninfo.privilege.MST_BRANDGROUP}
		<th  width=40>&nbsp;</th>
		{/if}
		<th >Code</th>
		<th >Description</th>
		<th >Brands</th>
		</tr>
</thead>
		{section name=i loop=$brgroups}
		<tr onmouseover="this.bgColor='{#TB_ROWHIGHLIGHT#}';" onmouseout="this.bgColor='';">
		{if $sessioninfo.privilege.MST_BRANDGROUP}
		<td bgcolor={#TB_ROWHEADER#} nowrap>
		<a href="javascript:void(ed({$brgroups[i].id}))"><img src=ui/ed.png title="Edit" border=0></a>
		<a href="javascript:void(act({$brgroups[i].id},{if $brgroups[i].active}0))"><img src=ui/deact.png title="Deactivate" border=0>{else}1))"><img src=ui/act.png title="Activate" border=0>{/if}</a>
		</td>
		{/if}
		<td><b>{$brgroups[i].code}</b>{if !$brgroups[i].active}<br><span class=small>(inactive)</span>{/if}</td>
		<td nowrap>{$brgroups[i].description}</td>
		<td class=small>{$brgroups[i].brands}</td>
		</tr>
		{/section}
		</table>
</div>
</div>

<script>
	parent.window.document.getElementById('udiv').innerHTML = document.getElementById('udiv').innerHTML;
	ts_makeSortable($('brgroup_tbl'));
</script>

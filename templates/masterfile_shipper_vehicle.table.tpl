{*
5/29/2018 11:50AM HockLee
- new form: transporter vehicle

8/27/2018 4:00PM HockLee
- Added Transporter column.
*}

{config_load file=site.conf}

<h5>{count var=$table} record{if count($table)>1}s{/if} <span id="span_refreshing"></span></h5>

<table class="sortable" id="trnsprt_tbl" border=0 cellpadding=4 cellspacing=1 width=100%>
	<tr>
		<th bgcolor={#TB_CORNER#} width=40>&nbsp;</th>
		<th bgcolor={#TB_COLHEADER#}>Plate No.</th>
		<th bgcolor={#TB_COLHEADER#}>Transporter</th>
		<th bgcolor={#TB_COLHEADER#}>Type</th>
		<th bgcolor={#TB_COLHEADER#}>Brand/Manufacturer</th>
		<th bgcolor={#TB_COLHEADER#}>Route</th>
		<th bgcolor={#TB_COLHEADER#}>Max Load (kg)</th>
		<th bgcolor={#TB_COLHEADER#}>Status</th>
		<th bgcolor={#TB_COLHEADER#}>Added</th>
		<th bgcolor={#TB_COLHEADER#}>Last Update</th>
	</tr>
	{foreach from=$table item=r}
	<tr onmouseover="this.bgColor='{#TB_ROWHIGHLIGHT#}';" onmouseout="this.bgColor='';">
		<td bgcolor={#TB_ROWHEADER#} nowrap>
			<a href="javascript:void(open_vehicle('{$r.id}'))"><img src=ui/ed.png title="Edit" border=0></a>
			<a href="javascript:void(act('{$r.id}','{if $r.active}0{else}1{/if}'))">
		    {if $r.active}
				<img src=ui/deact.png title="Deactivate" border=0>
			{else}
				<img src=ui/act.png title="Activate" border=0>
			{/if}
			</a>
		</td>
		<td>
		<b>{$r.plate_no}</b>{if !$r.active}<br><span class=small>(inactive)</span>{/if}
		</td>
		<td>{$r.transporter}</td>
		<td>{$r.type}</td>
		<td>{$r.brand}</td>
		<td>{$r.route}</td>
		<td>{$r.max_load}</td>
		<td>{$r.status}</td>
		<td>{$r.added}</td>
		<td>{$r.last_update}</td>
	</tr>
	{/foreach}
</table>

<script>
	ts_makeSortable($('trnsprt_tbl'));
</script>
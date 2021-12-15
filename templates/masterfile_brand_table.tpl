{*
2/5/2008 12:42:08 PM yinsee
- counter link to sku  

10/27/2011 10:40:00 AM Andy
- Fix link to masterfile sku should auto load SKU.

12/16/2011 3:30:54 PM Justin
- Added sort by header feature.

5/2/2012 9:30:04 AM Andy
- Add show loading process icon when reload vendor list.

10/23/2013 9:47 AM Fithri
- records is now displayed in pages, 20 per page
- re-arrange default filters behaviours
*}
{config_load file=site.conf}
{if $smarty.request.a ne 'ajax_reload_table'}
<div id="udiv" class="stdframe">
{/if}

<span id="span_loading_brand_list" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading...<br /><br /></span>

{if $pagination}
<div class="form-inline form-label mx-3">
	Page:&nbsp;&nbsp;
<select class="form-control" name="pg" id="pg" onchange="reload_table(true)">
	{$pagination}
</select>&nbsp;of &nbsp;<b>{$total_page}</b>
&nbsp;&nbsp;

{/if}
<span style="color:#CE0000;"><b>(Total of {$bcount} records)</b></span></div>
<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive" class=" table mb-0 text-md-nowrap  table-hover">
			<table class="sortable" id="brand_tbl" width="100%">
				<thead class="bg-gray-100">
					<tr>
						{if $sessioninfo.privilege.MST_BRAND}
						<th width="40">&nbsp;</th>
						{/if}
						<th >&nbsp;</th>
						<th >Code</th>
						<th >Description</th>
						<th >SKU in-used</th>
						</tr>
				</thead>
				{section name=i loop=$brands}
				<tbody class="fs-08">
					<tr onmouseover="this.bgColor='{#TB_ROWHIGHLIGHT#}';" onmouseout="this.bgColor='';">
						{if $sessioninfo.privilege.MST_BRAND}
						<td bgcolor={#TB_ROWHEADER#} nowrap>
						<a href="javascript:void(ed({$brands[i].id}))"><img src=ui/ed.png title="Edit" border=0></a>
						<a href="javascript:void(act({$brands[i].id},{if $brands[i].active}0))"><img src=ui/deact.png title="Deactivate" border=0>{else}1))"><img src=ui/act.png title="Activate" border=0>{/if}</a>
						</td>
						{/if}
						<td><a href="javascript:void(showtd({$brands[i].id}, '{$brands[i].description|escape:"javascript"}'))"><img src=ui/table.png title="open Trade Discount Table" border=0></a></td>
						<td><b>{$brands[i].code}</b>{if !$brands[i].active}<br><span class=small>(inactive)</span>{/if}</td>
						<td>{$brands[i].description}</td>
						{assign var=bid value=`$brands[i].id`}
						<td><a href="/masterfile_sku.php?load=1&brand_id={$brands[i].id}" title="Show SKU" target=_blank>{$brand_count[$bid]}</a></td>
						</tr>
				</tbody>
				{/section}
				</table>
		</div>
	</div>
</div>
{if $smarty.request.a ne 'ajax_reload_table'}
</div>

<script>
	parent.window.document.getElementById('udiv').innerHTML = document.getElementById('udiv').innerHTML;
	ts_makeSortable($('brand_tbl'));
</script>
{/if}

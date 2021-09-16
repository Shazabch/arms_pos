{*
3/13/2013 3:45 PM Justin
- Modified to make the font size larger.
*}

{if !$list}
<p align="left">- No record -</p>
{else}
<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table id="tbl_list" class="report_table table mb-0 text-md-nowrap table-sm table-hover "  width="100%">
				<thead>
					<tr class="text-center">
						<th>No</th>
						<th>Outlet</th>
						<th>Name</th>	
						<th>Position</th>	
						<th>Status</th>	
						<th>Last Login</th>
					</tr>
				</thead>
				
				{section name=i loop=$list}
				<tbody>
					<tr class="text-center">
						<td >{$smarty.section.i.iteration}</td>
						<td>{$list[i].b_code}</td>
						<td>{$list[i].name|default:$list[i].u}</td>
						<td>{$list[i].position|default:"&nbsp;-"}</td>	
						<td {if $list[i].status eq 'Locked'}style="color:red;"{/if}>{$list[i].status}</td>		
						<td>{$list[i].lastlogin}</td>	
					</tr>
				</tbody>
				{/section}
				</table>
		</div>
	</div>
</div>
{/if}
<script>
ts_makeSortable($('tbl_list'));
</script>

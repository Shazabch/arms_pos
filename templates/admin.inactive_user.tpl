{*
3/13/2013 3:45 PM Justin
- Modified to make the font size larger.
*}

{if !$list}
<p align=left>- No record -</p>
{else}
<table id=tbl_list class="tb" cellspacing=0 cellpadding=2 border=0 width=100%>
<tr bgcolor=#e2e3e5 height=24>
	<th width=30>No</th>
	<th width=100>Outlet</th>
	<th width=250>Name</th>	
	<th width=200>Position</th>	
	<th width=80>Status</th>	
	<th width=100>Last Login</th>
</tr>

{section name=i loop=$list}
<tr>
	<td align=center>{$smarty.section.i.iteration}</td>
	<td align=center>{$list[i].b_code}</td>
	<td>{$list[i].name|default:$list[i].u}</td>
	<td>{$list[i].position|default:"&nbsp;-"}</td>	
	<td align=center {if $list[i].status eq 'Locked'}style="color:red;"{/if}>{$list[i].status}</td>		
	<td>{$list[i].lastlogin}</td>	
</tr>
{/section}
</table>
{/if}
<script>
ts_makeSortable($('tbl_list'));
</script>

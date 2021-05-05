{include file='header.tpl'}

<script>
{literal}
function restore_database(bid, id){
	if(!confirm('Are you sure to restore this database?'))  return false;
	
	document.f_a['branch_id'].value = bid;
	document.f_a['id'].value = id;
	document.f_a.submit();
}
{/literal}
</script>

<form name="f_a" method="post" style="display:none;">
	<input type="hidden" name="a" value="restore_database" />
	<input type="hidden" name="branch_id" />
	<input type="hidden" name="id" />
</form>

<h1>{$PAGE_TITLE}</h1>

{if $err}
	The following error(s) has occured:
	<ul class="err">
		{foreach from=$err item=e}
			<li> {$e}</li>
		{/foreach}
	</ul>
{/if}

{if $smarty.request.msg}
	<p style="color:blue;">{$smarty.request.msg}</p>
{/if}

<table width="100%" class="report_table">
	<tr class="header">
	    <th></th>
	    <th>Timestamp</th>
	    <th width="60">User</th>
        <th width="70">Database Date</th>
        <th>Remark</th>
        <th>Error Log</th>
        <th>Restore Error Log</th>
        <th>Data<br />Archived</th>
        <th>Status</th>
	</tr>
	{foreach from=$archive_history item=r}
        <tr align="center" bgcolor="#ffffff">
            <td>
                {if !$r.status and $r.deleted_rows>0}
				<a href="javascript:void(restore_database('{$r.branch_id}', '{$r.id}'));"><img src="/ui/icons/database_lightning.png" align="absmiddle" border="0" title="Restore Database" /></a>
				{/if}
			</td>
            <td>{$r.added}</td>
            <td>{$r.u}</td>
            <td>{$r.date}</td>
            <td align="left">{$r.remark}</td>
            <td align="left">{$r.error_log|nl2br|default:'-'}</td>
            <td align="left">{$r.restore_error_log|nl2br|default:'-'}</td>
            <td class="r">{$r.deleted_rows|number_format}</td>
            <td>{if !$r.status}Archive{elseif $r.status eq 1}Restored{/if}
        </tr>
	{foreachelse}
		<tr bgcolor="#ffffff">
		    <td colspan="5">No Data</td>
		</tr>
    {/foreach}
</table>
{include file='footer.tpl'}

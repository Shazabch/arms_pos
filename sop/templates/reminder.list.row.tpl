<tr id="tr_reminder_row-{$reminder.branch_id}-{$reminder.id}">
	<td><span class="row_no">{$smarty.foreach.fr.iteration}</span>.</td>
	<td width="50">
	    <a href="javascript:void(0);" class="a_open_reminder">
			<img src="/ui/ed.png" title="Edit" border="0" />
		</a>
		<a href="javascript:void(0);" class="a_delete_reminder">
			<img src="/ui/icons/delete.png" title="Delete" border="0" />
		</a>
	</td>
	<td class="c">
	    <input type="checkbox" title="Active/Deactive" {if $reminder.active}checked {/if} id="chx_reminder_active-{$reminder.branch_id}-{$reminder.id}" />
	</td>
	<td>{$reminder.title|default:'-'}</td>
	<td>{$reminder.date_from|default:'-'}</td>
	<td>{$reminder.date_to|default:'-'}</td>
	<td>{$reminder.ref_task|default:'Custom'}</td>
	<td>
		{if $reminder.ref_task}
		    {$reminder.ref_info.task_name|default:'-'}
		{/if}
	</td>
	<td>{$reminder.added|default:'-'}</td>
	<td>{$reminder.last_update|default:'-'}</td>
</tr>

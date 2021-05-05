<tr id="tr_festival_row-{$festival.year}" class="is_year_row">
	<td width="20">
	    {$smarty.foreach.f.iteration}.
	</td>
	<td width="60">
        <a href="?a=open&year={$festival.year}" class="open_festival">
            {if ($festival.status eq 0 and $festival.approved eq 0) or ($festival.status eq 1 and $festival.approved eq 1)}
	        	<img src="/ui/ed.png" title="Edit" border="0" />
			{else}
			    <img src="/ui/view.png" title="View" border="0" />
	        {/if}
		</a>
		

        {if $festival.status eq 0 and $festival.approved eq 0}
			<a href="javascript:void(0);" class="delete_festival">
				<img src="/ui/icons/delete.png" title="Delete" border="0" />
			</a>
		{/if}
	</td>
	<td>{$festival.year|default:'-'}
        {if preg_match('/\d/',$festival.approvals)}
			<div class="small">Approvals: <font color="#0000ff">
				{get_user_list list=$festival.approvals aorder_id=$festival.approval_order_id}
			</font></div>
		{/if}
	</td>
	<td>{$festival.event_count|default:'0'}</td>
	<td>{$festival.added|default:'-'} <span class="small">({$festival.username|default:'-'})</span></td>
	<td>{$festival.last_update|default:'-'}</td>
</tr>

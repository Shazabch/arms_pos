<tr id="tr_festival_date-{$festival_date.id}" class="is_id_row">
	<td width="20"><span class="row_no">{$smarty.foreach.ffd.iteration}</span>.</td>
	<td width="80" nowrap>
	    {if !$approval_screen}
			{if ($festival_sheet.label eq 'approved' or $festival_sheet.label eq 'draft')}
			    <img src="/ui/ed.png" title="Edit" border="0" align="absmiddle" class="open_festival_date clickable" />
			    {if ($festival_sheet.label eq 'draft')}
			    <img src="/ui/icons/delete.png" title="Delete" border="0" align="absmiddle" class="delete_festival_date clickable" />
			    {/if}
		    {/if}
		{/if}
	</td>
	<td class="c">
	    <div class="{if ($festival_sheet.label eq 'approved' or $festival_sheet.label eq 'draft')}colorSelector{/if}" id="div_calendar_color-{$festival_date.id}" default_color="{$festival_date.calendar_color}" style="margin:0;padding:0;">
   			<div title="{$festival_date.calendar_color}" style="background-color:{$festival_date.calendar_color}">&nbsp;</div>
		</div>
	</td>
	<td class="c">
		<input type="checkbox" title="Active/Deactive" {if $festival_date.active}checked {/if} id="chx_festival_date_active-{$festival_date.id}" {if ($festival_sheet.label ne 'draft' and $festival_sheet.label ne 'approved')}disabled {/if} />
	</td>
	<td td_type="title">{$festival_date.title|default:'-'}</td>
	<td>{$festival_date.date_from|default:'-'}</td>
	<td>{$festival_date.date_to|default:'-'}</td>
	<td>{$festival_date.added|default:'-'} <span class="small">({$festival_date.created_by|default:'-'})</span></td>
	<td>{$festival_date.last_update|default:'-'}</td>
</tr>

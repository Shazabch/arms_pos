{*
*}

{foreach from=$date_list item=date_item_id key=date_from}
	<div class="table-responsive">
		<table id="sac_item_{$date_item_id}" class="sac_items" width="100%" {if $header_is_hidden}style="display:none;"{/if}>
			<tbody>
				<tr>
					<td colspan="2">
						<b >Date Start: </b>
						<input size="10" type="text" name="selected_date_from[{$date_item_id}]" id="selected_date_from_{$date_item_id}" value="{$date_from|date_format:'%Y-%m-%d'}" class="selected_date_list form-control">
						<img align="absmiddle" src="ui/calendar.gif" id="ds_{$date_item_id}" style="cursor: pointer;" title="Select Date">
					</td>
					<td align="right" colspan="2">
						<a onclick="SA_COMMISSION_MODULE.toggle_condition_dialog({$date_item_id});">Add Commission Item <img src="ui/icons/money_add.png" title="Add Commission Item" width="15" border="0"></a> &nbsp;&nbsp;&nbsp;
						<a onclick="SA_COMMISSION_MODULE.delete_commission({$date_item_id});">Delete Commission <img src="ui/del.png" title="Delete Commission" width="15" border="0"></a>
					</td>
				</tr>
				{if $err.dtl.$date_item_id}
					<tbody class="fs-08">
						<tr>
							<td colspan="2">
								<div id=err><div class=errmsg><ul>
									{foreach from=$err.dtl.$date_item_id item=e}
										<li> {$e}
									{/foreach}
								</ul></div></div>
							</td>
						</tr>
					</tbody>
				{/if}
				<tr>
					<th bgcolor="#dddddd" width="6%">&nbsp;</th>
					<th bgcolor="#cccccc" width="34%">Condition</th>
					<th bgcolor="#cccccc" width="34%">Additional Filter</th>
					<th bgcolor="#cccccc" width="26%">Commission Method</th>
				</tr>
				{include file="masterfile_sa_commission.open.item_row.tpl"}
				{if count($items.$date_item_id) eq 0}
					<tr id="no_data_{$date_item_id}">
						<td colspan="4" align="center" bgcolor="{#TB_CORNER#}">No Data</td>
					</tr>
				{/if}
				{literal}
				<script type="text/javascript">
					Calendar.setup({
						inputField     :    "selected_date_from_{/literal}{$date_item_id}{literal}",     // id of the input field
						ifFormat       :    "%Y-%m-%d",      // format of the input field
						button         :    "ds_{/literal}{$date_item_id}{literal}",  // trigger for the calendar (button ID)
						align          :    "Bl",           // alignment (defaults to "Bl")
						singleClick    :    true,
						onUpdate	   :	SA_COMMISSION_MODULE.calendar_updated
					});
				</script>
				{/literal}
			</tbody>
		</table>
	</div>
{/foreach}
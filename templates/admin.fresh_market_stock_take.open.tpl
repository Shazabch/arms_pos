{*
9/1/2010 6:02:40 PM Andy
- Add checking to get parent sku only.
- Clone selected branch, date, location and shelf to "Add New Stock".

6/8/2011 10:51:55 AM Andy
- Add artno column at stock take.

05/07/2020 5:34 PM Sheila
- Fixed overlapping button
*}

<form name="f_b" onSubmit="return false;">
<table width="100%">
	<tr>
	    <td><b>Date</b></td>
	    <td>
	        <input name="date" id="inp_stock_take_date" size="12" value="{$smarty.request.date|default:$smarty.now|date_format:'%Y-%m-%d'}" readonly />
			<img align="absmiddle" src="ui/calendar.gif" id="img_stock_take_date" style="cursor: pointer;" title="Select Date" />
	    </td>
	    {if $can_select_branch}
		    <td><b>Branch</b></td>
		    <td>
		        <select name="branch_id">
					{foreach from=$branches item=r}
						<option value="{$r.id}" {if $smarty.request.branch_id eq $r.id}selected {/if}>{$r.code}</option>
					{/foreach}
				</select>
		    </td>
		{else}
			<td colspan="2">
			    <input type="hidden" name="branch_id" value="{$sessioninfo.branch_id}" />
			</td>
	    {/if}
	</tr>
	<tr>
		<td nowrap><b>Location</b></td>
		<td>
			<input type="text" name="location" onchange="this.value=this.value.toUpperCase();" value="{$smarty.request.location}" />
			<img src="/ui/rq.gif" align="absmiddle" />
		</td>
		<td><b>Shelf</b></td>
		<td>
			<input type="text" name="shelf" onchange="this.value=this.value.toUpperCase();" value="{$smarty.request.shelf}" />
			<img src="/ui/rq.gif" align="absmiddle" />
		</td>
	</tr>
</table>
{include file='sku_items_autocomplete.tpl' parent_form='document.f_b' enable_handheld=1 fresh_market_filter='yes' show_qty_input=1 is_parent_only=1 multiple_add=1}
<div style="height:17px;">&nbsp;
	<span id="autocomplete_sku_indicator" style="padding:2px;background:yellow;display:none;">
		<img src="ui/clock.gif" align="absmiddle" /> Loading...
	</span>
	<div style="float:right;">
		<a href="javascript:void(show_possible_item());">
			<img src="ui/icons/application_view_list.png" border="0" align="absmiddle" /> Show all possible SKU.
		</a>
	</div>
</div>

<div style="border:3px inset #ddd;height:300px;overflow-x:hidden;overflow-y:auto;">
	<table width="100%" style="border-collapse:collapse;" border="1">
		<tr bgcolor="#cccccc">
		    <th width="50"></th>
			<th width="100">Arms Code</th>
			<th width="80">Art No.</th>
			<th>Description</th>
			<th width="60">UOM</th>
			<th width="120">Quantity</th>
		</tr>
		<tbody id="tbody_ajax_added_item">
		</tbody>
	</table>
</div>
<p align="center"><input class="btn btn-error" type="button" value="Close" onClick="default_curtain_clicked();" /></p>
</form>

<script>
{literal}
init_calendar();
reset_sku_autocomplete();
{/literal}
</script>

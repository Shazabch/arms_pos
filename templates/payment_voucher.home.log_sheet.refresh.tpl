{*

10/18/2012 2:07:00 PM Fithri
- payment voucher "Print & Re-Print Cheque By Log Sheet" add search log sheet

*}

<th width=100>Log Sheet</th>
<td>	
<select name="log_sheet_no"  onchange="load_ls_banker(this.value);{if $autocomplete}reset_autocomplete_field('{$autocomplete}_log_sheet_no');{/if}">
{foreach key=key item=item from=$ls_list}
<option {if $autocomplete}id="{$autocomplete}_opt_log_sheet_no_{$item.log_sheet_no}"{/if} value={$item.log_sheet_no} {if $selected==$key}selected{/if}>{$item.log_sheet_no}</option>
{/foreach}
</select>

{if $autocomplete}
<br />
<input id="{$autocomplete}_log_sheet_no" name="{$autocomplete}_log_sheet_no" size=10 />
<div id="{$autocomplete}_log_sheet_no_choices" class="autocomplete" style="display:none;height:150px !important;width:100px !important;overflow:auto !important;z-index:100"></div>
{/if}

</td>

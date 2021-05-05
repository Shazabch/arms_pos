{*
11/12/2013 3:20 PM Fithri
- add missing indicator for compulsory field

06/26/2020 Sheila 01:53 PM
- Updated button css.
*}
<form name="f_a" onSubmit="return false;">
<input type="hidden" name="id" value="{$form.id}" />
<b>Group Name:</b> <input type="text" name="group_name" value="{$form.group_name}" size="50" onChange="group_changed=true;" /> <img src="ui/rq.gif" align="absbottom" title="Required Field">
{include file='sku_items_autocomplete.tpl' multiple_add=1}

<table height="300" width="100%">
	<tr>
		<td width="90%">
			<div style="border:1px solid black;height:280px;background:#fcfcfc;">
				<select name="sku_item_id_list[]" id="sel_sku_list" style="width:100%;height:100%;" multiple>
				    {foreach from=$items item=r}
				        <option value="{$r.sku_item_id}">{$r.sku_item_code} - {$r.description}</option>
				    {/foreach}
				</select>
			</div>
		</td>
		<td>
		    <input class="btn btn-error" type="button" value="Remove" style="width:80px;" onClick="remove_item();" id="btn_remove_sku" {if !$items}disabled {/if} /><br /><br />
		    <input class="btn btn-primary" type="button" value="Clear" style="width:80px;" onClick="clear_item();" id="btn_clear_sku" {if !$items}disabled {/if}/>
		</td>
	</tr>
</table>
<div align="center">
	<input class="btn btn-success" type="button" value="Save" onClick="save_group();" id="btn_save_group" />
	<input class="btn btn-error" type="button" value="Close" onClick="close_group_window();" id="btn_close_group" />
</div>
</form>

<script>

{literal}
reset_sku_autocomplete();
{/literal}
</script>

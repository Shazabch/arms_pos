{*
1/27/2011 3:47:28 PM Andy
- Add department checking for SKU monitoring group items.
- Add filter available user list by branch.

1/28/2011 2:00:23 PM Andy
- Reduce the dialog popup size.

11/12/2013 3:20 PM Fithri
- add missing indicator for compulsory field

06/26/2020 1:10 PM Sheila
- Updated button css.
*}
<form name="f_a" onSubmit="return false;">
<input type="hidden" name="id" value="{$form.id}" />
<table width="100%">
	<tr>
	    <td nowrap><b>Group Name</b></td>
		<td width="90%" colspan="3"><input type="text" name="group_name" value="{$form.group_name}" size="50" onChange="this.value=this.value.toUpperCase();group_changed=true;" /> <img src="ui/rq.gif" align="absbottom" title="Required Field"></td>
	</tr>
	<tr>
	    <td nowrap><b>Department</b></td>
		<td>
		    <select name="dept_id" onChange="group_dept_changed();">
			    <option value="">-- Please Select --</option>
			    {foreach from=$depts item=r}
			        <option value="{$r.id}" {if $r.id eq $form.dept_id}selected {/if}>{$r.description}</option>
			    {/foreach}
			</select> <img src="ui/rq.gif" align="absbottom" title="Required Field">
			<input type="hidden" name="old_dept_id" value="{$form.dept_id}" />
		</td>
		<td class="r"><b>Start Monitoring Date</b></td>
		<td>
			<input size="10" type="text" name="start_monitoring_date" value="{$form.start_monitoring_date}" id="inp_start_monitoring_date" />
			<img align="absmiddle" src="ui/calendar.gif" id="img_start_monitoring_date" style="cursor: pointer;" title="Select Date" />
			 <img src="ui/rq.gif" align="absbottom" title="Required Field">
		</td>
	</tr>
	<tr>
	    <td colspan="4">
	    <hr />
	        <table width="100%">
	            <tr>
	                <td>
	                    <b>Text format Import (ARMS Code or MCode) Separate with "," (e.g: 9415007022510, 9555335609998)</b><br />
	        			<textarea name="text_import" style="width:100%;"></textarea>
	                </td>
	                <td width="150" valign="bottom">
	                    <span id="span_text_import_loading" style="padding:2px;background:yellow;display:none;">
							<img src="ui/clock.gif" align="absmiddle" /> Loading...
						</span><br />
	                    <input id="btn_import_sku_by_text" type="button" value="Import" style="width:100%;" onClick="import_sku_by_text();" />
					</td>
	            </tr>
	        </table>
	    </td>
	</tr>
</table>

{include file='sku_items_autocomplete.tpl' multiple_add=1 dept_filter_name='dept_id'}
<div style="height:17px;">&nbsp;
	<span id="autocomplete_sku_indicator" style="padding:2px;background:yellow;display:none;">
		<img src="ui/clock.gif" align="absmiddle" /> Loading...
	</span>
</div>

<table height="160" width="100%">
	<tr>
		<td width="50%">
			<div style="border:1px solid black;height:120px;background:#fcfcfc;">
				<select name="sku_item_id_list[]" id="sel_sku_list" style="width:100%;height:100%;" multiple>
				    {foreach from=$items item=r}
				        <option value="{$r.sku_item_id}">{$r.sku_item_code} - {$r.description}</option>
				    {/foreach}
				</select>
			</div>
		</td>
		<td>
		    <div style="border:1px solid black;height:120px;background:#fcfcfc;overflow-y:auto;" id="div_allowed_users">
		        {include file='masterfile_sku_monitoring_group.open.allowed_users.tpl' allowed_user=$form.allowed_user}
		    </div>
		</td>
	</tr>
	<tr>
	    <td align="center">
	        <input class="btn btn-error" type="button" value="Remove" style="width:80px;" onClick="remove_item();" id="btn_remove_sku" {if !$items}disabled {/if} />
		    <input class="btn btn-primary" type="button" value="Clear" style="width:80px;" onClick="clear_item();" id="btn_clear_sku" {if !$items}disabled {/if}/>
		</td>
		<td>
		    <input type="checkbox" onChange="toggle_all_allowed_user(this)" /> Toggle all
		    <select name="default_bid" onChange="reload_available_user();">
		        <option value="">-- All Branches --</option>
			    {foreach from=$branches key=bid item=b}
			        <option value="{$bid}">{$b.code}</option>
			    {/foreach}
		    </select>
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
Calendar.setup({
        inputField     :    "inp_start_monitoring_date",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "img_start_monitoring_date",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
    });
{/literal}
</script>

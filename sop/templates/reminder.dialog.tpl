<form name="f_reminder" method="post" onSubmit="return false;">
    <input type="hidden" name="branch_id" value="{$form.branch_id}" />
	<input type="hidden" name="id" value="{$form.id}" />
	<input type="hidden" name="a" value="save_reminder" />
	
	<h4>General Informations</h4>
	<div class="stdframe ui-corner-all" style="background:#fff">
	    <table>
		    <tr>
		        <td width="120"><b>Title</b></td>
		        <td>
		            <input type="text" name="title" value="{$form.title}" maxlength="100" size="50" title="Reminder Title" class="required" />
		            <img align="absbottom" title="Required Field" src="/ui/rq.gif" />
		        </td>
		    </tr>
		    <tr>
		        <td><b>Date</b></td>
		        <td>
		            <input name="date_from" size="12" value="{$form.date_from}" readonly title="Date From" class="required" />
		            <img align="absbottom" title="Required Field" src="/ui/rq.gif" />
					&nbsp;&nbsp;&nbsp;<b>to</b>&nbsp;&nbsp;&nbsp;
					<input name="date_to" size="12" value="{$form.date_to}" readonly title="Date To" class="required" />
					<img align="absbottom" title="Required Field" src="/ui/rq.gif" />
		        </td>
		    </tr>
		    <tr>
		        <td nowrap><b>References Task</b></td>
		        <td>
		            <select name="ref_task">
		                <option value="">-- Custom --</option>
		                {foreach from=$ref_task_list key=k item=v}
		                	<option value="{$k}" {if $form.ref_task eq $k}selected {/if}>{$v}</option>
		                {/foreach}
		            </select>
		        </td>
		    </tr>
		    <tr>
		        <td valign="top"><b>Remark</b></td>
		        <td><textarea name="remark" cols="50" rows="4">{$form.remark}</textarea></td>
		    </tr>
		    <tr id="tr_reminder_pick_task" style="{if !$form.ref_task}display:none;{/if}">
		        <td valign="top"><b>Pick Task</b></td>
		        <td>
		            <input type="text" readonly size="50" name="ref_info[task_name]" value="{$form.ref_info.task_name}" />
		            <img align="absbottom" title="Required Field" src="/ui/rq.gif">
		            <img src="/ui/icons/application_edit.png" align="absmiddle" title="Pick Task" class="clickable" id="img_pick_reminder_task" />
		            
		            <!-- hidden info -->
		            <input type="hidden" name="ref_table" value="{$form.ref_table}" />
		            <input type="hidden" name="ref_id" value="{$form.ref_id}" />
		            <span id="span_reminder_all_ref_info">
			            {foreach from=$form.ref_info key=k item=v}
			                {if $k ne 'task_name'}
			                	<input type="hidden" name="ref_info[{$k}]" value="{$v}" />
			                {/if}
			            {/foreach}
		            </span>
		        </td>
		    </tr>
		</table>
	</div>
</form>

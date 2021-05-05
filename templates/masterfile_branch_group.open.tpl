{*
11/12/2013 3:20 PM Fithri
- add missing indicator for compulsory field
*}

<script>

{literal}

{/literal}
</script>
<form id="form_branch_open" name="form_branch_open" onSubmit="return false;">
<input type="hidden" name="a" value="save" />
<input type="hidden" name="id" value="{$form.id}">
<table>
    <tr>
	    <td><b>Code</b></td>
		<td colspan="3"><input type="text" name="code" value="{$form.code}" style="width:100px;" onChange="ucwords(this);" maxlength="10"> <img src="ui/rq.gif" align="absbottom" title="Required Field"></td>
	</tr>
	<tr>
	    <td><b>Description</b></td>
		<td colspan="3"><input type="text" name="description" value="{$form.description}" style="width:400px;" onChange="ucwords(this);" maxlength="100"> <img src="ui/rq.gif" align="absbottom" title="Required Field"></td>
	</tr>
</table>
<table>
	<tr>
	    <th><br />Select Branch</th>
	    <td></td>
	    <th><br />Selected Branch <img src="ui/rq.gif" align="absbottom" title="Required Field"></th>
	</tr>
	<tr>
	    <td>
	        <select id="select_branches" multiple style="height:300px;width:240px;">
	            {foreach from=$branches key=bid item=r}
	                <option value="{$bid}">{$r.code} - {$r.description}</option>
	            {/foreach}
	        </select>
	    </td>
	    <td>
	        <button onClick="insert_branch();">>></button><br /><br />
	        <button onClick="remove_branch();"><<</button>
	    </td>
	    <td>
	        <select multiple name="branches_list[]" id="select_branches_list" style="height:300px;width:240px;">
	            {foreach from=$branches_list key=bid item=r}
	                <option value="{$bid}">{$r.code} - {$r.description}</option>
	            {/foreach}
	        </select>
	    </td>
	</tr>
</table>
</form>
<p style="text-align:right;">
	<input type="button" value="Save" id="btn_save" onClick="save();" />
	<input type="button" value="Close" onClick="default_curtain_clicked();" />
</p>

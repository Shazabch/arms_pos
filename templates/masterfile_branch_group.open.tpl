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
	    <td><b class="form-label">Code<span class="text-danger" title="Required Field"> *</span></b></td>
		<td colspan="3"><input class="form-control" type="text" name="code" value="{$form.code}" style="width:100px;" onChange="ucwords(this);" maxlength="10"> </td>
	</tr>
	<tr>
	    <td><b class="form-label">Description<span class="text-danger" title="Required Field"> *</span></b></td>
		<td colspan="3"><input class="form-control" type="text" name="description" value="{$form.description}" style="width:400px;" onChange="ucwords(this);" maxlength="100"> </td>
	</tr>
</table>
<table>
	<tr>
	    <th class="form-label"><br />Select Branch</th>
	    <td></td>
	    <th class="form-label"><br />Selected Branch <span class="text-danger" title="Required Field"> *</span></th>
	</tr>
	<tr>
	    <td>
	        <select class="form-control ml-3" id="select_branches" multiple style="height:300px;width:240px;">
	            {foreach from=$branches key=bid item=r}
	                <option value="{$bid}">{$r.code} - {$r.description}</option>
	            {/foreach}
	        </select>
	    </td>
	    <td>
	        <button class="btn btn-sm btn-dark" onClick="insert_branch();">>></button><br /><br />
	        <button class="btn btn-sm btn-dark" onClick="remove_branch();"><<</button>
	    </td>
	    <td>
	        <select class="form-control" multiple name="branches_list[]" id="select_branches_list" style="height:300px;width:240px;">
	            {foreach from=$branches_list key=bid item=r}
	                <option value="{$bid}">{$r.code} - {$r.description}</option>
	            {/foreach}
	        </select>
	    </td>
	</tr>
</table>
</form>
<p style="text-align:right;">
	<input type="button" class="btn btn-primary" value="Save" id="btn_save" onClick="save();" />
	<input type="button" class="btn btn-danger" value="Close" onClick="default_curtain_clicked();" />
</p>

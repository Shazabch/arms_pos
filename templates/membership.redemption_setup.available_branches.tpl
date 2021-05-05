<form name="f_ab" onSubmit="return false;" id="f_ab">
{*<input type="hidden" name="a" value="ajax_save_available_branches" />*}
<input type="hidden" name="id" value="{$form.id}" />
<input type="hidden" name="branch_id" value="{$form.branch_id}" />

<table width="100%">
	<tr style="background:#ffc;">
	    <th width="30"><input type="checkbox" onClick="toggle_ab(this);"></th>
	    <th width="80">Code</th>
	    <th>Description</th>
	</tr>
	<tbody style="overflow:auto;background:#fff;{if count($branches)>15}height:350px;{/if}">
	{foreach from=$branches key=bid item=b}
	    <tr onmouseover="this.bgColor='#ffffcc';" onmouseout="this.bgColor='';">
	        <td><input type="checkbox" name="available_branches[{$bid}]" class="inp_ab" value="{$bid}" id="inp_ab_selected,{$bid}" onClick="return check_ab_can_change(this);" /></td>
	        <td>{$b.code}</td>
	        <td>{$b.description}</td>
	    </tr>
	{/foreach}
	</tbody>
</table>
<p align="center">
	<input type="button" value="OK" name="save" onClick="save_available_branches();" />
	<input type="button" value="Cancel" name="close" onClick="default_curtain_clicked();" />
</p>
</form>

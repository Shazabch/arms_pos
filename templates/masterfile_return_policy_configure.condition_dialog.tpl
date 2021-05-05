<!-- SKU -->
{include file='sku_items_autocomplete.tpl' parent_form='document.f_condition'}
<br />
<table width="100%">
	<tr>
		<td colspan="2" align="center"><b>OR</b></td>
	</tr>

	<!-- Category -->
	<tr>
		<td width="11%"><b>Category <br />(Max lv10)</b></td>
		<td>
			<input id="inp_search_cat_autocomplete" name="search_cat_autocomplete" style="font-size:14px;width:500px;" /> <input type="button" value="Add" id="btn_add_cat" />
			<div id="div_search_cat_autocomplete_choices" class="autocomplete" style="display:none;height:150px !important;width:500px !important;overflow:auto !important;z-index:100"></div>
		</td>
	</tr>
	
	<tr>
		<td colspan="2" align="center"><b>OR</b></td>
	</tr>
	
	<tr>
		<td><b>SKU Group</b></td>
		<td>
			<select name="sg_id">
				<option value="">--</option>
				{foreach from=$sg_list key=r item=sg name=sglist}
					{if $smarty.foreach.sglist.first || $sg.branch_id ne $prv_branch_id}
						{if $prv_branch_id}</optgroup>{/if}
						<optgroup label="{$sg.branch_code}">
					{/if}
					<option value="{$sg.sku_group_id},{$sg.branch_id}">{$sg.code}</option>
					{if $smarty.foreach.sglist.last}
						</optgroup>
					{/if}
					{assign var=prv_branch_id value=$sg.branch_id}
				{/foreach}
			</select>
			<input type="button" value="Add" id="btn_add_sg" />
		</td>
	</tr>
</table>
<span id="span_sac_autocomplete_loading" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading...</span>
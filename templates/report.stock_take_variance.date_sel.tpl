<select name="{$sel_name|default:'date'}" onClick="change_stock_take_type(this);" onchange="load_location();">
    <option value="">-- Please Select --</option>
    {foreach from=$date_list item=d name=f}
		<option value="{$d}" {if ($smarty.request.stock_take_type eq 1 and $smarty.request.date eq $d) or ($smarty.request.stock_take_type eq 2 and $smarty.request.pre_date eq $d)}selected {/if}>{$d}</option>
    {/foreach}
</select>

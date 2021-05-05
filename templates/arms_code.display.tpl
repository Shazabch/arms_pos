{if $arms_code}
<tr>
	<td><b>Arms Code</b></td>
	<td>
	    <select name=arms_code>
	    {foreach from=$arms_code item=val}
	    <option value="{$val.sku_item_code}">{$val.sku_item_code}</option>
      {/foreach}
	    </select>
	</td>
</tr>
{/if}
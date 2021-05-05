{if $bran_date}
<select name=branch_date>
{foreach from=$bran_date item=val}
<option value="{$val}">{$val}</option>
{/foreach}
</select>
{/if}

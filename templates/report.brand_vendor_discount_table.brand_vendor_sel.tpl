<ul class="ul_sel">
	{if $commission_tbl}
		<li><input type="checkbox" onChange="toggle_chx(this, 'vb');" /> <b>All</b></li>
	{/if}
	{foreach from=$commission_tbl item=r}
	    <li>
	        <img src="ui/pixel.gif" width="20" />
			<input type="checkbox" name="commission_tbl_id[]" value="{$r.id}" {if is_array($smarty.request.commission_tbl_id)}{if in_array($r.id, $smarty.request.commission_tbl_id)}checked {/if}{/if} onChange="VB_DISCOUNT_TABLE.vendor_chx_changed();" />
			{$r.description}
	    </li>
	{/foreach}
</ul>
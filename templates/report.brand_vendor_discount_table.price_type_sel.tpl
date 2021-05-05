<ul class="ul_sel">
	{if $price_type}
		<li><input type="checkbox" onChange="toggle_chx(this, 'price_type');" /> <b>All</b></li>
	{/if}
  	{foreach from=$price_type item=r}
      <li>
          <img src="ui/pixel.gif" width="20" />
	<input type="checkbox" name="price_type[]" value="{$r.code}" {if is_array($smarty.request.price_type)}{if in_array($r.code, $smarty.request.price_type)}checked {/if}{/if} onChange="VB_DISCOUNT_TABLE.price_type_chx_changed();" />
	{$r.code}
      </li>
  {/foreach}
</ul>
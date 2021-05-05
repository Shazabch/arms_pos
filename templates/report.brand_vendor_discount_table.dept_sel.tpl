<ul class="ul_sel">
	{if $depts}
		<li><input type="checkbox" onChange="toggle_chx(this, 'dept');" /> <b>All</b></li>
	{/if}
  	{foreach from=$depts item=r}
      <li>
          <img src="ui/pixel.gif" width="20" />
	<input type="checkbox" name="dept_id[]" value="{$r.id}" {if is_array($smarty.request.dept_id)}{if in_array($r.id, $smarty.request.dept_id)}checked {/if}{/if} />
	{$r.description}
      </li>
  {/foreach}
</ul>
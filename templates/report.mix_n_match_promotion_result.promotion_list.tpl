{if !$promo_list}
	- No Promotion Found -
{else}
	Total {count var=$promo_list} Promotion Found
	
	<input type="checkbox" onChange="toggle_branch_promo_id(this);" /> Toggle All
	<ul style="list-style:none;">
		{foreach from=$promo_list item=r}
			<li>
				{assign var=v value=$r.id*1000+$r.branch_id}
				<input type="checkbox" name="branch_promo_id[]" value="{$v}" {if is_array($smarty.request.branch_promo_id) and in_array($v, $smarty.request.branch_promo_id)}checked {/if} />
				<a href="promotion.php?a=view&branch_id={$r.branch_id}&id={$r.id}" target="_blank">
				ID#{$r.id}
				</a> 
				created at {$r.bcode} (Title: {$r.title})
				{if $r.active eq 1 and $r.status eq 1 and $r.approved eq 1}
					(Approved)
				{elseif $r.active eq 1 and $r.status eq 1 and !$r.approved}
					(Waiting for Approval)
				{elseif $r.status eq 4 or $r.status eq 5}
					(Cancelled/Terminated)
				{elseif $r.status eq 2}
					(Rejected)
				{elseif $r.active eq 1 and $r.status eq 0}
					(Saved)
				{/if}
			</li>
		{/foreach}
	</ul>
{/if}
{assign var=count value=0}
{foreach from=$po_items item=item name=fitem}
	{include file=po.new.po_row.tpl}

	{if $item.last_po}
		{assign var=l_item value=$item.last_po}
		{include file=po.new.last_po_row.tpl pi_id=$item.id}		
	{/if}
	
	{if $config.po_set_max_items}
		{assign var=count value=$count+1}
	{/if}
{/foreach}

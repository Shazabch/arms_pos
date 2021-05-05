{foreach from=$table key=k item=r}
	"{$smarty.request.from}","{$smarty.request.to}","{$r.info.sku_item_code}","{$r.cost}","{$r.selling_price}","{$r.open_bal}","{$r.open_bal_val}","{$r.closing_bal}","{$r.closing_bal_val}"<br />
{/foreach}

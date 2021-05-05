{if $form.do_type eq 'open'}
	{include file="hasani/do_checkout.print_invoice.prefix_layout.tpl"}
{else}
	{include file="hasani/do_checkout.print.tpl"}
{/if}
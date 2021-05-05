{include file=header.tpl}

<h1>{$PAGE_TITLE}</h1>
{if $create_msg}
<ul>
{foreach from=$create_msg item=e}
<li> {$e}
{/foreach}
</ul>
{/if}

<h3>Select an option below to continue</h3>

<ul>
<li>
<a href=/vendor_po_request.home.php>Create another PO</a>
<li>
<a href=/vendor_po_request.home.php?a=logout>Logout</a>
</ul>
{include file=footer.tpl}

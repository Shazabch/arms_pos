{include file=header.tpl}


{if $err}
The following error(s) has occured:

<ul class=err>
{foreach from=$err item=e}
<li> {$e}
{/foreach}
{/if}



{include file=footer.tpl}

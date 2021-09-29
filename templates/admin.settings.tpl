{include file=header.tpl}


{if $err}
<div class="card mx-3">
    <div class="card-body">
        <span class="text-danger">The following error(s) has occured:</span>

<ul class="err text-muted">
{foreach from=$err item=e}
<li> {$e}
{/foreach}
{/if}
    </div>
</div>



{include file=footer.tpl}

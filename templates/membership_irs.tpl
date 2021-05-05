<div id="errdiv" class=errmsg>
{if $errmsg}<ul>{foreach item=m from=$errmsg}<li>{$m}{/foreach}</ul>{/if}
</div>

<script>
parent.window.document.getElementById('errdiv').innerHTML = document.getElementById('errdiv').innerHTML;
</script>
